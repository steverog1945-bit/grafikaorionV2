<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    public function index() {
        $bank = DB::table('bank_perusahaan')->get();
        return view('master.bank.index', compact('bank'));
    }

    public function store(Request $request) {
        $request->validate(['nama_bank' => 'required', 'nomor_rekening' => 'required', 'atas_nama' => 'required']);
        DB::table('bank_perusahaan')->insert($request->only('nama_bank', 'nomor_rekening', 'atas_nama'));
        return redirect()->back()->with('success', 'Rekening bank berhasil disimpan.');
    }
    public function update(Request $request, $id)
{
    $request->validate([
        'nama_bank' => 'required',
        'nomor_rekening' => 'required',
        'atas_nama' => 'required'
    ]);

    DB::table('bank_perusahaan')->where('id_bank', $id)->update([
        'nama_bank'      => $request->nama_bank,
        'nomor_rekening' => $request->nomor_rekening,
        'atas_nama'      => $request->atas_nama,
    ]);

    return redirect()->back()->with('success', 'Data rekening berhasil diperbarui.');
}

    public function destroy($id) {
        DB::table('bank_perusahaan')->where('id_bank', $id)->delete();
        return redirect()->back()->with('success', 'Rekening dihapus.');
    }
}