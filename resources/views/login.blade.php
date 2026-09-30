@php
    $appSetting = \App\Models\AppSetting::current();
    $urlLogo = $appSetting->logo_path ? asset('storage/' . $appSetting->logo_path) : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Masuk - {{ $appSetting->nama_aplikasi }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Font dilayani sendiri, sama seperti halaman dalam aplikasi.
         Halaman login adalah halaman pertama yang dilihat setiap pengguna,
         jadi permintaan ke domain ketiga di sini paling terasa: form login
         tidak muncul sampai font selesai diunduh. --}}
    <link rel="stylesheet" href="{{ asset('vendor/fonts/inter.css') }}">

    <style>
        /* Halaman login tidak memakai layout aplikasi, jadi token dan
           komponen yang ada di ui-refresh.css tidak ikut terbawa. Yang
           dipakai di sini ditulis ulang supaya tetap satu sumber:
           radius 8px, bobot 400/500, tinggi input 40px.

           Yang TIDAK ada di sini, dan disengaja: gradient ungu, kartu
           besar melayang, radius 24px, dan bayangan panjang. Semua itu
           bentuk yang tidak ada di OpenCode. */
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            margin: 0;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            /* Safe area: mencegah notch di HP dan bilah gestur di iOS */
            padding-top: max(1.5rem, env(safe-area-inset-top));
            padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
        }

        /* Dua kolom di layar lebar: identitas di kiri, form di kanan.
           Di bawah 900px jadi satu kolom dan identitasnya disembunyikan,
           karena di HP layar penuh untuk form jauh lebih berguna. */
        .login-shell {
            width: 100%;
            max-width: 880px;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 3rem;
            align-items: center;
        }

        @media (max-width: 900px) {
            .login-shell { grid-template-columns: 1fr; max-width: 400px; gap: 0; }
            .login-identitas { display: none; }
        }

        .login-kartu {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.75rem;
        }

        @media (max-width: 400px) {
            .login-kartu { padding: 1.25rem; }
        }

        /* Kolom kiri: nama dan logo perusahaan, tanpa kartu. */
        .login-identitas { text-align: left; }
        .login-identitas .logo { height: 44px; width: auto; object-fit: contain; }
        .login-nama { font-size: 1.5rem; font-weight: 500; letter-spacing: -0.01em; margin: 1.25rem 0 0; }
        .login-tagline { font-size: 0.875rem; color: #64748b; margin: 0.375rem 0 0; font-weight: 400; }

        .login-judul { font-size: 1.125rem; font-weight: 500; margin: 0 0 1.25rem; letter-spacing: -0.01em; }

        .login-label { display: block; font-size: 0.8125rem; font-weight: 500; color: #334155; margin-bottom: 0.375rem; }

        /* Tinggi dan padding sengaja TIDAK ditulis di sini.

           ui-refresh.css mengunci semua isian satu baris ke 38px dengan
           selector `input:not([type=checkbox]):not(...)` - specificity-nya
           lebih tinggi dari kelas apa pun yang bisa ditulis di halaman ini,
           jadi nilai yang ditulis di blok style ini akan kalah. Menulis 40px
           lalu melihat hasilnya 38px sama saja dengan menulis 38px.

           Yang dipakai: 38px, sama dengan input di seluruh aplikasi. Field
           login yang lebih tinggi daripada form di dalam aplikasi akan
           jadi satu-satunya tempat yang terasa berbeda. Yang tetap ditulis di
           sini hanya yang benar-benar tidak diatur global: warna latar,
           radius, dan state fokus.

           Tombol TIDAK ikut aturan global itu, jadi tingginya ditulis di
           sini: 40px, sedikit lebih tinggi dari isian supaya tombol
           terbaca sebagai aksi, bukan field. */
        .login-input {
            width: 100%;
            padding: 0 0.75rem;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.875rem;
            font-family: inherit;
            color: #0f172a;
            outline: none;
            transition: border-color 140ms cubic-bezier(0.23, 1, 0.32, 1),
                        box-shadow 140ms cubic-bezier(0.23, 1, 0.32, 1);
        }
        .login-input::placeholder { color: #94a3b8; }
        .login-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .login-tombol {
            width: 100%;
            height: 40px;
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: background-color 140ms cubic-bezier(0.23, 1, 0.32, 1);
        }
        .login-tombol:hover { background: #4338ca; }
        .login-tombol:active { background: #3730a3; }

        /* Indikator fokus untuk tombol.

           Aturan fokus di ui-refresh.css hanya menutup input, textarea,
           dan select - tombol di halaman ini tidak punya apa pun,
           jadi orang yang memakai keyboard tidak akan tahu tombol mana
           yang sedang aktif. :focus-visible dipakai supaya cincin tidak
           muncul saat tombol diklik mouse, tapi tetap muncul saat
           dinavigasi Tab. */
        .login-tombol:focus-visible,
        #login-toggle:focus-visible {
            outline: 2px solid #4f46e5;
            outline-offset: 2px;
        }

        /* Ikon mata punya warna teks sendiri, jadi butuh kontras fokus
           yang tidak bergantung pada background kartu. */
        #login-toggle:focus-visible {
            outline-color: #4f46e5;
        }

        .login-galat {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 6px;
            padding: 0.625rem 0.75rem;
            font-size: 0.8125rem;
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
        }

        /* Warna catatan diukur, bukan dipilih karena terlihat "lembut".
           #94a3b8 di atas putih hanya 2.56:1 - gagal WCAG AA (butuh 4.5:1
           untuk teks 12px). #64748b memberi 4.76:1, jadi masih terasa
           sekunder tanpa jadi tidak terbaca. */
        .login-catatan { font-size: 0.75rem; color: #64748b; margin: 1rem 0 0; text-align: center; }
    </style>
</head>
<body>
    <main class="login-shell">
        {{-- Identitas. disembunyikan di bawah 900px (lihat .login-shell). --}}
        <section class="login-identitas">
            @if($urlLogo)
                <img src="{{ $urlLogo }}" alt="{{ $appSetting->nama_aplikasi }}" class="logo">
            @endif
            <h1 class="login-nama">{{ $appSetting->nama_aplikasi }}</h1>
            <p class="login-tagline">Sistem Kendali Produksi Terpadu</p>
        </section>

        <section class="login-kartu">
            <h2 class="login-judul">Masuk ke sistem</h2>

            <form action="{{ url('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="login-label" for="login-username">Username</label>
                    <input type="text" id="login-username" name="username" class="login-input"
                           placeholder="Nama pengguna" required autofocus
                           autocomplete="username" autocapitalize="none" spellcheck="false">
                </div>

                <div>
                    <label class="login-label" for="login-password">Password</label>
                    {{-- Tombol tampil/sembunyikan. Tanpa ini, salah ketik
                         password di HP berarti menghapus seluruhnya karakter
                         demi karakter, dan tidak ada cara memastikan sudah
                         benar sebelum mengirim. --}}
                    <div class="relative">
                        <input type="password" id="login-password" name="password" class="login-input pr-10"
                               placeholder="Kata sandi" required autocomplete="current-password">
                        <button type="button" id="login-toggle"
                                class="absolute right-1 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 rounded-md"
                                aria-label="Tampilkan password" aria-pressed="false">
                            <i class="fas fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                @if($errors->any())
                    <div class="login-galat" role="alert">
                        <i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <button type="submit" class="login-tombol">Masuk</button>
            </form>

            <p class="login-catatan">Gunakan akun yang didaftarkan oleh Administrator.</p>
        </section>
    </main>

    <script>
        // Tampilkan / sembunyikan password
        (function () {
            var input = document.getElementById('login-password');
            var tombol = document.getElementById('login-toggle');
            if (!input || !tombol) return;

            tombol.addEventListener('click', function () {
                var revealing = input.type === 'password';
                input.type = revealing ? 'text' : 'password';
                tombol.setAttribute('aria-pressed', String(revealing));
                tombol.setAttribute('aria-label', revealing ? 'Sembunyikan password' : 'Tampilkan password');
                tombol.innerHTML = '<i class="fas ' + (revealing ? 'fa-eye-slash' : 'fa-eye') + ' text-sm"></i>';
                // Fokus dikembalikan ke input supaya kursor tidak hilang
                input.focus();
            });
        })();
    </script>
</body>
</html>
