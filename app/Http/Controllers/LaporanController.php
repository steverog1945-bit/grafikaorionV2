<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Laporan Rekap Transaksi Order - baca langsung dari transaksi, tidak ada
     * tabel rekap terpisah supaya tidak mungkin selisih dengan data aslinya.
     */
    public function order(Request $request)
    {
        $query = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->join('golongan_konsumen', 'konsumen.id_golongan', '=', 'golongan_konsumen.id_golongan')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.nomor_nota',
                'transaksi.tgl_order',
                'transaksi.total_bayar',
                'transaksi.status_pembayaran',
                'transaksi.status_nota',
                'konsumen.nama_konsumen',
                'golongan_konsumen.nama_golongan'
            );

        if ($request->filled('tgl_dari')) {
            $query->whereDate('transaksi.tgl_order', '>=', $request->tgl_dari);
        }
        if ($request->filled('tgl_sampai')) {
            $query->whereDate('transaksi.tgl_order', '<=', $request->tgl_sampai);
        }
        if ($request->filled('status_pembayaran')) {
            $query->where('transaksi.status_pembayaran', $request->status_pembayaran);
        }
        if ($request->filled('id_golongan')) {
            $query->where('konsumen.id_golongan', $request->id_golongan);
        }
        if ($request->filled('konsumen')) {
            $query->where('konsumen.nama_konsumen', 'like', '%' . $request->konsumen . '%');
        }

        // Resume dihitung dari query YANG SAMA (sebelum pagination) supaya konsisten dengan filter.
        // Nota yang sudah dibatalkan tetap ditampilkan di listing (untuk jejak
        // audit, ditandai badge Dibatalkan) tapi dikeluarkan dari resume angka
        // supaya total transaksi/piutang tidak ikut menghitung order yang
        // sudah batal.
        $resumeQuery = (clone $query)->where('transaksi.status_nota', '!=', 'Dibatalkan');
        $jumlahTransaksi = $resumeQuery->count();
        $totalTransaksi = (clone $resumeQuery)->sum('transaksi.total_bayar');
        $totalPiutang = (clone $resumeQuery)->where('transaksi.status_pembayaran', '!=', 'Lunas')->sum('transaksi.total_bayar');

        $data = $query->orderByDesc('transaksi.tgl_order')->paginate(20)->withQueryString();

        $golongan = DB::table('golongan_konsumen')->orderBy('nama_golongan')->get();

        return view('laporan.order', compact('data', 'golongan', 'jumlahTransaksi', 'totalTransaksi', 'totalPiutang'));
    }

    /**
     * Laporan Produksi - gabungan ringkasan per divisi/produk (sesuai kebutuhan
     * rekap produk) dan detail per SPK (sesuai kebutuhan laporan produksi),
     * karena keduanya membaca baris transaksi_detail yang persis sama, hanya
     * beda cara mengelompokkannya.
     */
    public function produksi(Request $request)
    {
        $base = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->join('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->leftJoin('mesin', 'produk.id_mesin', '=', 'mesin.id_mesin')
            ->leftJoin('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
            ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan');

        if ($request->filled('tgl_dari')) {
            $base->whereDate('transaksi.tgl_order', '>=', $request->tgl_dari);
        }
        if ($request->filled('tgl_sampai')) {
            $base->whereDate('transaksi.tgl_order', '<=', $request->tgl_sampai);
        }
        if ($request->filled('id_mesin')) {
            $base->where('produk.id_mesin', $request->id_mesin);
        }
        if ($request->filled('status_spk')) {
            // Diterapkan ke $base (sebelum di-clone) supaya ringkasan dan
            // detail SELALU menghitung dari himpunan baris yang sama persis.
            // Sebelumnya filter ini hanya ditempel ke $detailQuery di bawah,
            // jadi kartu "Ringkasan per Divisi & Produk" (termasu total
            // keseluruhannya) tetap menghitung SEMUA status walau user sudah
            // memfilter status di halaman yang sama - laporan yang dicetak
            // jadi tidak sinkron antara ringkasan dan detailnya sendiri.
            $base->where('transaksi_detail.status_spk', $request->status_spk);
        }

        // --- RINGKASAN PER DIVISI & PRODUK ---
        $ringkasan = (clone $base)
            ->select(
                'mesin.nama_mesin as divisi',
                'produk.nama_produk',
                DB::raw('COUNT(DISTINCT transaksi_detail.id_transaksi) as total_transaksi'),
                DB::raw('SUM(transaksi_detail.qty) as total_order'),
                DB::raw('SUM(COALESCE(transaksi_detail.qty_cetak, 0)) as total_cetak')
            )
            ->groupBy('mesin.nama_mesin', 'produk.nama_produk')
            ->orderBy('mesin.nama_mesin')
            ->orderBy('produk.nama_produk')
            ->get();

        $totalOrderKeseluruhan = $ringkasan->sum('total_order');
        $totalCetakKeseluruhan = $ringkasan->sum('total_cetak');

        // --- DETAIL PER SPK (status_spk sudah diterapkan di $base di atas) ---
        $detail = (clone $base)
            ->select(
                'transaksi_detail.id_detail',
                'transaksi_detail.nama_file',
                'transaksi_detail.qty',
                'transaksi_detail.qty_cetak',
                'transaksi_detail.status_spk',
                'transaksi.nomor_nota',
                'transaksi.tgl_order',
                'konsumen.nama_konsumen',
                'mesin.nama_mesin as divisi',
                DB::raw('COALESCE(karyawan.nama_karyawan, users.username) as desainer')
            )
            ->orderByDesc('transaksi_detail.id_detail')
            ->paginate(20)
            ->withQueryString();

        $divisis = DB::table('mesin')->orderBy('nama_mesin')->get();

        return view('laporan.produksi', compact('ringkasan', 'detail', 'divisis', 'totalOrderKeseluruhan', 'totalCetakKeseluruhan'));
    }

    /**
     * Laporan Kas - rekap sesi kas_harian lintas tanggal. Sebelumnya kas
     * hanya bisa dilihat satu sesi per satu sesi (cetak closing per sesi
     * atau halaman Kelola Kas yang cuma menampilkan hari ini) - tidak ada
     * cara melihat tren/rekap kas lintas periode. Murni baca dari
     * kas_harian yang sudah lengkap, tidak ada tabel/kolom baru.
     */
    public function kas(Request $request)
    {
        $query = DB::table('kas_harian')
            ->leftJoin('users', 'kas_harian.id_user', '=', 'users.id_user')
            ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
            ->select(
                'kas_harian.*',
                DB::raw('COALESCE(karyawan.nama_karyawan, users.username) as nama_kasir')
            );

        $tglDari = $request->filled('tgl_dari') ? $request->tgl_dari : now()->startOfMonth()->format('Y-m-d');
        $tglSampai = $request->filled('tgl_sampai') ? $request->tgl_sampai : now()->format('Y-m-d');

        $query->whereDate('kas_harian.tgl_kas', '>=', $tglDari)
              ->whereDate('kas_harian.tgl_kas', '<=', $tglSampai);

        if ($request->filled('status_lock')) {
            $query->where('kas_harian.status_lock', $request->status_lock);
        }

        // Ringkasan dihitung dari query YANG SAMA (sebelum pagination) supaya
        // konsisten dengan filter - pola yang sama dipakai laporan Order.
        $ringkasanQuery = clone $query;
        $totalTunaiMasuk = (clone $ringkasanQuery)->sum('kas_harian.total_tunai_masuk');
        $totalTransferMasuk = (clone $ringkasanQuery)->sum('kas_harian.total_transfer_masuk');
        $totalDebitMasuk = (clone $ringkasanQuery)->sum('kas_harian.total_debit_masuk');
        $totalKeluar = (clone $ringkasanQuery)->sum('kas_harian.total_tunai_keluar');
        $totalSelisih = (clone $ringkasanQuery)->sum('kas_harian.selisih');
        $jumlahSesi = (clone $ringkasanQuery)->count();

        $data = $query->orderByDesc('kas_harian.tgl_kas')->paginate(20)->withQueryString();

        return view('laporan.kas', compact(
            'data', 'tglDari', 'tglSampai',
            'totalTunaiMasuk', 'totalTransferMasuk', 'totalDebitMasuk', 'totalKeluar', 'totalSelisih', 'jumlahSesi'
        ));
    }

    /**
     * Laporan Belanja Bahan - pembukuan pembelian bahan baku TERPISAH dari
     * Laporan Kas (kas fisik laci kasir) dan Keuangan Jersey. Pembelian
     * bahan sebelumnya cuma terlihat lewat jurnal akuntansi (akun 1200/
     * 2000) tanpa daftar operasional per faktur - laporan ini murni baca
     * dari pembelian_bahan, tidak ada tabel/kolom baru.
     */
    public function belanjaBahan(Request $request)
    {
        $query = DB::table('pembelian_bahan');

        $tglDari = $request->filled('tgl_dari') ? $request->tgl_dari : now()->startOfMonth()->format('Y-m-d');
        $tglSampai = $request->filled('tgl_sampai') ? $request->tgl_sampai : now()->format('Y-m-d');
        $query->whereDate('tgl_transaksi', '>=', $tglDari)->whereDate('tgl_transaksi', '<=', $tglSampai);

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }
        if ($request->filled('tipe_pembelian')) {
            $query->where('tipe_pembelian', $request->tipe_pembelian);
        }

        $ringkasanQuery = clone $query;
        $totalBelanja = (clone $ringkasanQuery)->sum('grand_total');
        $totalHutang = (clone $ringkasanQuery)->where('status_pembayaran', '!=', 'Lunas')->sum('grand_total');
        $jumlahFaktur = (clone $ringkasanQuery)->count();

        $data = $query->orderByDesc('tgl_transaksi')->paginate(20)->withQueryString();

        return view('laporan.belanja-bahan', compact(
            'data', 'tglDari', 'tglSampai', 'totalBelanja', 'totalHutang', 'jumlahFaktur'
        ));
    }
}
