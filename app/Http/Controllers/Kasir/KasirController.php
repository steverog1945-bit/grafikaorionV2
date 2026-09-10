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

        if ($request->filled('tgl_mulai')) {
            $query->whereDate('transaksi.tgl_order', '>=', $request->tgl_mulai);
        }
        if ($request->filled('tgl_selesai')) {
            $query->whereDate('transaksi.tgl_order', '<=', $request->tgl_selesai);
        }

        $transaksi = $query->orderBy('transaksi.updated_at', 'desc')->paginate(10)->withQueryString();

        return view('kasir.monitoring_transaksi', compact('transaksi', 'waitingCount'));
    }

    /**
     * Closing cepat lewat modal di Kelola Kas (beda dari alur preview di
     * closing-preview -> simpanClosing, yang menampilkan rincian dulu sebelum
     * dikunci). Dipakai oleh tombol "Closing Kas Sekarang" di kelola_kas.blade.php.
     * Kedua alur ini sengaja dipertahankan berdampingan karena sama-sama nyata
     * dipakai di UI - bukan disatukan di sini tanpa sepengetahuan pemilik aplikasi.
     */
    public function prosesClosing(Request $request)
    {
        DB::beginTransaction();
        try {
            $id_kas = $request->id_kas;
            $saldo_fisik = (float) str_replace('.', '', $request->saldo_fisik ?? 0);

            if ($saldo_fisik < 0) {
                throw new \Exception('Saldo fisik tidak boleh negatif.');
            }

            $kas = DB::table('kas_harian')->where('id_kas', $id_kas)->lockForUpdate()->first();

            if (!$kas || $kas->status_lock === 'closed') {
                throw new \Exception('Sesi kas sudah ditutup atau tidak ditemukan.');
            }

            // Rincian tunai/transfer DIHITUNG ULANG dari pembayaran_riwayat (sama
            // seperti simpanClosing()) dan disimpan ke kolom total_tunai_masuk/
            // total_transfer_masuk/total_tunai_keluar. Sebelumnya kolom-kolom ini
            // tidak pernah diisi oleh alur closing yang benar-benar terhubung ke
            // tombol "CLOSING KAS SEKARANG", sehingga nota cetak closing
            // (print_closing.blade.php) selalu menampilkan Rp 0 untuk semua
            // rincian pemasukan/pengeluaran walau uang sungguhan sudah masuk.
            $pemasukan = DB::table('pembayaran_riwayat')
                ->whereDate('tgl_bayar', $kas->tgl_kas)
                ->select(
                    DB::raw("SUM(CASE WHEN metode = 'Tunai' THEN nominal ELSE 0 END) as tunai_masuk"),
                    DB::raw("SUM(CASE WHEN metode = 'Transfer' THEN nominal ELSE 0 END) as transfer_masuk")
                )->first();

            $tunaiMasuk = (float) ($pemasukan->tunai_masuk ?? 0);
            $transferMasuk = (float) ($pemasukan->transfer_masuk ?? 0);
            $tunaiKeluar = 0; // Belum ada fitur Kas Keluar.
            $saldoAkhirSistem = $kas->saldo_awal + $tunaiMasuk - $tunaiKeluar;
            $selisih = $saldo_fisik - $saldoAkhirSistem;

            DB::table('kas_harian')->where('id_kas', $id_kas)->update([
                'total_tunai_masuk' => $tunaiMasuk,
                'total_transfer_masuk' => $transferMasuk,
                'total_tunai_keluar' => $tunaiKeluar,
                'saldo_akhir' => $saldoAkhirSistem,
                'saldo_fisik' => $saldo_fisik,
                'selisih' => $selisih,
                'status_lock' => 'closed',
                'waktu_tutup' => now(),
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
        $banks = DB::table('bank_perusahaan')->orderBy('nama_bank')->get();

        return view('kasir.form_pembayaran', compact('transaksi', 'items', 'totalTerbayar', 'sisaTagihan', 'banks'));
    }

    public function prosesBayar(Request $request)
    {
        DB::beginTransaction();
        try {
            $id_transaksi = $request->id_transaksi;
            $uangDiterimaSekarang = (float) (str_replace('.', '', $request->jumlah_bayar) ?: 0);
            $diskon = (float) str_replace('.', '', $request->diskon ?? 0);
            $metode = $request->metode_pembayaran;

            if ($uangDiterimaSekarang < 0) {
                throw new \Exception("Jumlah bayar tidak boleh negatif.");
            }
            if ($diskon < 0) {
                throw new \Exception("Diskon tidak boleh negatif.");
            }

            // id_bank hanya relevan untuk Transfer - divalidasi ke tabel
            // bank_perusahaan supaya id yang dipalsukan dari client tidak
            // lolos ke foreign key pembayaran_riwayat.id_bank.
            $idBank = null;
            if ($metode === 'Transfer' && $request->filled('id_bank')) {
                $idBank = (int) $request->id_bank;
                if (!DB::table('bank_perusahaan')->where('id_bank', $idBank)->exists()) {
                    throw new \Exception("Rekening bank tujuan tidak ditemukan.");
                }
            }

            // 1. Update Detail Barang. Kasir memang sengaja diberi wewenang untuk
            // menyesuaikan harga di sini (lihat label "Penyesuaian Harga" di form) -
            // itu TIDAK dihapus. Yang dijaga hanya batas wajarnya: harga tidak boleh
            // negatif, dan qty dipakai dari data transaksi_detail yang tersimpan
            // (bukan dari field tersembunyi di request) supaya tidak bisa dikalikan
            // dengan qty palsu untuk memperbesar/memperkecil subtotal di luar
            // penyesuaian harga yang sesungguhnya dimaksud.
            if ($request->items) {
                foreach ($request->items as $id_detail => $data) {
                    $detailRow = DB::table('transaksi_detail')
                        ->where('id_detail', $id_detail)
                        ->where('id_transaksi', $id_transaksi)
                        ->first();

                    if (!$detailRow) {
                        continue;
                    }

                    $harga_bersih = (float) str_replace('.', '', $data['harga'] ?? 0);

                    if ($harga_bersih < 0) {
                        throw new \Exception("Harga unit tidak boleh negatif.");
                    }

                    DB::table('transaksi_detail')->where('id_detail', $id_detail)->update([
                        'subtotal' => $harga_bersih * $detailRow->qty
                    ]);
                }
            }

            $totalNotaBaru = DB::table('transaksi_detail')->where('id_transaksi', $id_transaksi)->sum('subtotal');

            if ($diskon > $totalNotaBaru) {
                throw new \Exception("Diskon tidak boleh lebih besar dari total nota.");
            }

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
                    'id_bank'      => $idBank,
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
    $saldo_fisik = (float) str_replace('.', '', $request->saldo_fisik ?? 0);

    if ($saldo_fisik < 0) {
        return back()->with('error', 'Saldo fisik tidak boleh negatif.');
    }

    DB::beginTransaction();
    try {
        // Lock row supaya dua klik "Verifikasi & Kunci Kasir" yang hampir
        // bersamaan tidak sama-sama lolos menutup kas yang sama.
        $kas = DB::table('kas_harian')->where('id_kas', $id_kas)->lockForUpdate()->first();

        if (!$kas) {
            throw new \Exception('Sesi kas tidak ditemukan.');
        }
        if ($kas->status_lock === 'closed') {
            throw new \Exception('Sesi kas ini sudah ditutup sebelumnya.');
        }

        // Total pemasukan DIHITUNG ULANG dari pembayaran_riwayat di server
        // (bukan dipercaya dari hidden field), sama seperti previewClosing() -
        // supaya laporan yang tersimpan permanen tidak bisa diselipi angka
        // yang tidak sesuai riwayat pembayaran sesungguhnya.
        $pemasukan = DB::table('pembayaran_riwayat')
            ->whereDate('tgl_bayar', $kas->tgl_kas)
            ->select(
                DB::raw("SUM(CASE WHEN metode = 'Tunai' THEN nominal ELSE 0 END) as tunai_masuk"),
                DB::raw("SUM(CASE WHEN metode = 'Transfer' THEN nominal ELSE 0 END) as transfer_masuk")
            )->first();

        $tunaiMasuk = (float) ($pemasukan->tunai_masuk ?? 0);
        $transferMasuk = (float) ($pemasukan->transfer_masuk ?? 0);
        $tunaiKeluar = 0; // Belum ada fitur Kas Keluar - lihat catatan di previewClosing().
        $saldoAkhirSistem = $kas->saldo_awal + $tunaiMasuk - $tunaiKeluar;
        $selisih = $saldo_fisik - $saldoAkhirSistem;

        DB::table('kas_harian')->where('id_kas', $id_kas)->update([
            'total_tunai_masuk' => $tunaiMasuk,
            'total_transfer_masuk' => $transferMasuk,
            'total_tunai_keluar' => $tunaiKeluar,
            'saldo_akhir' => $saldoAkhirSistem,
            'saldo_fisik' => $saldo_fisik,
            'selisih' => $selisih,
            'status_lock' => 'closed',
            'waktu_tutup' => now(),
            'updated_at' => now()
        ]);

        DB::commit();
        return redirect()->route('kasir.kelola')->with('success', 'Kas Berhasil Ditutup dan Dikunci. Selisih: Rp ' . number_format($selisih, 0, ',', '.'));

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}

/**
 * Cetak nota transaksi. Format ditentukan lewat query string (?format=thermal
 * untuk struk 58mm, default 'a4' untuk invoice biasa) - satu view yang sama,
 * cuma CSS lebar kertasnya beda, supaya tidak perlu 2 template terpisah.
 */
public function printOpsi(Request $request, $id)
{
    $transaksi = DB::table('transaksi')
        ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
        ->leftJoin('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
        ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
        ->where('transaksi.id_transaksi', $id)
        ->select(
            'transaksi.*',
            'konsumen.nama_konsumen',
            'konsumen.alamat',
            'konsumen.telepon',
            DB::raw('COALESCE(karyawan.nama_karyawan, users.username) as nama_kasir')
        )
        ->first();

    if (!$transaksi) {
        abort(404, 'Transaksi tidak ditemukan.');
    }

    $items = DB::table('transaksi_detail')
        ->where('id_transaksi', $id)
        ->select('nama_file', 'qty', 'subtotal')
        ->get();

    $totalTerbayar = DB::table('pembayaran_riwayat')->where('id_transaksi', $id)->sum('nominal') ?? 0;
    $format = $request->query('format', 'a4') === 'thermal' ? 'thermal' : 'a4';

    return view('kasir.print_nota', compact('transaksi', 'items', 'totalTerbayar', 'format'));
}

/**
 * Cetak laporan closing kasir yang SUDAH ditutup (butuh saldo_fisik/selisih,
 * jadi hanya bisa dicetak untuk sesi yang status_lock-nya 'closed').
 */
public function printReport($id, $format)
{
    $kas = DB::table('kas_harian')
        ->leftJoin('users', 'kas_harian.id_user', '=', 'users.id_user')
        ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
        ->where('kas_harian.id_kas', $id)
        ->select('kas_harian.*', DB::raw('COALESCE(karyawan.nama_karyawan, users.username) as nama_kasir'))
        ->first();

    if (!$kas) {
        abort(404, 'Sesi kas tidak ditemukan.');
    }

    $format = $format === 'thermal' ? 'thermal' : 'a4';

    return view('kasir.print_closing', compact('kas', 'format'));
}

/**
 * Riwayat proses sebuah nota - dari data yang benar-benar ada (kapan order
 * dibuat, riwayat pembayaran, status SPK tiap item saat ini). transaksi_detail
 * tidak punya kolom timestamp sama sekali, jadi "kapan status SPK berubah"
 * tidak bisa direkonstruksi - yang ditampilkan adalah status TERKINI per item,
 * bukan linimasa perubahan status (jangan berpura-pura tahu yang tidak diketahui).
 */
public function getHistory($id)
{
    $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();

    if (!$transaksi) {
        return response()->json(['status' => 'error', 'msg' => 'Transaksi tidak ditemukan.'], 404);
    }

    $items = DB::table('transaksi_detail')
        ->where('id_transaksi', $id)
        ->select('nama_file', 'status_spk', 'qty', 'qty_cetak')
        ->get();

    $pembayaran = DB::table('pembayaran_riwayat')
        ->where('id_transaksi', $id)
        ->orderBy('tgl_bayar')
        ->select('nominal', 'metode', 'tgl_bayar', 'keterangan')
        ->get();

    return response()->json([
        'status' => 'success',
        'nomor_nota' => $transaksi->nomor_nota,
        'tgl_order' => $transaksi->tgl_order,
        'status_pembayaran' => $transaksi->status_pembayaran,
        'items' => $items,
        'pembayaran' => $pembayaran,
    ]);
}
    public function checkNewTransaction()
    {
        $count = DB::table('transaksi')->where('tgl_order', date('Y-m-d'))->where('status_pembayaran', 'Belum Bayar')->count();
        return response()->json(['total' => $count]);
    }
}