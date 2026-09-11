<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CRUD per-baris untuk tier harga Jasa (jasa_harga) - pola identik dengan
 * FinishingHargaController (satu baris satu aksi, bukan bulk replace,
 * SELALU JSON supaya dipanggil lewat fetch() tanpa reload halaman).
 */
class JasaHargaController extends Controller
{
    public function store(Request $request, $idJasa)
    {
        // numeric (bukan integer) - konsisten dengan Price List produk &
        // Finishing (lihat migrasi 2026_09_11_240000), walau qty jasa
        // biasanya bilangan bulat, disamakan supaya satu aturan berlaku di
        // ketiga tabel harga bertingkat ini.
        $request->validate([
            'range_min' => 'required|numeric|min:0.01',
            'range_max' => 'nullable|numeric|gte:range_min',
            'harga'     => 'required|numeric|min:0',
        ]);

        $id = DB::table('jasa_harga')->insertGetId([
            'id_jasa'    => $idJasa,
            'range_min'  => $request->range_min,
            'range_max'  => $request->range_max ?: null,
            'harga'      => $request->harga,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('jasa_harga')->where('id_jasa_harga', $id)->first();

        return response()->json(['success' => true, 'message' => 'Tier harga ditambahkan.', 'row' => $row]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'range_min' => 'required|numeric|min:0.01',
            'range_max' => 'nullable|numeric|gte:range_min',
            'harga'     => 'required|numeric|min:0',
        ]);

        DB::table('jasa_harga')->where('id_jasa_harga', $id)->update([
            'range_min'  => $request->range_min,
            'range_max'  => $request->range_max ?: null,
            'harga'      => $request->harga,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Tier harga diperbarui.']);
    }

    public function destroy($id)
    {
        DB::table('jasa_harga')->where('id_jasa_harga', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Tier harga dihapus.']);
    }
}
