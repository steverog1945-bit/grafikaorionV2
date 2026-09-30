@php($appSetting = \App\Models\AppSetting::current())
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ $appSetting->nama_aplikasi }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Self-contained (tanpa Tailwind/Vite): halaman login tetap tampil benar walau aset build belum dibuat / server LAN offline. --}}
    <style>
        /* Token visual bergaya opencode: kanvas gelap pekat, garis tipis, satu aksen, tipografi mono. */
        :root {
            --bg: #0b0b0c; --bg-elev: #131315; --bg-input: #0e0e10;
            --line: #26262a; --line-strong: #3a3a40;
            --text: #ededef; --text-dim: #a1a1a8; --text-faint: #6b6b73;
            --accent: #fab283; --accent-ink: #1a1207; --danger: #ff6b6b; --ok: #7fd88f;
            --mono: 'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            --sans: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }
        @media (prefers-color-scheme: light) {
            :root {
                --bg: #f7f6f3; --bg-elev: #ffffff; --bg-input: #fbfaf8;
                --line: #e4e1da; --line-strong: #cfcbc1;
                --text: #1c1b19; --text-dim: #55524b; --text-faint: #8a867d;
                --accent: #d9622b; --accent-ink: #ffffff; --danger: #c93636; --ok: #2f8a48;
            }
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body { background: var(--bg); color: var(--text); font-family: var(--sans); -webkit-font-smoothing: antialiased; }
        .shell { min-height: 100vh; display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); }

        /* --- Panel kiri: landing --- */
        .hero { position: relative; padding: 48px 56px; display: flex; flex-direction: column; justify-content: space-between; border-right: 1px solid var(--line); overflow: hidden; }
        .hero > * { position: relative; }
        .brand { display: flex; align-items: center; gap: 12px; font-family: var(--mono); font-size: 14px; font-weight: 600; letter-spacing: .02em; }
        .brand img { width: 32px; height: 32px; object-fit: contain; }
        .brand .mark { width: 28px; height: 28px; border: 1px solid var(--line-strong); border-radius: 6px; display: grid; place-items: center; color: var(--accent); font-size: 14px; }
        .hero h1 { font-family: var(--mono); font-weight: 500; font-size: clamp(28px, 3.4vw, 46px); line-height: 1.15; letter-spacing: -.02em; max-width: 16ch; }
        .hero h1 em { font-style: normal; color: var(--accent); }
        .hero .lead { margin-top: 20px; color: var(--text-dim); font-size: 15px; line-height: 1.65; max-width: 46ch; }
        .features { margin-top: 36px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1px; background: var(--line); border: 1px solid var(--line); border-radius: 10px; overflow: hidden; max-width: 560px; }
        .feature { background: var(--bg-elev); padding: 16px 18px; }
        .feature b { display: block; font-family: var(--mono); font-size: 12px; font-weight: 500; color: var(--accent); margin-bottom: 6px; }
        .feature span { font-size: 13px; color: var(--text-dim); line-height: 1.5; }
        .foot { font-family: var(--mono); font-size: 12px; color: var(--text-faint); display: flex; gap: 18px; flex-wrap: wrap; }
        .foot .dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: var(--ok); margin-right: 8px; }

        /* --- Panel kanan: form --- */
        .panel { display: flex; align-items: center; justify-content: center; padding: 40px 24px; }
        .card { width: 100%; max-width: 380px; }
        .card h2 { font-family: var(--mono); font-size: 20px; font-weight: 500; letter-spacing: -.01em; }
        .card .sub { margin-top: 8px; color: var(--text-dim); font-size: 14px; line-height: 1.5; }
        form { margin-top: 28px; display: grid; gap: 18px; }
        label { display: block; font-family: var(--mono); font-size: 12px; color: var(--text-dim); margin-bottom: 8px; }
        .field { position: relative; }
        input[type=text], input[type=password] {
            width: 100%; height: 46px; padding: 0 14px; background: var(--bg-input); color: var(--text);
            border: 1px solid var(--line); border-radius: 8px; font: 400 15px var(--sans); outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        input::placeholder { color: var(--text-faint); }
        input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 22%, transparent); }
        .field .toggle { position: absolute; right: 6px; bottom: 6px; height: 34px; padding: 0 10px; background: none; border: 0; color: var(--text-faint); font: 500 12px var(--mono); cursor: pointer; border-radius: 6px; }
        .field .toggle:hover { color: var(--text); }
        .error { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border: 1px solid color-mix(in srgb, var(--danger) 45%, transparent); background: color-mix(in srgb, var(--danger) 10%, transparent); color: var(--danger); border-radius: 8px; font-size: 13.5px; line-height: 1.45; }
        .btn { height: 46px; border: 0; border-radius: 8px; background: var(--accent); color: var(--accent-ink); font: 600 14px var(--mono); letter-spacing: .01em; cursor: pointer; transition: transform .1s, filter .15s; }
        .btn:hover { filter: brightness(1.06); }
        .btn:active { transform: scale(.985); }
        .btn[disabled] { opacity: .65; cursor: progress; }
        .note { margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--line); font-size: 12.5px; color: var(--text-faint); line-height: 1.55; }
        .kbd { font-family: var(--mono); font-size: 11px; border: 1px solid var(--line-strong); border-bottom-width: 2px; border-radius: 4px; padding: 1px 6px; color: var(--text-dim); }

        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .hero { display: none; }
            .panel { min-height: 100vh; }
            .mobile-brand { display: flex !important; }
        }
        .mobile-brand { display: none; margin-bottom: 32px; }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>
<div class="shell">

    <section class="hero" aria-label="Tentang aplikasi">
        <div class="brand">
            @if($appSetting->logo_path)
                <img src="{{ asset('storage/' . $appSetting->logo_path) }}" alt="Logo">
            @else
                <span class="mark">&gt;_</span>
            @endif
            <span>{{ $appSetting->nama_aplikasi }}</span>
        </div>

        <div>
            <h1>Satu sistem untuk <em>seluruh alur</em> percetakan.</h1>
            <p class="lead">Dari order, desain, produksi, hingga kas dan laporan &mdash; tercatat rapi, real-time, dan dapat ditelusuri di jaringan kantor Anda.</p>

            <div class="features">
                <div class="feature"><b>01 / Kasir &amp; Order</b><span>Nota, deposit, dan pembayaran dalam satu layar.</span></div>
                <div class="feature"><b>02 / Produksi</b><span>SPK, antrean kerja, QC, hingga collecting.</span></div>
                <div class="feature"><b>03 / Stok Bahan</b><span>Kartu stok, pembelian, dan opname terkontrol.</span></div>
                <div class="feature"><b>04 / Keuangan</b><span>Jurnal, closing periode, dan audit bulanan.</span></div>
            </div>
        </div>

        <div class="foot">
            <span><span class="dot"></span>Jaringan lokal (LAN)</span>
            <span>Akses sesuai peran</span>
            <span>Aktivitas tercatat</span>
        </div>
    </section>

    <main class="panel">
        <div class="card">
            <div class="brand mobile-brand">
                @if($appSetting->logo_path)
                    <img src="{{ asset('storage/' . $appSetting->logo_path) }}" alt="Logo">
                @else
                    <span class="mark">&gt;_</span>
                @endif
                <span>{{ $appSetting->nama_aplikasi }}</span>
            </div>

            <h2>Masuk ke sistem</h2>
            <p class="sub">Gunakan akun yang didaftarkan Administrator. Setelah masuk Anda langsung diarahkan ke Dashboard.</p>

            <form action="{{ url('login') }}" method="POST" id="formLogin" autocomplete="on">
                @csrf

                <div class="field">
                    <label for="username">username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}"
                           placeholder="mis. kasir01" autocomplete="username" required autofocus>
                </div>

                <div class="field">
                    <label for="password">password</label>
                    <input type="password" id="password" name="password" placeholder="Kata sandi" autocomplete="current-password" required>
                    <button type="button" class="toggle" id="togglePw" aria-label="Tampilkan atau sembunyikan kata sandi">tampilkan</button>
                </div>

                @if($errors->any())
                    <div class="error" role="alert"><span aria-hidden="true">!</span><span>{{ $errors->first() }}</span></div>
                @endif

                <button type="submit" class="btn" id="btnLogin">Masuk &rarr;</button>
            </form>

            <p class="note">Tekan <span class="kbd">Enter</span> untuk masuk. Lupa kata sandi? Hubungi Administrator.</p>
        </div>
    </main>

</div>
<script>
    (function () {
        var pw = document.getElementById('password'), t = document.getElementById('togglePw');
        t.addEventListener('click', function () {
            var show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            t.textContent = show ? 'sembunyikan' : 'tampilkan';
        });
        // Cegah klik ganda saat kirim.
        document.getElementById('formLogin').addEventListener('submit', function () {
            var b = document.getElementById('btnLogin'); b.disabled = true; b.textContent = 'Memeriksa...';
        });
        // Tombol Back setelah logout memulihkan halaman dari cache -> pastikan tombol tidak macet.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) { var b = document.getElementById('btnLogin'); b.disabled = false; b.innerHTML = 'Masuk &rarr;'; }
        });
    })();
</script>
</body>
</html>
