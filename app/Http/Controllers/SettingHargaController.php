<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Satuan;
use App\Models\Produk;

class SettingHargaController extends Controller
{
    /**
     * Menampilkan Master Price List
     */
    public function index(Request $request)
    {
        // PERBAIKAN: Join ke tabel 'mesin' bukan 'kategori' 
        // agar id_mesin di produk sinkron dengan nama_mesin
        $query = DB::table('produk')
            ->leftJoin('mesin', 'produk.id_mesin', '=', 'mesin.id_mesin')
            ->select(
                'produk.*', 
                'mesin.nama_mesin' // Alias untuk tampilan di blade
            );

        // Quick Search
        if ($request->filled('search')) {
            $query->where('produk.nama_produk', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan mesin (yang Anda anggap sebagai kategori)
        if ($request->filled('category')) {
            $query->where('produk.id_mesin', $request->category);
        }

        // Sortir
        $sort = $request->get('sort', 'produk.nama_produk');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        // Pagination 25 data
        $produk = $query->paginate(25)->withQueryString();

        // Data Pendukung
        $golongan = DB::table('golongan_konsumen')->get();
        $satuan = Satuan::all();
        
        // Ambil dari tabel MESIN untuk dropdown filter
        $categories = DB::table('mesin')->get(); 

        $all_harga = DB::table('setting_harga')
            ->whereIn('id_produk', $produk->pluck('id_produk'))
            ->get();

        return view('master.setting_harga.index', compact(
            'produk', 'golongan', 'all_harga', 'satuan', 'categories'
        ));
    }

    public function update_bulk(Request $request)
    {
        $id_produk = $request->id_produk;

        DB::transaction(function () use ($id_produk, $request) {
            // OPTIONAL: Update id_mesin produk jika diubah di modal
            if ($request->has('id_mesin')) {
            DB::table('produk')
                ->where('id_produk', $id_produk)
                ->update(['id_mesin' => $request->id_mesin]);
        }

            // Hapus SEMUA harga lama
            DB::table('setting_harga')->where('id_produk', $id_produk)->delete();

            // 1. Simpan Harga Flat
            if ($request->harga_flat) {
                foreach ($request->harga_flat as $id_golongan => $harga) {
                    if ($harga && !isset($request->ranges[$id_golongan])) {
                        DB::table('setting_harga')->insert([
                            'id_produk'     => $id_produk,
                            'id_golongan'   => $id_golongan,
                            'range_min'     => 1,
                            'range_max'     => 9999999,
                            'harga_jual'    => $harga,
                            'qty_berlaku'   => 1,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }

            // 2. Simpan Harga Range (Tiering)
            if ($request->ranges) {
                foreach ($request->ranges as $id_golongan => $rows) {
                    foreach ($rows as $row) {
                        if ($row['harga']) {
                            DB::table('setting_harga')->insert([
                                'id_produk'          => $id_produk,
                                'id_golongan'        => $id_golongan,
                                'range_min'          => $row['min'] ?? 0,
                                'range_max'          => $row['max'] ?? 0,
                                'id_satuan'          => $row['id_satuan'] ?? null,
                                'harga_jual'         => $row['harga'],
                                'qty_berlaku'        => $row['qty_berlaku'] ?? 1,
                                'id_satuan_berlaku'  => $row['id_satuan_berlaku'] ?? null,
                                'created_at'         => now(),
                                'updated_at'         => now(),
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Harga dan Mesin Berhasil Diperbarui!');    }

    public function destroy($id)
    {
        DB::table('setting_harga')->where('id_produk', $id)->delete();
        $produk = Produk::findOrFail($id);
        $produk->delete();

        return redirect()->back()->with('success', 'Produk dan data harga berhasil dihapus');
    }
}