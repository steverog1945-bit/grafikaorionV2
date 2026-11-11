{{-- Master page untuk 404 / 500 / 403 / 503.
     Standalone (tidak memuat layouts.app karena halaman error ini adalah
     tangkapan terakhir saat app tidak jalan; untuk itu self-contained,
     tidak ada bergantung pada CSS vendor atau koneksi internet). --}}
@props([
    'kode'    => '500',
    'judul'   => 'Terjadi kesalahan',
    'pesan'   => 'Maaf, ada gangguan.',
    'tampilkanPintasan' => true,
    'pintasan' => [],
])
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#f8fafc">
<title>{{ $kode }} — {{ $judul }} · ORION GRAFIKA</title>
<style>
    :root {
        --ink: #0f172a;
        --muted: #64748b;
        --line: #e2e8f0;
        --brand: #006c7b;
        --brand-soft: rgba(0, 108, 123, .08);
        --brand-soft-2: rgba(0, 108, 123, .14);
        --peringatan: #b45309;
        --peringatan-soft: rgba(180, 83, 9, .08);
        --rose: #be123d;
        --rose-soft: rgba(190, 18, 61, .08);
        --amber: #d97706;
        --amber-soft: rgba(217, 119, 6, .08);
        --indigo: #4f46e5;
        --indigo-soft: rgba(79, 70, 229, .08);
        --emerald: #059669;
        --emerald-soft: rgba(5, 150, 105, .08);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; height: 100%; }
    body {
        min-height: 100vh;
        display: flex; align-items: center; justify-content: center;
        padding: 28px 20px;
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        color: var(--ink);
        background: #f8fafc;
        background-image:
            radial-gradient(1100px 620px at 85% -10%, var(--brand-soft-2) 0%, transparent 60%),
            radial-gradient(900px 520px at -15% 110%, var(--indigo-soft) 0%, transparent 55%),
            radial-gradient(700px 420px at 50% -30%, var(--emerald-soft) 0%, transparent 50%);
        background-attachment: fixed;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }
    .wrap { width: 100%; max-width: 720px; }

    /* Kartu utama */
    .panel {
        position: relative;
        border-radius: 20px;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, .88);
        backdrop-filter: blur(10px);
        box-shadow:
            0 1px 2px rgba(15, 23, 42, .04),
            0 12px 40px -12px rgba(15, 23, 42, .10);
        padding: 30px 40px 28px;
        overflow: hidden;
    }
    .panel::before {
        content: "";
        position: absolute; inset: 0 0 auto 0; height: 4px;
        background: linear-gradient(90deg, var(--brand) 0%, #17a0a1 55%, #054f5d 100%);
        border-top-left-radius: 20px; border-top-right-radius: 20px;
    }

    @media (prefers-reduced-motion: no-preference) {
        @keyframes err-fade-up { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        @keyframes err-pop     { from { opacity: 0; transform: scale(.97) } to { opacity: 1; transform: none } }
        .panel { animation: err-fade-up .45s cubic-bezier(.22,1,.36,1) both; }
        .icon-tile { animation: err-pop .35s .1s both; }
        .btn { transition: transform .15s ease, box-shadow .15s ease, background-color .2s ease; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px -10px rgba(15,23,42,.35); }
        .btn:active { transform: translateY(0) scale(.98); box-shadow: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation: none !important; transition: none !important; }
    }

    /* Layout header panel */
    .ver {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        margin-bottom: 22px; padding-bottom: 16px; border-bottom: 1px solid var(--line);
    }
    .brand { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .brand-mark {
        width: 34px; height: 34px; border-radius: 10px;
        background: linear-gradient(135deg, #006c7b 0%, #0b7a87 100%);
        color: #fff; display: grid; place-items: center;
        font-weight: 700; font-size: 15px; letter-spacing: 0.02em;
        box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 1px 2px rgba(0,0,0,.25);
        flex: 0 0 auto;
    }
    .brand-text { display: flex; flex-direction: column; line-height: 1.1; }
    .brand-text b { font-size: 12px; font-weight: 700; color: #0f172a; letter-spacing: 0.04em; }
    .brand-text span { font-size: 10px; color: var(--muted); letter-spacing: 0.08em; text-transform: uppercase; }

    .kode-chip {
        flex: 0 0 auto;
        font: 700 12px/1 'Inter', system-ui, -apple-system, sans-serif;
        letter-spacing: 0.14em; text-transform: uppercase;
        color: var(--brand); background: var(--brand-soft);
        border: 1px solid var(--brand-soft-2);
        padding: 6px 10px; border-radius: 8px;
    }

    /* Utama */
    .hero { display: grid; grid-template-columns: auto 1fr; gap: 22px; align-items: center; }
    @media (max-width: 640px) { .hero { grid-template-columns: 1fr; text-align: center; } }

    .icon-tile {
        width: 84px; height: 84px; border-radius: 22px;
        display: grid; place-items: center; flex: 0 0 auto;
        background: var(--icon-bg, var(--brand-soft));
        border: 1px solid var(--icon-line, var(--brand-soft-2));
    }
    @media (max-width: 640px) { .icon-tile { margin: 0 auto; } }
    .icon-tile svg { width: 44px; height: 44px; }

    .hero-text { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
    @media (max-width: 640px) { .hero-text { align-items: center; } }

    h1 { font-size: 20px; line-height: 1.25; font-weight: 700; letter-spacing: -0.01em; margin: 0; }
    p.msg { margin: 0; font-size: 13.5px; line-height: 1.55; color: #475569; }

    /* Tombol aksi — sesuai bentuk tombol aplikasi lain */
    .btn {
        appearance: none; border: 0; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 9px;
        font: 600 13px/1 'Inter', system-ui, -apple-system, sans-serif;
        padding: 12px 18px; border-radius: 10px; transition: 120ms ease;
        background: var(--brand); color: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .08), 0 0 0 1px rgba(6, 91, 102, .5) inset;
    }
    .btn:hover { background: #085862; }
    .btn:active { transform: translateY(1px); }
    .btn:focus-visible { outline: 3px solid var(--brand); outline-offset: 3px; }
    .btn svg { width: 14px; height: 14px; }

    .btn-ghost {
        background: transparent; color: var(--brand);
        box-shadow: none; border: 1px solid var(--line);
    }
    .btn-ghost:hover { background: var(--brand-soft); }

    .aksi { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 6px; }
    @media (max-width: 640px) { .aksi { justify-content: center; } }

    /* Kotak kecil di bawah */
    .foot {
        margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--line);
        display: flex; flex-wrap: wrap; gap: 10px 18px; align-items: center; justify-content: space-between;
        color: var(--muted); font-size: 11px;
    }
    .foot b { color: var(--ink); font-weight: 600; }
    .dotline { display: inline-flex; align-items: center; gap: 8px; }
    .dotline i { width: 6px; height: 6px; border-radius: 999px; background: #10b981; }
</style>
</head>
<body role="main">
    <main class="wrap">
        <section class="panel" aria-labelledby="err-title">
            <header class="ver">
                <div class="brand" aria-hidden="true">
                    <div class="brand-mark">O</div>
                    <div class="brand-text"><b>ORION GRAFIKA</b><span>ERP Notice</span></div>
                </div>
                <span class="kode-chip" aria-label="Kode kesalahan">Error {{ $kode }}</span>
            </header>

            <div class="hero">
                <div class="icon-tile" style="{{ $iconStyle ?? '' }}">
                    {!! $ikon !!}
                </div>
                <div class="hero-text">
                    <h1 id="err-title">{{ $judul }}</h1>
                    <p class="msg">{{ $pesan }}</p>
                    @if(!empty($detail))
                        <p class="msg" style="font-size:12px; color:#64748b;">{{ $detail }}</p>
                    @endif
                    <div class="aksi" role="group" aria-label="Tindakan yang bisa dilakukan">
                        @foreach($pintasan as $p)
                            <a href="{{ $p['href'] }}" class="btn {{ ($p['varian'] ?? 'primer') === 'ghost' ? 'btn-ghost' : '' }}" rel="noopener">
                                @if(!empty($p['ikon'])){!! $p['ikon'] !!}@endif
                                {{ $p['teks'] }}
                            </a>
                        @endforeach
                        {{-- CTA default: ke home --}}
                        @if($tampilkanPintasan)
                            <a href="{{ url('/') }}" class="btn btn-ghost" rel="noopener">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l9-9 9 9"/><path d="M9 21V12h6v9"/></svg>
                                Ke Beranda
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <footer class="foot">
                <span class="dotline"><i></i>Modul yang berjalan normal — <b>cek kembali status aplikasi</b></span>
                <span>Kendala berulang? Hubungi <b>Administrator</b></span>
            </footer>
        </section>
    </main>
</body>
</html>
