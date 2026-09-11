<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Satuan;

/**
 * Price List & Level - REDESAIN TOTAL dari versi sebelumnya. Sebelumnya
 * satu produk punya toggle Flat/Range TERPISAH per golongan (state ganda
 * yang membingungkan - "flat" sebenarnya cuma kasus khusus dari "range
 * dengan satu baris"), disimpan lewat SATU submit besar yang menghapus
 * SEMUA baris harga produk itu lalu insert ulang semuanya (update_bulk) -
 * riwayat bug: key array bentrok bikin baris hilang diam-diam, dan field
 * id_satuan/qty_berlaku/id_satuan_berlaku TERNYATA TIDAK PERNAH dipakai
 * sama sekali di HargaService::getHargaEfektif (murni dekoratif, cuma
 * menambah kebingungan tanpa fungsi).
 *
 * Sekarang: SATU MODE SAJA (selalu berupa baris rentang qty->harga, "flat"
 * cukup satu baris dengan Max kosong/tak terbatas), field yang tidak
 * pernah dipakai dihapus dari UI, dan setiap baris py aksi CRUD sendiri
 * (storeRow/updateRow/destroyRow) dengan id_harga asli di database -
 * tidak ada lagi "hapus semua lalu insert ulang", jadi tidak ada lagi
 * kelas bug "baris lain ikut hilang". Alokasi Mesin Produksi juga
 * DIHAPUS dari modal ini - itu domain Master Produk, sudah py tempatnya
 * sendiri di sana, tidak perlu dobel di halaman harga.
 */
class SettingHargaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('produk')
            ->leftJoin('mesin', 'produk.id_mesin', '=', 'mesin.id_mesin')
            ->select('produk.*', 'mesin.nama_mesin');

        if ($request->filled('search')) {
            $query->where('produk.nama_produk', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('produk.id_mesin', $request->category);
        }

        $sort = $request->get('sort', 'produk.nama_produk');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        $produk = $query->paginate(25)->withQueryString();

        $golongan = DB::table('golongan_konsumen')->orderBy('nama_golongan')->get();
        $categories = DB::table('mesin')->orderBy('nama_mesin')->get();

        $all_harga = DB::table('setting_harga')
            ->whereIn('id_produk', $produk->pluck('id_produk'))
            ->orderBy('id_golongan')
            ->orderBy('range_min')
            ->get()
            ->groupBy('id_produk');

        return view('master.setting_harga.index', compact(
            'produk', 'golongan', 'all_harga', 'categories'
        ));
    }

    public function storeRow(Request $request, $idProduk)
    {
        $request->validate([
            'id_golongan' => 'required|exists:golongan_konsumen,id_golongan',
            'range_min'   => 'required|integer|min:1',
            'range_max'   => 'nullable|integer|gte:range_min',
            'harga_jual'  => 'required|numeric|min:0',
        ]);

        if ($this->tumpangTindih($idProduk, $request->id_golongan, $request->range_min, $request->range_max)) {
            return back()->with('error', 'Rentang qty ini tumpang tindih dengan tier yang sudah ada untuk golongan tersebut. Sesuaikan dulu Min/Max-nya.');
        }

        DB::table('setting_harga')->insert([
            'id_produk'   => $idProduk,
            'id_golongan' => $request->id_golongan,
            'range_min'   => $request->range_min,
            'range_max'   => $request->range_max ?: null,
            'harga_jual'  => $request->harga_jual,
            'qty_berlaku' => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', 'Tier harga ditambahkan.');
    }

    public function updateRow(Request $request, $id)
    {
        $request->validate([
            'range_min'  => 'required|integer|min:1',
            'range_max'  => 'nullable|integer|gte:range_min',
            'harga_jual' => 'required|numeric|min:0',
        ]);

        $row = DB::table('setting_harga')->where('id_harga', $id)->first();
        if (!$row) {
            return back()->with('error', 'Tier harga tidak ditemukan.');
        }

        if ($this->tumpangTindih($row->id_produk, $row->id_golongan, $request->range_min, $request->range_max, $id)) {
            return back()->with('error', 'Rentang qty ini tumpang tindih dengan tier lain untuk golongan tersebut. Sesuaikan dulu Min/Max-nya.');
        }

        DB::table('setting_harga')->where('id_harga', $id)->update([
            'range_min'  => $request->range_min,
            'range_max'  => $request->range_max ?: null,
            'harga_jual' => $request->harga_jual,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Tier harga diperbarui.');
    }

    public function destroyRow($id)
    {
        DB::table('setting_harga')->where('id_harga', $id)->delete();

        return back()->with('success', 'Tier harga dihapus.');
    }

    /**
     * Cek apakah rentang [min, max] (max null = tak terbatas) tumpang
     * tindih dengan tier LAIN yang sudah ada untuk produk+golongan yang
     * sama - sebelumnya TIDAK ADA validasi ini sama sekali, jadi dua tier
     * yang saling tumpang tindih bisa tersimpan berdampingan dan
     * HargaService::getHargaEfektif akan diam-diam memilih salah satu
     * secara tidak terduga (bergantung urutan ORDER BY range_min DESC).
     */
    private function tumpangTindih($idProduk, $idGolongan, $min, $max, $excludeId = null): bool
    {
        return DB::table('setting_harga')
            ->where('id_produk', $idProduk)
            ->where('id_golongan', $idGolongan)
            ->when($excludeId, fn ($q) => $q->where('id_harga', '!=', $excludeId))
            ->where('range_min', '<=', $max ?? 999999999)
            ->where(function ($q) use ($min) {
                $q->whereNull('range_max')->orWhere('range_max', '>=', $min);
            })
            ->exists();
    }
}
