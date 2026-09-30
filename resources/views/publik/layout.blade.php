@php
    $appSetting = \App\Models\AppSetting::current();
    $urlLogo = $appSetting->logo_path ? asset('storage/' . $appSetting->logo_path) : null;
    // Dialog masuk terbuka sendiri bila login gagal, atau bila orang ini baru saja ditolak dari halaman dalam.
    $bukaMasuk = $errors->any() || !empty($bukaMasuk) || request()->has('masuk');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('judul', $appSetting->nama_aplikasi)</title>
    @hasSection('deskripsi')<meta name="description" content="@yield('deskripsi')">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Semua font dan ikon dilayani sendiri: server LAN tanpa internet tetap tampil benar. --}}
    <link rel="stylesheet" href="{{ asset('vendor/fonts/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fonts/majalah.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/all.min.css') }}">
    <style>
        /* Halaman depan bergaya jurnal: serif untuk judul, sans untuk antarmuka, banyak ruang kosong,
           garis rambut sebagai pemisah. Tanpa gradien, kaca, bayangan berwarna, atau huruf mono.
           Gerak: hanya transform dan opacity, di bawah 300ms, ease-out kuat; hormati reduced-motion. */
        :root {
            --kertas: #faf9f7; --putih: #ffffff; --garis: #e9e5df; --garis-kuat: #d6d0c8;
            --tinta: #1b1a18; --tinta-2: #55514b; --tinta-3: #6e6962;
            --aksen: #4f46e5; --aksen-tekan: #4338ca;
            --serif: 'Playfair Display', Georgia, 'Times New Roman', serif;
            --sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
            --keluar: cubic-bezier(0.23, 1, 0.32, 1);
        }
        * { box-sizing: border-box; }
        body.bl { margin: 0; background: var(--kertas); color: var(--tinta); font-family: var(--sans); font-size: 16px; line-height: 1.6; -webkit-font-smoothing: antialiased; }
        .bl a { color: inherit; text-decoration: none; }
        .bl img { display: block; max-width: 100%; }
        .bl-lebar { max-width: 1120px; margin: 0 auto; padding-left: max(1.25rem, env(safe-area-inset-left)); padding-right: max(1.25rem, env(safe-area-inset-right)); }
        .bl :focus-visible { outline: 2px solid var(--aksen); outline-offset: 3px; border-radius: 4px; }

        /* Kepala */
        .bl-kepala { position: sticky; top: 0; z-index: 20; background: var(--kertas); border-bottom: 1px solid var(--garis); }
        .bl-kepala .bl-lebar { display: flex; align-items: center; justify-content: space-between; height: 64px; gap: 1rem; }
        .bl-merek { display: flex; align-items: center; gap: 0.75rem; font-weight: 600; letter-spacing: -0.01em; }
        .bl-merek img { height: 32px; width: auto; }
        .bl-kepala nav { display: flex; align-items: center; gap: 1.5rem; font-size: 0.9375rem; }
        .bl-taut { color: var(--tinta-2); transition: color 150ms ease; }
        .bl-tombol { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; height: 40px; padding: 0 1.125rem; background: var(--tinta); color: #fff !important; border: 0; font: 500 0.9375rem var(--sans); cursor: pointer; transition: transform 140ms var(--keluar), background-color 150ms ease; }
        .bl-tombol:active { transform: scale(0.97); }
        .bl-tombol-garis { background: transparent; color: var(--tinta) !important; border: 1px solid var(--garis-kuat); }
        @media (hover: hover) {
            .bl-taut:hover { color: var(--tinta); }
            .bl-tombol:hover { background: var(--aksen); }
            .bl-tombol-garis:hover { background: var(--putih); border-color: var(--tinta); }
        }

        /* Kaki */
        .bl-kaki { margin-top: 5rem; border-top: 1px solid var(--garis); padding: 2rem 0 max(2rem, env(safe-area-inset-bottom)); font-size: 0.875rem; color: var(--tinta-3); }
        .bl-kaki .bl-lebar { display: flex; flex-wrap: wrap; gap: 0.5rem 2rem; justify-content: space-between; }
        .bl-kaki b { color: var(--tinta); font-weight: 600; }

        /* Dialog masuk: muncul dari tengah (modal tidak berjangkar ke pemicu), mulai dari 0.96, bukan dari nol. */
        .bl-dialog { width: min(400px, calc(100vw - 2rem)); padding: 0; border: 1px solid var(--garis); border-radius: 10px; background: var(--putih); color: var(--tinta); }
        .bl-dialog::backdrop { background: rgba(27, 26, 24, 0.45); }
        .bl-dialog[open] { animation: bl-masuk 220ms var(--keluar); }
        .bl-dialog[open]::backdrop { animation: bl-pudar 220ms ease-out; }
        @keyframes bl-masuk { from { opacity: 0; transform: scale(0.96) translateY(6px); } }
        @keyframes bl-pudar { from { opacity: 0; } }
        .bl-dialog-isi { padding: 1.75rem; }
        .bl-dialog h2 { font-family: var(--serif); font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem; letter-spacing: -0.01em; }
        .bl-dialog p.ket { margin: 0 0 1.25rem; font-size: 0.875rem; color: var(--tinta-3); }
        .login-label { display: block; font-size: 0.8125rem; font-weight: 500; color: var(--tinta-2); margin-bottom: 0.375rem; }
        /* Tinggi isian mengikuti aturan global aplikasi (38px), lihat ui-refresh.css. */
        .login-input { width: 100%; padding: 0 0.75rem; background: #fff; border: 1px solid var(--garis-kuat); font-size: 0.9375rem; font-family: inherit; color: var(--tinta); outline: none; transition: border-color 140ms ease, box-shadow 140ms ease; }
        .login-input:focus { border-color: var(--aksen); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15) !important; }
        .login-galat { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 6px; padding: 0.625rem 0.75rem; font-size: 0.8125rem; display: flex; gap: 0.5rem; align-items: flex-start; }
        .bl-tutup { position: absolute; top: 0.75rem; right: 0.75rem; width: 32px; height: 32px; border: 0; background: transparent; color: var(--tinta-3); cursor: pointer; }

        @media (prefers-reduced-motion: reduce) {
            .bl *, .bl *::before, .bl *::after, .bl-dialog, .bl-dialog::backdrop { animation: none !important; transition: none !important; }
        }
        @yield('gaya')
    </style>
</head>
<body class="bl">
    <header class="bl-kepala">
        <div class="bl-lebar">
            <a class="bl-merek" href="{{ url('/') }}">
                @if($urlLogo)<img src="{{ $urlLogo }}" alt="">@endif
                <span>{{ $appSetting->nama_aplikasi }}</span>
            </a>
            <nav aria-label="Utama">
                <a class="bl-taut" href="{{ url('/') }}#tulisan">Tulisan</a>
                <button type="button" class="bl-tombol" data-buka-masuk>Masuk</button>
            </nav>
        </div>
    </header>

    <main>@yield('isi')</main>

    <footer class="bl-kaki">
        <div class="bl-lebar">
            <span><b>{{ $appSetting->nama_aplikasi }}</b></span>
            @if($appSetting->alamat)<span>{{ $appSetting->alamat }}</span>@endif
            @if($appSetting->telepon)<span>{{ $appSetting->telepon }}</span>@endif
        </div>
    </footer>

    <dialog class="bl-dialog" id="dialog-masuk" aria-labelledby="judul-masuk">
        <div class="bl-dialog-isi">
            <button type="button" class="bl-tutup" data-tutup-masuk aria-label="Tutup"><i class="fas fa-xmark"></i></button>
            <h2 id="judul-masuk">Masuk ke sistem</h2>
            <p class="ket">Untuk staf. Akun dibuat oleh Administrator.</p>

            <form action="{{ url('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="login-label" for="login-username">Username</label>
                    <input type="text" id="login-username" name="username" class="login-input" placeholder="Nama pengguna" required
                           autocomplete="username" autocapitalize="none" spellcheck="false">
                </div>
                <div>
                    <label class="login-label" for="login-password">Password</label>
                    <div class="relative">
                        <input type="password" id="login-password" name="password" class="login-input pr-10" placeholder="Kata sandi" required autocomplete="current-password">
                        <button type="button" id="login-toggle" class="absolute right-1 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-500"
                                aria-label="Tampilkan password" aria-pressed="false"><i class="fas fa-eye text-sm"></i></button>
                    </div>
                </div>
                @if($errors->any())
                    <div class="login-galat" role="alert"><i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i><span>{{ $errors->first() }}</span></div>
                @endif
                <button type="submit" class="bl-tombol" style="width:100%">Masuk</button>
            </form>
        </div>
    </dialog>

    <script>
    (function () {
        var dialog = document.getElementById('dialog-masuk');
        function buka() { if (!dialog.open) { dialog.showModal(); document.getElementById('login-username').focus(); } }
        document.querySelectorAll('[data-buka-masuk]').forEach(function (t) { t.addEventListener('click', buka); });
        dialog.querySelector('[data-tutup-masuk]').addEventListener('click', function () { dialog.close(); });
        // Klik di luar kotak menutup dialog.
        dialog.addEventListener('click', function (e) { if (e.target === dialog) dialog.close(); });
        @if($bukaMasuk) buka(); @endif

        var input = document.getElementById('login-password'), tombol = document.getElementById('login-toggle');
        tombol.addEventListener('click', function () {
            var tampil = input.type === 'password';
            input.type = tampil ? 'text' : 'password';
            tombol.setAttribute('aria-pressed', String(tampil));
            tombol.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
            tombol.innerHTML = '<i class="fas ' + (tampil ? 'fa-eye-slash' : 'fa-eye') + ' text-sm"></i>';
            input.focus();
        });
    })();
    </script>
    @stack('skrip')
</body>
</html>
