<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CRUD per-baris untuk tier harga Jasa (jasa_harga) - pola identik dengan
 * FinishingHargaController (satu baris satu aksi, bukan bulk replace).
 */
class JasaHargaController extends Controller
{
    public function store(Request $request, $idJasa)
    {
        $request->validate([
            'range_min' => 'required|integer|min:1',
            'range_max' => 'nullable|integer|gte:range_min',
            'harga'     => 'required|numeric|min:0',
        ]);

        DB::table('jasa_harga')->insert([
            'id_jasa'    => $idJasa,
            'range_min'  => $request->range_min,
            'range_max'  => $request->range_max ?: null,
            'harga'      => $request->harga,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Tier harga jasa ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'range_min' => 'required|integer|min:1',
            'range_max' => 'nullable|integer|gte:range_min',
            'harga'     => 'required|numeric|min:0',
        ]);

        DB::table('jasa_harga')->where('id_jasa_harga', $id)->update([
            'range_min'  => $request->range_min,
            'range_max'  => $request->range_max ?: null,
            'harga'      => $request->harga,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Tier harga jasa diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('jasa_harga')->where('id_jasa_harga', $id)->delete();

        return redirect()->back()->with('success', 'Tier harga jasa dihapus.');
    }
}
