<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Preview SELALU noindex. URL preview tidak boleh terindeks. --}}
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>{{ $seo['title'] ?? $page->title }} — Preview</title>
    @if(!empty($seo['description']))
    <meta name="description" content="{{ $seo['description'] }}">
    @endif
    @if(!empty($seo['og_title']))
    <meta property="og:title" content="{{ $seo['og_title'] }}">
    @endif
    @if(!empty($seo['og_description']))
    <meta property="og:description" content="{{ $seo['og_description'] }}">
    @endif
    {{-- Token tema harus ada sebelum renderer-styles supaya preview memakai
         warna preset aktif, sama seperti halaman publik. --}}
    @include('publik.partials.theme-vars')

    @include('pengaturan.partials.renderer-styles')
    <style>
        :root {
            /* Palet resmi ORION — ROYAL INDIGO */
            --kertas: var(--color-indigo-50);   /* permukaan netral (violet tint) */
            --tinta: var(--color-indigo-950);    /* DEEP NIGHT */
            --aksen: var(--color-indigo-600);    /* ELECTRIC VIOLET */
            --keluar: var(--color-indigo-50);   /* tint violet muda */
            --kontras-aksen: var(--color-white);  /* teks di atas aksen — 5.29:1 AA */
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--kertas);
            color: var(--tinta);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        .preview-banner {
            background-color: var(--tinta);
            color: var(--orion-neutral-100, var(--color-white));
            text-align: center;
            padding: 8px 16px;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        .preview-banner strong { color: var(--orion-cyan-light, var(--color-slate-200)); }
        /* ---- Responsive switcher (preview only) ---- */
        .preview-switcher {
            display: inline-flex; gap: 4px; margin-left: 16px;
            background: color-mix(in srgb, var(--color-white) 10%, transparent); border-radius: 8px; padding: 3px;
        }
        .preview-switcher button {
            background: transparent; border: 0; color: var(--orion-neutral-700, var(--color-slate-500));
            font-size: 12px; font-weight: 600; padding: 5px 12px;
            border-radius: 6px; cursor: pointer; font-family: inherit;
        }
        .preview-switcher button.is-active { background: var(--aksen); color: var(--kontras-aksen); }
        .preview-switcher button:hover:not(.is-active) { color: var(--color-white); }
        .preview-switcher button:focus-visible { outline: 2px solid var(--orion-cyan-light, var(--color-slate-200)); outline-offset: 2px; }
        /* Iframe container */
        .preview-iframe-wrapper {
            display: block; width: 100%; height: calc(100vh - 52px); min-height: 600px;
            border: 0; background: var(--color-white); border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 24px color-mix(in srgb, var(--color-indigo-950) 8%, transparent);
            transition: width 200ms ease, height 200ms ease;
            overflow: hidden; /* mencegah konten melebarkan iframe */
        }
        .preview-iframe-wrapper.lpb-mode-desktop { width: 100%; max-width: none; height: calc(100vh - 52px); }
        .preview-iframe-wrapper.lpb-mode-tablet { width: 768px; max-width: 768px; height: calc(100vh - 52px); margin: 0 auto; }
        .preview-iframe-wrapper.lpb-mode-mobile { width: 375px; max-width: 375px; height: calc(100vh - 52px); margin: 0 auto; }
    </style>
</head>
<body>
    <div class="preview-banner">
        <strong>PREVIEW MODE</strong> — {{ ucfirst($previewMode) }} | Not indexed | Admin only
        <span class="preview-switcher" role="group" aria-label="Lebar preview">
            <button type="button" data-preview-width="desktop" class="is-active" aria-pressed="true">Desktop</button>
            <button type="button" data-preview-width="tablet" aria-pressed="false">Tablet</button>
            <button type="button" data-preview-width="mobile" aria-pressed="false">Mobile</button>
        </span>
    </div>

    {{-- Src HARUS mengikuti mode preview.
         Kalau selalu tanpa parameter, maka "Published Testing" justru
         menampilkan isi DRAFT — operator menyetujui sesuatu yang
         belum pernah dipublish. --}}
    @php
        $iframeRoute = route('landing-page-builder.preview.draft.iframe');
        $previewSrc = match ($previewMode) {
            'published' => $iframeRoute . '?mode=published',
            'revision'  => $iframeRoute . '?mode=revision&rev=' . (int) ($revisionNumber ?? 1),
            default     => $iframeRoute . '?mode=draft',
        };
    @endphp
    <iframe class="preview-iframe-wrapper lpb-mode-desktop"
            src="{{ $previewSrc }}"
            title="Preview Website"
            sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox">
    </iframe>

    <script>
        // Responsive switcher via iframe. Mengubah ukuran iframe, BUKAN container width.
        // Iframe memiliki viewport sendiri sehingga @media query BERFUNGSI SEBENARNYA.
        (function () {
            var switcher = document.querySelector('.preview-switcher');
            var iframe = document.querySelector('.preview-iframe-wrapper');
            if (!switcher || !iframe) return;

            var UKURAN = {
                desktop: { width: '100%', height: 'calc(100vh - 52px)', margin: '0' },
                tablet:  { width: '768px',  height: 'calc(100vh - 52px)', margin: '0 auto' },
                mobile:  { width: '375px',  height: 'calc(100vh - 52px)', margin: '0 auto' }
            };

            function terapkan(mode) {
                iframe.classList.remove('lpb-mode-desktop', 'lpb-mode-tablet', 'lpb-mode-mobile');
                iframe.classList.add('lpb-mode-' + mode);
                var sz = UKURAN[mode] || UKURAN.desktop;
                iframe.style.width = sz.width;
                iframe.style.height = sz.height;
                iframe.style.margin = sz.margin;
            }

            switcher.addEventListener('click', function (e) {
                var btn = e.target.closest('button[data-preview-width]');
                if (!btn) return;
                switcher.querySelectorAll('button').forEach(function (b) {
                    b.classList.remove('is-active');
                    b.setAttribute('aria-pressed', 'false');
                });
                btn.classList.add('is-active');
                btn.setAttribute('aria-pressed', 'true');
                terapkan(btn.getAttribute('data-preview-width'));
            });
        })();
    </script>
</body>
</html>