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
<meta name="theme-color" content="var(--color-slate-50)">
<title>{{ $kode }} — {{ $judul }} · ORION GRAFIKA</title>
<style>
    :root {
        --ink: var(--color-slate-900);
        --muted: var(--color-slate-500);
        --line: var(--color-slate-200);
        --brand: var(--brand-accent-light);
        --brand-soft: color-mix(in srgb, var(--brand-accent-light) 8%, transparent);
        --brand-soft-2: color-mix(in srgb, var(--brand-accent-light) 14%, transparent);
        --peringatan: var(--color-amber-700);
        --peringatan-soft: color-mix(in srgb, var(--color-amber-700) 8%, transparent);
        --rose: var(--color-rose-700);
        --rose-soft: color-mix(in srgb, var(--color-rose-700) 8%, transparent);
        --amber: var(--color-amber-600);
        --amber-soft: color-mix(in srgb, var(--color-amber-600) 8%, transparent);
        --indigo: var(--color-indigo-600);
        --indigo-soft: color-mix(in srgb, var(--color-indigo-600) 8%, transparent);
        --emerald: var(--color-emerald-600);
        --emerald-soft: color-mix(in srgb, var(--color-emerald-600) 8%, transparent);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; height: 100%; }
    body {
        min-height: 100vh;
        display: flex; align-items: center; justify-content: center;
        padding: 28px 20px;
        font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        color: var(--ink);
        background: var(--color-slate-50);
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
        background: color-mix(in srgb, var(--color-white) 88%, transparent);
        backdrop-filter: blur(10px);
        box-shadow:
            0 1px 2px color-mix(in srgb, var(--color-slate-900) 4%, transparent),
            0 12px 40px -12px color-mix(in srgb, var(--color-slate-900) 10%, transparent);
        padding: 30px 40px 28px;
        overflow: hidden;
    }
    .panel::before {
        content: "";
        position: absolute; inset: 0 0 auto 0; height: 4px;
        background: linear-gradient(90deg, var(--brand) 0%, var(--brand-accent-dark) 55%, var(--brand-sidebar-border) 100%);
        border-top-left-radius: 20px; border-top-right-radius: 20px;
    }

    @media (prefers-reduced-motion: no-preference) {
        @keyframes err-fade-up { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        @keyframes err-pop     { from { opacity: 0; transform: scale(.97) } to { opacity: 1; transform: none } }
        .panel { animation: err-fade-up .45s cubic-bezier(.22,1,.36,1) both; }
        .icon-tile { animation: err-pop .35s .1s both; }
        .btn { transition: transform .15s ease, box-shadow .15s ease, background-color .2s ease; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px -10px color-mix(in srgb, var(--color-slate-900) 35%, transparent); }
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
        background: linear-gradient(135deg, var(--brand-accent-light) 0%, var(--brand-accent-hover-light) 100%);
        color: var(--color-white); display: grid; place-items: center;
        font-weight: 700; font-size: 15px; letter-spacing: 0.02em;
        box-shadow: 0 1px 0 color-mix(in srgb, var(--color-white) 60%, transparent) inset, 0 1px 2px color-mix(in srgb, var(--color-black) 25%, transparent);
        flex: 0 0 auto;
    }
    .brand-text { display: flex; flex-direction: column; line-height: 1.1; }
    .brand-text b { font-size: 12px; font-weight: 700; color: var(--color-slate-900); letter-spacing: 0.04em; }
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
    p.msg { margin: 0; font-size: 13.5px; line-height: 1.55; color: var(--color-slate-600); }

    /* Tombol aksi — sesuai bentuk tombol aplikasi lain */
    .btn {
        appearance: none; border: 0; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 9px;
        font: 600 13px/1 'Inter', system-ui, -apple-system, sans-serif;
        padding: 12px 18px; border-radius: 10px; transition: 120ms ease;
        background: var(--brand); color: var(--color-white);
        box-shadow: 0 1px 2px color-mix(in srgb, var(--color-slate-900) 8%, transparent), 0 0 0 1px color-mix(in srgb, var(--brand-accent-hover-light) 50%, transparent) inset;
    }
    .btn:hover { background: var(--brand-accent-hover-light); }
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
    .dotline i { width: 6px; height: 6px; border-radius: 999px; background: var(--color-emerald-500); }
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
                        <p class="msg" style="font-size:12px; color:var(--color-slate-500);">{{ $detail }}</p>
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
