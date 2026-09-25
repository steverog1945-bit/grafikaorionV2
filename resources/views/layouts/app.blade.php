@php
    // Diambil sekali di sini (bukan per-blok) supaya title tab, brand
    // sidebar, dan topbar mobile semuanya konsisten dari satu sumber -
    // sebelumnya ketiganya hardcode terpisah dengan nilai yang berbeda-beda.
    $appSetting = \App\Models\AppSetting::current();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Tambahkan Meta CSRF --}}
    <title>{{ $appSetting->nama_aplikasi }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
    :root { --sidebar-width: 288px; --sidebar-width-collapsed: 96px; }
    body { font-family: 'Inter', sans-serif; scroll-behavior: smooth; background-color: #f5f7fa; overflow-x: hidden; }

    /* SIDEBAR CORE (Tetap Dipertahankan) */
    aside {
        width: var(--sidebar-width);
        background: linear-gradient(180deg, #0f172a 0%, #131d33 55%, #17223b 100%);
        color: #f1f5f9;
        position: fixed;
        top: 0; bottom: 0; left: 0;
        z-index: 50;
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s ease;
        display: flex;
        flex-direction: column;
        border-right: 1px solid rgba(255,255,255,0.06);
        box-shadow: 8px 0 32px rgba(15,23,42,0.18);
    }

    @media (max-width: 1023px) {
        aside { transform: translateX(-100%); }
        aside.mobile-open { transform: translateX(0); }
        main { margin-left: 0 !important; }
    }

    /* Collapse desktop = ICON-ONLY, bukan sidebar hilang total (dulu
       translateX(-100%) penuh - melanggar prinsip "sidebar tetap harus
       terlihat, minimal ikonnya"). Teks menu/group-header/subtitle brand
       disembunyikan lewat selector generik (bukan nambah class di setiap
       baris menu - menu di file ini ada puluhan baris, nambah class satu-
       satu terlalu berisiko salah satu kelewat) - dipilih berdasarkan
       struktur HTML yang memang sudah konsisten (span di dalam <a> nav,
       div label group pakai tracking-[0.2em]).) */
    body.sidebar-collapsed aside { width: var(--sidebar-width-collapsed); }
    body.sidebar-collapsed main { margin-left: var(--sidebar-width-collapsed); }
    body.sidebar-collapsed aside nav { padding-left: 14px; padding-right: 14px; }
    body.sidebar-collapsed aside nav > a,
    body.sidebar-collapsed aside .menu-group-toggle { justify-content: center; }
    body.sidebar-collapsed aside nav > a { padding: 14px 0; }
    body.sidebar-collapsed aside nav > a span { display: none; }
    body.sidebar-collapsed aside nav > a svg { width: 26px; height: 26px; }
    body.sidebar-collapsed aside .menu-group-items { display: none !important; }
    body.sidebar-collapsed aside .menu-group-toggle > span,
    body.sidebar-collapsed aside .menu-group-toggle > i.fa-chevron-down { display: none; }
    body.sidebar-collapsed aside .menu-group-toggle { padding: 14px 0; margin-top: 6px; border-radius: 14px; border-top: 1px solid rgba(255,255,255,0.07); border-radius: 0; }
    body.sidebar-collapsed aside .sidebar-brand-text,
    body.sidebar-collapsed aside .sidebar-text-label { display: none; }
    body.sidebar-collapsed aside .sidebar-kas-status { justify-content: center; }
    body.sidebar-collapsed aside #sidebarBrandHeader { padding: 18px 10px; }
    body.sidebar-collapsed aside #sidebarCollapseToggle i { transform: rotate(180deg); }

    /* Ikon grup (hanya tampil pada rel ringkas) */
    aside .menu-group-toggle .rail-icon { display: none; }
    body.sidebar-collapsed aside .menu-group-toggle .rail-icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 52px; height: 52px; border-radius: 16px; font-size: 21px;
        color: #cbd5e1; background: rgba(255,255,255,0.05);
        transition: background .2s, color .2s, transform .2s;
    }
    body.sidebar-collapsed aside .menu-group-toggle:hover .rail-icon,
    body.sidebar-collapsed aside .menu-group-toggle.flyout-open .rail-icon { background: rgba(255,255,255,0.14); color: #fff; transform: translateY(-1px); }
    body.sidebar-collapsed aside .menu-group-toggle.has-active .rail-icon {
        background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; box-shadow: 0 8px 20px rgba(99,102,241,0.35);
    }

    /* Tampilan lebar: penanda grup yang berisi halaman aktif */
    aside .menu-group-toggle.has-active > span { color: #fff; }
    aside .menu-group-toggle.has-active > span::after {
        content: ''; display: inline-block; width: 6px; height: 6px; margin-left: 8px; border-radius: 999px;
        background: #818cf8; vertical-align: middle;
    }
    aside nav > a.sb-active::before {
        content: ''; position: absolute; left: -14px; top: 22%; bottom: 22%; width: 4px; border-radius: 0 4px 4px 0; background: #a5b4fc;
    }
    body.sidebar-collapsed aside nav > a.sb-active::before { left: -14px; }
    aside .menu-group-items a.sb-active::before { content: none; }

    /* Flyout grup & tooltip untuk rel ringkas */
    #railFlyout, #railTip { position: fixed; z-index: 70; display: none; }
    #railFlyout {
        min-width: 250px; max-width: 300px; max-height: 78vh; overflow-y: auto; padding: 10px;
        background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 18px;
        box-shadow: 0 24px 60px rgba(2,6,23,0.45);
    }
    #railFlyout .fly-title { padding: 6px 12px 10px; font-size: 11px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #94a3b8; }
    #railFlyout a { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px; color: #e2e8f0; font-size: 14px; font-weight: 500; text-decoration: none; }
    #railFlyout a:hover { background: rgba(255,255,255,0.1); color: #fff; }
    #railFlyout a.fly-active { background: linear-gradient(90deg, #6366f1, #8b5cf6); color: #fff; }
    #railFlyout a svg { width: 20px; height: 20px; flex-shrink: 0; color: #94a3b8; }
    #railFlyout a:hover svg, #railFlyout a.fly-active svg { color: #fff; }
    #railTip {
        padding: 8px 14px; border-radius: 10px; background: #0f172a; color: #fff; font-size: 13px; font-weight: 600;
        box-shadow: 0 10px 30px rgba(2,6,23,0.4); border: 1px solid rgba(255,255,255,0.1); white-space: nowrap; pointer-events: none;
    }

    /* Logo perusahaan - diatur dari Pengaturan Aplikasi (ukuran, gaya latar, mode ringkas) */
    .sidebar-logo { position: relative; display: flex; align-items: center; justify-content: center; max-width: 100%; text-decoration: none; transition: transform .35s ease, background .3s ease; }
    .sidebar-logo .logo-full { display: block; width: auto; height: auto; object-fit: contain; transition: transform .35s ease, filter .35s ease; }
    .sidebar-logo .logo-icon { display: none; }
    .sidebar-logo.logo-kecil .logo-full { max-height: 34px; max-width: 150px; }
    .sidebar-logo.logo-sedang .logo-full { max-height: 46px; max-width: 190px; }
    .sidebar-logo.logo-besar .logo-full { max-height: 60px; max-width: 220px; }
    .sidebar-logo:hover { transform: translateY(-1px); }
    .sidebar-logo:hover .logo-full { transform: scale(1.03); }

    /* Gaya polos: tanpa kotak; kilau lembut di belakang + garis tipis terang supaya tepi logo gelap tetap terbaca */
    .sidebar-logo.logo-gaya-polos { padding: 8px 12px; }
    .sidebar-logo.logo-gaya-polos::before {
        content: ''; position: absolute; inset: -6px -14px; z-index: 0; pointer-events: none;
        background: radial-gradient(closest-side, rgba(129,140,248,0.30), rgba(129,140,248,0) 72%);
    }
    .sidebar-logo.logo-gaya-polos img { position: relative; z-index: 1; filter: drop-shadow(0 0 1px rgba(255,255,255,0.95)) drop-shadow(0 6px 14px rgba(2,6,23,0.45)); }
    /* Gaya kaca */
    .sidebar-logo.logo-gaya-kaca { padding: 10px 16px; border-radius: 18px; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.14); box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 10px 30px rgba(2,6,23,0.25); backdrop-filter: blur(6px); }
    .sidebar-logo.logo-gaya-kaca img { filter: drop-shadow(0 4px 10px rgba(2,6,23,0.35)); }
    /* Gaya putih */
    .sidebar-logo.logo-gaya-putih { padding: 10px 16px; border-radius: 18px; background: #fff; box-shadow: 0 10px 30px rgba(2,6,23,0.35); }

    /* Mode ringkas */
    body.sidebar-collapsed aside .sidebar-logo .logo-full { display: none; }
    body.sidebar-collapsed aside .sidebar-logo .logo-icon { display: block; }
    body.sidebar-collapsed aside .sidebar-logo .logo-ikon-file { width: 52px; height: 52px; object-fit: contain; }
    /* Potong otomatis: tampilkan bagian kiri logo (biasanya lambang) */
    body.sidebar-collapsed aside .sidebar-logo .logo-crop { display: block; width: 46px; height: 54px; overflow: hidden; position: relative; z-index: 1; }
    body.sidebar-collapsed aside .sidebar-logo .logo-crop img { height: 54px; width: auto; max-width: none; display: block; }
    body.sidebar-collapsed aside .sidebar-logo .logo-penuh { max-width: 60px; max-height: 46px; object-fit: contain; }
    body.sidebar-collapsed aside .sidebar-logo.logo-gaya-polos img,
    body.sidebar-collapsed aside .sidebar-logo .logo-icon img { filter: drop-shadow(0 0 1px rgba(255,255,255,0.95)) drop-shadow(0 6px 12px rgba(2,6,23,0.45)); }
    body.sidebar-collapsed aside .sidebar-logo.logo-gaya-kaca,
    body.sidebar-collapsed aside .sidebar-logo.logo-gaya-putih { padding: 8px; border-radius: 16px; }

    /* MAIN - satu-satunya definisi (sebelumnya didefinisikan ulang & bentrok
       di style block kedua di bagian bawah file ini) */
    main {
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        padding: 32px 24px;
        background: #f5f7fa;
        transition: margin-left 0.3s ease, all 0.3s ease;
        display: flex;
        flex-direction: column;
        min-width: 0;
        width: 100%;
    }

    /* Topbar sebelumnya tidak pernah punya definisi CSS sama sekali (class
       "topbar" tidak dipakai di manapun selain di sini) - burger icon &
       judul cuma numpang di elemen tanpa padding/tinggi/alignment eksplisit,
       jadinya terlihat "nempel asal" alih-alih menyatu dengan header. */
    .topbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 24px;
        border-bottom: 1px solid #e2e8f0;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    /* Topbar mobile - sebelumnya sama persis dengan desktop (padding 18px
       24px, nama aplikasi full text tanpa batas lebar) - di layar sempit
       ini gampang mepet/tabrakan dengan avatar & tombol logout di kanan.
       Dirapatkan + judul dibatasi lebar & dipotong elipsis supaya topbar
       tetap satu baris rapi di HP sekecil apa pun. */
    @media (max-width: 639px) {
        .topbar { padding: 12px 16px; gap: 8px; }
        .topbar-app-name { font-size: 13px; max-width: 38vw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .topbar-burger { width: 36px; height: 36px; }
    }

    /* Wadah konten halaman. Sengaja TIDAK diberi warna/card/shadow di sini -
       setiap halaman sudah membangun container & card putihnya sendiri,
       jadi wrapper ini murni pengatur lebar, bukan pembungkus visual. */
    .content-wrapper {
        width: 100%;
        max-width: 100vw;
        margin: 0 auto;
    }

    /* Sisa design tokens (.modern-table, .card, .chip-*, .modern-nav, dst)
       dipindahkan ke resources/css/app.css supaya diproses satu jalur oleh
       Tailwind (beberapa rule di sana memakai @apply yang HANYA berfungsi
       lewat pipeline itu, tidak bisa jalan dari <style> inline seperti ini). */

    /* SIDEBAR SCROLL FIX: aside punya tinggi tetap (top:0;bottom:0) dan nav
       di dalamnya sekarang berisi puluhan menu (SPK, Produksi, Collecting,
       Laporan, Harga Promo, dst semuanya ditambahkan seiring waktu) - tanpa
       overflow di sini, menu-menu di bagian bawah (System Control, Laporan,
       bahkan Logout) terpotong habis oleh viewport dan sama sekali tidak
       bisa dijangkau, oleh role manapun termasuk Administrator. nav diberi
       flex:1 supaya mengisi sisa tinggi aside (setelah header & status kas),
       lalu overflow-y:auto supaya sisanya bisa discroll di dalam sidebar
       sendiri tanpa menggeser halaman utama. */
    #mainSidebar nav {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
    }
    #mainSidebar nav::-webkit-scrollbar { width: 6px; }
    #mainSidebar nav::-webkit-scrollbar-track { background: transparent; }
    #mainSidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 999px; }
    #mainSidebar nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }
</style>
    
</head>
<body class="text-slate-900">
    <script>
        // Terapkan state collapse SEBELUM sidebar dirender (bukan nunggu
        // DOMContentLoaded) supaya tidak ada kedipan "kebuka lebar dulu baru
        // menyempit" tiap pindah halaman (app ini full page reload antar
        // route, jadi state collapse harus dipasang secepat mungkin di
        // setiap load, bukan cuma sekali di SPA).
        try {
            if (window.innerWidth >= 1024 && localStorage.getItem('sidebarCollapsed') === '1') {
                document.body.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    </script>

    <div id="sidebarOverlay" onclick="toggleMobileMenu()"></div>

    <div class="flex min-h-screen">
        <aside id="mainSidebar">
            <div id="sidebarBrandHeader" class="relative px-5 py-5 border-b border-white/10 flex items-center justify-center">
                @php
                    $logoUkuran = in_array($appSetting->logo_ukuran, ['kecil', 'sedang', 'besar'], true) ? $appSetting->logo_ukuran : 'sedang';
                    $logoGaya = in_array($appSetting->logo_gaya, ['polos', 'kaca', 'putih'], true) ? $appSetting->logo_gaya : 'polos';
                    $logoRingkas = $appSetting->logo_ringkas === 'penuh' ? 'penuh' : 'potong';
                    $urlLogo = $appSetting->logo_path ? asset('storage/' . $appSetting->logo_path) : null;
                @endphp
                <a href="{{ url('/dashboard') }}" class="sidebar-logo logo-{{ $logoUkuran }} logo-gaya-{{ $logoGaya }}" title="{{ $appSetting->nama_aplikasi }}">
                    @if($urlLogo)
                        <img class="logo-full" src="{{ $urlLogo }}" alt="Logo {{ $appSetting->nama_aplikasi }}">
                        <span class="logo-icon">
                            @if($appSetting->logo_ikon_path)
                                <img class="logo-ikon-file" src="{{ asset('storage/' . $appSetting->logo_ikon_path) }}" alt="">
                            @elseif($logoRingkas === 'potong')
                                <span class="logo-crop"><img src="{{ $urlLogo }}" alt=""></span>
                            @else
                                <img class="logo-penuh" src="{{ $urlLogo }}" alt="">
                            @endif
                        </span>
                    @else
                        <span class="logo-full text-4xl leading-none">🖨️</span>
                        <span class="logo-icon text-3xl leading-none">🖨️</span>
                    @endif
                </a>
                <button onclick="toggleMobileMenu()" class="lg:hidden absolute top-3 right-3 w-9 h-9 rounded-full bg-white/10 text-slate-200 hover:bg-white/20 sidebar-brand-text" aria-label="Tutup menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>
{{-- Status Kas --}}
<div class="mt-5 px-4">
    @if($kasAktif)
        <div class="sidebar-kas-status {{ $kasBasi ? 'bg-amber-500/15 border-amber-400/40' : 'bg-emerald-500/15 border-emerald-400/30' }} border rounded-xl px-3 py-2.5 flex items-center gap-2.5" title="{{ $kasBasi ? 'Kasir Open lebih dari 24 jam - belum di-closing' : 'Kasir: Open' }}">
            <span class="relative flex h-2.5 w-2.5 shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
            </span>
            <span class="text-xs font-bold {{ $kasBasi ? 'text-amber-300' : 'text-emerald-300' }} uppercase tracking-wider sidebar-text-label">{{ $kasBasi ? 'Kasir Open >24 jam' : 'Kasir Open' }}</span>
        </div>
    @else
        <div class="sidebar-kas-status bg-rose-500/15 border border-rose-400/30 rounded-xl px-3 py-2.5 flex items-center gap-2.5" title="Kasir: Standby">
            <span class="h-2.5 w-2.5 rounded-full bg-rose-400 shrink-0"></span>
            <span class="text-xs font-bold text-rose-300 uppercase tracking-wider sidebar-text-label">Kasir Standby</span>
        </div>
    @endif
</div>
            <nav class="mt-5 px-4 pb-24 space-y-1.5">
    @php
        $userAkses = DB::table('role_permissions')
                        ->where('id_role', Auth::user()->id_role)
                        ->pluck('id_menu')
                        ->toArray();

        // Administrator selalu lolos di sini juga, sama seperti bypass di
        // CheckMenuAccess middleware. Tanpa ini, menu sidebar Administrator
        // bisa ikut hilang kalau grant-nya di role_permissions tidak sengaja
        // tercabut/kosong (mis. dari halaman Setting Akses) - padahal
        // rute-rutenya sendiri tetap bisa diakses karena middleware sudah
        // bypass duluan, jadi menu yang "hilang" itu cuma ilusi UI.
        $isAdministrator = optional(Auth::user()->role)->nama_role === 'Administrator';

        // Menu yang isinya harga/nilai uang disembunyikan untuk role tanpa hak lihat harga
        // (Finishing Produk = 21, Quotation = 58), walau menunya tercentang di Hak Akses.
        $menuBerharga = [21, 58];
        $bolehLihatHargaSidebar = \App\Support\AksesHarga::bolehLihat();

        $cekAkses = function($idMenu) use ($userAkses, $isAdministrator, $menuBerharga, $bolehLihatHargaSidebar) {
            if (!$bolehLihatHargaSidebar && in_array($idMenu, $menuBerharga, true)) {
                return false;
            }

            return $isAdministrator || in_array($idMenu, $userAkses);
        };

        // Helper untuk styling menu agar konsisten
        $menuClass = "flex items-center gap-3.5 px-3.5 py-3 rounded-xl transition-all duration-200 group relative ";
        $activeClass = "sb-active bg-gradient-to-r from-indigo-500 to-violet-500 text-white shadow-lg shadow-indigo-950/40";
        $nonActiveClass = "text-slate-300 hover:bg-white/10 hover:text-white";
        
        $iconClass = "h-[22px] w-[22px] shrink-0 transition-colors ";
        $iconActive = "text-white";
        $iconNonActive = "text-slate-400 group-hover:text-white";
    @endphp

    {{-- DASHBOARD --}}
    <a href="{{ url('/dashboard') }}" 
       class="{{ $menuClass }} {{ Request::is('dashboard*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('dashboard*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
        </svg>
        <span class="text-sm font-medium">Dashboard</span>
    </a>

    @if($isAdministrator)
    <a href="{{ route('eksekutif.dashboard') }}" class="{{ $menuClass }} {{ Request::is('eksekutif*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('eksekutif*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
        </svg>
        <span class="text-sm font-medium">Dashboard Eksekutif</span>
    </a>
    @endif

    @php
        $grpProduksiVisible = $cekAkses(1) || $cekAkses(20) || $cekAkses(17) || $cekAkses(22) || $cekAkses(23) || $cekAkses(24) || $cekAkses(15) || $cekAkses(31) || $cekAkses(30) || $cekAkses(10) || $cekAkses(27) || $cekAkses(33);
        $grpProduksiAktif = Request::is('bahan-baku*') || Request::is('kartu-stok*') || Request::is('stock-opname*')
            || Request::is('pembelian-bahan*') || Request::is('kedatangan-bahan*')
            || Request::is('purchase-request*') || Request::is('purchase-order*') || Request::is('qc*') || Request::is('waste*') || Request::is('design-proof*')
            || request()->routeIs('spk.*') || request()->routeIs('produksi.*') || request()->routeIs('collecting.*')
            || request()->routeIs('kategori.*') || Request::is('jenis-bahan*') || Request::is('merk*')
            || Request::is('satuan*') || Request::is('mesin*') || Request::is('gudang*');
    @endphp
    @if($grpProduksiVisible)
    <button type="button" onclick="toggleMenuGroup(this)" class="menu-group-toggle w-full flex items-center justify-between pt-5 pb-2 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-[0.16em] hover:text-white transition-colors">
        <span>Produksi & Logistik</span>
        <i class="fas fa-chevron-down text-[10px] transition-transform {{ $grpProduksiAktif ? 'rotate-180' : '' }}"></i>
    </button>
    <div class="menu-group-items space-y-1 {{ $grpProduksiAktif ? '' : 'hidden' }}">
    @endif

    @if($cekAkses(1))
    <a href="{{ url('/bahan-baku') }}" class="{{ $menuClass }} {{ Request::is('bahan-baku*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('bahan-baku*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <span class="text-sm font-medium">Bahan Baku</span>
    </a>
    <a href="{{ url('/kartu-stok') }}" class="{{ $menuClass }} {{ Request::is('kartu-stok*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('kartu-stok*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4m0 0l-3-3m3 3l-3 3M5 7v10a2 2 0 002 2h10a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H7a2 2 0 00-2 2z" />
        </svg>
        <span class="text-sm font-medium">Kartu Stok</span>
    </a>
    <a href="{{ route('stock-opname.index') }}" class="{{ $menuClass }} {{ request()->routeIs('stock-opname.*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('stock-opname.*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-7 9l2 2 4-4" />
        </svg>
        <span class="text-sm font-medium">Stock Opname</span>
    </a>
    @endif
    @if($cekAkses(20))
    <a href="{{ route('pembelian-bahan.index') }}"
    class="{{ $menuClass }} {{ Request::is('pembelian-bahan*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg"
            class="{{ $iconClass }} {{ Request::is('pembelian-bahan*') ? $iconActive : $iconNonActive }}"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 14l6-6m0 0l-6-6m6 6H3" />
        </svg>
        <span class="text-sm font-medium">Pembelian Bahan</span>
    </a>
    @endif
    @if($cekAkses(1) || $cekAkses(20))
    <a href="{{ url('/purchase-request') }}" class="{{ $menuClass }} {{ Request::is('purchase-request*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('purchase-request*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6m-6 4h4" />
        </svg>
        <span class="text-sm font-medium">Purchase Request</span>
    </a>
    @endif
    @if($cekAkses(20))
    <a href="{{ url('/purchase-order') }}" class="{{ $menuClass }} {{ Request::is('purchase-order*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('purchase-order*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="text-sm font-medium">Purchase Order</span>
    </a>
    @endif
    @if($cekAkses(17))
<a href="{{ url('/kedatangan-bahan') }}" 
   id="menu-kedatangan-bahan"
   class="{{ $menuClass }} {{ Request::is('kedatangan-bahan*') ? $activeClass : $nonActiveClass }}">

    <svg xmlns="http://www.w3.org/2000/svg"
         class="{{ $iconClass }} {{ Request::is('kedatangan-bahan*') ? $iconActive : $iconNonActive }}"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor">

        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 17l4 4 4-4m0-5l-4-4-4 4m-4 5h16" />
    </svg>

    <span class="text-sm font-medium">
        Kedatangan Bahan
    </span>
</a>
@endif

    @if($cekAkses(22))
    <a href="{{ route('spk.index') }}" class="{{ $menuClass }} {{ request()->routeIs('spk.*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('spk.*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="text-sm font-medium">SPK</span>
    </a>
    @endif

    @if($cekAkses(23))
    <a href="{{ route('produksi.index') }}" class="{{ $menuClass }} {{ request()->routeIs('produksi.*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('produksi.*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V8l-6-6H6a2 2 0 00-2 2v16a2 2 0 002 2h11a2 2 0 002-2zM8 12h8m-8 4h5" />
        </svg>
        <span class="text-sm font-medium">Produksi</span>
    </a>
    @endif

    @if($cekAkses(3) || $cekAkses(23) || $cekAkses(41))
    <a href="{{ url('/design-proof') }}" class="{{ $menuClass }} {{ Request::is('design-proof*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('design-proof*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-sm font-medium">Design Proof</span>
    </a>
    @endif

    @if($cekAkses(23))
    <a href="{{ url('/qc') }}" class="{{ $menuClass }} {{ Request::is('qc*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('qc*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <span class="text-sm font-medium">QC & Rework</span>
    </a>
    @endif

    @if($cekAkses(1) || $cekAkses(23))
    <a href="{{ url('/waste') }}" class="{{ $menuClass }} {{ Request::is('waste*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('waste*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        <span class="text-sm font-medium">Waste Management</span>
    </a>
    @endif

    @if($cekAkses(24))
    <a href="{{ route('collecting.index') }}" class="{{ $menuClass }} {{ request()->routeIs('collecting.*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('collecting.*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span class="text-sm font-medium">Collecting</span>
    </a>
    @endif

    @if($cekAkses(15))
    <a href="{{ route('kategori.index') }}" class="{{ $menuClass }} {{ request()->routeIs('kategori.*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('kategori.*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
        <span class="text-sm font-medium">Kategori Bahan</span>
    </a>
    @endif

    @if($cekAkses(31))
    <a href="{{ route('jenis-bahan.index') }}" class="{{ $menuClass }} {{ Request::is('jenis-bahan*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('jenis-bahan*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
        <span class="text-sm font-medium">Jenis Bahan</span>
    </a>
    @endif

    @if($cekAkses(30))
    <a href="{{ route('merk.index') }}" class="{{ $menuClass }} {{ Request::is('merk*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('merk*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
        <span class="text-sm font-medium">Merk</span>
    </a>
    @endif

    @if($cekAkses(10))
    <a href="{{ url('/satuan') }}" class="{{ $menuClass }} {{ Request::is('satuan*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('satuan*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
        </svg>
        <span class="text-sm font-medium">Satuan & Konversi</span>
    </a>
    @endif

    @if($cekAkses(27))
    <a href="{{ url('/mesin') }}" class="{{ $menuClass }} {{ Request::is('mesin*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('mesin*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="text-sm font-medium">Aset Mesin</span>
    </a>
    @endif

    @if($cekAkses(33))
    <a href="{{ route('gudang.index') }}" class="{{ $menuClass }} {{ Request::is('gudang*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('gudang*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="text-sm font-medium">Gudang</span>
    </a>
    @endif
    @if($grpProduksiVisible)
    </div>
    @endif

    @php
        $grpRelasiVisible = $cekAkses(7) || $cekAkses(34) || $cekAkses(18) || $cekAkses(13) || $cekAkses(21) || $cekAkses(32) || $cekAkses(14) || $cekAkses(28) || $cekAkses(9) || $cekAkses(16);
        $grpRelasiAktif = Request::is('konsumen*') || Request::is('golongan*') || Request::is('produk*')
            || Request::is('finishing*') || Request::is('jasa*') || Request::is('setting-harga*')
            || Request::is('harga-promo*') || Request::is('supplier*') || Request::is('kasir/monitoring*')
            || Request::is('kasir/kelola-kas*');
    @endphp
    @if($grpRelasiVisible)
    <button type="button" onclick="toggleMenuGroup(this)" class="menu-group-toggle w-full flex items-center justify-between pt-5 pb-2 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-[0.16em] hover:text-white transition-colors">
        <span>Relasi Bisnis</span>
        <i class="fas fa-chevron-down text-[10px] transition-transform {{ $grpRelasiAktif ? 'rotate-180' : '' }}"></i>
    </button>
    <div class="menu-group-items space-y-1 {{ $grpRelasiAktif ? '' : 'hidden' }}">
    @endif

    @if($cekAkses(7))
    <a href="{{ url('/konsumen') }}" class="{{ $menuClass }} {{ Request::is('konsumen*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('konsumen*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span class="text-sm font-medium">Data Konsumen</span>
    </a>
    @endif

    @if($cekAkses(34))
    <a href="{{ route('konsumen-deposit.index') }}" class="{{ $menuClass }} {{ Request::is('konsumen-deposit*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('konsumen-deposit*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3v-1m-3 1v-5m11-4V6a2 2 0 00-2-2H5a2 2 0 00-2 2v9a2 2 0 002 2h1" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 17a5 5 0 100-10 5 5 0 000 10z" />
        </svg>
        <span class="text-sm font-medium">Deposit Konsumen</span>
    </a>
    @endif

    @if($cekAkses(18))
<a href="{{ route('golongan.index') }}" 
   class="{{ $menuClass }} {{ Request::is('golongan*') ? $activeClass : $nonActiveClass }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('golongan*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
    <span class="text-sm font-medium">Golongan</span>
</a>
@endif

    @if($cekAkses(13))
    <a href="{{ url('/produk') }}" class="{{ $menuClass }} {{ Request::is('produk*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('produk*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
        </svg>
        <span class="text-sm font-medium">Produk & BOM</span>
    </a>
    @endif

    @if($cekAkses(21))
    <a href="{{ url('/finishing') }}" class="{{ $menuClass }} {{ Request::is('finishing*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('finishing*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
        </svg>
        <span class="text-sm font-medium">Finishing Produk</span>
    </a>
    @endif

    @if($cekAkses(32))
    <a href="{{ route('jasa.index') }}" class="{{ $menuClass }} {{ Request::is('jasa*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('jasa*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
        </svg>
        <span class="text-sm font-medium">Jasa</span>
    </a>
    @endif

    @if($cekAkses(14))
    <a href="{{ url('/setting-harga') }}" class="{{ $menuClass }} {{ Request::is('setting-harga*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('setting-harga*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <span class="text-sm font-medium">Price List & Level</span>
    </a>
    @endif

    @if($cekAkses(28))
    <a href="{{ url('/harga-promo') }}" class="{{ $menuClass }} {{ Request::is('harga-promo*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('harga-promo*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5.586a1 1 0 01.707.293l7.414 7.414a1 1 0 010 1.414l-7.586 7.586a1 1 0 01-1.414 0L4.293 12.293A1 1 0 014 11.586V6a3 3 0 013-3z" />
        </svg>
        <span class="text-sm font-medium">Harga Promo</span>
    </a>
    @endif

    @if($cekAkses(9))
    <a href="{{ url('/supplier') }}" class="{{ $menuClass }} {{ Request::is('supplier*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('supplier*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span class="text-sm font-medium">Supplier</span>
    </a>
    @endif
@if($cekAkses(16))
<a href="{{ url('/kasir/monitoring') }}" 

   class="{{ $menuClass }} {{ Request::is('kasir/monitoring*') ? 'bg-emerald-500/10 text-emerald-400' : $nonActiveClass }} {{ !$kasAktif ? 'opacity-50' : '' }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('kasir/monitoring*') ? 'text-emerald-400' : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
    <span class="text-sm font-medium">Monitoring Kasir</span>
    <span id="badge-notif" class="hidden ml-auto bg-rose-500 text-white text-[10px] px-2 py-0.5 rounded-full animate-bounce">0</span>
</a>

<a href="{{ url('/kasir/kelola-kas') }}" 
   class="{{ $menuClass }} {{ Request::is('kasir/kelola-kas*') ? $activeClass : $nonActiveClass }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('kasir/kelola-kas*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
    <span class="text-sm font-medium">Buka/Tutup Kas</span>
</a>
@endif
    @if($grpRelasiVisible)
    </div>
    @endif

    @php
        $grpFinanceVisible = $cekAkses(3) || $cekAkses(41) || $cekAkses(39) || $cekAkses(45) || $cekAkses(11) || $cekAkses(5) || $cekAkses(48);
        $grpFinanceAktif = Request::is('quotation*') || Request::is('penjualan/kasir*') || Request::is('penjualan-barang*') || Request::is('penjualan/riwayat*')
            || request()->routeIs('kas-kecil.*') || request()->routeIs('kategori-kas-kecil.*')
            || Request::is('bank*') || Request::is('karyawan*');
    @endphp
    @if($grpFinanceVisible)
    <button type="button" onclick="toggleMenuGroup(this)" class="menu-group-toggle w-full flex items-center justify-between pt-5 pb-2 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-[0.16em] hover:text-white transition-colors">
        <span>Finance & HR</span>
        <i class="fas fa-chevron-down text-[10px] transition-transform {{ $grpFinanceAktif ? 'rotate-180' : '' }}"></i>
    </button>
    <div class="menu-group-items space-y-1 {{ $grpFinanceAktif ? '' : 'hidden' }}">
    @endif

    @if($cekAkses(3) || $cekAkses(41))
    <a href="{{ route('penjualan.create') }}"
       class="{{ $menuClass }} {{ Request::is('penjualan/kasir*') ? 'bg-emerald-500/10 text-emerald-400' : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('penjualan/kasir*') ? 'text-emerald-400' : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span class="text-sm font-medium">Input Order</span>
    </a>
    @endif

    @if($cekAkses(3) || $cekAkses(41))
    <a href="{{ url('/quotation') }}" class="{{ $menuClass }} {{ Request::is('quotation*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('quotation*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="text-sm font-medium">Quotation</span>
    </a>
    @endif

    @if($cekAkses(48))
    <a href="{{ route('penjualan-barang.create') }}"
       class="{{ $menuClass }} {{ Request::is('penjualan-barang*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('penjualan-barang*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <span class="text-sm font-medium">Penjualan Barang</span>
    </a>
    @endif

    @if($cekAkses(3))
    <a href="{{ route('penjualan.riwayat') }}"
       class="{{ $menuClass }} {{ Request::is('penjualan/riwayat*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('penjualan/riwayat*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm font-medium">Riwayat Penjualan</span>
    </a>
    @endif

    @if($cekAkses(39))
    <a href="{{ route('kas-kecil.index') }}" class="{{ $menuClass }} {{ request()->routeIs('kas-kecil.*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('kas-kecil.*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm font-medium">Kas Kecil</span>
    </a>
    @endif

    @if($cekAkses(45))
    <a href="{{ route('kategori-kas-kecil.index') }}" class="{{ $menuClass }} {{ request()->routeIs('kategori-kas-kecil.*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('kategori-kas-kecil.*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 9V4a1 1 0 011-1z" />
        </svg>
        <span class="text-sm font-medium">Kategori Kas Kecil</span>
    </a>
    @endif

    @if($cekAkses(11))
    <a href="{{ url('/bank') }}" class="{{ $menuClass }} {{ Request::is('bank*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('bank*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
        <span class="text-sm font-medium">Bank Perusahaan</span>
    </a>
    @endif

    @if($cekAkses(5))
    <a href="{{ url('/karyawan') }}" class="{{ $menuClass }} {{ Request::is('karyawan*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('karyawan*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
        </svg>
        <span class="text-sm font-medium">Data Karyawan</span>
    </a>
    @endif
    @if($grpFinanceVisible)
    </div>
    @endif

    @php
        // cekAkses(47) (Laporan HPP & Margin) sebelumnya TIDAK ikut kondisi
        // ini - kalau suatu role cuma dikasih akses id_menu 47 saja, header
        // "Laporan" tidak pernah muncul padahal link HPP-nya sendiri tetap
        // render (yatim tanpa header). Ditambahkan di sini.
        $grpLaporanVisible = $cekAkses(25) || $cekAkses(26) || $cekAkses(29) || $cekAkses(40) || $cekAkses(43) || $cekAkses(44) || $cekAkses(46) || $cekAkses(47);
        $grpLaporanAktif = request()->routeIs('laporan.order') || request()->routeIs('laporan.produksi')
            || request()->routeIs('laporan.kas') || request()->routeIs('laporan.belanja-bahan') || request()->routeIs('laporan.hutang')
            || request()->routeIs('laporan.order-jersey') || request()->routeIs('laporan.piutang')
            || request()->routeIs('laporan.stok-bahan') || request()->routeIs('laporan.hpp') || request()->routeIs('laporan.job-costing') || request()->routeIs('laporan.profitabilitas') || request()->routeIs('laporan.pemakaian-bahan') || request()->routeIs('laporan.biaya-tinta') || request()->routeIs('laporan.evaluasi-master');
    @endphp
    @if($grpLaporanVisible)
    <button type="button" onclick="toggleMenuGroup(this)" class="menu-group-toggle w-full flex items-center justify-between pt-5 pb-2 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-[0.16em] hover:text-white transition-colors">
        <span>Laporan</span>
        <i class="fas fa-chevron-down text-[10px] transition-transform {{ $grpLaporanAktif ? 'rotate-180' : '' }}"></i>
    </button>
    <div class="menu-group-items space-y-1 {{ $grpLaporanAktif ? '' : 'hidden' }}">
    @endif

    @if($cekAkses(25))
    <a href="{{ route('laporan.order') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.order') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.order') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <span class="text-sm font-medium">Laporan Order</span>
    </a>
    @endif

    @if($cekAkses(26))
    <a href="{{ route('laporan.produksi') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.produksi') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.produksi') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
        </svg>
        <span class="text-sm font-medium">Laporan Produksi</span>
    </a>
    @endif

    @if($cekAkses(29))
    <a href="{{ route('laporan.kas') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.kas') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.kas') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span class="text-sm font-medium">Laporan Kas Utama</span>
    </a>
    @endif

    @if($cekAkses(40))
    <a href="{{ route('laporan.belanja-bahan') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.belanja-bahan') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.belanja-bahan') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M4 7h16M5 7v11a2 2 0 002 2h10a2 2 0 002-2V7M9 7v11m6-11v11" />
        </svg>
        <span class="text-sm font-medium">Laporan Belanja Bahan</span>
    </a>
    <a href="{{ route('laporan.hutang') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.hutang') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.hutang') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm font-medium">Laporan Hutang Supplier</span>
    </a>
    @endif

    @if($cekAkses(43))
    <a href="{{ route('laporan.order-jersey') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.order-jersey') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.order-jersey') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M4 7h16M5 7v11a2 2 0 002 2h10a2 2 0 002-2V7M9 7v11m6-11v11" />
        </svg>
        <span class="text-sm font-medium">Laporan Order Jersey</span>
    </a>
    @endif

    @if($cekAkses(44))
    <a href="{{ route('laporan.piutang') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.piutang') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.piutang') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-sm font-medium">Laporan Piutang</span>
    </a>
    @endif

    @if($cekAkses(46))
    <a href="{{ route('laporan.stok-bahan') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.stok-bahan') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.stok-bahan') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
        </svg>
        <span class="text-sm font-medium">Laporan Stok Bahan Baku</span>
    </a>
    <a href="{{ route('laporan.pemakaian-bahan') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.pemakaian-bahan') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.pemakaian-bahan') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
        </svg>
        <span class="text-sm font-medium">Analisis Pemakaian Bahan</span>
    </a>
    @endif

    @if($cekAkses(47))
    <a href="{{ route('laporan.evaluasi-master') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.evaluasi-master') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.evaluasi-master') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        <span class="text-sm font-medium">Lembar Evaluasi Master</span>
    </a>
    <a href="{{ route('laporan.biaya-tinta') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.biaya-tinta') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.biaya-tinta') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c3.5 4.2 6 7 6 10a6 6 0 11-12 0c0-3 2.5-5.8 6-10z" />
        </svg>
        <span class="text-sm font-medium">Biaya Tinta</span>
    </a>
    <a href="{{ route('laporan.hpp') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.hpp') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.hpp') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 4h4m-2-8a9 9 0 100 18 9 9 0 000-18zM9.5 15.5c.5 1 1.5 1.5 2.5 1.5s2-.5 2.5-1.5M9.5 9c.5-1 1.5-1.5 2.5-1.5s2 .5 2.5 1.5" />
        </svg>
        <span class="text-sm font-medium">Laporan HPP & Margin</span>
    </a>
    <a href="{{ route('laporan.job-costing') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.job-costing') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.job-costing') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <span class="text-sm font-medium">Laporan Job Costing</span>
    </a>
    <a href="{{ route('laporan.profitabilitas') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.profitabilitas') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.profitabilitas') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <span class="text-sm font-medium">Laporan Profitabilitas</span>
    </a>
    @endif
    @if($grpLaporanVisible)
    </div>
    @endif

    @php
        // Accounting Period cuma bisa diakses Administrator (menu_access.php
        // 'accounting-period' => [] - tidak ada id_menu yang bisa
        // menggrant-nya lewat role_permissions), jadi dicek $isAdministrator
        // langsung, bukan $cekAkses(N).
        $grpAkuntansiVisible = $cekAkses(35) || $cekAkses(36) || $isAdministrator;
        $grpAkuntansiAktif = Request::is('akun*') || Request::is('akuntansi*') || Request::is('accounting-period*');
    @endphp
    @if($grpAkuntansiVisible)
    <button type="button" onclick="toggleMenuGroup(this)" class="menu-group-toggle w-full flex items-center justify-between pt-5 pb-2 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-[0.16em] hover:text-white transition-colors">
        <span>Akuntansi</span>
        <i class="fas fa-chevron-down text-[10px] transition-transform {{ $grpAkuntansiAktif ? 'rotate-180' : '' }}"></i>
    </button>
    <div class="menu-group-items space-y-1 {{ $grpAkuntansiAktif ? '' : 'hidden' }}">
    @endif

    @if($cekAkses(35))
    <a href="{{ route('akun.index') }}" class="{{ $menuClass }} {{ Request::is('akun*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('akun*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <span class="text-sm font-medium">Chart of Accounts</span>
    </a>
    @endif

    @if($cekAkses(36))
    <a href="{{ route('akuntansi.jurnal') }}" class="{{ $menuClass }} {{ Request::is('akuntansi*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('akuntansi*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <span class="text-sm font-medium">Laporan Keuangan</span>
    </a>
    @endif

    @if($isAdministrator)
    <a href="{{ route('accounting-period.index') }}" class="{{ $menuClass }} {{ Request::is('accounting-period*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('accounting-period*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-sm font-medium">Accounting Period</span>
    </a>
    @endif
    @if($grpAkuntansiVisible)
    </div>
    @endif

    @php $grpJerseyAktif = Request::is('jersey*'); @endphp
    @if($cekAkses(38) || $cekAkses(42))
    <button type="button" onclick="toggleMenuGroup(this)" class="menu-group-toggle w-full flex items-center justify-between pt-5 pb-2 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-[0.16em] hover:text-white transition-colors">
        <span>Order Jersey</span>
        <i class="fas fa-chevron-down text-[10px] transition-transform {{ $grpJerseyAktif ? 'rotate-180' : '' }}"></i>
    </button>
    <div class="menu-group-items space-y-1 {{ $grpJerseyAktif ? '' : 'hidden' }}">
    @if($cekAkses(38))
    <a href="{{ url('/jersey') }}" class="{{ $menuClass }} {{ (Request::is('jersey*') && !Request::is('jersey/master*') && !Request::is('jersey/keuangan*') && !Request::is('jersey/create')) ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ (Request::is('jersey*') && !Request::is('jersey/master*') && !Request::is('jersey/keuangan*') && !Request::is('jersey/create')) ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M4 7h16M5 7v11a2 2 0 002 2h10a2 2 0 002-2V7M9 7v11m6-11v11" />
        </svg>
        <span class="text-sm font-medium">Daftar Order</span>
    </a>
    @endif
    @if(!$cekAkses(38) && $cekAkses(42))
    <a href="{{ route('jersey.orders.create') }}" class="{{ $menuClass }} {{ Request::is('jersey/create') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('jersey/create') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M4 7h16M5 7v11a2 2 0 002 2h10a2 2 0 002-2V7M9 7v11m6-11v11" />
        </svg>
        <span class="text-sm font-medium">Order Jersey Baru</span>
    </a>
    @endif
    @if($cekAkses(38))
    <a href="{{ url('/jersey/master') }}" class="{{ $menuClass }} {{ Request::is('jersey/master') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('jersey/master') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="text-sm font-medium">Master Data Jersey</span>
    </a>
    <a href="{{ url('/jersey/keuangan') }}" class="{{ $menuClass }} {{ Request::is('jersey/keuangan*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('jersey/keuangan*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span class="text-sm font-medium">Keuangan Jersey</span>
    </a>
    @endif
    </div>
    @endif

    @php
        // Approval Workflow, Log Aktivitas, Backup & Recovery cuma bisa
        // diakses Administrator (menu_access.php rule-nya [] kosong - tidak
        // ada id_menu yang bisa menggrant lewat role_permissions), jadi
        // dicek $isAdministrator langsung, sama seperti Accounting Period.
        $grpSystemVisible = $cekAkses(6) || $cekAkses(4) || $cekAkses(37) || $isAdministrator;
        $grpSystemAktif = Request::is('users*') || Request::is('setting-akses*') || Request::is('pengaturan-aplikasi*')
            || Request::is('approval*') || Request::is('log-aktivitas*') || Request::is('backup*');
    @endphp
    @if($grpSystemVisible)
    <button type="button" onclick="toggleMenuGroup(this)" class="menu-group-toggle w-full flex items-center justify-between pt-5 pb-2 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-[0.16em] hover:text-white transition-colors">
        <span>System Control</span>
        <i class="fas fa-chevron-down text-[10px] transition-transform {{ $grpSystemAktif ? 'rotate-180' : '' }}"></i>
    </button>
    <div class="menu-group-items space-y-1 {{ $grpSystemAktif ? '' : 'hidden' }}">
    @endif

    @if($cekAkses(6))
    <a href="{{ url('/users') }}" class="{{ $menuClass }} {{ Request::is('users*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('users*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <span class="text-sm font-medium">Manajemen User</span>
    </a>
    @endif
    
@if($cekAkses(4))
<a href="{{ url('/setting-akses') }}" 
   class="{{ $menuClass }} {{ Request::is('setting-akses*') ? $activeClass : $nonActiveClass }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('setting-akses*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
    </svg>
    <span class="text-sm font-medium">Setting Akses</span>
</a>
@endif

@if($cekAkses(37))
<a href="{{ url('/pengaturan-aplikasi') }}"
   class="{{ $menuClass }} {{ Request::is('pengaturan-aplikasi*') ? $activeClass : $nonActiveClass }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('pengaturan-aplikasi*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
    <span class="text-sm font-medium">Pengaturan Aplikasi</span>
</a>
@endif

@if($isAdministrator)
<a href="{{ route('approval.index') }}" class="{{ $menuClass }} {{ Request::is('approval*') ? $activeClass : $nonActiveClass }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('approval*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span class="text-sm font-medium">Approval Workflow</span>
    <span id="badge-approval" class="hidden ml-auto min-w-[1.25rem] h-5 px-1.5 rounded-full bg-rose-500 text-white text-[11px] font-bold items-center justify-center"></span>
</a>
<a href="{{ route('audit-log.index') }}" class="{{ $menuClass }} {{ Request::is('log-aktivitas*') ? $activeClass : $nonActiveClass }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('log-aktivitas*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span class="text-sm font-medium">Log Aktivitas</span>
</a>
<a href="{{ route('backup.index') }}" class="{{ $menuClass }} {{ Request::is('backup*') ? $activeClass : $nonActiveClass }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('backup*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7c0 1.657 3.582 3 8 3s8-1.343 8-3m-16 0c0-1.657 3.582-3 8-3s8 1.343 8 3m-16 0v10c0 1.657 3.582 3 8 3s8-1.343 8-3V7" />
    </svg>
    <span class="text-sm font-medium">Backup & Recovery</span>
</a>
@endif
@if($grpSystemVisible)
</div>
@endif
</nav>
        </aside>

        <main id="mainContent" class="transition-all duration-300">
    <div class="topbar sticky top-0 shadow-sm z-30 justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <button id="sidebarCollapseToggle" onclick="toggleSidebar()" class="topbar-burger w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all shrink-0" title="Sembunyikan/tampilkan menu">
                <i class="fas fa-bars"></i>
            </button>
            <span class="topbar-app-name font-bold text-slate-700 truncate">{{ $appSetting->nama_aplikasi }}</span>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            @php $namaTampil = optional(Auth::user()->karyawan)->nama_karyawan ?? Auth::user()->username; @endphp
            <div class="hidden sm:flex flex-col items-end leading-tight">
                <span class="text-[13px] font-bold text-slate-700">{{ $namaTampil }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ optional(Auth::user()->role)->nama_role }}</span>
            </div>
            <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0 ring-2 ring-indigo-50">
                {{ strtoupper(substr($namaTampil, 0, 1)) }}
            </div>
            <form action="{{ url('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all" title="Keluar Sistem">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="content-wrapper">
         @yield('content')
    </div>
</main>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Refresh opsi select2 saat dibuka - opsi umumnya dirender server
        // sekali saat halaman dimuat, jadi data yang baru ditambah di halaman
        // lain (konsumen, bahan, supplier, dst) tidak muncul tanpa reload.
        // Cara kerja: saat select2 dibuka, ambil ulang halaman yang sama
        // (satu fetch dibagi semua select, di-throttle 4 detik), cari
        // <select> yang sama lewat id/name, dan kalau daftar opsinya
        // berbeda, ganti opsi (pilihan terpilih dipertahankan) lalu buka
        // ulang dropdown. Lewati select yang opsinya dibangun client
        // (ajax/data-no-refresh) atau tidak ditemukan di halaman segar.
        (function () {
            let lastFetch = 0, pending = null, refreshing = false;

            function fetchFreshDoc() {
                const now = Date.now();
                if (pending && now - lastFetch < 4000) return pending;
                lastFetch = now;
                pending = fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(r => r.ok ? r.text() : null)
                    .then(html => html ? new DOMParser().parseFromString(html, 'text/html') : null)
                    .catch(() => null);
                return pending;
            }

            function findFresh(doc, el) {
                if (el.id) return doc.getElementById(el.id);
                if (el.name) return doc.querySelector('select[name="' + el.name.replace(/"/g, '\\"') + '"]');
                return null;
            }

            $(document).on('select2:opening', 'select', function () {
                if (refreshing || this.hasAttribute('data-no-refresh') || $(this).data('select2')?.options?.options?.ajax) return;
                const el = this, $el = $(this);
                if (!el.id && !el.name) return;

                fetchFreshDoc().then(function (doc) {
                    if (!doc) return;
                    const fresh = findFresh(doc, el);
                    if (!fresh) return;

                    const sig = s => Array.from(s.options).map(o => o.value + '|' + o.textContent.trim()).join('\n');
                    if (sig(fresh) === sig(el)) return;

                    const selected = $el.val();
                    el.innerHTML = fresh.innerHTML;
                    $el.val(selected);
                    refreshing = true;
                    $el.trigger('change.select2');
                    if ($el.data('select2') && $el.data('select2').isOpen()) {
                        $el.select2('close');
                        $el.select2('open');
                    }
                    refreshing = false;
                });
            });
        })();
    </script>
    @include('partials.rupiah-format-script')
    <script>
        // Filter konsisten di SELURUH aplikasi: memilih nilai pada dropdown di dalam form GET (filter/pencarian)
        // langsung menerapkan filter - tidak perlu klik tombol atau memilih dua kali. Termasuk dropdown select2
        // (memakai event jQuery). Nonaktifkan per form/dropdown dengan atribut data-no-autosubmit.
        $(document).on('change', 'select', function () {
            const form = this.form;
            if (!form || (form.getAttribute('method') || '').toLowerCase() !== 'get') return;
            if (form.hasAttribute('data-no-autosubmit') || this.hasAttribute('data-no-autosubmit')) return;
            if (form.__terkirim) return;
            form.__terkirim = true;
            setTimeout(() => { form.requestSubmit ? form.requestSubmit() : form.submit(); }, 0);
        });
    </script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        @if(session('success'))
            @php $lanjut = session('lanjut'); @endphp
            @if($lanjut)
                {{-- Jalan pintas: beri tahu ke mana proses berlanjut setelah aksi ini. --}}
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success', timer: 9000, timerProgressBar: true,
                    title: @json(session('success')),
                    text: @json($lanjut['teks'] ?? ''),
                    showConfirmButton: {{ !empty($lanjut['url']) ? 'true' : 'false' }},
                    confirmButtonText: @json(($lanjut['tombol'] ?? 'Lanjut') . ' →'),
                    confirmButtonColor: '#4f46e5', showCloseButton: true
                }).then(r => { if (r.isConfirmed) window.location.href = @json($lanjut['url'] ?? '#'); });
            @else
                Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
            @endif
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Opps!', text: "{{ session('error') }}", customClass: { popup: 'rounded-3xl' } });
        @endif
    </script>
    <script>
    let currentTransactionCount = null;

    function checkTransactions() {
        fetch("{{ url('/kasir/check-pembayaran') }}")
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('badge-notif');
                if(data.total > 0) {
                    badge.innerText = data.total;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
                // Jika ini pengecekan pertama, set jumlah awal
                if (currentTransactionCount === null) {
                    currentTransactionCount = data.total;
                    return;
                }

                // Jika jumlah transaksi bertambah dari sebelumnya
                if (data.total > currentTransactionCount) {
                    currentTransactionCount = data.total;
                    
                    // Mainkan suara notifikasi (Opsional)
                    // let audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-software-interface-start-2574.mp3');
                    // audio.play();

                    Swal.fire({
                        title: 'NOTA BARU!',
                        text: `Ada ${data.total} transaksi menunggu divalidasi.`,
                        icon: 'info',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: true,
                        confirmButtonText: 'Lihat List',
                        confirmButtonColor: '#4f46e5',
                        timer: 10000,
                        timerProgressBar: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('kasir.index') }}";
                        }
                    });
                } else {
                    currentTransactionCount = data.total;
                }
            })
            .catch(error => console.error('Error checking transactions:', error));
    }

    // Jalankan pengecekan setiap 10 detik - HANYA untuk role yang benar-benar
    // punya akses ke endpoint-nya sendiri. /kasir/check-pembayaran ada di
    // bawah prefix "kasir" yang di config/menu_access.php dijaga id_menu 16
    // (BUKAN 3/41 - itu gate untuk prefix "penjualan"/Input Order, beda
    // modul). Sebelumnya jalan untuk SEMUA user login tanpa
    // syarat, jadi role tanpa id_menu 16 (termasuk Operator Desain yang
    // cuma punya akses input-order) tetap polling endpoint ini tiap 10
    // detik selamanya dan SELALU dapat 403 (dijaga CheckMenuAccess) -
    // percuma membebani server & memenuhi console dengan error "Unexpected
    // token '<'" (403 balikannya HTML, bukan JSON).
    @if(auth()->check() && $cekAkses(16))
        setInterval(checkTransactions, 10000);
    @endif
    </script>
    <script>
    // Refresh otomatis ringan: cek "sidik jari" perubahan data tiap 30 detik, HANYA saat tab terlihat
    // (tab latar tidak membebani server). Bila berubah: halaman daftar-pantau (daftar putih) yang idle dimuat ulang sendiri; bila user
    // sedang mengetik / modal terbuka / halaman form, cukup munculkan tombol "Muat ulang".
    (function () {
        let basis = null, sibuk = false, kotor = false, jeda = 30000, timer = null;
        // DAFTAR PUTIH: hanya halaman pantau/daftar (tanpa input) yang boleh dimuat ulang otomatis.
        // Semua halaman lain (input nota, jersey, kasir bayar, master, dst) TIDAK PERNAH di-reload
        // otomatis - hanya menampilkan tombol - agar keranjang/isian tidak hilang.
        const bolehOtomatis = /^\/(dashboard|kasir\/monitoring|spk|produksi|approval)\/?$/.test(location.pathname.replace(/^\/percetakan-app\/public/, ''));
        document.addEventListener('input', () => { kotor = true; }, true);
        document.addEventListener('change', () => { kotor = true; }, true);
        function aman() {
            if (!bolehOtomatis || kotor) return false;
            // Lapis kedua: ada field isian terlihat di form non-GET (mis. modal terbuka) = jangan reload.
            const fieldTerlihat = [...document.querySelectorAll('form:not([method=get]):not([method=GET]) :is(input:not([type=hidden]):not([type=checkbox]),textarea,select)')]
                .some(el => el.offsetParent !== null);
            if (fieldTerlihat) return false;
            if (document.querySelector('.swal2-popup:not(.swal2-toast), dialog[open]')) return false;
            const a = document.activeElement;
            return !(a && /^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName));
        }
        function pill() {
            if (document.getElementById('pill-segarkan')) return;
            const b = document.createElement('button');
            b.id = 'pill-segarkan'; b.type = 'button';
            b.className = 'fixed bottom-5 left-1/2 -translate-x-1/2 z-[9999] px-5 py-2.5 rounded-full bg-indigo-600 text-white text-sm font-semibold shadow-xl hover:bg-indigo-700';
            b.textContent = 'Data diperbarui di tab/pengguna lain — Muat ulang';
            b.onclick = () => location.reload();
            document.body.appendChild(b);
        }
        async function cek() {
            if (document.hidden || sibuk) return;
            sibuk = true;
            try {
                const r = await fetch(@json(url('/sinyal')), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!r.ok) throw new Error(r.status);
                const d = await r.json();
                jeda = 30000;
                const bd = document.getElementById('badge-approval');
                if (bd && d.approval !== null) { bd.textContent = d.approval; bd.classList.toggle('hidden', d.approval < 1); bd.classList.toggle('inline-flex', d.approval >= 1); }
                if (basis === null) basis = d.v;
                else if (d.v !== basis) { aman() ? location.reload() : pill(); }
            } catch (e) { jeda = Math.min(jeda * 2, 300000); }
            finally { sibuk = false; jadwal(); }
        }
        function jadwal() { clearTimeout(timer); timer = setTimeout(cek, jeda); }
        document.addEventListener('visibilitychange', () => { if (!document.hidden) cek(); });
        cek();
    })();
    </script>
    <script>
        // Toggle Sidebar Desktop - collapse ke ICON-ONLY (bukan hilang total,
        // lihat rule body.sidebar-collapsed di <style>), disimpan ke
        // localStorage supaya pilihan user tidak reset tiap pindah halaman
        // (aplikasi ini full page reload antar route, bukan SPA).
        function toggleSidebar() {
            if (window.innerWidth >= 1024) {
                const collapsed = document.body.classList.toggle('sidebar-collapsed');
                try { localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0'); } catch (e) {}
            } else {
                toggleMobileMenu();
            }
        }

        // Toggle Sidebar Mobile
        function toggleMobileMenu() {
            const sidebar = document.getElementById('mainSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
                $(overlay).fadeOut();
            } else {
                sidebar.classList.add('mobile-open');
                $(overlay).fadeIn();
            }
        }

        // Tooltip native (title) di tiap link menu - satu-satunya cara tahu
        // nama menu saat ikon-only tanpa mengetik ulang ratusan baris label
        // secara manual di semua menu yang sudah ada (state collapse sendiri
        // sudah dipasang lebih awal, lihat script tepat setelah <body>).
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#mainSidebar nav a').forEach(function(a) {
                const span = a.querySelector('span');
                if (span && span.textContent.trim()) {
                    a.setAttribute('title', span.textContent.trim());
                }
            });
        });

        // Sembunyikan sidebar otomatis jika layar diresize ke kecil
        window.addEventListener('resize', function() {
            if (window.innerWidth < 1024) {
                document.body.classList.remove('sidebar-collapsed');
            }
        });

        // Accordion grup menu sidebar - state buka/tutup TIDAK disimpan ke
        // localStorage (sengaja): tiap halaman full reload, dan grup yang
        // berisi link aktif sudah otomatis terbuka lewat variable $grpXAktif
        // yang dihitung di server (lihat blok masing-masing grup), jadi
        // state manual user cukup bertahan selama sesi klik di halaman yang
        // sama saja - lebih sederhana daripada sinkronisasi localStorage
        // per grup untuk manfaat yang tipis.
        function toggleMenuGroup(btn) {
            // Rel ringkas (sidebar dilipat): grup membuka flyout, bukan accordion.
            if (window.railAktif && window.railAktif()) { window.railBukaFlyout(btn, true); return; }
            const items = btn.nextElementSibling;
            const icon = btn.querySelector('i.fa-chevron-down');
            items.classList.toggle('hidden');
            if (icon) icon.classList.toggle('rotate-180');
        }

        // ===== Rel ikon (sidebar ringkas): ikon grup + flyout + tooltip =====
        (function () {
            const ikonGrup = [
                [/produksi|logistik/i, 'fa-industry'], [/relasi|bisnis/i, 'fa-handshake'], [/finance|hr|keuangan/i, 'fa-wallet'],
                [/laporan/i, 'fa-chart-line'], [/akuntansi/i, 'fa-scale-balanced'], [/jersey/i, 'fa-shirt'], [/system|sistem/i, 'fa-gear'],
                [/penjualan|kasir/i, 'fa-cash-register'], [/master/i, 'fa-database'],
            ];
            const pilihIkon = (teks) => (ikonGrup.find(([re]) => re.test(teks)) || [null, 'fa-layer-group'])[1];
            const rail = () => document.body.classList.contains('sidebar-collapsed') && window.innerWidth >= 1024;
            window.railAktif = rail;

            let fly, tip, tutupTimer, tombolAktif = null;
            const buat = () => {
                fly = document.createElement('div'); fly.id = 'railFlyout';
                tip = document.createElement('div'); tip.id = 'railTip';
                document.body.append(fly, tip);
                fly.addEventListener('mouseenter', () => clearTimeout(tutupTimer));
                fly.addEventListener('mouseleave', () => tutupFlyout());
            };
            const tutupFlyout = (segera) => {
                clearTimeout(tutupTimer);
                const tutup = () => { fly.style.display = 'none'; if (tombolAktif) tombolAktif.classList.remove('flyout-open'); tombolAktif = null; };
                segera ? tutup() : (tutupTimer = setTimeout(tutup, 180));
            };
            window.railBukaFlyout = (btn, klik) => {
                if (!rail()) return;
                clearTimeout(tutupTimer);
                tip.style.display = 'none';
                                if (tombolAktif) tombolAktif.classList.remove('flyout-open');
                tombolAktif = btn; btn.classList.add('flyout-open');

                const wadah = btn.nextElementSibling;
                const judul = (btn.dataset.label || '').replace(/</g, '&lt;');
                fly.innerHTML = '<div class="fly-title">' + judul + '</div>';
                wadah.querySelectorAll('a').forEach((a) => {
                    const item = document.createElement('a');
                    item.href = a.href;
                    if (a.classList.contains('sb-active')) item.classList.add('fly-active');
                    const svg = a.querySelector('svg');
                    if (svg) { const c = svg.cloneNode(true); c.removeAttribute('class'); item.appendChild(c); }
                    const label = document.createElement('span');
                    label.textContent = (a.querySelector('span') || a).textContent.trim();
                    item.appendChild(label);
                    fly.appendChild(item);
                });
                fly.style.display = 'block';
                const r = btn.getBoundingClientRect();
                const tinggi = fly.offsetHeight;
                fly.style.left = (r.right + 10) + 'px';
                fly.style.top = Math.max(12, Math.min(r.top, window.innerHeight - tinggi - 12)) + 'px';
            };
            const tampilTip = (el, teks) => {
                if (!rail()) return;
                tip.textContent = teks; tip.style.display = 'block';
                const r = el.getBoundingClientRect();
                tip.style.left = (r.right + 12) + 'px';
                tip.style.top = (r.top + r.height / 2 - tip.offsetHeight / 2) + 'px';
            };

            document.addEventListener('DOMContentLoaded', () => {
                buat();
                document.querySelectorAll('#mainSidebar .menu-group-toggle').forEach((btn) => {
                    const label = (btn.querySelector('span') || btn).textContent.trim();
                    btn.dataset.label = label;
                    const ikon = document.createElement('i');
                    ikon.className = 'rail-icon fas ' + pilihIkon(label);
                    btn.insertBefore(ikon, btn.firstChild);
                    if (btn.nextElementSibling && btn.nextElementSibling.querySelector('.sb-active')) btn.classList.add('has-active');
                    btn.addEventListener('mouseenter', () => window.railBukaFlyout(btn, false));
                    btn.addEventListener('mouseleave', () => { if (rail()) tutupFlyout(); });
                });
                document.querySelectorAll('#mainSidebar nav > a').forEach((a) => {
                    const label = (a.querySelector('span') || a).textContent.trim();
                    a.addEventListener('mouseenter', () => tampilTip(a, label));
                    a.addEventListener('mouseleave', () => { tip.style.display = 'none'; });
                });
                document.addEventListener('click', (e) => { if (fly && !fly.contains(e.target) && !e.target.closest('.menu-group-toggle')) tutupFlyout(true); });
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && fly) tutupFlyout(true); });
                window.addEventListener('resize', () => { if (!rail() && fly) tutupFlyout(true); });
            });
        })();
    </script>
    {{-- Angka desimal DB (mis. 226.000) ditampilkan rapi (226) di SEMUA input number, termasuk saat modal edit
         diisi lewat JS (input.value = ...), baris yang ditambah dinamis, dan value bawaan server. Nilai numerik
         tidak berubah; hanya nol di belakang koma yang dibuang. --}}
    <script>
    (function () {
        const rapikan = (v) => (typeof v === 'string' && /^-?\d+\.\d+$/.test(v))
            ? v.replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '')
            : v;

        const desc = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
        Object.defineProperty(HTMLInputElement.prototype, 'value', {
            configurable: true,
            get() { return desc.get.call(this); },
            set(v) {
                if (this.type === 'number' && v !== null && v !== undefined) v = rapikan(String(v));
                desc.set.call(this, v);
            },
        });

        const perbaiki = (el) => {
            const awal = el.getAttribute('value');
            if (awal === null) return;
            const baru = rapikan(awal);
            if (baru !== awal) el.setAttribute('value', baru);
        };
        const semua = (root) => root.querySelectorAll('input[type="number"]').forEach(perbaiki);

        document.addEventListener('DOMContentLoaded', () => semua(document));
        new MutationObserver((daftar) => daftar.forEach((m) => m.addedNodes.forEach((n) => {
            if (n.nodeType !== 1) return;
            if (n.matches && n.matches('input[type="number"]')) perbaiki(n);
            if (n.querySelectorAll) semua(n);
        }))).observe(document.documentElement, { childList: true, subtree: true });
    })();
    </script>
    @stack('scripts')
</body>
</html>