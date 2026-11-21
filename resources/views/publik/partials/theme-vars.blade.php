{{--
    Token warna untuk halaman Publik / Landing Page Builder.

    Masalah yang diperbaiki: setelah migrasi tema, Blade publik memakai
    var(--color-indigo-*) / var(--brand-*) tetapi token itu hanya disuntikkan
    oleh layouts/app.blade.php (halaman ERP). Halaman publik tidak memuat
    layout itu, sehingga semua warna jadi kosong -> halaman putih polos dan
    tombol login putih.

    Sumber nilai:
    - dinamis: BrandThemeService::current()['variables'] (sama persis dengan
      yang dipakai layouts/app.blade.php), jadi tema pilihan owner tetap
      berlaku di halaman publik.
    - statis: ui-tokens.css + ramp amber/rose/emerald dari @theme app.css.
      Tidak diubah nilainya, hanya disalin supaya halaman tanpa @vite pun
      punya token yang sama dengan halaman ERP.
--}}
@php
    $publikBrandTheme = app(\App\Services\BrandThemeService::class)->current();
    $publikBrandVars = collect($publikBrandTheme['variables'] ?? [])
        ->map(fn ($value, $name) => $name . ':' . htmlspecialchars((string) $value, ENT_NOQUOTES, 'UTF-8'))
        ->implode(';');
@endphp
<style>
    :root {
        --color-white: #ffffff;
        --color-black: #000000;
        --ink-900: #171717;
        --ink-600: #6f6f6f;
        --line-200: #e5e5e5;
        --neutral-100: #f3f3f3;
        --paper: #fcfcfc;

        --color-amber-50: #FEF6EA;
        --color-amber-100: #FCE9CC;
        --color-amber-200: #F8D29A;
        --color-amber-300: #F2B565;
        --color-amber-400: #EC9C3B;
        --color-amber-500: var(--brand-sidebar-accent);
        --color-amber-600: #A15810;
        --color-amber-700: #A15810;
        --color-amber-800: #7A440F;
        --color-amber-900: #54300B;

        --color-rose-50: #FAF0EA;
        --color-rose-100: #F4DDD1;
        --color-rose-200: #E7BAA6;
        --color-rose-300: #D8947A;
        --color-rose-400: #C56F51;
        --color-rose-500: #A5501D;
        --color-rose-600: #93481A;
        --color-rose-700: #743A15;
        --color-rose-800: #552B10;
        --color-rose-900: #3A1D0B;

        --color-emerald-50: #EAF6F1;
        --color-emerald-100: #D2EBE2;
        --color-emerald-200: #A6D6C5;
        --color-emerald-300: #6FBBA1;
        --color-emerald-400: #2E9C7E;
        --color-emerald-500: #0B7A5F;
        --color-emerald-600: #0B6E58;
        --color-emerald-700: #095745;
        --color-emerald-800: #073F32;
        --color-emerald-900: #052C23;

        {!! $publikBrandVars !!}
    }
</style>
