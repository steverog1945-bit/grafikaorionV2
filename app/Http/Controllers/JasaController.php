<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JasaController extends Controller
{
    public function index()
    {
        $jasa = DB::table('jasa')
            ->leftJoin('satuan', 'jasa.id_satuan', '=', 'satuan.id_satuan')
            ->select('jasa.*', 'satuan.nama_satuan')
            ->orderBy('jasa.nama_jasa')
            ->get();

        $satuan = DB::table('satuan')->orderBy('nama_satuan')->get();

        return view('master.jasa.index', compact('jasa', 'satuan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jasa'  => 'required|max:100',
            'id_satuan'  => 'nullable|exists:satuan,id_satuan',
            'keterangan' => 'nullable',
        ]);

        DB::table('jasa')->insert([
            'nama_jasa'  => $request->nama_jasa,
            'id_satuan'  => $request->id_satuan ?: null,
            'keterangan' => $request->keterangan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Jasa berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_jasa' => 'required|max:100',
            'id_satuan' => 'nullable|exists:satuan,id_satuan',
        ]);

        DB::table('jasa')->where('id_jasa', $id)->update([
            'nama_jasa'  => $request->nama_jasa,
            'id_satuan'  => $request->id_satuan ?: null,
            'keterangan' => $request->keterangan,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Jasa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('jasa')->where('id_jasa', $id)->delete();
        return redirect()->back()->with('success', 'Jasa berhasil dihapus.');
    }
}
