<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KasirController extends Controller
{
    public function index(Request $request)
    {
        // 1. Hitung jumlah antrean baru (Hanya status 'Belum Bayar')
        $waitingCount = DB::table('transaksi')
            ->where('status_pembayaran', 'Belum Bayar')
            ->count();

        // 2. Query Monitoring Utama (Hanya yang SUDAH DIPROSES: DP/Lunas)
        $query = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->join('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
            ->select(
                'transaksi.*', 
                'konsumen.nama_konsumen', 
                'users.username',
                DB::raw("(SELECT SUM(nominal) FROM pembayaran_riwayat WHERE pembayaran_riwayat.id_transaksi = transaksi.id_transaksi) as total_masuk")
            )
            ->where('transaksi.status_pembayaran', '!=', 'Belum Bayar'); // Sembunyikan yang belum disentuh kasir

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('transaksi.nomor_nota', 'like', "%{$request->search}%")
                  ->orWhere('konsumen.nama_konsumen', 'like', "%{$request->search}%");
            });
        }

        if ($request->status_bayar) {
            $query->where('transaksi.status_pembayaran', $request->status_bayar);
        }

        $transaksi = $query->orderBy('transaksi.updated_at', 'desc')->paginate(10);

        return view('kasir.monitoring_transaksi', compact('transaksi', 'waitingCount'));
    }
public function prosesClosing(Request $request)
{
    DB::beginTransaction();
    try {
        $id_kas = $request->id_kas;
        $saldo_fisik = str_replace('.', '', $request->saldo_fisik);
        
        // 1. Ambil data kas yang sedang aktif
        $kas = DB::table('kas_harian')->where('id_kas', $id_kas)->first();
        
        if (!$kas || $kas->status_lock == 'closed') {
            throw new \Exception("Sesi kas sudah ditutup atau tidak ditemukan.");
        }

        // 2. Hitung Selisih
        // Selisih = Saldo Fisik di Tangan - Saldo Akhir di Sistem
        $selisih = $saldo_fisik - $kas->saldo_akhir;

        // 3. Update data kas dan LOCK (Closing)
        DB::table('kas_harian')->where('id_kas', $id_kas)->update([
            'saldo_fisik' => $saldo_fisik,
            'selisih' => $selisih,
            'status_lock' => 'closed',
            'waktu_tutup' => now(), // Jika Anda punya kolom ini
            'keterangan_closing' => $request->keterangan,
            'updated_at' => now()
        ]);

        DB::commit();
        return redirect()->route('kasir.kelola')->with('success', 'Kas Berhasil Ditutup. Selisih: Rp ' . number_format($selisih, 0, ',', '.'));

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal Closing: ' . $e->getMessage());
    }
}
    public function waitingList()
    {
        // Pastikan hanya mengambil status 'Belum Bayar'
        $waiting = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->where('transaksi.status_pembayaran', 'Belum Bayar')
            ->select('transaksi.*', 'konsumen.nama_konsumen')
            ->orderBy('transaksi.created_at', 'desc')
            ->get();

        return view('kasir.waiting_list', compact('waiting'));
    }

    public function bayarForm($id)
    {
        $transaksi = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->where('transaksi.id_transaksi', $id)
            ->first();

        if (!$transaksi) {
            return redirect()->route('kasir.index')->with('error', 'Transaksi tidak ditemukan!');
        }

        $items = DB::table('transaksi_detail') 
            ->join('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->where('transaksi_detail.id_transaksi', $id)
            ->select('transaksi_detail.*', 'produk.nama_produk', 'produk.metode_hitung')
            ->get();

        $totalTerbayar = DB::table('pembayaran_riwayat')->where('id_transaksi', $id)->sum('nominal') ?? 0;
        $sisaTagihan = $transaksi->total_bayar - $totalTerbayar;

        return view('kasir.form_pembayaran', compact('transaksi', 'items', 'totalTerbayar', 'sisaTagihan'));
    }

    public function prosesBayar(Request $request)
    {
        DB::beginTransaction();
        try {
            $id_transaksi = $request->id_transaksi;
            $uangDiterimaSekarang = str_replace('.', '', $request->jumlah_bayar) ?: 0;
            $diskon = str_replace('.', '', $request->diskon ?? 0);
            $metode = $request->metode_pembayaran;

            // 1. Update Detail Barang
            if ($request->items) {
                foreach ($request->items as $id_detail => $data) {
                    $harga_bersih = str_replace('.', '', $data['harga']);
                    DB::table('transaksi_detail')->where('id_detail', $id_detail)->update([
                        'subtotal' => $harga_bersih * $data['qty_hidden']
                    ]);
                }
            }

            $totalNotaBaru = DB::table('transaksi_detail')->where('id_transaksi', $id_transaksi)->sum('subtotal');
            $grandTotalFinal = $totalNotaBaru - $diskon;

            // 2. Catat Pembayaran (Hanya jika uang > 0)
            if ($uangDiterimaSekarang > 0) {
                $kas = DB::table('kas_harian')
                        ->where('tgl_kas', date('Y-m-d'))
                        ->where('status_lock', 'open')
                        ->first();

                if (!$kas) {
                    throw new \Exception("Kasir belum dibuka! Silakan buka kas terlebih dahulu.");
                }

                DB::table('pembayaran_riwayat')->insert([
                    'id_transaksi' => $id_transaksi,
                    'nominal'      => $uangDiterimaSekarang,
                    'metode'       => $metode,
                    'tgl_bayar'    => now(),
                    'keterangan'   => "Pembayaran via Kasir"
                ]);

                // Update Saldo Kas
                if ($metode == 'Tunai') {
                    DB::table('kas_harian')->where('id_kas', $kas->id_kas)->increment('saldo_akhir', $uangDiterimaSekarang);
                } else {
                    DB::table('kas_harian')->where('id_kas', $kas->id_kas)->increment('total_transfer', $uangDiterimaSekarang);
                }
            }

            // 3. Update Status (Paksa pindah dari Belum Bayar ke DP)
            $totalTerbayarSemua = DB::table('pembayaran_riwayat')->where('id_transaksi', $id_transaksi)->sum('nominal') ?? 0;

            if ($totalTerbayarSemua >= $grandTotalFinal && $grandTotalFinal > 0) {
                $status = 'Lunas';
            } else {
                // Berapapun bayarnya (termasuk 0), status jadi DP agar masuk Monitoring
                $status = 'DP';
            }

            DB::table('transaksi')->where('id_transaksi', $id_transaksi)->update([
                'total_bayar'       => $grandTotalFinal,
                'status_pembayaran' => $status,
                'metode_pembayaran' => $metode,
                'updated_at'        => now()
            ]);

            DB::commit();
            return redirect()->route('kasir.index')->with('success', 'Transaksi berhasil diproses ke Monitoring.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Manajemen Kas Harian
     */
    public function kelolaKas()
    {
        $hari_ini = date('Y-m-d');
        $kasAktif = DB::table('kas_harian')->where('tgl_kas', $hari_ini)->where('status_lock', 'open')->first();
        
        $mutasi = DB::table('pembayaran_riwayat')
            ->join('transaksi', 'pembayaran_riwayat.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->whereDate('pembayaran_riwayat.tgl_bayar', $hari_ini)
            ->select('pembayaran_riwayat.*', 'transaksi.nomor_nota', 'transaksi.status_pembayaran', 'konsumen.nama_konsumen')
            ->orderBy('pembayaran_riwayat.tgl_bayar', 'desc')
            ->get();

        $data_kas = collect([(object) [
            'nominal_tunai' => $mutasi->where('metode', 'Tunai')->sum('nominal'),
            'nominal_transfer' => $mutasi->where('metode', 'Transfer')->sum('nominal'),
        ]]);

        return view('kasir.kelola_kas', compact('kasAktif', 'mutasi', 'data_kas'));
    }

   public function storeKas(Request $request)
{
    $request->validate(['jumlah' => 'required']);
    $nominal = str_replace('.', '', $request->jumlah);

    $sudahBuka = DB::table('kas_harian')
        ->where('tgl_kas', now()->format('Y-m-d'))
        ->where('id_user', auth()->id())
        ->where('status_lock', 'open')
        ->exists();

    if ($sudahBuka) {
        return redirect()->route('kasir.kelola')->with('error', 'Anda sudah membuka sesi kas hari ini.');
    }

    DB::table('kas_harian')->insert([
        'tgl_kas'     => now()->format('Y-m-d'),
        'id_user'     => auth()->id(),
        'saldo_awal'  => $nominal,
        'saldo_akhir' => $nominal, 
        'status_lock' => 'open',
        'created_at'  => now(),
        'updated_at'  => now()
    ]);

    // Setelah simpan, lempar ke halaman monitoring
    return redirect()->route('kasir.index')->with('success', 'Kas Berhasil Dibuka!');
}
public function previewClosing()
{
    $hari_ini = date('Y-m-d');
    $kas = DB::table('kas_harian')
        ->where('tgl_kas', $hari_ini)
        ->where('status_lock', 'open')
        ->where('id_user', auth()->id())
        ->first();

    // Proteksi: Tidak bisa closing jika tidak ada kas aktif
    if (!$kas) {
        return redirect()->route('kasir.kelola')->with('error', 'Tidak ada alur kas aktif yang bisa ditutup!');
    }

    // Hitung Rincian Pemasukan dari Riwayat Pembayaran
    $pemasukan = DB::table('pembayaran_riwayat')
        ->whereDate('tgl_bayar', $hari_ini)
        ->select(
            DB::raw("SUM(CASE WHEN metode = 'Tunai' THEN nominal ELSE 0 END) as tunai_masuk"),
            DB::raw("SUM(CASE WHEN metode = 'Transfer' THEN nominal ELSE 0 END) as transfer_masuk")
        )->first();

    // Belum ada tabel pengeluaran/kas_keluar di database, jadi pengeluaran tunai
    // sementara selalu 0 sampai fitur "Kas Keluar" benar-benar dibangun.
    $pengeluaran_tunai = 0;

    // Kalkulasi Saldo Akhir Laci (Hanya Tunai)
    $saldo_akhir_sistem = ($kas->saldo_awal + $pemasukan->tunai_masuk) - $pengeluaran_tunai;

    return view('kasir.preview_closing', compact('kas', 'pemasukan', 'pengeluaran_tunai', 'saldo_akhir_sistem'));
}

public function simpanClosing(Request $request)
{
    $id_kas = $request->id_kas;
    $saldo_fisik = str_replace('.', '', $request->saldo_fisik);

    $kas = DB::table('kas_harian')->where('id_kas', $id_kas)->first();
    
    // Hitung selisih antara fisik di laci dengan hitungan sistem
    $selisih = $saldo_fisik - $request->saldo_sistem;

    DB::table('kas_harian')->where('id_kas', $id_kas)->update([
        'total_tunai_masuk' => $request->tunai_masuk,
        'total_transfer_masuk' => $request->transfer_masuk,
        'total_tunai_keluar' => $request->tunai_keluar,
        'saldo_akhir' => $request->saldo_sistem, // Saldo akhir sistem
        'saldo_fisik' => $saldo_fisik,
        'selisih' => $selisih,
        'status_lock' => 'closed',
        'waktu_tutup' => now(),
        'updated_at' => now()
    ]);

    return redirect()->route('kasir.kelola')->with('success', 'Kas Berhasil Ditutup dan Dikunci.');
}
    public function checkNewTransaction()
    {
        $count = DB::table('transaksi')->where('tgl_order', date('Y-m-d'))->where('status_pembayaran', 'Belum Bayar')->count();
        return response()->json(['total' => $count]);
    }
}