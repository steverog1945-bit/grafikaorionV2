<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CRUD per-baris untuk tier harga Finishing (finishing_harga) - sengaja
 * SATU BARIS SATU AKSI (bukan bulk delete-lalu-insert-ulang seperti pola
 * lama Price List produk) supaya tidak ada kelas bug "baris lain ikut
 * hilang karena key array bentrok" - setiap baris py id_finishing_harga
 * asli di database, add/edit/hapus tidak pernah menyentuh baris lain.
 *
 * SELALU JSON (bukan redirect): dipanggil murni lewat fetch() dari modal
 * "Harga Bertingkat" supaya menambah/edit/hapus satu tier tidak perlu
 * reload seluruh halaman - sebelumnya submit form biasa (full page
 * reload) terasa lambat kalau mau memasukkan beberapa tier berturut-turut.
 */
class FinishingHargaController extends Controller
{
    public function store(Request $request, $idFinishing)
    {
        $request->validate([
            'range_min' => 'required|integer|min:1',
            'range_max' => 'nullable|integer|gte:range_min',
            'harga'     => 'required|numeric|min:0',
        ]);

        $id = DB::table('finishing_harga')->insertGetId([
            'id_finishing' => $idFinishing,
            'range_min'    => $request->range_min,
            'range_max'    => $request->range_max ?: null,
            'harga'        => $request->harga,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $row = DB::table('finishing_harga')->where('id_finishing_harga', $id)->first();

        return response()->json(['success' => true, 'message' => 'Tier harga ditambahkan.', 'row' => $row]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'range_min' => 'required|integer|min:1',
            'range_max' => 'nullable|integer|gte:range_min',
            'harga'     => 'required|numeric|min:0',
        ]);

        DB::table('finishing_harga')->where('id_finishing_harga', $id)->update([
            'range_min'  => $request->range_min,
            'range_max'  => $request->range_max ?: null,
            'harga'      => $request->harga,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Tier harga diperbarui.']);
    }

    public function destroy($id)
    {
        DB::table('finishing_harga')->where('id_finishing_harga', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Tier harga dihapus.']);
    }
}
