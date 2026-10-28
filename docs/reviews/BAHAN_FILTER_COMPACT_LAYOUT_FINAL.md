# Master Bahan — filter desktop dan padding monitor kecil

Tanggal: 28 Oktober 2026 (Asia/Jakarta).

Status: implementasi selesai; validasi visual/runtime belum tersedia. Owner telah mengizinkan push feature branch dan pembuatan Draft PR dalam percakapan.

Branch: `fix/bahan-filter-compact-layout`.
Base: `main`, `dbb18b668a1578e66e7a548aa2aafb118091d073`.
PR: belum tersedia untuk perubahan ini.

## Tujuan dan perubahan

- `resources/css/mobile-bahan.css`: netralisasi kotak native `::details-content` milik filter Master Bahan pada desktop >=768px. Wrapper `details` tertutup sebelumnya tetap menyembunyikan isi di Chromium meskipun wrapper sudah memakai `display: contents`. Kotak isi kini memakai `display: contents` dan `content-visibility: visible`, mempertahankan susunan flex asli. Browser yang tidak mendukung pseudo-element ini mengabaikan aturan tambahan; perilakunya belum diverifikasi.
- `resources/views/layouts/app.blade.php`: padding khusus desktop kecil (lebar 768–1440px atau lebar >=768px dengan tinggi <=800px). Area utama 32px 24px menjadi 20px 16px; topbar 18px 24px menjadi 10px 16px dan margin 8px; wrapper halaman langsung dengan `max-w-[1600px].py-10` menjadi padding vertikal 24px dari 40px. Ukuran teks dan kontrol tetap.
- Perubahan ini mengikuti permintaan owner untuk merapatkan desktop, sebagai pengecualian terhadap aturan zero-diff desktop tugas mobile terdahulu. Tidak melanjutkan mandat lintas modul PR #21 yang berada di luar permintaan sesi ini.

## Validasi

- PASS: `npm ci --no-audit --no-fund`.
- PASS: `npm run build` setelah kedua perubahan; Vite 7.3.1, 111 modul.
- PASS: `git diff --check`.
- PASS (source review): aturan filter ditambahkan hanya di media desktop; CSS mobile Master Bahan, markup/ID/name/action/handler form tidak berubah.
- PASS (source review): aturan padding dibatasi `screen` dan desktop >=768px; tidak mengubah print atau aturan HP.
- NOT TESTED: render Laravel, visual browser pada 1366×768/1024×768/1920×1080/390×844, interaksi filter dan transisi breakpoint, light/dark runtime.
- BLOCKED: PHP/Composer tidak tersedia; executable Chromium Playwright tidak ada, unduhan browser gagal. Build bukan bukti visual atau keberhasilan interaksi runtime.
- Screenshot: TIDAK ADA. Tidak menggunakan fixture statis sebagai bukti ERP.

## Agent, skill, dan keamanan

Agent khusus menangani investigasi serta patch filter; agent utama menangani layout dan review gabungan. Instruksi `AGENTS.md` dan `.github/copilot-instructions.md` diikuti sesuai scope terbaru. Skill PromptCraft dipakai untuk mempertahankan bahasa desain dan membatasi perubahan. Paket ECC, Caveman, dan antislop yang dirujuk repo tidak tersedia dalam clone (folder skill diabaikan Git).

Produksi `C:\xampp\htdocs\grafikaorion`, `.env`, database, backend bisnis, RBAC, route, dan service worker tidak disentuh. Tidak menjalankan Artisan/migration/seeder. Tidak merge/deploy. Push feature branch dan pembuatan Draft PR diizinkan owner; merge/deploy tidak diizinkan. Hasil publikasi dan SHA remote dicatat pada laporan chat setelah verifikasi.

## Pemeriksaan lanjutan pada staging

Pada desktop 1366×768 dan sidebar terbuka/tertutup, pastikan pencarian, kategori, status, harga, urutan, reset tampil serta mengirim query yang sama. Pada 390×844 pastikan panel Filter tetap dapat dibuka/tutup. Bandingkan desktop 1920×1080 dan print dengan baseline. Gunakan staging sintetis terisolasi.
