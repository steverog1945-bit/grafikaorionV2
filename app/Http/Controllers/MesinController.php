<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use Illuminate\Http\Request;

class MesinController extends Controller
{
    public function index()
    {
        $mesin = Mesin::all();
        return view('master.mesin.index', compact('mesin'));
    }

    public function store(Request $request)
    {
        Mesin::create($request->all());
        return back()->with('success', 'Mesin berhasil ditambah!');
    }

    public function update(Request $request, $id)
    {
        $mesin = Mesin::findOrFail($id);
        $mesin->update($request->all());
        return back()->with('success', 'Mesin berhasil diubah!');
    }

    public function destroy($id)
    {
        $mesin = Mesin::findOrFail($id);
        // Cek jika sudah dipakai di bahan_baku (mencegah error constraint)
        try {
            $mesin->delete();
            return back()->with('success', 'Mesin berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal! Mesin masih digunakan pada data bahan baku.');
        }
    }
}