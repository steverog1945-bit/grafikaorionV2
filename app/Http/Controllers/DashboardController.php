<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard murni membaca data yang sudah ada (transaksi, transaksi_detail,
     * kas_harian, pembayaran_riwayat) - tidak ada angka statis/dummy dan tidak
     * ada tabel/kolom baru. Beberapa catatan jujur soal keterbatasan data:
     *
     * - transaksi_detail TIDAK punya kolom timestamp sama sekali, jadi "selesai
     *   hari ini" tidak bisa dihitung akurat (kita hanya tahu status TERKINI,
     *   bukan kapan statusnya berubah). Bagian status produksi karena itu
     *   ditampilkan sebagai snapshot langsung (live saat ini), bukan "hari ini".
     * - Metrik yang benar-benar berbasis tanggal (order masuk, pemasukan kas)
     *   dihitung dari kolom timestamp yang memang ada (tgl_order, tgl_bayar).
     */
    public function index()
    {
        $today = now()->format('Y-m-d');

        // --- ORDER & NOTA HARI INI ---
        $orderHariIni = DB::table('transaksi')->whereDate('tgl_order', $today)->count();
        $nilaiOrderHariIni = DB::table('transaksi')->whereDate('tgl_order', $today)->sum('total_bayar');
        $itemSpkHariIni = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->whereDate('transaksi.tgl_order', $today)
            ->count();

        // --- STATUS PRODUKSI (snapshot langsung dari transaksi_detail.status_spk) ---
        $statusSpk = DB::table('transaksi_detail')
            ->select('status_spk', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status_spk')
            ->pluck('jumlah', 'status_spk');

        $spkAntre = (int) ($statusSpk['Antre'] ?? 0);
        // Disamakan dengan filter default ProduksiController@index ('progress' =
        // Antre+Desain+Cetak) supaya angka di tile "Produksi Berjalan" selalu
        // cocok dengan jumlah baris yang muncul saat tile itu diklik. Sebelumnya
        // tile hanya menghitung Desain+Cetak (tanpa Antre) sementara link-nya ke
        // produksi.index() tanpa filter menampilkan ketiganya - jumlah di tile
        // selalu lebih kecil dari isi halaman yang dituju.
        $spkProses = (int) (($statusSpk['Antre'] ?? 0) + ($statusSpk['Desain'] ?? 0) + ($statusSpk['Cetak'] ?? 0));
        $spkSelesai = (int) ($statusSpk['Selesai'] ?? 0);
        $spkBatal = (int) ($statusSpk['Batal'] ?? 0);

        $urgentAktif = DB::table('transaksi_detail')
            ->where('status_urgent', 1)
            ->whereNotIn('status_spk', ['Selesai', 'Batal'])
            ->count();

        // --- COLLECTING (agregasi per nota, sama seperti CollectingController) ---
        $collectingRaw = DB::table('transaksi')
            ->join('transaksi_detail', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->select(
                'transaksi.id_transaksi',
                DB::raw('COUNT(transaksi_detail.id_detail) as total_item'),
                DB::raw("SUM(CASE WHEN transaksi_detail.status_spk IN ('Selesai','Batal') THEN 1 ELSE 0 END) as item_kelar")
            )
            ->groupBy('transaksi.id_transaksi')
            ->get();

        $notaLengkap = $collectingRaw->filter(fn ($r) => $r->total_item > 0 && $r->item_kelar == $r->total_item)->count();
        $notaBelumLengkap = $collectingRaw->count() - $notaLengkap;

        // --- KEUANGAN ---
        $piutang = DB::table('transaksi')->where('status_pembayaran', '!=', 'Lunas')->sum('total_bayar');
        $piutangCount = DB::table('transaksi')->where('status_pembayaran', '!=', 'Lunas')->count();
        $kasMasukHariIni = DB::table('pembayaran_riwayat')->whereDate('tgl_bayar', $today)->sum('nominal');

        $kasAktif = DB::table('kas_harian')
            ->where('tgl_kas', $today)
            ->where('status_lock', 'open')
            ->where('id_user', auth()->id())
            ->first();

        // --- DAFTAR AKSI CEPAT ---
        $urgentList = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->where('transaksi_detail.status_urgent', 1)
            ->whereNotIn('transaksi_detail.status_spk', ['Selesai', 'Batal'])
            ->select('transaksi_detail.id_detail', 'transaksi_detail.nama_file', 'transaksi_detail.status_spk', 'konsumen.nama_konsumen')
            ->orderByDesc('transaksi_detail.id_detail')
            ->limit(5)
            ->get();

        $orderTerbaru = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->select('transaksi.id_transaksi', 'transaksi.nomor_nota', 'transaksi.tgl_order', 'transaksi.total_bayar', 'transaksi.status_pembayaran', 'konsumen.nama_konsumen')
            ->orderByDesc('transaksi.id_transaksi')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'orderHariIni', 'nilaiOrderHariIni', 'itemSpkHariIni',
            'spkAntre', 'spkProses', 'spkSelesai', 'spkBatal', 'urgentAktif',
            'notaLengkap', 'notaBelumLengkap',
            'piutang', 'piutangCount', 'kasMasukHariIni', 'kasAktif',
            'urgentList', 'orderTerbaru'
        ));
    }
}
