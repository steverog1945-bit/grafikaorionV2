# Orion Grafika — Galeri Tipografi Form & Filter (PR #22)

Bukti visual **nyata** dari Blade Laravel yang dijalankan di staging terisolasi
(Chromium/Playwright), bukan mockup HTML. Semua gambar di bawah adalah PNG asli
yang tersimpan di `docs/reviews/assets/pwa-mobile-typography/`.

## Provenance (asal bukti)

| Item | Nilai |
|---|---|
| Branch | `fix/form-filter-month-typography` |
| Base commit (main, sebelum PR) | `b9aaec5` |
| Route | `GET /kasir/monitoring` → `kasir.index` |
| Halaman | Monitoring Kasir (filter disubmit: `tgl_mulai=2026-10-01&tgl_selesai=2026-10-31&status_filtered=1`) |
| Role uji (sintetis) | `kasir` (Operator Produksi) |
| Tema | Light (Theme Manager default) |
| Lingkungan | `APP_ENV=staging`, database SQLite terisolasi `orion_pwa_staging_pr21` (file sementara, bukan produksi) |
| Server | `php artisan serve` 127.0.0.1:8000, aset Vite hasil `npx vite build` |
| Browser | Chromium headless (Playwright), render Blade asli |
| Data | Sintetis (`StagingFixtureSeeder`), 5 transaksi contoh |

Login nyata berhasil (`/masuk` → `/dashboard` → `/kasir/monitoring`). Halaman
yang difoto **bukan** halaman login.

![Login nyata (Blade asli)](assets/pwa-mobile-typography/login-800-real.png)

## BEFORE vs AFTER — halaman & filter yang sama

BEFORE = CSS dari base `b9aaec5` (tanpa `form-typography.css`).
AFTER = CSS branch ini (dengan `form-typography.css`).

### Desktop 1366px — filter terbuka

| BEFORE | AFTER |
|---|---|
| ![before](assets/pwa-mobile-typography/filter-1366-before.png) | ![after](assets/pwa-mobile-typography/filter-1366-after.png) |

Perbedaan terukur: tinggi glif input filter **13px → 14px** (standar input desktop).

### Mobile 390px — filter terbuka

| BEFORE | AFTER |
|---|---|
| ![before](assets/pwa-mobile-typography/filter-390-before.png) | ![after](assets/pwa-mobile-typography/filter-390-after.png) |

Mobile sudah memakai 16px (aturan lapisan `mobile-ui`); PR menjaga nilainya.

## Tangkapan multi-viewport (AFTER)

| 320 | 360 | 390 | 430 |
|---|---|---|---|
| ![](assets/pwa-mobile-typography/monitoring-320-after.png) | ![](assets/pwa-mobile-typography/monitoring-360-after.png) | ![](assets/pwa-mobile-typography/monitoring-390-after.png) | ![](assets/pwa-mobile-typography/monitoring-430-after.png) |

| 768 | 1024 | 1366 |
|---|---|---|
| ![](assets/pwa-mobile-typography/monitoring-768-after.png) | ![](assets/pwa-mobile-typography/monitoring-1024-after.png) | ![](assets/pwa-mobile-typography/monitoring-1366-after.png) |

## Computed style terukur (getComputedStyle)

Sumber: `computed-before.json`, `computed-after.json`, `computed-styles.json`.

| Properti | Standar | BEFORE 390 | AFTER 390 | BEFORE 1366 | AFTER 1366 | Hasil |
|---|---|---|---|---|---|---|
| Input font-size | 16 mobile / 14 desktop | 16px | 16px | **13px** | **14px** | PASS |
| Input font-weight | 400 | 400 | 400 | 400 | 400 | PASS |
| Input font-family | Font Theme Manager | Inter | Inter | Inter | Inter | PASS |
| Label font-size | 13px | 13px | 13px | 13px | 13px | PASS |
| Label font-weight | 500 | 500 | 500 | 500 | 500 | PASS |
| Select font-size | (dropdown) 13px | 13px | 13px | 13px | 13px | PASS |
| Table header | 13px / 500 | 13/500 | 13/500 | 13/500 | 13/500 | PASS |

## Root cause & perbaikan

1. Sebelumnya tidak ada lapisan tipografi form terpusat; ukuran input mengikuti
   token `.text-sm` Theme Manager (13px) sehingga input desktop menyimpang dari
   standar 14px.
2. `form-typography.css` baru menormalkan: input 14px desktop / 16px mobile,
   label 13px/500, bobot input 400, line-height konsisten, touch target 44px,
   Select2/modal/chip seragam. Semua override mobile di dalam
   `@media screen and (max-width: 767.98px)`.
3. Input filter memakai selector ber-spesifisitas cukup (`.pwa-ops-page
   .pwa-ops-filter-form input…`) agar menang atas pemetaan `.text-sm` tanpa
   `!important` masal.
4. Field nominal pembayaran sengaja lebih besar (16px desktop / 18px mobile).
5. Native date/month picker tetap mekanisme browser (tidak diganti).

## Catatan / residual

- Halaman yang diuji: Monitoring Kasir (fokus PR). Halaman lain belum difoto
  pada sesi ini → **NOT TESTED** untuk modul lain.
- Kartu filter dalam keadaan tertutup (collapse) tampil sebagai kotak kosong
  tipis di desktop — perilaku `<details>` yang sudah ada sebelumnya, di luar
  lingkup tipografi; tidak diubah.
- Tidak ada akses `.env`/`oriondb`/produksi. Staging memakai SQLite sementara.
