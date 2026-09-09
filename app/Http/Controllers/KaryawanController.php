<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawan = Karyawan::all();
        return view('master.karyawan.index', compact('karyawan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_karyawan' => 'required',
            'bagian' => 'required',
            'telepon' => 'required'
        ]);
        $data = $request->all();
        Karyawan::create($request->all());
        $data['status_aktif'] = 1;
        return back()->with('success', 'Data karyawan berhasil ditambahkan!');
    }
   public function update(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update([
            'nama_karyawan' => $request->nama_karyawan,
            'bagian'        => $request->bagian,
            'telepon'       => $request->telepon,
            'status_aktif'  => $request->status_aktif,
        ]);

        return back()->with('success', 'Data karyawan berhasil diperbarui!');
    }

    // TAMBAHKAN FUNGSI DESTROY INI
    public function destroy($id)
{
    try {
        // 1. Hapus User yang terhubung dulu (jika ada)
        \DB::table('users')->where('id_karyawan', $id)->delete();

        // 2. Baru hapus Karyawannya
        $deleted = \DB::table('karyawan')->where('id_karyawan', $id)->delete();

        if ($deleted) {
            return back()->with('success', 'Karyawan dan akun loginnya berhasil dihapus.');
        }
        
        return back()->with('error', 'Data tidak ditemukan.');
        
    } catch (\Exception $e) {
        // Jika masih gagal (mungkin sudah ada data Transaksi), tangkap errornya
        return back()->with('error', 'Gagal hapus! Karyawan ini sudah tercatat dalam data transaksi/kas.');
    }
}
}