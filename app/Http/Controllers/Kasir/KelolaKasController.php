<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KelolaKasController extends Controller
{
    public function index()
{
    $hari_ini = date('Y-m-d');

    // 1. Cek Kas Aktif (Untuk logic tombol Buka/Closing)
    $kasAktif = DB::table('kas_harian')
        ->where('tgl_kas', $hari_ini)
        ->where('status_lock', 'open')
        ->first();

    // 2. Ambil Rincian Mutasi (Untuk tabel)
    $mutasi = DB::table('pembayaran_riwayat')
        ->join('transaksi', 'pembayaran_riwayat.id_transaksi', '=', 'transaksi.id_transaksi')
        ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
        ->whereDate('pembayaran_riwayat.tgl_bayar', $hari_ini)
        ->select(
            'pembayaran_riwayat.*', 
            'transaksi.nomor_nota', 
            'transaksi.status_pembayaran', 
            'konsumen.nama_konsumen'
        )
        ->orderBy('pembayaran_riwayat.tgl_bayar', 'desc')
        ->get();

    // 3. Siapkan Data untuk Badge Total (Tunai & Transfer)
    // Kita buat dalam bentuk Collection agar fungsi ->sum() di Blade bekerja
    $data_kas = collect([
        (object) [
            'nominal_tunai' => $mutasi->where('metode', 'Tunai')->sum('nominal'),
            'nominal_transfer' => $mutasi->where('metode', 'Transfer')->sum('nominal'),
        ]
    ]);

    return view('kasir.kelola_kas', compact('kasAktif', 'mutasi', 'data_kas'));
}

public function storeKas(Request $request)
{
    // Mengambil input dari query parameter (window.location.href)
    $jumlah = $request->query('jumlah');

    DB::table('kas_harian')->insert([
        'tgl_kas' => date('Y-m-d'),
        'id_user' => auth()->id(),
        'saldo_awal' => $jumlah,
        'saldo_akhir' => $jumlah, // Akan bertambah otomatis saat prosesBayar
        'total_transfer' => 0,
        'status_lock' => 'open',
        'created_at' => now()
    ]);

    return redirect()->route('kasir.kelola')->with('success', 'Kasir berhasil dibuka!');
}

    public function bukaKas(Request $request)
    {
        $request->validate(['jumlah' => 'required']);
        $nominal = str_replace('.', '', $request->jumlah);

        // Cek apakah sudah ada kas terbuka hari ini
        $cek = DB::table('kas_harian')
            ->where('tgl_kas', date('Y-m-d'))
            ->where('status_lock', 'open')
            ->first();

        if ($cek) {
            return back()->with('error', 'Kas hari ini sudah dibuka!');
        }

        DB::table('kas_harian')->insert([
            'tgl_kas' => date('Y-m-d'),
            'id_user' => Auth::id(),
            'saldo_awal' => $nominal,
            'saldo_akhir' => $nominal, // Awalnya sama dengan modal
            'status_lock' => 'open',
            'created_at' => now()
        ]);

        return back()->with('success', 'Kas berhasil dibuka. Selamat bertugas!');
    }
}