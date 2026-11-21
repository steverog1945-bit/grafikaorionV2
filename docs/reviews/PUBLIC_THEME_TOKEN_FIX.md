# Perbaikan Halaman Publik Putih Polos (Landing Page & Login)

Tanggal: 21 November 2026
Branch: `feat/min-luas-hybrid-t4a`
Scope: **Blade tampilan publik saja**. Tidak ada perubahan logika bisnis, route, controller, model, database, atau tema ERP.

## Gejala

- Landing page publik (`/`) tampil putih polos; teks hitam, tautan biru default browser, tombol tidak berwarna.
- Halaman login (`/masuk`) latarnya putih/transparan dan tombol **Masuk** putih sehingga teks putih tidak terbaca.

## Akar masalah

Commit `b8dacc45` ("tema Apple Design + nol warna hardcode") mengganti hex di Blade publik dengan
`var(--color-indigo-*)`, `var(--color-slate-*)` dan `var(--brand-*)`.

Token tersebut hanya disuntikkan di `resources/views/layouts/app.blade.php` melalui
`BrandThemeService::current()` — yaitu layout **ERP**. Halaman publik memakai layout/view mandiri
(`publik/layout.blade.php`, `publik/layout-masuk.blade.php`, `publik/layout-artikel.blade.php`,
`publik/lacak-nota.blade.php`, `landing-page-public.blade.php`) yang tidak memuat layout ERP dan
sebagian tidak memuat `@vite` sama sekali.

Akibatnya:

| Halaman | Sebelum perbaikan |
|---|---|
| `/` (Builder published) | 0 stylesheet eksternal, semua `--color-indigo-*` / `--brand-*` kosong |
| `/masuk` | CSS Vite dimuat, tetapi `--color-indigo-900/950/100`, `--brand-sidebar-deep`, `--brand-accent-light` kosong → latar & tombol transparan |
| `/lacak-nota`, artikel | Sama seperti `/`: token tidak pernah didefinisikan |

Catatan: `app.css` juga memetakan `--color-indigo-100/200/300/400` ke dirinya sendiri
(`var(--color-indigo-100)` dst.), sehingga tanpa nilai dari `BrandThemeService` variabel itu
memang valid-parse tetapi bernilai kosong. Ini bukan sumber utama, tapi ikut memperparah.

## Perbaikan

File baru: `resources/views/publik/partials/theme-vars.blade.php`

- Mengambil `app(\App\Services\BrandThemeService::class)->current()` — **sumber yang sama** dengan layout ERP — sehingga tema yang dipilih owner tetap berlaku di halaman publik.
- Menyalin token statis dari `ui-tokens.css` (`--color-white`, `--paper`, `--ink-900`, `--line-200`, dll.) dan ramp amber/rose/emerald dari `@theme` `app.css`, tanpa mengubah nilainya.
- Disertakan sebelum stylesheet yang memakai token, pada 5 file:
  - `resources/views/landing-page-public.blade.php` (sebelum `renderer-styles`)
  - `resources/views/publik/layout.blade.php` (setelah `@vite`)
  - `resources/views/publik/layout-masuk.blade.php` (setelah `@vite`, sebelum `<style>` login)
  - `resources/views/publik/layout-artikel.blade.php`
  - `resources/views/publik/lacak-nota.blade.php`

Tidak ada `id`, `name`, `action` form, event handler, atau kelas yang diubah. Desktop tidak tersentuh.

## Validasi

**PASS**

- `php artisan view:clear` + `php artisan view:cache` — semua Blade terkompilasi.
- HTTP 200 untuk `/`, `/masuk`, `/lacak-nota`; markup memuat token tema.
- Computed style browser (Chrome terhubung, halaman diakses lewat `localhost/grafikaorionV2/public/...`):

| Halaman | Sebelum | Sesudah |
|---|---|---|
| `/` | `body background: rgba(0,0,0,0)`, `--color-indigo-950` kosong | `body background: rgb(237,244,246)`, `--color-indigo-950 = #15232A` |
| `/masuk` | `body background: rgba(0,0,0,0)`, tombol `.mk-tombol` transparan | `body background: rgb(21,35,42)`, tombol `rgb(63,101,120)` dengan teks putih |
| `/masuk` | `--brand-accent-light`, `--brand-sidebar-deep` kosong | keduanya terisi dari tema aktif |

Screenshot diambil lewat browser, tetapi agen tidak dapat membaca piksel gambar (model tanpa image input),
jadi klaim visual dibatasi pada computed style + HTTP dan bukan "saya melihat tampilannya".

**NOT TESTED** — uji lintas tema (ganti preset di Pengaturan lalu muat ulang halaman publik) belum dijalankan;
nilai diambil dari tema aktif saat pemeriksaan.

## Yang tidak disentuh

- Tema ERP / `layouts/app.blade.php`, `BrandThemeService`, `app.css`, Vite, service worker.
- Data master, konversi bahan, finishing, stok, HPP, order lama.
- Produksi, merge, dan deploy.
