# Instruksi permanen GitHub Copilot — ORION GRAFIKA

Instruksi ini berlaku untuk **setiap tugas implementasi kode dalam repository ini**, termasuk tugas yang dikerjakan Copilot melalui agent, coding session, atau subagent. Bahasa laporan kepada owner: **Bahasa Indonesia**. Gunakan instruksi ini otomatis, tanpa menunggu owner meminta laporan.

## Batas keselamatan

- Repository kerja: clone **development** yang terisolasi dan feature branch yang dipilih owner. **Jangan mengakses** folder produksi `C:\xampp\htdocs\grafikaorion`, database operasional `oriondb`, layanan Windows/Apache, sertifikat HTTPS, scheduler, atau backup produksi.
- **Jangan membaca/menyalin/mengubah file `.env`**, kredensial, data asli, atau database apa pun. Jangan menjalankan migrasi, seeder, Artisan, atau aplikasi jika ketergantungannya bisa menyentuh database / rahasia. Uji yang butuh runtime wajib menggunakan fixture dan lingkungan sintetis yang benar-benar terisolasi; jika tidak tersedia, tandai **NOT TESTED**.
- Jangan mengubah business logic, transaksi, stok, perhitungan, jurnal, route, model, controller, RBAC, API, PWA service worker, atau konfigurasi produksi ketika scope-nya hanya UI/CSS/Blade presentasional.
- Semua warna brand mobile harus memakai theme tokens existing. Orion Classic default dan desktop >=768px harus tetap seperti semula kecuali owner memerintahkan lain. Aksi nyata, izin, nama field, ID, handler, dan semantik status tidak boleh hilang.
- **Jangan merge, deploy, force-push, atau push ke `main` otomatis.** Push hanya ke feature branch yang secara eksplisit menjadi target tugas dan sudah diizinkan owner.

## Definition of Done — WAJIB, tanpa pengingat

Untuk **setiap pekerjaan implementasi kode**, tugas **BELUM SELESAI** sampai seluruh poin ini dikerjakan dalam urutan berikut:

1. **Implementasi:** selesaikan perubahan dalam scope; hindari audit, proposal, sesi, atau branch baru yang tidak diminta.
2. **Validasi faktual:** tinjau diff dan whitespace; jalankan lint/tes yang aman dan tersedia. Bedakan dengan tegas **PASS**, **FAIL**, **NOT TESTED**, dan **BLOCKED**. Jangan menyebut preview statis sebagai screenshot Laravel/PWA asli atau mengklaim pixel-perfect tanpa bukti.
3. **Laporan Markdown permanen:** buat/perbarui laporan **SEBELUM commit akhir** pada lokasi yang sudah diminta owner (misalnya `docs/reviews/MOBILE_VISUAL_FIDELITY_FINAL.md`), **bukan membuat laporan duplikat**. Jika belum ditetapkan, pilih `docs/reviews/<nama-tugas>_FINAL.md`. Isi wajib: tujuan; status Selesai/Parsial/Terblokir; branch/base; file dan perubahan nyata; hasil tes; bukti visual beserta jenisnya; risiko/hal belum diuji; keamanan/produksi; commit implementasi bila tersedia; tautan PR bila tersedia. Jangan menulis SHA final commit laporan di dalam commit yang sama (SHA belum ada sampai commit dibuat).
4. **Commit:** commit kode dan laporan ke feature branch aktif. Jika tidak ada perubahan, jangan buat empty commit; jelaskan status.
5. **Push terverifikasi:** apabila owner telah mengizinkan push feature branch, lakukan normal push, lalu cocokkan SHA HEAD lokal dan remote, serta periksa worktree. Bila push gagal, laporkan hambatan secara eksplisit; jangan mengaku berhasil.
6. **PR:** gunakan PR existing. Buat Draft PR hanya bila tugas memerlukannya dan aman. Jangan merge atau deploy.
7. **LAPORAN AKHIR DI CHAT UTAMA:** setelah semua langkah di atas, **WAJIB kirim satu pesan akhir yang lengkap tanpa diminta lagi**. Jika menggunakan subagent, agent utama bertanggung jawab mengumpulkan hasil dan mengirim pesan akhir. Jangan berhenti di pesan "delegated", "sedang berjalan", "session started", "sudah push", atau hanya menyimpan file Markdown.

### Format wajib laporan akhir di chat

```text
ORION GRAFIKA — LAPORAN FINAL
Status: SELESAI / PARSIAL / BLOCKED
Task:
Branch:
HEAD lokal:
HEAD remote: (sama / berbeda / belum dicek)
Commit perubahan:
PR: (URL / tidak tersedia)
File berubah:
Hasil implementasi:
Validasi: PASS [...]; FAIL [...]; NOT TESTED [...]
Screenshot: ERP ASLI / STATIC PREVIEW / TIDAK ADA
Risiko atau hambatan:
Produksi: TIDAK DISENTUH / jelaskan jika terdeteksi pelanggaran
Merge/deploy: TIDAK DILAKUKAN
```

Laporan harus **menjadi pesan final yang muncul pada percakapan utama Copilot**, bukan hanya pada sesi anak/background. Jika lingkungan Copilot tidak menyediakan mekanisme untuk mengirim ke percakapan utama dari subagent, jangan mengklaim sudah mengirim: tulis keterbatasan itu di hasil subagent dan pastikan agen pengundang meneruskan ringkasan.

## Saat tugas tidak berhasil diselesaikan

Tetap berikan laporan status **PARSIAL** atau **BLOCKED** secara otomatis: apa yang sudah dikerjakan, apa yang tidak, alasan, apakah ada commit/push, dan langkah pemulihan yang aman. Jangan menyembunyikan kegagalan. **Tugas audit/read-only tidak boleh melakukan commit/push hanya demi memenuhi format laporan.**

## Gaya kerja

Langsung ke inti. Tidak mengulang audit atau proposal yang sudah selesai. Jangan menciptakan data operasional, screenshot, hasil tes, atau klaim keberhasilan. Gunakan bukti Git dan alat yang memang tersedia.
