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

        DB::table('finishing_harga')->insert([
            'id_finishing' => $idFinishing,
            'range_min'    => $request->range_min,
            'range_max'    => $request->range_max ?: null,
            'harga'        => $request->harga,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->back()->with('success', 'Tier harga finishing ditambahkan.');
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

        return redirect()->back()->with('success', 'Tier harga finishing diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('finishing_harga')->where('id_finishing_harga', $id)->delete();

        return redirect()->back()->with('success', 'Tier harga finishing dihapus.');
    }
}
