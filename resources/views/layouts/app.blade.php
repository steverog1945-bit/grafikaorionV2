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
    :root { --sidebar-width: 280px; --sidebar-width-collapsed: 84px; }
    body { font-family: 'Inter', sans-serif; scroll-behavior: smooth; background-color: #f5f7fa; overflow-x: hidden; }

    /* SIDEBAR CORE (Tetap Dipertahankan) */
    aside {
        width: var(--sidebar-width);
        background: #151e2c;
        color: #f8fafc;
        position: fixed;
        top: 0; bottom: 0; left: 0;
        z-index: 50;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        box-shadow: 4px 0 12px rgba(0,0,0,0.1);
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
    body.sidebar-collapsed aside nav a { justify-content: center; }
    body.sidebar-collapsed aside nav a span,
    body.sidebar-collapsed aside nav .tracking-\[0\.2em\],
    body.sidebar-collapsed aside .sidebar-brand-text,
    body.sidebar-collapsed aside .sidebar-text-label { display: none; }
    body.sidebar-collapsed aside .sidebar-kas-status { justify-content: center; }
    body.sidebar-collapsed aside #sidebarBrandHeader { justify-content: center; }
    body.sidebar-collapsed aside #sidebarCollapseToggle i { transform: rotate(180deg); }

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
            <div id="sidebarBrandHeader" class="p-6 border-b border-slate-800/50 flex justify-between items-center">
                <div class="sidebar-brand-text">
                    <h4 class="text-xl font-extrabold tracking-tighter text-indigo-400 flex items-center gap-2">
                        @if($appSetting->logo_path)
                            <img src="{{ asset('storage/' . $appSetting->logo_path) }}" alt="Logo" class="w-8 h-8 rounded-lg object-contain bg-indigo-500/20 p-1">
                        @else
                            <span class="bg-indigo-500/20 p-1.5 rounded-lg text-lg">🖨️</span>
                        @endif
                        {{ $appSetting->nama_aplikasi }}
                    </h4>
                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest mt-1">v1.0 Printing System</p>
                </div>
                <button onclick="toggleMobileMenu()" class="lg:hidden text-slate-400 sidebar-brand-text">
                    <i class="fas fa-times"></i>
                </button>
            </div>
{{-- Tambahan Status Kas --}}
<div class="mt-4 px-2">
    @if($kasAktif)
        <div class="sidebar-kas-status bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-2 flex items-center gap-2" title="Kasir: Open">
            <span class="relative flex h-2 w-2 shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-[10px] font-bold text-emerald-400 uppercase sidebar-text-label">Kasir: Open</span>
        </div>
    @else
        <div class="sidebar-kas-status bg-rose-500/10 border border-rose-500/20 rounded-lg p-2 flex items-center gap-2" title="Kasir: Standby">
            <span class="h-2 w-2 rounded-full bg-rose-500 shrink-0"></span>
            <span class="text-[10px] font-bold text-rose-400 uppercase sidebar-text-label">Kasir: Standby</span>
        </div>
    @endif
</div>
            <nav class="mt-6 px-4 pb-20 space-y-1">
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

        $cekAkses = function($idMenu) use ($userAkses, $isAdministrator) {
            return $isAdministrator || in_array($idMenu, $userAkses);
        };

        // Helper untuk styling menu agar konsisten
        $menuClass = "flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all group ";
        $activeClass = "bg-indigo-500/10 text-indigo-400";
        $nonActiveClass = "text-slate-400 hover:bg-slate-800 hover:text-white";
        
        $iconClass = "h-5 w-5 transition-colors ";
        $iconActive = "text-indigo-400";
        $iconNonActive = "text-slate-500 group-hover:text-white";
    @endphp

    {{-- DASHBOARD --}}
    <a href="{{ url('/dashboard') }}" 
       class="{{ $menuClass }} {{ Request::is('dashboard*') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ Request::is('dashboard*') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
        </svg>
        <span class="text-sm font-medium">Dashboard</span>
    </a>

    @if($cekAkses(1) || $cekAkses(20) || $cekAkses(17) || $cekAkses(22) || $cekAkses(23) || $cekAkses(24) || $cekAkses(15) || $cekAkses(31) || $cekAkses(30) || $cekAkses(10) || $cekAkses(27) || $cekAkses(33))
    <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">Produksi & Logistik</div>
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

    @if($cekAkses(7) || $cekAkses(34) || $cekAkses(18) || $cekAkses(13) || $cekAkses(21) || $cekAkses(32) || $cekAkses(14) || $cekAkses(28) || $cekAkses(9) || $cekAkses(16))
    <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">Relasi Bisnis</div>
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
    @if($cekAkses(3) || $cekAkses(41) || $cekAkses(39) || $cekAkses(45) || $cekAkses(11) || $cekAkses(5))
    <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">Finance & HR</div>
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

    @if($cekAkses(25) || $cekAkses(26) || $cekAkses(29) || $cekAkses(40) || $cekAkses(43) || $cekAkses(44) || $cekAkses(46))
    <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">Laporan</div>
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
    @endif

    @if($cekAkses(47))
    <a href="{{ route('laporan.hpp') }}" class="{{ $menuClass }} {{ request()->routeIs('laporan.hpp') ? $activeClass : $nonActiveClass }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} {{ request()->routeIs('laporan.hpp') ? $iconActive : $iconNonActive }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 4h4m-2-8a9 9 0 100 18 9 9 0 000-18zM9.5 15.5c.5 1 1.5 1.5 2.5 1.5s2-.5 2.5-1.5M9.5 9c.5-1 1.5-1.5 2.5-1.5s2 .5 2.5 1.5" />
        </svg>
        <span class="text-sm font-medium">Laporan HPP & Margin</span>
    </a>
    @endif

    @if($cekAkses(35) || $cekAkses(36))
    <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">Akuntansi</div>
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

    @if($cekAkses(38) || $cekAkses(42))
    <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">Order Jersey</div>
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
    @endif

    @if($cekAkses(6) || $cekAkses(4) || $cekAkses(37))
    <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">System Control</div>
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
</nav>
        </aside>

        <main id="mainContent" class="transition-all duration-300">
    <div class="topbar sticky top-0 bg-white shadow-sm z-30 justify-between">
        <div class="flex items-center gap-3">
            <button id="sidebarCollapseToggle" onclick="toggleSidebar()" class="p-2 rounded-lg hover:bg-slate-100 transition-transform duration-300" title="Sembunyikan/tampilkan menu">
                <i class="fas fa-bars"></i>
            </button>
            <span class="font-bold text-slate-700">{{ $appSetting->nama_aplikasi }}</span>
        </div>

        <div class="flex items-center gap-3">
            @php $namaTampil = optional(Auth::user()->karyawan)->nama_karyawan ?? Auth::user()->username; @endphp
            <div class="hidden sm:flex flex-col items-end leading-tight">
                <span class="text-[13px] font-bold text-slate-700">{{ $namaTampil }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">{{ optional(Auth::user()->role)->nama_role }}</span>
            </div>
            <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">
                {{ strtoupper(substr($namaTampil, 0, 1)) }}
            </div>
            <form action="{{ url('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all" title="Keluar Sistem">
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
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        @if(session('success'))
            Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
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
    </script>
    @stack('scripts')
</body>
</html>