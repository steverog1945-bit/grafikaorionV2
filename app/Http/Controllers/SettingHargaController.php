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

        $produk = $query->paginate(20)->withQueryString();

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
        // Rentang wajib BILANGAN BULAT untuk produk metode_hitung='quantity'
        // (satuan Pcs - pecahan tidak bermakna sama sekali disini, mis. Min
        // "1,01" pcs) - tapi TETAP numeric/pecahan untuk metode_hitung=
        // 'ukuran' (lihat migrasi 2026_09_11_240000: satuan rentang di situ
        // luas m² pecahan, mis. 0.09 m² utk stiker 30x30cm - integer|min:1
        // di situ dulu membuat order di bawah 1 m² MUSTAHIL dapat harga).
        // Divalidasi di server (bukan cuma dibulatkan di JS) karena endpoint
        // ini dipanggil lewat fetch() JSON, bukan submit form native, jadi
        // atribut "step" HTML tidak pernah benar-benar dicek browser.
        $metodeHitung = DB::table('produk')->where('id_produk', $idProduk)->value('metode_hitung');
        $isPcs = $metodeHitung !== 'ukuran';
        $request->validate([
            'id_golongan' => 'required|exists:golongan_konsumen,id_golongan',
            'range_min'   => $isPcs ? 'required|integer|min:1' : 'required|numeric|min:0.01',
            'range_max'   => ($isPcs ? 'nullable|integer' : 'nullable|numeric') . '|gte:range_min',
            'harga_jual'  => 'required|numeric|min:0',
        ]);

        if ($this->tumpangTindih($idProduk, $request->id_golongan, $request->range_min, $request->range_max)) {
            return response()->json(['success' => false, 'message' => 'Rentang qty ini tumpang tindih dengan tier yang sudah ada untuk golongan tersebut. Sesuaikan dulu Min/Max-nya.'], 422);
        }

        $id = DB::table('setting_harga')->insertGetId([
            'id_produk'   => $idProduk,
            'id_golongan' => $request->id_golongan,
            'range_min'   => $request->range_min,
            'range_max'   => $request->range_max ?: null,
            'harga_jual'  => $request->harga_jual,
            'qty_berlaku' => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $row = DB::table('setting_harga')->where('id_harga', $id)->first();

        return response()->json(['success' => true, 'message' => 'Tier harga ditambahkan.', 'row' => $row]);
    }

    public function updateRow(Request $request, $id)
    {
        $row = DB::table('setting_harga')->where('id_harga', $id)->first();
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Tier harga tidak ditemukan.'], 404);
        }

        // Optimistic locking: tier ini sudah diubah orang lain sejak halaman dimuat -> jangan menimpa.
        if (\App\Support\VersiBaris::bentrok($request, $row)) {
            return response()->json(['success' => false, 'message' => \App\Support\VersiBaris::pesan($row)], 409);
        }

        // integer utk Pcs, numeric/pecahan utk m² - lihat catatan di storeRow().
        $metodeHitung = DB::table('produk')->where('id_produk', $row->id_produk)->value('metode_hitung');
        $isPcs = $metodeHitung !== 'ukuran';
        $request->validate([
            'range_min'  => $isPcs ? 'required|integer|min:1' : 'required|numeric|min:0.01',
            'range_max'  => ($isPcs ? 'nullable|integer' : 'nullable|numeric') . '|gte:range_min',
            'harga_jual' => 'required|numeric|min:0',
        ]);

        if ($this->tumpangTindih($row->id_produk, $row->id_golongan, $request->range_min, $request->range_max, $id)) {
            return response()->json(['success' => false, 'message' => 'Rentang qty ini tumpang tindih dengan tier lain untuk golongan tersebut. Sesuaikan dulu Min/Max-nya.'], 422);
        }

        DB::table('setting_harga')->where('id_harga', $id)->update([
            'range_min'  => $request->range_min,
            'range_max'  => $request->range_max ?: null,
            'harga_jual' => $request->harga_jual,
            'updated_at' => $sekarang = now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Tier harga diperbarui.', 'versi' => $sekarang->format('Y-m-d H:i:s')]);
    }

    public function destroyRow($id)
    {
        DB::table('setting_harga')->where('id_harga', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Tier harga dihapus.']);
    }

    /**
     * Copy SELURUH tier harga satu golongan (golongan yang sedang aktif di
     * tab modal) ke SEMUA golongan lain untuk produk yang sama - dipakai
     * saat harga memang sama rata di semua golongan, supaya tidak perlu
     * mengetik ulang tier satu-satu di tiap tab. Tier golongan tujuan
     * DIGANTI TOTAL (hapus lalu insert ulang) dengan salinan tier golongan
     * sumber - ini aksi destruktif yang disengaja (harus dikonfirmasi dulu
     * di sisi klien), bukan digabung dengan tier yang sudah ada di golongan
     * tujuan supaya hasilnya benar-benar identik dengan golongan sumber,
     * tidak tercampur sisa tier lama yang mungkin tumpang tindih.
     */
    public function copyKeSemuaGolongan(Request $request, $idProduk)
    {
        $request->validate([
            'id_golongan_sumber' => 'required|exists:golongan_konsumen,id_golongan',
        ]);

        $idSumber = (int) $request->id_golongan_sumber;

        $tiersSumber = DB::table('setting_harga')
            ->where('id_produk', $idProduk)
            ->where('id_golongan', $idSumber)
            ->orderBy('range_min')
            ->get();

        if ($tiersSumber->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Golongan ini belum punya tier harga untuk disalin.'], 422);
        }

        $golonganLain = DB::table('golongan_konsumen')->where('id_golongan', '!=', $idSumber)->pluck('id_golongan');

        DB::transaction(function () use ($idProduk, $idSumber, $tiersSumber, $golonganLain) {
            DB::table('setting_harga')
                ->where('id_produk', $idProduk)
                ->whereIn('id_golongan', $golonganLain)
                ->delete();

            $baris = [];
            foreach ($golonganLain as $idGolonganTujuan) {
                foreach ($tiersSumber as $t) {
                    $baris[] = [
                        'id_produk'   => $idProduk,
                        'id_golongan' => $idGolonganTujuan,
                        'range_min'   => $t->range_min,
                        'range_max'   => $t->range_max,
                        'harga_jual'  => $t->harga_jual,
                        'qty_berlaku' => 1,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }
            if (!empty($baris)) {
                DB::table('setting_harga')->insert($baris);
            }
        });

        // Kembalikan SELURUH tier produk ini (semua golongan) yang sudah
        // diperbarui, dikelompokkan per golongan - dipakai klien untuk
        // menggambar ulang semua tab tanpa reload halaman.
        $semuaTier = DB::table('setting_harga')
            ->where('id_produk', $idProduk)
            ->orderBy('id_golongan')
            ->orderBy('range_min')
            ->get()
            ->groupBy('id_golongan');

        return response()->json([
            'success' => true,
            'message' => 'Harga golongan ini berhasil disalin ke ' . $golonganLain->count() . ' golongan lain.',
            'tiers' => $semuaTier,
        ]);
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
