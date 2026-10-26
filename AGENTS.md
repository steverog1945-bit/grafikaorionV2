# AGENTS.md — Orion Grafika ERP

## Workspace & Branch
- Repo: `steverog1945-bit/percetakan-app`
- Clone kerja: `C:\repos\grafikaorion-copilot-mobile\percetakan-app`
- Branch aktif: `fix/mobile-visual-final-polish` (PR #15, Draft)
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
