# ORION GRAFIKA — PWA Mobile Staging & ERP Production Release Runbook

**Tanggal checkpoint:** 26 Oktober 2026. **Status:** **NO-GO / BELUM SIAP RILIS**. Dokumen operator; **bukan otorisasi untuk mengakses/menjalankan deployment produksi**.

## Scope dan artefak

- Source GitHub: `steverog1945-bit/percetakan-app`.
- Branch release candidate saat ini: `integration/pwa-mobile-all-modules` pada [Draft PR #20](https://github.com/steverog1945-bit/percetakan-app/pull/20).
- PR #15 Master Bahan dan #18 POS sudah merged ke `main`; SPK #19 sudah dimasukkan ke branch #20 tetapi tidak perlu di-merge ulang ke `main`; Produksi/Collecting/Jersey memiliki perubahan dalam branch integrasi.
- Catatan cakupan [PWA_MOBILE_COVERAGE_MATRIX.md](PWA_MOBILE_COVERAGE_MATRIX.md): inventaris source saat ini masih mengandung banyak `GENERIC ONLY / UI TODO`. Jangan menganggap seluruh modul sudah mobile-ready.
- Tidak ada perubahan skema atau migrasi dalam pekerjaan UI ini yang ditujukan untuk rilis; **verifikasi diff final sebelum release**, bukan mengasumsikannya.

## Gate kesiapan (semua wajib PASS sebelum owner GO)

| Gate | Status saat dibuat | Bukti yang diwajibkan |
|---|---|---|
| Semua route/menu/role operasional terinventarisasi dan UI mobile dikerjakan | **PENDING** | Matriks route+view+role, tidak ada layar aktif generic-only tanpa penilaian |
| Vite production build + whitespace CI | **PARTIAL / CI PUSH PASS**, PR checks perlu verifikasi setiap SHA | URL workflow GitHub + manifest Vite dari kandidat akhir |
| Desktop >=768px unchanged | **NOT TESTED** | Screenshot dan regresi DOM/computed CSS per pola layar |
| Browser mobile 320/360/390/430/767px | **NOT TESTED** | Screenshot Laravel asli, bukan mockup |
| Pembayaran POS, SPK→Produksi→Collecting, stok/pembelian, jersey XS anak | **NOT TESTED** | Fixture transaksi sintetis, request/response dan tes regresi |
| Hak akses/RBAC/IDOR (termasuk kasir lintas user dan berkas privat) | **NOT TESTED** | Tes negatif multi-peran pada DB sintetis |
| Modal/drawer/keyboard/select2/pagination/bulk actions | **NOT TESTED** | Uji browser nyata dengan file/data sintetis |
| PWA Android/iOS, offline, SW cache, manifest dan Vite hash | **NOT TESTED** | Uji perangkat & browser di staging terisolasi |
| Backup-restore serta rollback kode+asset pasangan manifest | **NOT TESTED** | Verifikasi operator rilis dan bukti restore di lingkungan yang berwenang |
| Owner menyetujui merge main dan rilis produksi | **PENDING** | Persetujuan eksplisit dua tahap, SHA yang disetujui |

**Jika satu gate kritis FAIL/NOT TESTED/BLOCKED: jangan deploy.**

## Prosedur staging yang aman

1. Operator/developer menyiapkan **clone terpisah** yang dipastikan bukan `C:\xampp\htdocs\grafikaorion`, dan **database sintetis** yang tidak mungkin tersambung ke `oriondb` atau data bisnis. Jangan membaca atau menyalin `.env` produksi.
2. Setelah cek branch HEAD dan diff dari `main`, checkout kandidat release di staging, jalankan `npm ci`/build sesuai pipeline yang tidak mengakses rahasia. Jalankan PHPUnit/Artisan **hanya setelah** isolasi data dan environment dibuktikan.
3. Render Blade Laravel dan sistem PWA pada HTTPS staging yang mempunyai origin terpisah dari produksi (tidak menggunakan sertifikat/service worker/scope produksi). Uji semua route per role, alur kerja dan akses negatif IDOR pada fixture.
4. Catat manifest Vite, CSS/JS hashed filenames, SHA commit dan daftar asset yang harus dipasang **sebagai satu unit**. Tidak cukup menyalin satu file CSS.
5. Gunakan perangkat iOS/Android atau emulasi browser yang jelas dilabeli; verifikasi panel filter aktif, safe area, penggunaan keyboard, dialog bukti serah terima, layar panjang, dan semua menu.
6. Jika semua PASS, siapkan release approval untuk owner beserta rollback yang diuji; sebelum itu status tetap NO-GO.

## Proses rilis oleh operator berwenang saja, setelah GO owner

1. Operator memilih waktu rilis yang aman dan memeriksa health transaksi operasional; jangan melakukan tindakan ini lewat agen coding tanpa otorisasi resmi.
2. Operator **memverifikasi** backup bisnis terenkripsi, snapshot kode aplikasi aktif, pasangan `public/build/manifest.json` dengan hashed assets, serta **uji restore** yang sudah disetujui. Jangan hanya mengklaim backup ada.
3. Operator menerapkan artefak SHA yang disetujui melalui prosedur deploy resmi. Jangan mengubah konfigurasi produksi, `.env`, server SSL/Apache/scheduler atau menjalankan migrasi kecuali change request terpisah menyetujuinya.
4. Operator menjalankan smoke test non-mutatif untuk login/peran, menu, visual desktop+mobile, membaca nota/status, service worker, asset hash dan offline page. **Jangan membuat transaksi uji pada `oriondb`.**
5. Jika ada regresi, operator mengaktifkan rollback terotorisasi untuk mengembalikan **kode+build manifest+hashed assets secara serempak**, lalu memverifikasi asset lama tersedia, SW/cache mengambil versi semestinya, dan smoke test kembali normal.
6. Rekam hasil, SHA release, operator, waktu, keputusan GO/rollback dan insiden untuk jejak audit.

## Batasan kewenangan agen AI

- Agent/OpenCode boleh mengubah hanya branch dev yang ditetapkan dan menjalankan tes pada clone+data sintetis aman.
- **Dilarang** membaca/menulis `.env` rahasia, `oriondb`, kredensial, `C:\xampp\htdocs\grafikaorion`, service Windows, Apache/SSL, scheduler, atau backup produksi.
- **Dilarang** merge `main` atau deployment tanpa approval owner tersendiri. GitHub status `mergeable=true` bukan berarti release safe.
- Permintaan tambahan perubahan aturan bisnis/RBAC/status pembayaran/stok harus dipisah dan disetujui sebelum diimplementasi.

**Keputusan pada checkpoint ini: NO-GO.**
