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
            ->leftJoin('users', 'transaksi.id_user_kasir', '=', 'users.id_user')
            ->leftJoin('karyawan', 'users.id_karyawan', '=', 'karyawan.id_karyawan')
            ->select(
                'transaksi.id_transaksi',
                'transaksi.nomor_nota',
                'transaksi.no_po',
                'transaksi.tipe_order',
                'transaksi.tgl_order',
                'transaksi.created_at',
                'transaksi.total_bayar',
                'transaksi.status_pembayaran',
                'transaksi.status_nota',
                'transaksi.metode_pembayaran',
                'konsumen.nama_konsumen',
                'golongan_konsumen.nama_golongan',
                DB::raw('COALESCE(karyawan.nama_karyawan, users.username) as nama_operator')
            )
            // Piutang RIIL per nota = total_bayar dikurangi yang sudah
            // dibayar (pembayaran_riwayat) - sebelumnya resume "Total
            // Piutang" memakai total_bayar PENUH untuk nota berstatus
            // bukan Lunas, jadi overstate untuk nota yang sudah DP
            // sebagian. Kolom ini dipakai baik di tabel maupun resume.
            ->selectSub(function ($q) {
                $q->from('pembayaran_riwayat')
                    ->selectRaw('COALESCE(SUM(nominal), 0)')
                    ->whereColumn('pembayaran_riwayat.id_transaksi', 'transaksi.id_transaksi');
            }, 'total_dibayar');

        // Default rekap HARI INI SAJA - berlaku sama di semua laporan,
        // supaya begitu dibuka langsung menampilkan pergerakan hari itu
        // tanpa perlu isi filter dulu.
        $tglDari = $request->filled('tgl_dari') ? $request->tgl_dari : now()->format('Y-m-d');
        $tglSampai = $request->filled('tgl_sampai') ? $request->tgl_sampai : now()->format('Y-m-d');
        $query->whereDate('transaksi.tgl_order', '>=', $tglDari)
              ->whereDate('transaksi.tgl_order', '<=', $tglSampai);

        if ($request->filled('status_pembayaran')) {
            $query->where('transaksi.status_pembayaran', $request->status_pembayaran);
        }
        if ($request->filled('id_golongan')) {
            $query->where('konsumen.id_golongan', $request->id_golongan);
        }
        // Sebelumnya text-input dicocokkan LIKE ke nama_konsumen (rawan
        // typo/salah pilih kalau nama mirip) - diganti pilih pasti lewat
        // id_konsumen dari dropdown Select2 (live search) di baris ini.
        if ($request->filled('id_konsumen')) {
            $query->where('transaksi.id_konsumen', $request->id_konsumen);
        }

        // Resume dihitung dari query YANG SAMA (sebelum pagination) supaya konsisten dengan filter.
        // Nota yang sudah dibatalkan tetap ditampilkan di listing (untuk jejak
        // audit, ditandai badge Dibatalkan) tapi dikeluarkan dari resume angka
        // supaya total transaksi/piutang tidak ikut menghitung order yang
        // sudah batal.
        $resumeQuery = (clone $query)->where('transaksi.status_nota', '!=', 'Dibatalkan');
        $jumlahTransaksi = $resumeQuery->count();
        $totalTransaksi = (clone $resumeQuery)->sum('transaksi.total_bayar');
        $totalPiutang = (clone $resumeQuery)
            ->where('transaksi.status_pembayaran', '!=', 'Lunas')
            ->get()
            ->sum(fn ($t) => max(0, $t->total_bayar - $t->total_dibayar));

        // Breakdown per Divisi/Mesin (proxy kategori - lihat catatan di
        // laporan.order.blade.php, tabel produk tidak punya kolom
        // kategori) - biaya cetak/design/finishing dijumlah dari
        // transaksi_detail, DIBATASI ke nota yang sama persis dengan hasil
        // filter resume di atas supaya kedua bagian laporan selalu sinkron.
        $idNotaSesuaiFilter = (clone $resumeQuery)->pluck('transaksi.id_transaksi');
        $breakdownDivisi = DB::table('transaksi_detail')
            ->leftJoin('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->leftJoin('mesin', 'produk.id_mesin', '=', 'mesin.id_mesin')
            ->whereIn('transaksi_detail.id_transaksi', $idNotaSesuaiFilter)
            ->select(
                DB::raw("COALESCE(mesin.nama_mesin, 'Lainnya') as divisi"),
                DB::raw('SUM(transaksi_detail.biaya_cetak) as total_biaya_cetak'),
                DB::raw('SUM(transaksi_detail.biaya_desain) as total_biaya_desain'),
                DB::raw('SUM(transaksi_detail.biaya_finishing) as total_biaya_finishing')
            )
            ->groupBy(DB::raw("COALESCE(mesin.nama_mesin, 'Lainnya')"))
            ->orderByDesc(DB::raw('SUM(transaksi_detail.biaya_cetak)'))
            ->get();

        // Paginasi lebih kecil dari laporan lain (10, bukan 20) - tiap nota
        // sekarang tampil sebagai blok PENUH (header + tabel item + total),
        // bukan satu baris ringkas, supaya satu halaman tidak kepanjangan.
        $data = $query->orderByDesc('transaksi.tgl_order')->paginate(10)->withQueryString();

        // Detail item per nota - HANYA untuk nota di halaman yang sedang
        // tampil (bukan seluruh hasil filter) supaya query tetap ringan.
        // SELALU ditampilkan penuh (bukan lagi expand/collapse) sesuai
        // permintaan user - rincian konsumen & item pesanan harus langsung
        // terlihat semua tanpa perlu klik.
        $idNotaHalamanIni = collect($data->items())->pluck('id_transaksi');
        $itemPerNota = DB::table('transaksi_detail')
            ->leftJoin('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->leftJoin('jasa', 'transaksi_detail.id_jasa', '=', 'jasa.id_jasa')
            ->whereIn('transaksi_detail.id_transaksi', $idNotaHalamanIni)
            ->select('transaksi_detail.*', 'produk.nama_produk', 'jasa.nama_jasa')
            ->orderBy('transaksi_detail.id_detail')
            ->get()
            ->groupBy('id_transaksi');

        $golongan = DB::table('golongan_konsumen')->orderBy('nama_golongan')->get();
        $konsumenList = DB::table('konsumen')->orderBy('nama_konsumen')->get(['id_konsumen', 'nama_konsumen', 'telepon']);

        return view('laporan.order', compact(
            'data', 'golongan', 'konsumenList', 'jumlahTransaksi', 'totalTransaksi', 'totalPiutang', 'breakdownDivisi', 'itemPerNota',
            'tglDari', 'tglSampai'
        ));
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

        // Default rekap HARI INI SAJA (bukan lagi seluruh riwayat) - berlaku
        // sama di semua laporan, supaya begitu dibuka langsung menampilkan
        // pergerakan hari itu tanpa perlu isi filter dulu.
        $tglDari = $request->filled('tgl_dari') ? $request->tgl_dari : now()->format('Y-m-d');
        $tglSampai = $request->filled('tgl_sampai') ? $request->tgl_sampai : now()->format('Y-m-d');
        $base->whereDate('transaksi.tgl_order', '>=', $tglDari)
             ->whereDate('transaksi.tgl_order', '<=', $tglSampai);

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
        // m2 HANYA dihitung untuk produk metode_hitung='ukuran' (outdoor/
        // area, harga dari panjang x lebar) - produk metode 'quantity'
        // (harga per pcs) tidak punya makna luas, dibiarkan 0.
        //
        // PENTING: m2 SENGAJA TIDAK dijumlahkan lintas divisi jadi satu
        // angka gabungan (permintaan eksplisit user, mengacu ke referensi -
        // tiap divisi/kategori di sana selalu tampil sebagai bagian/blok
        // SENDIRI dengan totalnya masing-masing, tidak pernah digabung jadi
        // satu total besar lintas divisi yang unitnya beda-beda). Baris
        // mentah dikelompokkan PER DIVISI di PHP (bukan cuma di-flatten)
        // supaya tiap divisi punya subtotalnya sendiri di view.
        $ringkasanMentah = (clone $base)
            ->select(
                'mesin.nama_mesin as divisi',
                'produk.nama_produk',
                'produk.metode_hitung',
                DB::raw('COUNT(DISTINCT transaksi_detail.id_transaksi) as total_transaksi'),
                DB::raw('SUM(transaksi_detail.qty) as total_order'),
                DB::raw('SUM(COALESCE(transaksi_detail.qty_cetak, 0)) as total_cetak'),
                DB::raw("SUM(CASE WHEN produk.metode_hitung = 'ukuran' THEN (transaksi_detail.p_ukuran * transaksi_detail.l_ukuran / 10000) * transaksi_detail.qty ELSE 0 END) as total_m2_order"),
                DB::raw("SUM(CASE WHEN produk.metode_hitung = 'ukuran' THEN (transaksi_detail.p_ukuran * transaksi_detail.l_ukuran / 10000) * COALESCE(transaksi_detail.qty_cetak, 0) ELSE 0 END) as total_m2_cetak")
            )
            ->groupBy('mesin.nama_mesin', 'produk.nama_produk', 'produk.metode_hitung')
            ->orderBy('produk.nama_produk')
            ->get();

        $ringkasanPerDivisi = $ringkasanMentah
            ->groupBy(fn ($r) => $r->divisi ?? 'Lainnya')
            ->sortKeys()
            ->map(fn ($baris, $namaDivisi) => (object) [
                'divisi' => $namaDivisi,
                'baris' => $baris,
                // Subtotal divisi ini SAJA - qty pcs dan m2 dipisah (bukan
                // dijumlah jadi satu angka) karena satuannya beda.
                'subtotal_qty_order_pcs' => $baris->where('metode_hitung', '!=', 'ukuran')->sum('total_order'),
                'subtotal_qty_cetak_pcs' => $baris->where('metode_hitung', '!=', 'ukuran')->sum('total_cetak'),
                'subtotal_m2_order' => $baris->where('metode_hitung', 'ukuran')->sum('total_m2_order'),
                'subtotal_m2_cetak' => $baris->where('metode_hitung', 'ukuran')->sum('total_m2_cetak'),
            ]);

        // Total keseluruhan LAPORAN (jumlah nota &amp; qty pesanan) tetap
        // ditampilkan di resume atas - ini beda dari m2, memang lazim
        // dijumlahkan lintas divisi (sama seperti "Jumlah Transaksi" di
        // Laporan Order), karena satuannya konsisten (pcs/nota).
        $totalOrderKeseluruhan = $ringkasanMentah->sum('total_order');
        $totalCetakKeseluruhan = $ringkasanMentah->sum('total_cetak');

        // --- DETAIL PER SPK (status_spk sudah diterapkan di $base di atas) ---
        $detail = (clone $base)
            ->select(
                'transaksi_detail.id_detail',
                'transaksi_detail.nama_file',
                'transaksi_detail.qty',
                'transaksi_detail.qty_cetak',
                'transaksi_detail.p_ukuran',
                'transaksi_detail.l_ukuran',
                'transaksi_detail.status_spk',
                'produk.metode_hitung',
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

        return view('laporan.produksi', compact(
            'ringkasanPerDivisi', 'detail', 'divisis', 'totalOrderKeseluruhan', 'totalCetakKeseluruhan',
            'tglDari', 'tglSampai'
        ));
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

        $tglDari = $request->filled('tgl_dari') ? $request->tgl_dari : now()->format('Y-m-d');
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

        $data = $query->orderByDesc('kas_harian.tgl_kas')->paginate(10)->withQueryString();

        // Rincian transaksi manual (pemasukan/pengeluaran non-penjualan) per
        // sesi kas, supaya langsung terlihat semua tanpa perlu klik - pola
        // sama dengan itemPerNota di laporan Order.
        $idSesiHalamanIni = collect($data->items())->pluck('id_kas');
        $manualPerSesi = DB::table('kas_manual')
            ->leftJoin('users', 'kas_manual.id_user', '=', 'users.id_user')
            ->whereIn('kas_manual.id_kas', $idSesiHalamanIni)
            ->select('kas_manual.*', 'users.username as nama_user')
            ->orderBy('kas_manual.waktu')
            ->get()
            ->groupBy('id_kas');

        return view('laporan.kas', compact(
            'data', 'tglDari', 'tglSampai',
            'totalTunaiMasuk', 'totalTransferMasuk', 'totalDebitMasuk', 'totalKeluar', 'totalSelisih', 'jumlahSesi',
            'manualPerSesi'
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
        $query = DB::table('pembelian_bahan')
            ->leftJoin('kedatangan_bahan', 'pembelian_bahan.id_kedatangan', '=', 'kedatangan_bahan.id_kedatangan')
            ->leftJoin('supplier', 'kedatangan_bahan.id_supplier', '=', 'supplier.id_supplier')
            ->select('pembelian_bahan.*', 'supplier.nama_supplier');

        $tglDari = $request->filled('tgl_dari') ? $request->tgl_dari : now()->format('Y-m-d');
        $tglSampai = $request->filled('tgl_sampai') ? $request->tgl_sampai : now()->format('Y-m-d');
        $query->whereDate('pembelian_bahan.tgl_transaksi', '>=', $tglDari)->whereDate('pembelian_bahan.tgl_transaksi', '<=', $tglSampai);

        if ($request->filled('status_pembayaran')) {
            $query->where('pembelian_bahan.status_pembayaran', $request->status_pembayaran);
        }
        if ($request->filled('tipe_pembelian')) {
            $query->where('pembelian_bahan.tipe_pembelian', $request->tipe_pembelian);
        }

        $ringkasanQuery = clone $query;
        $totalBelanja = (clone $ringkasanQuery)->sum('pembelian_bahan.grand_total');
        $totalHutang = (clone $ringkasanQuery)->where('pembelian_bahan.status_pembayaran', '!=', 'Lunas')->sum('pembelian_bahan.grand_total');
        $jumlahFaktur = (clone $ringkasanQuery)->count();

        $data = $query->orderByDesc('pembelian_bahan.tgl_transaksi')->paginate(10)->withQueryString();

        // Rincian bahan yang dibeli per faktur, supaya langsung terlihat
        // semua tanpa perlu klik - pola sama dengan itemPerNota di laporan
        // Order.
        $idFakturHalamanIni = collect($data->items())->pluck('id_pembelian');
        $itemPerFaktur = DB::table('pembelian_bahan_detail')
            ->leftJoin('bahan_baku', 'pembelian_bahan_detail.id_bahan', '=', 'bahan_baku.id_bahan')
            ->whereIn('pembelian_bahan_detail.id_pembelian', $idFakturHalamanIni)
            ->select('pembelian_bahan_detail.*', 'bahan_baku.nama_bahan', 'bahan_baku.satuan_stok')
            ->orderBy('pembelian_bahan_detail.id_detail')
            ->get()
            ->groupBy('id_pembelian');

        return view('laporan.belanja-bahan', compact(
            'data', 'tglDari', 'tglSampai', 'totalBelanja', 'totalHutang', 'jumlahFaktur', 'itemPerFaktur'
        ));
    }

    /**
     * Laporan Order Jersey - pola sama dengan laporan Order percetakan
     * biasa (resume Jumlah Order/Total Transaksi/Total Piutang + breakdown
     * + detail per order), tapi TERPISAH karena modul jersey punya tabel
     * &amp; alur sendiri (jersey_orders, bukan transaksi). Piutang pakai
     * accessor sisa_tagihan (total - deposit, deposit = ledger riil dari
     * jersey_order_payments) - sudah akurat sejak awal, beda dengan
     * laporan Order biasa yang baru diperbaiki di method order() di atas.
     */
    public function orderJersey(Request $request)
    {
        $query = \App\Models\JerseyOrder::with(['konsumen', 'designer']);

        // Default rekap HARI INI SAJA - berlaku sama di semua laporan.
        $tglDari = $request->filled('tgl_dari') ? $request->tgl_dari : now()->format('Y-m-d');
        $tglSampai = $request->filled('tgl_sampai') ? $request->tgl_sampai : now()->format('Y-m-d');
        $query->whereDate('order_date', '>=', $tglDari)
              ->whereDate('order_date', '<=', $tglSampai);

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }
        if ($request->filled('jersey_designer_id')) {
            $query->where('jersey_designer_id', $request->jersey_designer_id);
        }
        if ($request->filled('id_konsumen')) {
            $query->where('id_konsumen', $request->id_konsumen);
        }

        // Resume &amp; breakdown dihitung dari SELURUH hasil filter (bukan cuma
        // halaman yang tampil) - pola sama dengan laporan Order/Kas/Belanja
        // Bahan, supaya angkanya benar-benar total, bukan per-halaman.
        $semuaSesuaiFilter = (clone $query)->get();
        $jumlahOrder = $semuaSesuaiFilter->count();
        $totalTransaksi = $semuaSesuaiFilter->sum('total');
        $totalPiutang = $semuaSesuaiFilter->sum(fn ($o) => $o->sisa_tagihan);

        $breakdownDesainer = $semuaSesuaiFilter
            ->groupBy(fn ($o) => $o->designer->name ?? ($o->designer_name ?: 'Belum Ditentukan'))
            ->map(fn ($grup, $nama) => (object) [
                'nama' => $nama,
                'jumlah_order' => $grup->count(),
                'total' => $grup->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();

        $idOrderSesuaiFilter = $semuaSesuaiFilter->pluck('id');
        $breakdownKategori = DB::table('jersey_order_lines')
            ->leftJoin('jersey_categories', 'jersey_order_lines.jersey_category_id', '=', 'jersey_categories.id')
            ->whereIn('jersey_order_lines.jersey_order_id', $idOrderSesuaiFilter)
            ->select(
                DB::raw("COALESCE(jersey_categories.name, 'Belum Ditentukan') as kategori"),
                DB::raw('SUM(jersey_order_lines.qty) as total_qty'),
                DB::raw('SUM(jersey_order_lines.subtotal) as total_subtotal')
            )
            ->groupBy(DB::raw("COALESCE(jersey_categories.name, 'Belum Ditentukan')"))
            ->orderByDesc(DB::raw('SUM(jersey_order_lines.subtotal)'))
            ->get();

        $data = $query->orderByDesc('order_date')->paginate(10)->withQueryString();

        // Detail barang per order - hanya untuk order di halaman yang
        // sedang tampil, sama seperti pola detail-per-nota di laporan
        // Order percetakan biasa.
        $idOrderHalamanIni = collect($data->items())->pluck('id');
        $linePerOrder = \App\Models\JerseyOrderLine::whereIn('jersey_order_id', $idOrderHalamanIni)
            ->orderBy('id')
            ->get()
            ->groupBy('jersey_order_id');

        $designers = \App\Models\JerseyDesigner::orderBy('name')->get();
        $konsumenList = DB::table('konsumen')->orderBy('nama_konsumen')->get(['id_konsumen', 'nama_konsumen', 'telepon']);

        return view('laporan.order-jersey', compact(
            'data', 'designers', 'konsumenList', 'jumlahOrder', 'totalTransaksi', 'totalPiutang',
            'breakdownDesainer', 'breakdownKategori', 'linePerOrder', 'tglDari', 'tglSampai'
        ));
    }
}
