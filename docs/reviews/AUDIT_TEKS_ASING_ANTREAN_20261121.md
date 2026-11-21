# Audit teks asing / mojibake — antrean pesanan baru dan halaman lain

Tanggal: 21 November 2026
Scope: teks tampilan dan komentar sumber saja. Tidak ada perubahan logika, route,
controller, model, skema, RBAC, atau konfigurasi.

## Temuan utama

Halaman **Antrean Pesanan Baru** (`resources/views/kasir/waiting_list.blade.php`)
memiliki dua teks rusak:

| Baris | Sebelum | Sesudah |
|-------|---------|---------|
| 6 | `kasir â€” Terima` | `kasir — Terima` |
| 6 | `Terima &amp; Bayar` (prop komponen) | `Terima & Bayar` |
| 55 | `Terima â‰  bayar sekarang` | `Terima ≠ bayar sekarang` |

`&amp;` pada atribut `<x-page-header>` bukan entity biasa: Blade meneruskannya
sebagai string literal ke prop, lalu `{{ $subtitle }}` di
`components/page-header.blade.php` meng-escape lagi menjadi `&amp;amp;`, sehingga
browser menampilkan `&amp;` secara harfiah. Entity pada slot/konten HTML biasa
tidak bermasalah dan tidak diubah.

Dua prop komponen lain dengan kasus sama juga diperbaiki:
`master/kategori-kas-kecil/index.blade.php:5` dan
`master/pendukung/index.blade.php:5`.

## Halaman dan sumber lain

| File | Jenis | Perbaikan |
|------|-------|-----------|
| `resources/views/jersey/master/index.blade.php:201,707` | mojibake em dash | `Ã¢â‚¬â€` → `—` |
| `resources/views/jersey/master/index.blade.php:236` | mojibake middle dot | `Ã‚Â·` → `·` |
| `resources/views/penjualan/create.blade.php:1077,1085` | mojibake em dash + middle dot pada notifikasi konsumen | `—`, `·` |
| `resources/views/penjualan/create.blade.php:1461,2078,2932,3486,3487,3700,3702,4179` | mojibake pada komentar JS dan daftar pelanggaran minimum order | `—`, `m²`, `§6`, `•` |
| `database/seeders/StagingFixtureSeeder.php:109` | nama fixture bercampur aksara Jepang | `Softすることが Vinly` → `Soft Touch Vinly` |
| `scripts/audit-*.cjs`, `scripts/uji-patch.cjs` | komentar bercampur aksara Rusia/Korea/Cina/Jepang | diganti padanan bahasa Indonesia |
| `tests/Feature/UserSerializationSecurityTest.php:67` | komentar `元素` | `elemen` |
| 8 dokumen `LANDINGPAGE_BUILDER_CHECKPOINT*.md` dan `ROLLBACK_RUNBOOK_LANDING_PAGE.md` | campuran aksara Sirilik/Cina/Jepang/Jerman | diganti padanan bahasa Indonesia |

Dua frasa pada dokumen historis yang tidak punya padanan pasti direkonstruksi
secara konservatif: `guekahl的全 regression` → `run regression penuh`, dan
`Total tidak指挥官` → `Total gagal`. Angka, nama tabel, dan keputusan tidak diubah.

## Sengaja tidak diubah

- `scripts/audit-teks-asing.cjs:30` — rentang Unicode di regex memang pola deteksi.
- `scripts/cek-teks.cjs:17` — daftar kata Cina memang daftar deteksi.
- `tests/Feature/StokIntegritasAlurTest.php:19,85` — `Σ` adalah simbol penjumlahan.

## Validasi

- PASS — pemindaian seluruh `app`, `resources`, `routes`, `database`, `config`,
  `tests`, `scripts`, `docs`, file akar, dan `public/build`: 0 karakter asing/mojibake
  di luar 3 pengecualian di atas.
- PASS — `git diff --check` tanpa output.
- PASS — `php -l` untuk 7 file PHP/Blade yang berubah.
- PASS — `node --check` untuk 5 file `.cjs` yang berubah.
- PASS — `php artisan view:cache`; hasil kompilasi `waiting_list`, kategori kas
  kecil, dan master pendukung memuat `&`, bukan `&amp;`, pada nilai prop.
- NOT TESTED — render browser dengan data login: tidak dijalankan karena butuh
  sesi aplikasi; pembuktian dibatasi pada source dan Blade terkompilasi.

## Keselamatan

Produksi tidak disentuh. Database, migrasi, seeder, dan patch eksternal tidak
dijalankan. Merge dan deploy tidak dilakukan.
