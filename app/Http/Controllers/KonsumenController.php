<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Konsumen;
class KonsumenController extends Controller
{
    public function index(Request $request)
    {
        // Menggabungkan tabel konsumen dengan nama golongannya
        $query = DB::table('konsumen')
            ->join('golongan_konsumen', 'konsumen.id_golongan', '=', 'golongan_konsumen.id_golongan')
            ->select('konsumen.*', 'golongan_konsumen.nama_golongan');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('konsumen.nama_konsumen', 'like', '%' . $request->search . '%')
                  ->orWhere('konsumen.telepon', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('id_golongan')) {
            $query->where('konsumen.id_golongan', $request->id_golongan);
        }

        $konsumen = $query->orderBy('konsumen.nama_konsumen')->paginate(15)->withQueryString();

        $golongan = DB::table('golongan_konsumen')->get();

        return view('master.konsumen.index', compact('konsumen', 'golongan'));
    }
    public function update(Request $request, $id)
{
    // 1. Validasi input
    $request->validate([
        'nama_konsumen' => 'required|max:255',
        'telepon' => 'required',
        'id_golongan' => 'required|numeric',
    ]);

    // 2. Update data ke database
    DB::table('konsumen')->where('id_konsumen', $id)->update([
        'nama_konsumen' => $request->nama_konsumen,
        'telepon'       => $request->telepon,
        'id_golongan'   => $request->id_golongan,
        'alamat'        => $request->alamat,
        // Jika ada kolom tipe_konsumen di form, tambahkan di sini
        // 'tipe_konsumen' => $request->tipe_konsumen, 
    ]);

    // 3. Kembali ke halaman sebelumnya dengan pesan sukses
    return redirect()->back()->with('success', 'Data konsumen berhasil diperbarui.');
}
public function storeAjax(Request $request)
{
    // VALIDASI SESI KAS SEBELUM SIMPAN KONSUMEN
    // Menggunakan kas_harian (tabel sesi kas yang sesungguhnya dipakai di seluruh
    // modul Kasir). Sebelumnya kode ini mengecek tabel kas_sesi, yang tidak pernah
    // diisi data oleh bagian manapun dari aplikasi, sehingga pengecekan ini selalu
    // gagal walau kasir sudah membuka sesi kas hari itu.
    $sesi = DB::table('kas_harian')
        ->where('tgl_kas', date('Y-m-d'))
        ->where('status_lock', 'open')
        ->where('id_user', auth()->id())
        ->first();

    if (!$sesi) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal: Sesi Kas belum dibuka. Silahkan buka saldo awal dulu.'
        ], 403);
    }
    // 1. Validasi ketat di sisi Server
    $request->validate([
        'nama_konsumen' => 'required|string|max:255',
        'id_golongan'   => 'required',
        'tipe_konsumen' => 'nullable|in:permanent,temporary',
    ]);

    try {
        // 2. Simpan menggunakan DB Table agar tidak masalah dengan Model fillable
        $id = DB::table('konsumen')->insertGetId([
            'nama_konsumen' => $request->nama_konsumen,
            'telepon'       => $request->telepon ?? '-',
            'id_golongan'   => $request->id_golongan,
            'tipe_konsumen' => $request->tipe_konsumen ?? 'temporary',
            'tgl_registrasi'=> now(),
            'nama_konsumen_manual' => $request->nama_konsumen // Seringkali kolom ini NOT NULL di DB
        ]);

        // 3. Ambil data lengkap untuk dikirim balik ke JavaScript
        $konsumen = DB::table('konsumen')
            ->join('golongan_konsumen', 'konsumen.id_golongan', '=', 'golongan_konsumen.id_golongan')
            ->where('id_konsumen', $id)
            ->select('konsumen.*', 'golongan_konsumen.nama_golongan')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $konsumen
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
    public function store(Request $request)
    {
        $request->validate([
            'nama_konsumen' => 'required|string|max:100',
            'id_golongan'   => 'required|exists:golongan_konsumen,id_golongan',
            'telepon'       => 'required'
        ]);

        DB::table('konsumen')->insert([
            'nama_konsumen' => $request->nama_konsumen,
            'telepon'       => $request->telepon,
            'alamat'        => $request->alamat,
            'id_golongan'   => $request->id_golongan,
            'tipe_konsumen' => $request->tipe_konsumen ?? 'temporary',
            'tgl_registrasi'=> now()
        ]);

        return redirect()->back()->with('success', 'Data konsumen berhasil disimpan.');
    }

    public function destroy($id)
    {
        DB::table('konsumen')->where('id_konsumen', $id)->delete();
        return redirect()->back()->with('success', 'Konsumen dihapus.');
    }
}