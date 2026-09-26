<?php

namespace App\Http\Controllers;

use App\Services\KasSesiService;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard dulunya SATU tampilan sama untuk semua role, termasuk angka
     * uang (nilai order, kas masuk, piutang) yang seharusnya cuma boleh
     * dibaca Administrator & Kasir. Sekarang dipecah per role:
     *
     * - Administrator, Kasir       -> dashboard penuh (semua nominal & aksi).
     * - Operator Desain            -> alur order/produksi + status pembayaran
     *   (badge Lunas/DP/Belum Bayar boleh terlihat), TAPI TIDAK ADA satu pun
     *   angka Rupiah (nilai order, kas masuk, piutang) - role ini cuma input
     *   order & lihat status, tidak pernah menyentuh transaksi/nominal.
     * - Operator Produksi ("operator cetak") -> SPK-sentris: daftar SPK yang
     *   perlu dikerjakan langsung tersaji (bukan cuma angka), dibatasi ke
     *   mesin/divisi tanggung jawabnya sendiri (pola sama dengan
     *   SpkController::index()), tanpa nominal sama sekali.
     * - Gudang                     -> ringkasan stok bahan baku (habis/
     *   menipis/belum ada batas minimal) & kedatangan bahan terbaru, tanpa
     *   nominal (harga rata-rata TIDAK ditampilkan).
     *
     * Semua tetap murni baca data yang sudah ada - tidak ada tabel/kolom
     * baru, tidak ada angka statis/dummy.
     */
    public function index()
    {
        $user = auth()->user();
        $namaRole = optional($user->role)->nama_role;

        if ($namaRole === 'Owner') {
            return redirect()->route('audit-owner.index');
        }

        return match ($namaRole) {
            'Operator Desain' => $this->dashboardDesain(),
            'Operator Produksi' => $this->dashboardProduksi($user),
            'Gudang' => $this->dashboardGudang(),
            default => $this->dashboardFull(), // Administrator, Kasir, & role lain yang belum diklasifikasi eksplisit
        };
    }

    /**
     * Dashboard penuh - Administrator & Kasir (satu-satunya role yang boleh
     * lihat nominal, sesuai instruksi eksplisit). Logika sama persis dengan
     * versi sebelum dashboard dipecah per role.
     */
    private function dashboardFull()
    {
        $today = now()->format('Y-m-d');

        $orderHariIni = DB::table('transaksi')->whereDate('tgl_order', $today)->where('status_nota', '!=', 'Dibatalkan')->count();
        $nilaiOrderHariIni = DB::table('transaksi')->whereDate('tgl_order', $today)->where('status_nota', '!=', 'Dibatalkan')->sum('total_bayar');

        $statusSpk = DB::table('transaksi_detail')
            ->select('status_spk', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status_spk')
            ->pluck('jumlah', 'status_spk');

        $spkAntre = (int) ($statusSpk['Antre'] ?? 0);
        $spkProses = (int) (($statusSpk['Desain'] ?? 0) + ($statusSpk['Cetak'] ?? 0));
        $spkSelesai = (int) ($statusSpk['Selesai'] ?? 0);

        $urgentAktif = DB::table('transaksi_detail')
            ->where('status_urgent', 1)
            ->whereNotIn('status_spk', ['Selesai', 'Batal'])
            ->count();

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

        $piutang = DB::table('transaksi')->where('status_pembayaran', '!=', 'Lunas')->where('status_nota', '!=', 'Dibatalkan')->sum('total_bayar');
        $piutangCount = DB::table('transaksi')->where('status_pembayaran', '!=', 'Lunas')->where('status_nota', '!=', 'Dibatalkan')->count();
        $kasMasukHariIni = DB::table('pembayaran_riwayat')->whereDate('tgl_bayar', $today)->sum('nominal');

        $kasAktifSemua = KasSesiService::aktif();
        $kasAktif = ($kasAktifSemua && (int) $kasAktifSemua->id_user === (int) auth()->id()) ? $kasAktifSemua : null;

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
            ->select('transaksi.id_transaksi', 'transaksi.nomor_nota', 'transaksi.tgl_order', 'transaksi.total_bayar', 'transaksi.status_pembayaran', 'transaksi.status_nota', 'konsumen.nama_konsumen')
            ->orderByDesc('transaksi.id_transaksi')
            ->limit(5)
            ->get();

        // Pembanding hari kemarin + tren 7 hari terakhir (jumlah order & kas masuk per hari).
        $kemarin = now()->subDay()->format('Y-m-d');
        $orderKemarin = DB::table('transaksi')->whereDate('tgl_order', $kemarin)->where('status_nota', '!=', 'Dibatalkan')->count();
        $kasMasukKemarin = (float) DB::table('pembayaran_riwayat')->whereDate('tgl_bayar', $kemarin)->sum('nominal');
        $awal7 = now()->subDays(6)->startOfDay();
        $orderPerHari = DB::table('transaksi')->where('tgl_order', '>=', $awal7)->where('status_nota', '!=', 'Dibatalkan')
            ->selectRaw('DATE(tgl_order) as tgl, COUNT(*) as n')->groupBy('tgl')->pluck('n', 'tgl');
        $kasPerHari = DB::table('pembayaran_riwayat')->where('tgl_bayar', '>=', $awal7)
            ->selectRaw('DATE(tgl_bayar) as tgl, SUM(nominal) as total')->groupBy('tgl')->pluck('total', 'tgl');
        $tren = collect(range(6, 0))->map(function ($i) use ($orderPerHari, $kasPerHari) {
            $d = now()->subDays($i);

            return ['label' => $d->translatedFormat('D'), 'tgl' => $d->format('d M'), 'order' => (int) ($orderPerHari[$d->format('Y-m-d')] ?? 0), 'kas' => (float) ($kasPerHari[$d->format('Y-m-d')] ?? 0)];
        });

        // Pekerjaan yang menunggu tindakan (sama dengan lonceng notifikasi, khusus role ini).
        $perluTindakan = \App\Services\NotifikasiService::untuk(auth()->user())['items'];

        return view('dashboard', compact(
            'orderKemarin', 'kasMasukKemarin', 'tren', 'perluTindakan',
            'orderHariIni', 'nilaiOrderHariIni',
            'spkAntre', 'spkProses', 'spkSelesai', 'urgentAktif',
            'notaLengkap', 'notaBelumLengkap',
            'piutang', 'piutangCount', 'kasMasukHariIni', 'kasAktif',
            'urgentList', 'orderTerbaru'
        ));
    }

    /**
     * Operator Desain - alur order/produksi + status pembayaran (badge
     * saja), TANPA satu pun angka Rupiah. $orderTerbaru SENGAJA tidak
     * meng-select total_bayar sama sekali (bukan cuma disembunyikan di
     * view) supaya tidak ada jalan nominal itu ikut kebawa ke view ini.
     */
    private function dashboardDesain()
    {
        $today = now()->format('Y-m-d');

        $orderHariIni = DB::table('transaksi')->whereDate('tgl_order', $today)->where('status_nota', '!=', 'Dibatalkan')->count();

        $statusSpk = DB::table('transaksi_detail')
            ->select('status_spk', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status_spk')
            ->pluck('jumlah', 'status_spk');

        $spkAntre = (int) ($statusSpk['Antre'] ?? 0);
        $spkProses = (int) (($statusSpk['Desain'] ?? 0) + ($statusSpk['Cetak'] ?? 0));
        $spkSelesai = (int) ($statusSpk['Selesai'] ?? 0);

        $urgentAktif = DB::table('transaksi_detail')
            ->where('status_urgent', 1)
            ->whereNotIn('status_spk', ['Selesai', 'Batal'])
            ->count();

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

        $urgentList = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->where('transaksi_detail.status_urgent', 1)
            ->whereNotIn('transaksi_detail.status_spk', ['Selesai', 'Batal'])
            ->select('transaksi_detail.id_detail', 'transaksi_detail.nama_file', 'transaksi_detail.status_spk', 'konsumen.nama_konsumen')
            ->orderByDesc('transaksi_detail.id_detail')
            ->limit(5)
            ->get();

        // TIDAK ADA total_bayar di select - hanya status pembayaran (badge).
        $orderTerbaru = DB::table('transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->select('transaksi.id_transaksi', 'transaksi.nomor_nota', 'transaksi.tgl_order', 'transaksi.status_pembayaran', 'transaksi.status_nota', 'konsumen.nama_konsumen')
            ->orderByDesc('transaksi.id_transaksi')
            ->limit(6)
            ->get();

        return view('dashboard-desain', compact(
            'orderHariIni', 'spkAntre', 'spkProses', 'spkSelesai', 'urgentAktif',
            'notaLengkap', 'urgentList', 'orderTerbaru'
        ));
    }

    /**
     * Operator Produksi ("operator cetak") - SPK-sentris, dibatasi ke
     * mesin/divisi tanggung jawab karyawan yang login (pola sama persis
     * dengan SpkController::index() - Administrator/karyawan tanpa
     * penugasan mesin lihat semua). Tanpa nominal sama sekali.
     */
    private function dashboardProduksi($user)
    {
        $karyawan = optional($user)->karyawan;
        $mesinDiizinkan = null;
        $namaMesinDiizinkan = null;
        if ($karyawan) {
            $mesinKaryawan = $karyawan->mesin()->get();
            if ($mesinKaryawan->isNotEmpty()) {
                $mesinDiizinkan = $mesinKaryawan->pluck('id_mesin');
                $namaMesinDiizinkan = $mesinKaryawan->pluck('nama_mesin');
            }
        }

        $baseSpk = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->where('transaksi.status_nota', 'Aktif');
        if ($mesinDiizinkan) {
            $baseSpk->whereIn('produk.id_mesin', $mesinDiizinkan);
        }

        $statusSpk = (clone $baseSpk)
            ->select('transaksi_detail.status_spk', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('transaksi_detail.status_spk')
            ->pluck('jumlah', 'status_spk');

        $spkAntre = (int) ($statusSpk['Antre'] ?? 0);
        $spkProses = (int) (($statusSpk['Desain'] ?? 0) + ($statusSpk['Cetak'] ?? 0));
        $spkSelesai = (int) ($statusSpk['Selesai'] ?? 0);

        $urgentAktif = (clone $baseSpk)
            ->where('transaksi_detail.status_urgent', 1)
            ->whereNotIn('transaksi_detail.status_spk', ['Selesai', 'Batal'])
            ->count();

        // Daftar SPK yang PERLU DIKERJAKAN, langsung tersaji (bukan cuma
        // angka) - Antre dulu (siap diambil), baru Desain/Cetak yang
        // urgent (sedang dikerjakan tapi butuh perhatian).
        $spkPerluDikerjakan = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi_detail.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('konsumen', 'transaksi.id_konsumen', '=', 'konsumen.id_konsumen')
            ->join('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->leftJoin('mesin', 'produk.id_mesin', '=', 'mesin.id_mesin')
            ->where('transaksi.status_nota', 'Aktif')
            ->whereIn('transaksi_detail.status_spk', ['Antre', 'Desain', 'Cetak'])
            ->when($mesinDiizinkan, fn ($q) => $q->whereIn('produk.id_mesin', $mesinDiizinkan))
            ->select(
                'transaksi_detail.id_detail', 'transaksi_detail.nama_file', 'transaksi_detail.qty',
                'transaksi_detail.status_spk', 'transaksi_detail.status_urgent',
                'transaksi.nomor_nota', 'konsumen.nama_konsumen',
                DB::raw("COALESCE(mesin.nama_mesin, 'Lainnya') as divisi")
            )
            ->orderByRaw("FIELD(transaksi_detail.status_spk, 'Antre', 'Cetak', 'Desain')")
            ->orderByDesc('transaksi_detail.status_urgent')
            ->orderBy('transaksi_detail.id_detail')
            ->limit(10)
            ->get();

        return view('dashboard-produksi', compact(
            'spkAntre', 'spkProses', 'spkSelesai', 'urgentAktif',
            'spkPerluDikerjakan', 'namaMesinDiizinkan'
        ));
    }

    /**
     * Gudang - ringkasan stok bahan baku (status sama seperti Laporan Stok
     * Bahan Baku) & kedatangan bahan terbaru. Harga rata-rata SENGAJA
     * tidak diambil/ditampilkan (nominal).
     */
    private function dashboardGudang()
    {
        $bahan = DB::table('bahan_baku')
            ->where('status', 'aktif')
            ->select('id_bahan', 'nama_bahan', 'kode_bahan', 'stok', 'stok_minimal', 'satuan_stok')
            ->get()
            ->map(function ($b) {
                $b->status_stok = match (true) {
                    $b->stok <= 0 => 'Habis',
                    $b->stok_minimal > 0 && $b->stok <= $b->stok_minimal => 'Menipis',
                    $b->stok_minimal <= 0 => 'Belum Ada Batas',
                    default => 'Aman',
                };
                return $b;
            });

        $jumlahBahan = $bahan->count();
        $jumlahHabis = $bahan->where('status_stok', 'Habis')->count();
        $jumlahMenipis = $bahan->where('status_stok', 'Menipis')->count();
        $jumlahBelumAdaBatas = $bahan->where('status_stok', 'Belum Ada Batas')->count();

        // Yang paling butuh perhatian (Habis dulu, baru Menipis) - dipakai
        // untuk daftar langsung di dashboard.
        $urutanStatus = ['Habis' => 0, 'Menipis' => 1];
        $bahanPerluPerhatian = $bahan
            ->whereIn('status_stok', ['Habis', 'Menipis'])
            ->sortBy(fn ($b) => [$urutanStatus[$b->status_stok], $b->nama_bahan])
            ->take(8)
            ->values();

        $kedatanganTerbaru = DB::table('kedatangan_bahan')
            ->leftJoin('supplier', 'kedatangan_bahan.id_supplier', '=', 'supplier.id_supplier')
            ->select('kedatangan_bahan.id_kedatangan', 'kedatangan_bahan.no_surat_jalan', 'kedatangan_bahan.tgl_datang', 'kedatangan_bahan.status', 'supplier.nama_supplier')
            ->orderByDesc('kedatangan_bahan.id_kedatangan')
            ->limit(5)
            ->get();

        return view('dashboard-gudang', compact(
            'jumlahBahan', 'jumlahHabis', 'jumlahMenipis', 'jumlahBelumAdaBatas',
            'bahanPerluPerhatian', 'kedatanganTerbaru'
        ));
    }
}
