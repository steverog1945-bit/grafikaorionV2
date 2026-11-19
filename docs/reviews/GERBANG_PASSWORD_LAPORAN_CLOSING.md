# Gerbang Password Administrator untuk Laporan Closing

Tanggal: 19 November 2026
Branch: `feat/min-luas-hybrid-t4a`
Lingkungan uji: MariaDB terisolasi `percetakan_erp_testing` @ `127.0.0.1:3307`
(DB sintetis; tidak menyentuh `.env`, `oriondb`, atau server produksi)

## Permintaan owner (19 Nov 2026)

1. **Jangan tampilkan riwayat closing di Kelola Kas Harian.**
2. **Untuk membuka laporan closing, harus memasukkan password akun
   Administrator.**

Sebelumnya (usulan awal sesi ini) sesi aktif dan riwayat closing digabung dalam
satu kartu di Kelola Kas. Usulan itu **dibatalkan seluruhnya** dan halaman
dikembalikan ke bentuk semula: Kelola Kas hanya memuat sesi aktif, filter
mutasi, pintasan laporan, tabel uang masuk, dan tabel kas tidak terduga.

## Perilaku yang berlaku sekarang

### Kelola Kas Harian (`/kasir/kelola-kas`)

- Tidak ada daftar/riwayat sesi yang sudah ditutup.
- Kartu "Laporan Riwayat Closing" tetap ada sebagai pintasan, tetapi tombolnya
  menuju **halaman gerbang**, bukan langsung ke laporan:
  `/laporan/kas/gerbang?status_lock=closed&tgl_dari=...&tgl_sampai=...`.
- Setelah closing, tombol "Cetak Thermal 58mm" dan "Cetak A4 Lengkap" juga
  menuju gerbang yang sama (`?cetak={id}&format={thermal|a4}`), dengan catatan
  kecil "Cetakan meminta password Administrator".

### Halaman gerbang (`GET /laporan/kas/gerbang`)

- Menampilkan form password Administrator; **tidak memuat angka laporan apa
  pun** (tidak ada isi laci, selisih, atau saldo).
- Menyebut tujuan: membuka Laporan Kas/riwayat closing, atau mencetak rekap
  sesi #tertentu.
- Password salah / password akun kasir sendiri / password kosong ditolak
  dengan pesan "Password Administrator tidak cocok. Laporan closing tetap
  terkunci."
- Password benar membuka laporan selama **30 menit** untuk sesi login itu,
  lalu terkunci lagi otomatis.

### Penegakan di server (bukan sekadar tautan)

- `LaporanController::kas` — seluruh halaman Laporan Kas dialihkan ke gerbang
  bila gembok belum terbuka. Halaman ini isinya rekap sesi + tombol cetak
  closing, jadi membatasi hanya `status_lock=closed` akan mudah dilewati
  dengan menghapus filter.
- `KasirController::printReport` — cetak rekap closing per sesi juga dialihkan
  ke gerbang.
- Administrator yang sedang login **tidak** perlu mengetik password (role
  `Administrator*` lolos). Password akun non-Administrator tidak pernah
  berlaku, walau role-nya punya menu Laporan Kas.
- Alamat laporan yang diketik langsung di address bar tetap terkena
  pengalihan — bukan hanya tombol yang disembunyikan.

### Detail teknis

- Service: `app/Services/GerbangLaporanClosing.php`
  - `terbuka()` — Administrator login, atau session
    `laporan_closing_terbuka_sampai` masih berlaku.
  - `passwordAdministratorCocok()` — `Hash::check` terhadap akun ber-role
    `Administrator*` yang `is_active = 1`. Hanya hash yang dibaca; password
    tidak disimpan/dicatat.
  - `parameter()`/`tujuan()` — hanya meneruskan parameter yang dikenal
    (`tgl_dari`, `tgl_sampai`, `status_lock`, `page`, `cetak`, `format`),
    sehingga halaman gerbang tidak bisa dipakai sebagai open redirect.
- Controller: `app/Http/Controllers/GerbangLaporanClosingController.php`
- View: `resources/views/laporan/kas_gerbang.blade.php`
- Route: `laporan.kas.gerbang` (GET) dan `laporan.kas.gerbang.buka` (POST,
  `throttle:10,1`).
- RBAC: `config/menu_access.php`
  - `'laporan/kas/gerbang' => [16, 29]` — kasir boleh **membuka halaman
    gerbang** (menu Kasir 16 atau Laporan Kas 29); password Administrator
    yang menentukan laporannya terbuka.
  - `laporan/kas/gerbang` didaftarkan di `post_baca` supaya POST password
    diperlakukan sebagai "membaca", bukan "menambah data".

## File yang berubah

| File | Perubahan |
| --- | --- |
| `app/Services/GerbangLaporanClosing.php` | Baru — gembok, verifikasi password, whitelist parameter/tujuan |
| `app/Http/Controllers/GerbangLaporanClosingController.php` | Baru — tampilkan gerbang & proses unlock |
| `resources/views/laporan/kas_gerbang.blade.php` | Baru — form password, tanpa data laporan |
| `app/Http/Controllers/LaporanController.php` | Guard gerbang di `kas()` |
| `app/Http/Controllers/Kasir/KasirController.php` | Guard gerbang di `printReport()` |
| `routes/web.php` | Route gerbang GET/POST |
| `config/menu_access.php` | Rule akses gerbang + `post_baca` |
| `resources/views/kasir/kelola_kas.blade.php` | Pintasan + tombol cetak menuju gerbang; tanpa riwayat closing |
| `tests/Feature/GerbangLaporanClosingTest.php` | Baru — 8 test penegakan gembok |
| `tests/Feature/KasCetakRekapTest.php` | Helper membuka gerbang sebelum membaca laporan |
| `tests/Feature/KasOpeningRekapTest.php` | Membuka gerbang di `setUp` (cetak rekap) |

Tidak ada perubahan pada perhitungan kas, closing, stok, jurnal, model, atau
tabel database.

## Bukti uji

Perintah (environment terisolasi):

```
php vendor/phpunit/phpunit/phpunit --filter "KasCetakRekapTest|KasOpeningRekapTest|KasSesiTest|KonsistensiHalamanTest|GerbangLaporanClosingTest|KasClosingPerDivisiTest" --testsuite Feature
```

Hasil: **52 test, 239 assertion**, gagal 3 — ketiganya **gagal yang sama tanpa
perubahan ini** (diverifikasi dengan `git stash` pada HEAD):

1. `KasOpeningRekapTest::test_sesi_sebelumnya_tidak_rolling_tidak_jadi_acuan`
2. `KasOpeningRekapTest::test_rekap_a4_menampilkan_rantai_rekonsiliasi_lengkap`
3. `KasSesiTest::test_tutup_paksa_administrator_wajib_alasan_dan_tercatat_kasir_lain_ditolak`

Uji lebih luas `--filter "Laporan|Kas"`: **246 test, 1187 assertion**, gagal 4
+ 1 error; empat di antaranya pre-existing (3 di atas + `UploadBerkasDesainTest`)
dan satu `DashboardRingkasanCollectingTest` juga gagal di HEAD tanpa perubahan
ini:

```
git stash push  → run DashboardRingkasanCollectingTest + UploadBerkasDesainTest → stash pop
Hasil: 15 test, 1 failure, 1 error (sama, tanpa perubahan ini)
```

Test baru `GerbangLaporanClosingTest` (8/8 PASS) memeriksa:

- kasir tanpa password dialihkan ke gerbang (Laporan Kas maupun cetak rekap);
- halaman gerbang tidak membocorkan angka laci/selisih;
- password akun kasir sendiri ditolak;
- password Administrator membuka laporan dan meneruskan filter tanggal/status;
- cetak setelah unlock langsung mendarat di sesi yang diminta;
- Administrator yang login tidak perlu password;
- Kelola Kas tidak lagi mendaftar sesi yang sudah ditutup.

`php artisan view:cache` PASS. `php -l` untuk semua file yang berubah PASS.
`npm run build` tidak diperlukan (tidak ada perubahan CSS/JS).

## Bukti visual

Diambil dari instance Laravel lokal (`http://localhost/grafikaorionV2`) pada
HEAD saat ini:

- `docs/reviews/assets/gerbang-closing/kelola-kas-laporan-terkunci.png` —
  Kelola Kas tanpa riwayat closing; pintasan "Laporan Riwayat Closing" terkunci.
- `docs/reviews/assets/gerbang-closing/laporan-kas-admin.png` — Laporan Kas
  tetap terbuka untuk Administrator yang login (bypass gembok).

**Keterbatasan jujur:** halaman gerbang belum bisa difoto dari browser lokal
karena satu-satunya sesi login yang tersedia adalah akun Administrator (role
`Administrator` otomatis lolos gembok). Login sebagai kasir memerlukan
kredensial yang tidak boleh ditebak/dipakai. Tampilan dan perilakunya
diverifikasi lewat Feature test (HTML respons nyata), bukan screenshot.

## Risiko & catatan operasional

- Kasir yang perlu mencetak rekap setelah closing harus memanggil Administrator
  untuk mengetik password. Ini konsekuensi langsung dari permintaan owner.
- Unlock berlaku 30 menit per sesi login; setelah itu laporan terkunci lagi.
- Role baca-saja (mis. Owner) yang bukan `Administrator` juga melewati gerbang.
  Bila owner ingin role tersebut ikut terkunci/dibebaskan, aturannya cukup
  diubah di `GerbangLaporanClosing::terbuka()`.
- API `/api/laporan/kas` tidak diubah; pagar API tetap RBAC token yang sudah
  ada.

## Status Git

- Commit fitur: `feat/min-luas-hybrid-t4a` (lokal).
- Push langsung ke `origin/feat/min-luas-hybrid-t4a` **ditolak** karena branch
  remote sudah berjalan 53 commit lebih maju (termasuk merge PR #48 dan
  perubahan `PenjualanController`, `JerseyOrderController`,
  `PenjualanCheckoutService`, `JerseyOrderPricingService`,
  `config/logging.php`). Menggabungkannya memaksa penyelesaian konflik pada
  logika bisnis POS/Jersey — di luar scope tugas UI/gembok ini dan berisiko.
  Branch lokal tidak di-force-push dan tidak di-reset.
- Karena itu commit di-cherry-pick ke branch tujuan kedua
  `sync/xampp-hybrid-t4a-20261116` (basis `9cb11894`, snapshot yang dipakai
  XAMPP) dan **berhasil di-push tanpa force**: `9cb11894..c52c4965`.
  `git ls-remote` mengonfirmasi remote `sync/xampp-hybrid-t4a-20261116` =
  `c52c4965` (sama dengan HEAD lokal saat push).
- Tidak ada merge ke `main`, tidak ada deploy.

## Status

- Produksi: tidak disentuh.
- Merge ke `main`/deploy: tidak dilakukan.
- Database produksi/`.env`/sertifikat/backup: tidak diakses.
