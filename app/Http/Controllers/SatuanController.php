<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Satuan;
class SatuanController extends Controller
{
    public function index()
    {
        $satuan = DB::table('satuan')->get();
        return view('master.satuan.index', compact('satuan'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_satuan' => 'required|max:20|unique:satuan,nama_satuan',
            'nilai_konversi' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $id = DB::table('satuan')->insertGetId([
            'nama_satuan' => $request->nama_satuan,
            'nilai_konversi' => $request->nilai_konversi,
        ]);

        $data = DB::table('satuan')->where('id_satuan', $id)->first();

        return response()->json(['success' => 'Satuan berhasil ditambahkan', 'satuan' => $data]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_satuan' => 'required|max:20|unique:satuan,nama_satuan,'.$id.',id_satuan',
            'nilai_konversi' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::table('satuan')->where('id_satuan', $id)->update([
            'nama_satuan' => $request->nama_satuan,
            'nilai_konversi' => $request->nilai_konversi,
        ]);

        $data = DB::table('satuan')->where('id_satuan', $id)->first();

        return response()->json(['success' => 'Satuan berhasil diperbarui', 'satuan' => $data]);
    }

    public function destroy($id)
    {
        // Cek berdasarkan kolom FK yang sesungguhnya dipakai BahanBaku
        // (id_satuan_stok / id_satuan_hitung), bukan kolom lama 'satuan_stok'.
        $terpakai = DB::table('bahan_baku')
            ->where('id_satuan_stok', $id)
            ->orWhere('id_satuan_hitung', $id)
            ->exists();
        if ($terpakai) {
            return response()->json(['error' => 'Satuan masih digunakan di Bahan Baku'], 422);
        }

        DB::table('satuan')->where('id_satuan', $id)->delete();
        return response()->json(['success' => 'Satuan berhasil dihapus']);
    }
}