<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class KartuStokController extends Controller
{
    public function index()
    {
        $bahan = DB::table('bahan_baku')
            ->orderBy('nama_bahan')
            ->get();

        return view('kartu_stok.index', compact('bahan'));
    }

    public function detail($id)
    {
        $bahan = DB::table('bahan_baku')
            ->where('id_bahan', $id)
            ->first();

        $kartu = DB::table('kartu_stok')
            ->where('id_bahan', $id)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('kartu_stok.detail', compact('bahan', 'kartu'));
    }
}