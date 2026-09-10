<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = Karyawan::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_karyawan', 'like', '%' . $request->search . '%')
                  ->orWhere('telepon', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('bagian')) {
            $query->where('bagian', $request->bagian);
        }

        $karyawan = $query->orderBy('nama_karyawan')->paginate(15)->withQueryString();
        $daftarBagian = Karyawan::select('bagian')->distinct()->orderBy('bagian')->pluck('bagian');

        return view('master.karyawan.index', compact('karyawan', 'daftarBagian'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_karyawan' => 'required',
            'bagian' => 'required',
            'telepon' => 'required'
        ]);
        Karyawan::create($request->only(['nama_karyawan', 'bagian', 'telepon']));
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