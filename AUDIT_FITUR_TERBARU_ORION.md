# AUDIT FITUR TERBARU ERP ORION GRAFIKA

Tanggal: 22 Oktober 2026 · Mode: READ-ONLY + testing terisolasi (percetakan_erp_testing)
Status: **DRAFT — bagian bertanda [PENDING] menunggu hasil suite penuh & pengujian konkurensi HTTP.**

---

## 1. Baseline commit

| Item | Nilai | Bukti |
|---|---|---|
| HEAD lokal | `a66b11384f42944752c97e62976821e8f2afa373` | `git rev-parse HEAD` |
| Tanggal baseline | 2026-10-03 09:13:44 +0700 | `git log -3 --date=iso` |
| Pesan commit terakhir | "Docs: panduan deploy ke VPS dengan subdomain Hostinger" | idem |
| Kesesuaian asumsi | Ya — GitHub main diketahui terakhir commit ±3 Okt 2026; tidak ada commit lokal setelahnya | reflog `.git/logs/HEAD`: setelah 3 Okt hanya `reset: moving to HEAD` (3 Blade? 8 Okt 14:05) dan 2 entri rename no-op (12 Okt) |

**Status git hari ini (porcelain):** 6840 berkas **A (staged, belum di-commit)**, 166 berkas **M (unstaged)**, 1 berkas **D (staged delete)**, 114 **?? (untracked)**.
Artinya: `git add` pernah dijalankan atas seluruh perubahan tetapi tidak pernah di-commit.

## 2. Volume perubahan vs HEAD

| Cakupan | Jumlah | |
|---|---|---|
| Seluruh working tree vs HEAD | 7007 berkas, +1.627.408 / −2.091 baris | terdominasi snapshot plugin `skills/ECC-main` & `skills/caveman-main` (serta 114 untracked) — **bukan kode ERP** |
| **Kode ERP saja** (app routes resources database config tests bootstrap docs public) | **280 berkas, +38.993 / −2.088 baris** | `git diff --cached --stat HEAD` dengan exclude |

Distribusi kode (top): `tests/Feature` 77, `app/Http/Controllers` 21, `resources/views/laporan` 12, `database/migrations` 11, `app/Http/Controllers/Api` 9, `app/Models` 9, `app/Services` 8, `resources/views/{partials,kasir,jersey/orders}` 22+, `app/Console/Commands` 4, docs 3.

**Catatan histori yang tidak dapat ditelusuri:** tidak ada commit/rebase/merge setelah 3 Okt dalam reflog, sehingga riwayat bulan ke bulan (Okt 8 → 22) hanya bisa dibaca dari nama berkas migration (ber-tanggal 2026-10-04 s.d. 2026-11-06). Rekonstruksi chronological pasti **tidak mungkin** dari git; yang bisa dipastikan hanya isi akhir working tree.

## 3. Inventaris fitur baru (dari diff + nama migration)

Kandidat berbukti nama berkas/route (penilaian kedalaman di [PENDING] per fitur):

1. **Potong Manual LINEAR_GRID / A3 + dimensi + minimum charge** — migration `2026_11_06_090000_potong_manual_a3`, `2026_11_02_090000_simpan_dimensi_potong`, `2026_11_06_200000_minimum_charge_finishing`; UI `finishing/index.blade.php`, `penjualan/create.blade.php`; test `PotongManualA3Test`, `PotongManualDimensiTest`, `PotongManualFrontendGateTest`, `PotongManualGateTest`.
2. **Approval edit nota** — migration `2026_11_05_100000_tambah_approval_edit_nota_ke_app_settings`; route `penjualan.edit-approval` (routes/web.php:283); service `NotaEditService`.
3. **Item susulan (Nota & Jersey)** — migration `2026_11_04_100000_tambah_is_susulan_transaksi_detail`; route `POST /jersey/{order}/lines/susulan`; test `TambahItemSusulanTest`, `PenjualanSusulanModeTest`, `RekonsiliasiTotalNotaSusulanTest`.
4. **API publik + token user** — migration `2026_11_04_110000_tambah_api_token_ke_users`; middleware `ApiAuth` (alias `api.auth`); test `ApiPublikTest`.
5. **Kas kecil terpisah dari laci + buku kas + jasa jahit dari kas kecil** — migration `2026_10_30_*` (kas_kecil_terpisah, kas_kecil_buku_kas, jasa_jahit_dibayar_dari_kas_kecil); controller `KasKecilController`, `KategoriKasKecilController`.
6. **Landing Page Builder** (sebagai homepage publik; Gate B) — migration `create_landing_page_builder_tables`; `LandingPageBuilderController`; `config/landing_page_builder.php`.
7. **Publik/PWA/Postingan** — migration `postingan`, `add_tampil_publik_to_posts`; controller `PublikController`, `PostinganController`, `PwaController`; route `/publik/baca/{slug}`, `/manifest.webmanifest`, `/sw.js`; test `PwaTest`, `PostinganTest`, `ApiPublikTest`.
8. **Backup offsite** — migration `backup_logs_offsite`, `backup_logs_berjalan_status`; `BackupController`/`BackupService`; test `BackupOffsiteTest`.
9. **Keterangan buka kas harian** — migration `2026_11_02_120000_keterangan_pembukaan_kas_harian`.
10. **Rework status "Ditutup"** — migration `2026_11_01_110000_rework_status_ditutup`.
11. **Hak input biaya desain** — migration `2026_11_03_090000_tambah_hak_input_biaya_desain`.
12. **Jersey: potong stok, material id_bahan, rib nonaktif, tarif jasa jahit** — migration `jersey_potong_stok`, `jersey_material_id_bahan`, `nonaktifkan_komponen_rib_jersey`, `jasa_jahit_penjahit_dan_tarif_jersey`.
13. **Pembelian online / platform belanja** — migration `pembelian_online`, `add_supplier_rencana_to_purchase_requests`; controller `PlatformBelanjaController`; test `PembelianOnlineTest`.
14. **Status order di transaksi + backfill** — migration `add_status_order_to_transaksi`, `backfill_status_order_existing`.
15. **Hardening indeks & rekonsiliasi** — `index_hardening_p1`, `big_dataset_indexes_jurnal_pembayaran`, `transaksi_pembayaran_index`, `index_kartu_stok_tanggal`, `create_audit_logs_arsip`, `indeks_audit_filter_and_sinyal`; command `audit:integritas`.
16. **Komisi desain / kelas desainer** — migration `komisi_desain`, `kelas_desainer_persen_awal`.
17. **Akun akuntansi baru** — `akun_beban_waste_rework`, `akun_selisih_persediaan`, `akun_dana_pemilik_pembelian`, `biaya_dibayar_pemilik`, `pembayaran_hutang_metode_kas_kecil`.

## 4. Matriks berkas → route → modul → dependensi

[PENDING — diproduksi dari diff per file setelah suite selesai; kerangka per fitur: UI(barak) → routes/web.php → middleware grup → Controller → Service → Model → migration → laporan modul terkait.]

## 5. Hasil E2E (Gate 3)

[PENDING — `php artisan test` suite penuh sedang berjalan oleh agent uji; hasil PASS/FAIL + pemetaan test per fitur akan diisi dari keluarannya.]

## 6. Temuan keamanan

Bukti statis yang sudah dikumpulkan (verifikasi runtime [PENDING]):

- Limiter login: 5/menit per username+IP (`AppServiceProvider.php:51-58`) — dibuktikan runtime dengan 429.
- Group tulis dilindungi `auth + menu.access + throttle.write` (routes/web.php:119); alias middleware terdaftar di `bootstrap/app.php`: `HeaderKeamanan`, `SubmitSekaliJalan`, `ThrottleWriteRequests`, `RestrictInputOnlyRoles`, `HanyaAdministrator`, `CheckMenuAccess`, `ApiAuth`.
- Berkas privat: route `/storage/{folder}/{path}` hanya untuk auth (routes/web.php:110, `BerkasPrivatController`); akses tanpa login terbukti 302 ke login pada audit PWA sebelumnya.

## 7. Temuan race condition

[PENDING — skenario HTTP nyata (double store nota, double bayar, stok terakhir, double closing kas, double ambil PE, throttle 429, IDOR) dijalankan terhadap server sementara ber-DB testing setelah suite selesai. Skrip sudah disiapkan: `storage/app/audit/audit_setup.php` + pengisi harness curl_multi.]

## 8. Temuan PWA yang belum selesai (Gate 5)

Dipatenakan dengan bukti file:line (tanpa mengulang audit manifest yang sudah PASS):

| ID | Temuan | Bukti | Kecocokan rencana perbaikan |
|---|---|---|---|
| P1 | **Gambar order memasukinya CacheStorage.** `isAsetStatis()` mencocokkan SEMUA pathname berakhiran ekstensi gambar/font (`.png .jpe?g .svg .webp ...`) tanpa mengkecualikan berkas privat | `app/Http/Controllers/PwaController.php:102-105` | Tepat sasaran, **dengan syarat** patch memblokir DUA bentuk pathname: (a) URL berkas privat via route `/grafikaorion/storage/{folder}/{path}` (BerkasPrivatController; routes/web.php:110) dan (b) URL langsung `/grafikaorion/public/storage/...` yang dipakai blade melalui `asset('storage/...')` (12+ titik: penjualan/create.blade.php:462,105,2976..., penjualan/show.blade.php:132, spk/print-this.blade.php:14, landing-page-public.blade.php:171). Cukup mengecualikan pathname yang memuat `/storage/` — kedua bentuk memuarkaninya. |
| P2 | Kunci cache `orion-pwa-v1` fallback karena `config('app.version')` NULL | `PwaController.php:143` | Rencana kunci eksplisit `orion-pwa-v2` + penghapusan (penghapusan cache lama sudah ada: `caches.keys().filter(k => k !== CACHE)` PwaController.php:97) — aman, tidak menyentuh data aplikasi lain karena filter hanya sapu cache SW. |
| P0 | HTTPS LAN belum aktif (HTTP 192.168.1.17) | `.env` APP_URL + audit PWA | Tidak diubah tanpa persetujuan; opsi mkcert + CA ke HP siap dijadikan paket patch. |
| P2 | `SESSION_SECURE=false`/NULL saat masih HTTP | `.env` pasca-audit | Ikut dinyalakan HANYA ketika HTTPS aktif; menyala lebih dulu akan mengunci HP LAN di sesi mati. |
| — | Aksesibilitas 0,85 (tingkat kontras/label/heading) | Lighthouse audit PWA | Patch CSS/label; read-only, mengantri persetujuan. |

Uji akun-beda-di-perangkat-yang-sama dan akses-after-logout file privat: [PENDING] (perlu perangkat; bagian yang bisa diuji desktop hanya simulasi SW — dinilai dari logika patch, bukan klaim PASS device).

## 9. Daftar bug (P0/P1/P2/P3) + file:line + bukti

[PENDING — dilengkapi setelah hasil suite & race masuk. Satu temuan statis baru: catat garis besar di §8.]

## 10. Rencana patch paling aman

- P1: dalam `PwaController.php` `isAsetStatis()` tambah guard `url.pathname.indexOf('/storage/') !== -1 → false` (blokir kedua bentuk URL privat di atas) + naikkan kunci ke `orion-pwa-v2`. Satu berkas, tanpa perubahan Apache/env.
- Others [PENDING].

## 11. Hasil test PASS/FAIL/BLOCKED/NOT TESTED

[PENDING. Sementara ini yang tidak diuji di desktop: perangkat nyata Android/iOS, uji per-role UI selain Administrator (tidak ada kredensial), instalan/uninstalan PWA.]

## 12. Perubahan yang memerlukan persetujuan operator

- Semua patch PWA (P0 HTTPS/mkcert, P1 SW, P2 SESSION_SECURE).
- Bersih-bersih repo: 6.727 berkas plugin skills yang ikut ter-stage ke git (simpan di luar repo atau .gitignore) — hanya bila owner ingin status git ruang.
- [PENDING dari temuan race/security.]
