{{-- =========================================================================
   BARIS KEDUA — Tema Premium ("Champagne & Ink")
   =========================================================================
   Super minimalis: [ ≡ Semua Menu ] [tab modul utama …]  — tidak ada dekorasi,
   satu aksen emas pada yang aktif. Klik "Semua Menu" membuka panel mega menu:
   SEMUA grup kerja (Beranda & Audit, Penjualan & Kasir, Produksi, Master,
   Pembelian & Stok, Keuangan, Laporan, Akuntansi, Pesanan Jersey, Sistem).
   Gerbang izin persis sama dengan sidebar ($cekAkses) - role yang tidak boleh
   melihat suatu modul otomatis tidak melihatnya di sini juga.
   Vanilla JS (tanpa jQuery), Esc menutup, klik di luar menutup.
   ========================================================================= --}}
@php
    $isLaporanAktif = Request::is('laporan*', 'eksekutif*', 'audit-owner*', 'kebocoran-tagihan*');
    $isMasterAktif = Request::is('bahan-baku*', 'kartu-stok*', 'stock-opname*', 'kategori*', 'jenis-bahan*', 'supplier*', 'merk*', 'satuan*', 'mesin*', 'gudang*', 'konsumen*', 'golongan*', 'produk*', 'finishing*', 'jasa*', 'setting-harga*', 'harga-promo*', 'platform-belanja*');
    $isKeuAktif = Request::is('kasir*', 'kas-kecil*', 'akun*', 'akuntansi*', 'bank*', 'karyawan*', 'kategori-kas-kecil*', 'penjualan-barang*', 'quotation*', 'penjualan.riwayat*');
    $isProduksiAktif = Request::is('spk*', 'produksi*', 'qc*', 'waste*', 'collecting*');
    $isPenjAktif = Request::is('penjualan*', 'quotation*', 'penjualan-barang*');
    $isPengAktif = Request::is('pengaturan*', 'users*', 'jam-kerja*', 'approval*', 'backup*', 'menu-editor*', 'desainer*', 'accounting-period*', 'setting-akses*');

    $tabUtama = [
        ['label' => 'Dashboard', 'url' => url('/dashboard'), 'ikon' => 'fa-gauge', 'aktif' => Request::is('dashboard', 'dashboard/*'), 'tampil' => true],
        ['label' => 'Penjualan', 'url' => route('penjualan.create'), 'ikon' => 'fa-cart-shopping', 'aktif' => $isPenjAktif, 'tampil' => $cekAkses(3) || $cekAkses(41) || $cekAkses(58)],
        ['label' => 'Produksi', 'url' => route('produksi.index'), 'ikon' => 'fa-industry', 'aktif' => $isProduksiAktif, 'tampil' => $cekAkses(23)],
        ['label' => 'Master', 'url' => url('/bahan-baku'), 'ikon' => 'fa-cubes', 'aktif' => $isMasterAktif, 'tampil' => $cekAkses(1) || $cekAkses(10) || $cekAkses(13) || $cekAkses(15) || $cekAkses(27) || $cekAkses(30) || $cekAkses(31) || $cekAkses(33) || $cekAkses(68)],
        ['label' => 'Keuangan', 'url' => url('/kasir/monitoring'), 'ikon' => 'fa-money-bill-wave', 'aktif' => $isKeuAktif, 'tampil' => $cekAkses(16) || $cekAkses(39) || $cekAkses(35) || $cekAkses(36) || $cekAkses(45) || $cekAkses(11)],
        ['label' => 'Laporan', 'url' => route('laporan.order'), 'ikon' => 'fa-chart-line', 'aktif' => $isLaporanAktif, 'tampil' => $cekAkses(25) || $cekAkses(26) || $cekAkses(29) || $cekAkses(40) || $cekAkses(43) || $cekAkses(44) || $cekAkses(46) || $cekAkses(47) || $cekAkses(65) || $cekAkses(60)],
        ['label' => 'Jersey', 'url' => url('/jersey'), 'ikon' => 'fa-shirt', 'aktif' => Request::is('jersey', 'jersey/*'), 'tampil' => $cekAkses(38) || $cekAkses(42)],
        ['label' => 'Pengaturan', 'url' => url('/pengaturan-aplikasi'), 'ikon' => 'fa-sliders', 'aktif' => $isPengAktif, 'tampil' => $cekAkses(37) || $cekAkses(6) || $cekAkses(50) || $cekAkses(52) || $cekAkses(4) || $cekAkses(63)],
    ];
    $tabUtama = array_values(array_filter($tabUtama, fn ($t) => $t['tampil']));

    // (URL, label, ikon, tampil-sebagai-izin). Semua memakai $cekAkses yang
    // sama dengan sidebar - beda tampilan, bukan beda izin.
    $grupMega = [
        ['judul' => 'Beranda & Audit', 'ikon' => 'fa-chart-pie', 'item' => [
            ['url' => url('/dashboard'), 'label' => 'Dashboard', 'ikon' => 'fa-house', 'izin' => true],
            ['url' => route('eksekutif.dashboard'), 'label' => 'Dashboard Eksekutif', 'ikon' => 'fa-gauge-high', 'izin' => $cekAkses(60)],
            ['url' => route('audit-owner.index'), 'label' => 'Audit Bulanan Owner', 'ikon' => 'fa-clipboard-check', 'izin' => $cekAkses(60)],
            ['url' => route('laporan.kebocoran-tagihan'), 'label' => 'Kebocoran Tagihan', 'ikon' => 'fa-droplet', 'izin' => $cekAkses(60)],
            ['url' => route('audit-log.index'), 'label' => 'Log Aktivitas', 'ikon' => 'fa-clock-rotate-left', 'izin' => $cekAkses(60) || $cekAkses(49)],
        ]],
        ['judul' => 'Penjualan & Kasir', 'ikon' => 'fa-cart-shopping', 'item' => [
            ['url' => route('penjualan.create'), 'label' => 'Transaksi', 'ikon' => 'fa-cash-register', 'izin' => $cekAkses(3) || $cekAkses(41)],
            ['url' => url('/quotation'), 'label' => 'Quotation', 'ikon' => 'fa-file-invoice', 'izin' => $cekAkses(3) || $cekAkses(41) || $cekAkses(58)],
            ['url' => route('penjualan-barang.create'), 'label' => 'Penjualan Barang', 'ikon' => 'fa-box-open', 'izin' => $cekAkses(48)],
            ['url' => route('penjualan.riwayat'), 'label' => 'Riwayat Nota', 'ikon' => 'fa-clock-rotate-left', 'izin' => $cekAkses(3) || $cekAkses(61)],
            ['url' => url('/kasir/monitoring'), 'label' => 'Kasir', 'ikon' => 'fa-money-bill-wave', 'izin' => $cekAkses(16)],
        ]],
        ['judul' => 'Produksi', 'ikon' => 'fa-industry', 'item' => [
            ['url' => route('spk.index'), 'label' => 'SPK', 'ikon' => 'fa-box', 'izin' => $cekAkses(22)],
            ['url' => route('produksi.index'), 'label' => 'Produksi', 'ikon' => 'fa-gears', 'izin' => $cekAkses(23)],
            ['url' => url('/qc'), 'label' => 'QC & Rework', 'ikon' => 'fa-microscope', 'izin' => $cekAkses(23) || $cekAkses(56)],
            ['url' => url('/waste'), 'label' => 'Waste Management', 'ikon' => 'fa-trash-can', 'izin' => $cekAkses(1) || $cekAkses(23) || $cekAkses(57)],
            ['url' => route('collecting.index'), 'label' => 'Collecting', 'ikon' => 'fa-inbox', 'izin' => $cekAkses(24)],
        ]],
        ['judul' => 'Pembelian & Stok', 'ikon' => 'fa-truck-ramp-box', 'item' => [
            ['url' => route('pembelian-bahan.index'), 'label' => 'Pembelian Bahan', 'ikon' => 'fa-cart-flatbed', 'izin' => $cekAkses(20)],
            ['url' => url('/purchase-request'), 'label' => 'Purchase Request', 'ikon' => 'fa-file-circle-plus', 'izin' => $cekAkses(1) || $cekAkses(20) || $cekAkses(54)],
            ['url' => url('/purchase-order'), 'label' => 'Purchase Order', 'ikon' => 'fa-file-contract', 'izin' => $cekAkses(20) || $cekAkses(55)],
            ['url' => url('/kedatangan-bahan'), 'label' => 'Kedatangan Bahan', 'ikon' => 'fa-dolly', 'izin' => $cekAkses(17)],
            ['url' => url('/bahan-baku'), 'label' => 'Bahan Baku', 'ikon' => 'fa-cubes', 'izin' => $cekAkses(1)],
            ['url' => route('stock-opname.index'), 'label' => 'Stock Opname', 'ikon' => 'fa-clipboard-list', 'izin' => $cekAkses(1) || $cekAkses(53)],
            ['url' => route('gudang.index'), 'label' => 'Gudang', 'ikon' => 'fa-warehouse', 'izin' => $cekAkses(33)],
        ]],
        ['judul' => 'Master', 'ikon' => 'fa-layer-group', 'item' => [
            ['url' => route('kategori.index'), 'label' => 'Kategori', 'ikon' => 'fa-tags', 'izin' => $cekAkses(15)],
            ['url' => route('jenis-bahan.index'), 'label' => 'Jenis Bahan', 'ikon' => 'fa-swatchbook', 'izin' => $cekAkses(31)],
            ['url' => url('/merk'), 'label' => 'Merk', 'ikon' => 'fa-bookmark', 'izin' => $cekAkses(30)],
            ['url' => url('/satuan'), 'label' => 'Satuan', 'ikon' => 'fa-ruler', 'izin' => $cekAkses(10)],
            ['url' => url('/mesin'), 'label' => 'Aset Mesin', 'ikon' => 'fa-print', 'izin' => $cekAkses(27)],
            ['url' => url('/konsumen'), 'label' => 'Konsumen', 'ikon' => 'fa-users', 'izin' => $cekAkses(7)],
            ['url' => route('konsumen-deposit.index'), 'label' => 'Deposit Konsumen', 'ikon' => 'fa-vault', 'izin' => $cekAkses(34)],
            ['url' => url('/golongan'), 'label' => 'Golongan Konsumen', 'ikon' => 'fa-id-card', 'izin' => $cekAkses(8) || $cekAkses(12) || $cekAkses(18)],
            ['url' => url('/produk'), 'label' => 'Produk', 'ikon' => 'fa-box', 'izin' => $cekAkses(13)],
            ['url' => url('/finishing'), 'label' => 'Finishing', 'ikon' => 'fa-scissors', 'izin' => $cekAkses(21)],
            ['url' => route('jasa.index'), 'label' => 'Jasa', 'ikon' => 'fa-wrench', 'izin' => $cekAkses(32)],
            ['url' => route('jasa-jahit.index'), 'label' => 'Jasa Jahit', 'ikon' => 'fa-shirt', 'izin' => $cekAkses(68)],
            ['url' => url('/setting-harga'), 'label' => 'Setting Harga', 'ikon' => 'fa-tags', 'izin' => $cekAkses(14)],
            ['url' => url('/harga-promo'), 'label' => 'Harga Promo', 'ikon' => 'fa-percent', 'izin' => $cekAkses(28)],
            ['url' => url('/supplier'), 'label' => 'Supplier', 'ikon' => 'fa-truck', 'izin' => $cekAkses(9)],
            ['url' => route('platform-belanja.index'), 'label' => 'Platform Belanja', 'ikon' => 'fa-store', 'izin' => $cekAkses(9) || $cekAkses(62)],
        ]],
        ['judul' => 'Keuangan', 'ikon' => 'fa-money-bill-wave', 'item' => [
            ['url' => route('kas-kecil.index'), 'label' => 'Kas Kecil', 'ikon' => 'fa-wallet', 'izin' => $cekAkses(39)],
            ['url' => route('kategori-kas-kecil.index'), 'label' => 'Kategori Kas Kecil', 'ikon' => 'fa-list', 'izin' => $cekAkses(45)],
            ['url' => url('/bank'), 'label' => 'Rekening Bank', 'ikon' => 'fa-building-columns', 'izin' => $cekAkses(11)],
            ['url' => url('/karyawan'), 'label' => 'Karyawan', 'ikon' => 'fa-id-badge', 'izin' => $cekAkses(5)],
        ]],
        ['judul' => 'Laporan', 'ikon' => 'fa-chart-line', 'item' => [
            ['url' => route('laporan.order'), 'label' => 'Laporan Order', 'ikon' => 'fa-file-lines', 'izin' => $cekAkses(25)],
            ['url' => route('laporan.produksi'), 'label' => 'Laporan Produksi', 'ikon' => 'fa-industry', 'izin' => $cekAkses(26)],
            ['url' => route('laporan.kas'), 'label' => 'Laporan Kas', 'ikon' => 'fa-money-bill', 'izin' => $cekAkses(29)],
            ['url' => route('laporan.belanja-bahan'), 'label' => 'Belanja Bahan', 'ikon' => 'fa-cart-flatbed', 'izin' => $cekAkses(40)],
            ['url' => route('laporan.hutang'), 'label' => 'Hutang Pemasok', 'ikon' => 'fa-file-invoice-dollar', 'izin' => $cekAkses(40)],
            ['url' => route('laporan.order-jersey'), 'label' => 'Pesanan Jersey', 'ikon' => 'fa-shirt', 'izin' => $cekAkses(43)],
            ['url' => route('laporan.piutang'), 'label' => 'Piutang', 'ikon' => 'fa-hand-holding-dollar', 'izin' => $cekAkses(44)],
            ['url' => route('laporan.stok-bahan'), 'label' => 'Stok Bahan Baku', 'ikon' => 'fa-boxes-stacked', 'izin' => $cekAkses(46)],
            ['url' => route('laporan.pemakaian-bahan'), 'label' => 'Pemakaian Bahan', 'ikon' => 'fa-chart-pie', 'izin' => $cekAkses(46)],
            ['url' => route('laporan.evaluasi-master'), 'label' => 'Lembar Evaluasi Master', 'ikon' => 'fa-clipboard-check', 'izin' => $cekAkses(47)],
            ['url' => route('laporan.biaya-tinta'), 'label' => 'Biaya Tinta', 'ikon' => 'fa-droplet', 'izin' => $cekAkses(47)],
            ['url' => route('laporan.hpp'), 'label' => 'HPP & Margin', 'ikon' => 'fa-percent', 'izin' => $cekAkses(47)],
            ['url' => route('laporan.profitabilitas'), 'label' => 'Profitabilitas', 'ikon' => 'fa-chart-simple', 'izin' => $cekAkses(47)],
            ['url' => route('laporan.job-costing'), 'label' => 'Job Costing', 'ikon' => 'fa-stopwatch', 'izin' => $cekAkses(47)],
            ['url' => route('laporan.biaya-desain'), 'label' => 'Biaya Desain', 'ikon' => 'fa-pen-ruler', 'izin' => $cekAkses(65)],
        ]],
        ['judul' => 'Akuntansi', 'ikon' => 'fa-scale-balanced', 'item' => [
            ['url' => route('akun.index'), 'label' => 'Akun (CoA)', 'ikon' => 'fa-sitemap', 'izin' => $cekAkses(35)],
            ['url' => route('akuntansi.jurnal'), 'label' => 'Jurnal Umum', 'ikon' => 'fa-book', 'izin' => $cekAkses(36)],
            ['url' => route('accounting-period.index'), 'label' => 'Periode Akuntansi', 'ikon' => 'fa-lock', 'izin' => $cekAkses(51) || $isAdministrator],
        ]],
        ['judul' => 'Pesanan Jersey', 'ikon' => 'fa-shirt', 'item' => [
            ['url' => url('/jersey'), 'label' => 'Daftar Pesanan', 'ikon' => 'fa-list', 'izin' => $cekAkses(38)],
            ['url' => route('jersey.orders.create'), 'label' => 'Pesanan Jersey Baru', 'ikon' => 'fa-plus', 'izin' => $cekAkses(42) || $cekAkses(38)],
            ['url' => url('/jersey/master'), 'label' => 'Data Master Jersey', 'ikon' => 'fa-gears', 'izin' => $cekAkses(38) && ! \App\Support\Izin::terkecualikanPath(Auth::user(), 'jersey/master')],
            ['url' => url('/jersey/keuangan'), 'label' => 'Keuangan Jersey', 'ikon' => 'fa-money-bill-transfer', 'izin' => $cekAkses(38)],
        ]],
        ['judul' => 'Sistem', 'ikon' => 'fa-sliders', 'item' => [
            ['url' => url('/users'), 'label' => 'Pengguna', 'ikon' => 'fa-user-group', 'izin' => $cekAkses(6)],
            ['url' => url('/pengaturan-aplikasi'), 'label' => 'Pengaturan Aplikasi', 'ikon' => 'fa-gear', 'izin' => $cekAkses(37)],
            ['url' => route('jam-kerja.index'), 'label' => 'Jam Kerja', 'ikon' => 'fa-calendar-days', 'izin' => $cekAkses(37) || $cekAkses(63)],
            ['url' => route('desainer.pengaturan'), 'label' => 'Bagi Hasil Desainer', 'ikon' => 'fa-pen-nib', 'izin' => $cekAkses(66)],
            ['url' => route('approval.index'), 'label' => 'Approval', 'ikon' => 'fa-shield-halved', 'izin' => $cekAkses(50) || $isAdministrator],
            ['url' => route('backup.index'), 'label' => 'Cadangan & Pemulihan', 'ikon' => 'fa-floppy-disk', 'izin' => $cekAkses(52) || $isAdministrator],
        ]],
    ];
@endphp

<nav class="premium-bar2" aria-label="Navigasi modul — tema premium">
    <div class="premium-bar2-inner">
        <button type="button" id="premium-semua-menu" class="premium-menu-btn" aria-expanded="false" aria-controls="premium-mega">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/></svg>
            <span>Semua Menu</span>
        </button>
        <span class="premium-bar2-sep" aria-hidden="true"></span>
        <div class="premium-tabs" role="navigation" aria-label="Modul utama">
            @foreach($tabUtama as $tab)
                <a href="{{ $tab['url'] }}" class="premium-tab {{ $tab['aktif'] ? 'is-active' : '' }}">
                    <i class="fas {{ $tab['ikon'] }}" aria-hidden="true"></i><span>{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- MEGA MENU - panel penuh, super minimalis: 10 kolom grup, satu aksen --}}
    <div id="premium-mega" class="premium-mega" role="dialog" aria-modal="false" aria-label="Semua menu" hidden>
        <div class="premium-mega-grid">
            @foreach($grupMega as $grup)
                @php $itemTampil = collect($grup['item'])->filter(fn ($i) => $i['izin'])->values(); @endphp
                @if($itemTampil->isNotEmpty())
                <section class="premium-mega-grup">
                    <h3 class="premium-mega-judul"><i class="fas {{ $grup['ikon'] }}" aria-hidden="true"></i>{{ $grup['judul'] }}</h3>
                    <ul class="premium-mega-list">
                        @foreach($itemTampil as $i)
                            <li><a href="{{ $i['url'] }}" class="premium-mega-link"><i class="fas {{ $i['ikon'] }}" aria-hidden="true"></i><span>{{ $i['label'] }}</span></a></li>
                        @endforeach
                    </ul>
                </section>
                @endif
            @endforeach
        </div>
    </div>
</nav>
<script>
// Tema Premium - baris kedua nav: buka/tutup mega menu, Esc menutup, klik di luar menutup.
(function () {
    var btn = document.getElementById('premium-semua-menu');
    var panel = document.getElementById('premium-mega');
    if (!btn || !panel) return;
    function set(terbuka) {
        panel.hidden = !terbuka;
        btn.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
        document.body.classList.toggle('premium-mega-open', terbuka);
        if (terbuka) { var first = panel.querySelector('a'); if (first) first.focus(); } else { btn.focus(); }
    }
    btn.addEventListener('click', function () { set(panel.hidden); });
    document.addEventListener('click', function (e) {
        if (panel.hidden) return;
        if (e.target === btn || btn.contains(e.target) || panel.contains(e.target)) return;
        set(false);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) set(false);
    });
})();
</script>
