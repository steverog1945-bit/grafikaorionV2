@php
    $appSetting = \App\Models\AppSetting::current();
    $urlLogo = $appSetting->logo_path ? asset('storage/' . $appSetting->logo_path) : null;

    // Isi landing diambil dari modul yang benar-benar ada di aplikasi ini.
    // Tidak ada angka, testimoni, atau klaim yang tidak bisa dibuktikan.
    $modul = [
        ['Penjualan dan kasir', 'Nota cetak, penjualan barang, DP, pelunasan, deposit pelanggan, buka dan tutup kas harian.'],
        ['Produksi', 'SPK per item, antrean desain dan cetak, jumlah cetak, salah cetak, QC dan rework.'],
        ['Serah terima', 'Barang keluar tercatat penerimanya. Nota yang belum lunas hanya keluar dengan alasan tertulis.'],
        ['Stok dan pembelian', 'Kartu stok, permintaan dan pesanan pembelian, kedatangan bahan, hutang supplier, stok opname.'],
        ['Order jersey', 'Daftar pemain, ukuran dari master, estimasi kain, pembayaran bertahap.'],
        ['Akuntansi', 'Jurnal otomatis dari setiap transaksi, buku besar, neraca, laba rugi, tutup periode.'],
        ['Laporan dan audit', 'Piutang, profitabilitas, pemakaian bahan, audit bulanan pemilik, jejak siapa mengubah apa.'],
    ];
    $alur = [
        ['Order', 'Kasir membuat nota. Harga mengikuti price list golongan pelanggan.'],
        ['Bayar', 'DP atau lunas, tunai, transfer, atau dari deposit.'],
        ['Produksi', 'Operator mengambil SPK. Bahan berkurang sesuai pemakaian.'],
        ['Serah terima', 'Barang diserahkan, nama penerima dan bukti dicatat.'],
        ['Buku', 'Piutang, kas, persediaan, dan pendapatan terjurnal sendiri.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Masuk - {{ $appSetting->nama_aplikasi }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Font dilayani sendiri (server LAN tanpa internet tetap tampil benar). --}}
    <link rel="stylesheet" href="{{ asset('vendor/fonts/inter.css') }}">

    <style>
        /* Landing + login. Token visual mengikuti opencode: kanvas kertas hangat, teks hampir hitam,
           garis rambut 1px, huruf mono untuk judul dan label, sudut kecil, satu aksen (indigo merek).
           Tanpa gradien, tanpa kaca, tanpa bayangan, tanpa kartu fitur kembar. */
        :root {
            --kertas: #fdfcfc; --lembar: #ffffff; --garis: #e6e2df; --garis-kuat: #cfc9c5;
            --tinta: #201d1d; --tinta-2: #57504e; --tinta-3: #6f6866;
            --aksen: #4f46e5; --aksen-tekan: #4338ca;
            --mono: ui-monospace, 'Cascadia Mono', 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
            --sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0; background: var(--kertas); color: var(--tinta);
            font-family: var(--sans); font-size: 15px; line-height: 1.6;
            padding: 0 max(1rem, env(safe-area-inset-right)) max(2rem, env(safe-area-inset-bottom)) max(1rem, env(safe-area-inset-left));
        }
        a { color: inherit; }

        /* Satu kolom bergaris, seperti halaman dokumen. */
        .lp { max-width: 1080px; margin: 0 auto; border-left: 1px solid var(--garis); border-right: 1px solid var(--garis); background: var(--kertas); }
        .lp > * { border-bottom: 1px solid var(--garis); }

        .lp-atas { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.875rem 1.5rem; position: sticky; top: 0; background: var(--kertas); z-index: 5; }
        .lp-merek { display: flex; align-items: center; gap: 0.625rem; font-family: var(--mono); font-weight: 600; font-size: 14px; text-decoration: none; }
        .lp-merek img { height: 28px; width: auto; object-fit: contain; }
        .lp-nav { display: flex; gap: 1.5rem; font-family: var(--mono); font-size: 13px; }
        .lp-nav a { text-decoration: none; color: var(--tinta-2); }
        .lp-nav a:hover { color: var(--tinta); text-decoration: underline; text-underline-offset: 4px; }

        .lp-hero { display: grid; grid-template-columns: minmax(0, 1fr) 380px; }
        .lp-hero-teks { padding: 3.5rem 1.5rem 3rem; border-right: 1px solid var(--garis); }
        .lp-hero h1 { font-family: var(--mono); font-weight: 600; font-size: clamp(1.625rem, 3.2vw, 2.375rem); line-height: 1.2; letter-spacing: -0.02em; margin: 0; max-width: 20ch; }
        .lp-lead { margin: 1.25rem 0 0; color: var(--tinta-2); max-width: 52ch; }
        .lp-fakta { list-style: none; margin: 2rem 0 0; padding: 0; font-family: var(--mono); font-size: 13px; color: var(--tinta-2); }
        .lp-fakta li { padding: 0.5rem 0; border-top: 1px solid var(--garis); display: flex; gap: 0.75rem; }
        .lp-fakta li::before { content: '[+]'; color: var(--tinta-3); }

        .lp-masuk { padding: 2.25rem 1.5rem; background: var(--lembar); scroll-margin-top: 4rem; }
        .login-judul { font-family: var(--mono); font-size: 1.0625rem; font-weight: 600; margin: 0 0 1.25rem; }
        .login-label { display: block; font-family: var(--mono); font-size: 13px; color: var(--tinta-2); margin-bottom: 0.375rem; }
        /* Tinggi isian mengikuti aturan global aplikasi (38px), lihat ui-refresh.css. */
        .login-input { width: 100%; padding: 0 0.75rem; background: #fff; border: 1px solid var(--garis-kuat); border-radius: 4px; font-size: 0.875rem; font-family: inherit; color: var(--tinta); outline: none; transition: border-color 140ms, box-shadow 140ms; }
        .login-input::placeholder { color: #8f8886; }
        .login-input:focus { border-color: var(--aksen); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); }
        .login-tombol { width: 100%; height: 40px; background: var(--tinta); color: #fff; border: none; border-radius: 4px; font-family: var(--mono); font-size: 14px; font-weight: 600; cursor: pointer; transition: background-color 140ms; }
        .login-tombol:hover { background: var(--aksen); }
        .login-tombol:active { background: var(--aksen-tekan); }
        .login-tombol:focus-visible, #login-toggle:focus-visible, .lp a:focus-visible { outline: 2px solid var(--aksen); outline-offset: 2px; }
        .login-galat { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 4px; padding: 0.625rem 0.75rem; font-size: 0.8125rem; display: flex; gap: 0.5rem; align-items: flex-start; }
        .login-catatan { font-size: 13px; color: var(--tinta-3); margin: 1rem 0 0; }

        .lp-bagian { padding: 2.5rem 1.5rem; scroll-margin-top: 3.5rem; }
        .lp-bagian h2 { font-family: var(--mono); font-size: 1rem; font-weight: 600; margin: 0 0 0.25rem; }
        .lp-bagian > p { margin: 0 0 1.5rem; color: var(--tinta-2); max-width: 60ch; }

        /* Modul: daftar dua kolom bergaris, bukan kartu. */
        .lp-daftar { margin: 0; border-top: 1px solid var(--garis); }
        .lp-daftar div { display: grid; grid-template-columns: 220px minmax(0, 1fr); gap: 1.5rem; padding: 0.75rem 0; border-bottom: 1px solid var(--garis); }
        .lp-daftar dt { font-family: var(--mono); font-size: 14px; font-weight: 600; }
        .lp-daftar dd { margin: 0; color: var(--tinta-2); }

        .lp-alur { list-style: none; margin: 0; padding: 0; counter-reset: langkah; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); border: 1px solid var(--garis); }
        .lp-alur li { counter-increment: langkah; padding: 1rem; border-right: 1px solid var(--garis); font-size: 14px; color: var(--tinta-2); }
        .lp-alur li:last-child { border-right: 0; }
        .lp-alur b { display: block; font-family: var(--mono); color: var(--tinta); margin-bottom: 0.25rem; }
        .lp-alur b::before { content: counter(langkah) '. '; color: var(--tinta-3); font-weight: 400; }

        .lp-aturan { list-style: disc; margin: 0; padding-left: 1.25rem; color: var(--tinta-2); max-width: 64ch; }
        .lp-aturan li { margin-bottom: 0.375rem; }

        .lp-kaki { padding: 1.5rem; font-family: var(--mono); font-size: 13px; color: var(--tinta-3); display: flex; flex-wrap: wrap; gap: 0.25rem 2rem; justify-content: space-between; }

        /* Layar sempit: form lebih dulu, karena halaman ini alat kerja. */
        @media (max-width: 900px) {
            .lp-nav { display: none; }
            .lp-hero { grid-template-columns: 1fr; }
            .lp-masuk { order: -1; border-bottom: 1px solid var(--garis); }
            .lp-hero-teks { border-right: 0; padding: 2rem 1.25rem; }
            .lp-daftar div { grid-template-columns: 1fr; gap: 0.125rem; }
            .lp-alur { grid-template-columns: 1fr; }
            .lp-alur li { border-right: 0; border-bottom: 1px solid var(--garis); }
            .lp-alur li:last-child { border-bottom: 0; }
            .lp-bagian, .lp-masuk { padding-left: 1.25rem; padding-right: 1.25rem; }
        }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } * { transition: none !important; } }
    </style>
</head>
<body>
<div class="lp">
    <header class="lp-atas">
        <a class="lp-merek" href="#masuk">
            @if($urlLogo)
                <img src="{{ $urlLogo }}" alt="">
            @endif
            <span>{{ $appSetting->nama_aplikasi }}</span>
        </a>
        <nav class="lp-nav" aria-label="Isi halaman">
            <a href="#modul">Modul</a>
            <a href="#alur">Alur nota</a>
            <a href="#akses">Akses</a>
            <a href="#masuk">Masuk</a>
        </nav>
    </header>

    <div class="lp-hero">
        <section class="lp-hero-teks">
            <h1>Order, produksi, stok, dan kas percetakan dalam satu catatan.</h1>
            <p class="lp-lead">Sistem kerja {{ $appSetting->nama_aplikasi }}. Satu nota diikuti dari meja kasir sampai barang diserahkan, dan angkanya masuk ke buku tanpa diketik ulang.</p>
            <ul class="lp-fakta">
                <li>Berjalan di jaringan kantor, tidak bergantung pada internet.</li>
                <li>Setiap orang hanya melihat menu sesuai perannya.</li>
                <li>Perubahan harga, stok, dan pembayaran tercatat beserta pelakunya.</li>
            </ul>
        </section>

        <section class="lp-masuk" id="masuk" aria-labelledby="judul-masuk">
            <h2 class="login-judul" id="judul-masuk">Masuk ke sistem</h2>

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
                    {{-- Tombol tampil/sembunyikan: salah ketik di HP tidak perlu menghapus semuanya. --}}
                    <div class="relative">
                        <input type="password" id="login-password" name="password" class="login-input pr-10"
                               placeholder="Kata sandi" required autocomplete="current-password">
                        <button type="button" id="login-toggle"
                                class="absolute right-1 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-slate-500 hover:text-slate-800 rounded"
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

            <p class="login-catatan">Gunakan akun yang didaftarkan oleh Administrator. Setelah masuk Anda langsung berada di Dashboard.</p>
        </section>
    </div>

    <section class="lp-bagian" id="modul">
        <h2>Modul</h2>
        <p>Yang tersedia setelah masuk, tergantung peran akun Anda.</p>
        <dl class="lp-daftar">
            @foreach($modul as [$nama, $isi])
                <div><dt>{{ $nama }}</dt><dd>{{ $isi }}</dd></div>
            @endforeach
        </dl>
    </section>

    <section class="lp-bagian" id="alur">
        <h2>Alur satu nota</h2>
        <p>Urutan kerja yang diikuti sistem. Tiap langkah dikerjakan oleh peran yang berbeda.</p>
        <ol class="lp-alur">
            @foreach($alur as [$nama, $isi])
                <li><b>{{ $nama }}</b>{{ $isi }}</li>
            @endforeach
        </ol>
    </section>

    <section class="lp-bagian" id="akses">
        <h2>Akses</h2>
        <p>Beberapa hal yang perlu diketahui sebelum memakai.</p>
        <ul class="lp-aturan">
            <li>Akun dibuat oleh Administrator. Tidak ada pendaftaran sendiri.</li>
            <li>Lupa password: minta Administrator mengatur ulang.</li>
            <li>Pembatalan nota, koreksi stok, dan pengeluaran kas kecil diajukan dulu, lalu disetujui orang lain.</li>
            <li>Keluar dari sistem sebelum meninggalkan komputer yang dipakai bersama.</li>
        </ul>
    </section>

    <footer class="lp-kaki">
        <span>{{ $appSetting->nama_aplikasi }}</span>
        @if($appSetting->alamat)<span>{{ $appSetting->alamat }}</span>@endif
        @if($appSetting->telepon)<span>{{ $appSetting->telepon }}</span>@endif
    </footer>
</div>

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
            input.focus();
        });
    })();
</script>
</body>
</html>
