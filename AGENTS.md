# AGENTS.md — Orion Grafika ERP

## Mandat khusus PR #20 — eksekusi semua modul (prioritas tertinggi)

## Checkpoint permanen: lanjut langsung dari matriks (26 Oktober 2026)

**READ FIRST:** `docs/reviews/PWA_MOBILE_COVERAGE_MATRIX.md`. Inventory awal: 194 Blade files = 10 source-specific mobile (NOT TESTED), 112 generic only / still UI TODO, 63 partial/components, 9 print views to validate. Ini **file inventory**, bukan hitungan route aktif atau kelulusan uji. Tugas berikutnya adalah **menyelesaikan UI nyata untuk semua route aktif** dari 112 generic, dimulai kasir tambahan, pembelian, gudang/stok, master lainnya, keuangan/laporan, lalu dashboard/setting; tandai tiap screen di matriks, bukan mengulang Master/POS/SPK/Produksi/Collecting/Jersey.

- CI frontend dari `.github/workflows/mobile-pwa-build.yml` telah ditambahkan; GitHub Actions push run `d16011f` sudah PASS untuk Vite build. CI **bukan** tes Laravel, UI visual, transaksi, role, PWA runtime.
- **JANGAN** mengakhiri sesi dengan "SELESAI (PARSIAL)" bila modul masih bisa diimplementasikan. Simpan checkpoint pada dokumen dan lanjut ke layar berikutnya selama sesi yang sama. Jika alat/runtime membatasi, laporkan hambatan spesifik beserta status NOT TESTED/BLOCKED tanpa menciptakan klaim selesai.
- PR #20 **sudah ada**, bukan untuk dibuat ulang: https://github.com/steverog1945-bit/percetakan-app/pull/20. Jangan merge ke `main` atau deploy ke produksi tanpa GO operator+owner setelah staging dan rollback diuji.


**Hanya ketika bekerja untuk Issue #16 dan PR #20**, gunakan branch `integration/pwa-mobile-all-modules`. Kontrak otoritatif: `docs/reviews/PWA_MOBILE_ONE_PASS_EXECUTION.md`. PR #20 sudah tersedia; **jangan membuat PR baru per modul**. Instruksi umum di bawah yang mengatur branch/module per-task tidak boleh dipakai untuk menghentikan pengerjaan satu-paket ini.

- **Status Git terbaru:** PR #15 Master Bahan dan #18 POS telah merged ke `main`; SPK #19 belum merged ke `main`, tetapi kode SPK dan Produksi sudah tergabung di branch #20. Collecting serta Jersey index/create/edit/keuangan sudah memiliki perubahan scoped pada branch #20. Lakukan `git fetch` dan verifikasi HEAD terbaru; **jangan mengulang merge**, membuat PR #20 lagi, atau menganggap generic CSS sebagai implementasi seluruh modul. Tidak perlu PR Produksi terpisah.
- **Berhenti mengirim "Laporan Final — SELESAI (PARSIAL)" setiap kali satu kelompok modul selesai.** Commit internal per kelompok boleh, tetapi lanjut otomatis ke kelompok berikutnya. Jika kehabisan konteks atau alat, tinggalkan checkpoint resume rinci di dokumen integrasi dengan modul DONE/PENDING/BLOCKED, SHA, langkah eksekusi berikut; jangan mengklaim seluruh PWA selesai.
- Prioritas implementasi **tanpa audit lama berulang**: Collecting → Jersey → Kasir tambahan/Logistik/Pembelian/Stok → semua Master → Keuangan/Laporan → Dashboard/Setting → layar aktif lain yang belum tercakup. Ikuti menu/routes/view aktif, bukan hanya nama modul dalam daftar.
- Setelah tiap kelompok, verifikasi build aman, pemeriksaan whitespace, perubahan desktop, status role/action, screenshots dari source yang benar. Simpan ketidakmampuan menjalankan Laravel/DB sintetis sebagai NOT TESTED dan lanjut ke pekerjaan UI lain yang tetap aman.
- Jadikan coverage matrix per screen dan tes akhir sebagai dasar status. **Satu laporan penyelesaian lintas modul**, satu Draft PR #20, dan hanya sesudah cakupan selesai ajukan GO/NO-GO rilis. Tidak boleh merge ke `main`, deploy, atau akses `C:\xampp\htdocs\grafikaorion`, `.env`, `oriondb`, sertifikat, backup atau server tanpa instruksi operator/owner tersendiri.
- Perintah lama "jangan merge" berlaku untuk `main`/rilis; integrasi perubahan antar-branch ke branch PR #20 yang secara eksplisit ditetapkan owner diperbolehkan, dengan konflik diperiksa dan tanpa force-push. Tetap jangan otomatis melakukan deployment.


## Workspace & Branch
- Repo: `steverog1945-bit/percetakan-app`
- Clone kerja: `C:\repos\grafikaorion-copilot-mobile\percetakan-app`
- Branch aktif: **ditentukan per sesi** — gunakan branch yang diminta owner; jangan mengunci branch secara permanen.
- **JANGAN** akses `C:\xampp\htdocs\grafikaorion` (produksi).

## Keselamatan (BATAS MUTLAK)
- Jangan baca/mengubah `.env`, kredensial, database `oriondb`, atau file produksi.
- Jangan menjalankan migration, seeder, Artisan, atau aplikasi yang menyentuh DB/rahasia.
- Jangan ubah business logic, transaksi, stok, perhitungan, jurnal, route, model, controller, RBAC, API, PWA SW, atau konfigurasi produksi untuk scope UI/CSS/Blade.
- Semua warna brand mobile harus memakai theme tokens existing. Desktop >=768px harus tetap seperti semula.
- Jangan merge, deploy, force-push, atau push ke `main` otomatis. Push hanya ke feature branch yang secara eksplisit menjadi target tugas.

## Standar Visual Mobile (<768px)
- Semua CSS khusus mobile WAJIB di dalam `@media screen and (max-width: 767.98px)`.
- Zero-diff desktop: layar >=768px TIDAK BOLEH mengalami perubahan visual.
- Modularitas: `mobile-ui.css` dispatcher → `mobile-dashboard.css`, `mobile-bahan.css`, `mobile-modal.css`, `mobile-nav.css`.
- Integritas fungsional: tidak mengubah `id`, `name`, `form action`, atau event handler JS yang ada.
- Toleran light/dark mode dan safe-area (`env(safe-area-inset-bottom)`).

## Kontrak Visual Master Bahan (ACUAN WAJIB)
- Screenshot referensi owner (852×1846 px, ~390×845 CSS px) adalah sumber kebenaran visual.
- Baris horizontal ringkas: tinggi ideal 70-90px (boleh bertambah untuk teks panjang).
- Komposisi: avatar/monogram 44-50px kiri | nama/SKU/tag tengah | status+stok+harga kanan | tombol ⋮ di ujung.
- Nama 14-15px weight 600-700, max 2 baris. SKU 11-12px. Status 11-12px. Stok/harga 12-14px.
- Touch target minimum 44×44px. Floating nav solid, tanpa glass/blur, kapsul.

## Definition of Done (WAJIB)
1. Implementasi selesaikan perubahan dalam scope.
2. Validasi faktual: tinjau diff & whitespace; jalankan lint/tes yang aman. Bedakan PASS/FAIL/NOT TESTED/BLOCKED.
3. Laporan Markdown permanen: buat/perbarui laporan SEBELUM commit akhir. Jangan duplikat.
4. Commit ke feature branch aktif. Jangan empty commit.
5. Push terverifikasi: normal push, cocokkan SHA HEAD lokal dan remote.
6. PR: gunakan PR existing. Jangan merge/deploy.
7. LAPORAN AKHIR DI CHAT UTAMA: satu pesan final lengkap tanpa diminta lagi.

## Format Laporan Final
```
ORION GRAFIKA — LAPORAN FINAL
Status: SELESAI / PARSIAL / BLOCKED
Task:
Branch:
HEAD lokal:
HEAD remote:
Commit perubahan:
PR:
File berubah:
Hasil implementasi:
Validasi: PASS [...]; FAIL [...]; NOT TESTED [...]
Screenshot: ERP ASLI / STATIC PREVIEW / TIDAK ADA
Risiko atau hambatan:
Produksi: TIDAK DISENTUH
Merge/deploy: TIDAK DILAKUKAN
```

## Database & Testing
- Gunakan hanya lingkungan sintetis yang terisolasi. Jika tidak tersedia, tandai NOT TESTED.
- Tidak ada akses ke database produksi `oriondb`.

## Pekerjaan Lanjutan
- PR #18 = POS (sudah ada, jangan implementasikan ulang tanpa review).
- Issue #16 = SPK, Produksi, Collecting, Jersey, Keuangan, Laporan.
- Jangan mencampur commit Master ke PR POS. Jangan mulai SPK di branch Master.
