<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GolonganController extends Controller
{
    public function index()
    {
        $golongan = DB::table('golongan_konsumen')->get();
        return view('master.golongan.index', compact('golongan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_golongan' => 'required|max:100',
            'keterangan' => 'nullable'
        ]);

        DB::table('golongan_konsumen')->insert([
            'nama_golongan' => $request->nama_golongan,
            'keterangan' => $request->keterangan
        ]);

        return redirect()->back()->with('success', 'Golongan konsumen berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'nama_golongan' => 'required|max:100'
    ]);

    DB::table('golongan_konsumen')->where('id_golongan', $id)->update([
        'nama_golongan' => $request->nama_golongan,
        // Gunakan operator ?? untuk memberikan string kosong jika input null
        'keterangan'    => $request->keterangan ?? '-' 
    ]);

    return redirect()->back()->with('success', 'Golongan berhasil diperbarui.');
}

    public function destroy($id)
    {
        // Cek apakah ada konsumen yang masih pakai golongan ini
        $cek = DB::table('konsumen')->where('id_golongan', $id)->exists();
        if ($cek) {
            return redirect()->back()->with('error', 'Gagal hapus! Golongan ini masih digunakan oleh data konsumen.');
        }

        DB::table('golongan_konsumen')->where('id_golongan', $id)->delete();
        return redirect()->back()->with('success', 'Golongan berhasil dihapus.');
    }
}