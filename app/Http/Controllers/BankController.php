<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    public function index(Request $request) {
        $query = DB::table('bank_perusahaan');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_bank', 'like', '%' . $request->search . '%')
                  ->orWhere('atas_nama', 'like', '%' . $request->search . '%')
                  ->orWhere('nomor_rekening', 'like', '%' . $request->search . '%');
            });
        }

        $bank = $query->orderBy('nama_bank')->paginate(20)->withQueryString();
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