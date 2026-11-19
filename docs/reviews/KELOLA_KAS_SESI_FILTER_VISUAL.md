# Kelola Kas Harian — Sesi + Laporan Dua Kolom, Filter Rentang & Pencarian Konsumen

Tanggal: 19 November 2026
Branch: `feat/min-luas-hybrid-t4a`
Lingkungan uji: MariaDB terisolasi `percetakan_erp_testing` @ `127.0.0.1:3307`
(DB sintetis; `.env`, `oriondb`, dan server produksi tidak disentuh)

## Permintaan owner

1. Sesi kas dan pintasan "Buka Laporan" dijadikan **satu blok dua kolom**.
2. Filter dilengkapi **pencarian nama konsumen**.
3. Tanggal filter dijadikan **rentang** (dari–sampai).

## Pendapat (sebelum dikerjakan)

Setuju untuk ketiganya, dengan tiga catatan yang menentukan cara
implementasinya:

- **Satu blok dua kolom.** Sesi dan laporan sama-sama "pintu" halaman ini:
  sesi memberi tahu uang laci siapa yang sedang dicatat, laporan adalah
  tempat menutup/mencetaknya. Dua kartu penuh terpisah membuat halaman
  terasa punya dua judul. Digabung berdampingan, keduanya terbaca sekali
  pandang, dan di HP otomatis menumpuk satu kolom.
- **Rentang tanggal.** Rekonsiliasi hampir tidak pernah satu hari; yang
  dicari biasanya "minggu ini" atau "sejak closing terakhir". Rentang juga
  menutup kasus sesi yang melewati tengah malam.
- **Pencarian konsumen.** Berguna saat nota pelanggan ditelusuri tanpa tahu
  tanggal bayarnya. Konsekuensinya harus disadari: angka rekap di atas tabel
  (Tunai/Debit/Transfer) ikut mengecil karena dihitung dari baris yang
  tampil — sama seperti filter metode yang sudah ada. Karena itu subjudul
  tabel sekarang menuliskan filter yang sedang aktif, bukan sekadar "Hasil
  filter".

## Yang berubah

### 1. Satu blok dua kolom (`resources/views/kasir/kelola_kas.blade.php`)

- Kartu "Sesi kas" (kiri) dan "Laporan Riwayat Closing" (kanan) berada
  dalam satu kartu `rounded-xl` dengan pembatas tipis; desktop
  `sm:grid-cols-2`, HP satu kolom dengan garis pemisah horizontal.
- Sesi menampilkan: status (Aktif/belum dibuka), pemilik, jam mulai, umur
  sesi, laci awal, dan peringatan >24 jam (latar amber tetap seperti
  sebelumnya).
- Saat kasir belum membuka sesi, kolom kiri menampilkan "Belum dibuka" +
  arahan membuka saldo kasir — dulu blok ini hilang sama sekali sehingga
  halaman terasa kosong.
- Tombol "Buka Laporan" tetap menuju gerbang password Administrator
  (`laporan/kas/gerbang?status_lock=closed&tgl_dari=...&tgl_sampai=...`),
  rata kanan pada desktop, lebar penuh di HP.

### 2. Filter (`KasirController::kelolaKas` + blade)

| Sebelum | Sesudah |
| --- | --- |
| satu input `tgl` | `tgl_dari` + `tgl_sampai` |
| — | pencarian `konsumen` (nama, parsial) |
| metode | metode (tidak berubah) |

- `?tgl=YYYY-MM-DD` lama **tetap berlaku** sebagai rentang satu hari, jadi
  tautan/bookmark lama tidak rusak.
- Rentang terbalik dirapikan otomatis (tukar), bukan ditolak.
- Tanggal divalidasi sebagai tanggal nyata (`DateTimeImmutable`, bukan hanya
  regex), sehingga `2026-13-40` diabaikan dan tidak memicu error 500 saat
  view memformat tanggal.
- Pencarian memakai `LIKE %...%` dengan karakter `\`, `%`, `_` di-escape:
  mengetik `%` tidak berubah menjadi "tampilkan semua".
- Batas input 60 karakter; hanya nama konsumen yang dicari (bukan nomor
  nota).
- Kas Tidak Terduga mengikuti rentang tanggal yang sama; perubahan hanya
  pada rentang, tidak pada sumber data atau rumus.
- Subjudul tabel menyebut filter aktif, mis.
  `Tanggal 20 Okt 2026 – 19 Nov 2026 · konsumen "Wi" - rekap di atas ikut
  filter ini`, dan judul menjadi `Uang Masuk 20 Okt 2026 – 19 Nov 2026`.
- Tanpa filter apa pun, perilaku lama dipertahankan: "Uang Masuk Sesi Ini"
  (atau sejak awal hari bila belum ada sesi).

Tidak ada perubahan pada perhitungan kas/closing, stok, jurnal, model,
route, atau RBAC.

## Bukti uji

Perintah (lingkungan terisolasi):

```
php vendor/phpunit/phpunit/phpunit --filter "KelolaKasFilterTest" --testsuite Feature
→ OK, 8 test / 41 assertion

php vendor/phpunit/phpunit/phpunit \
  --filter "GerbangLaporanClosingTest|KasCetakRekapTest|KasOpeningRekapTest|KasSesiTest|KelolaKasFilterTest|KonsistensiHalamanTest|KasClosingPerDivisiTest|CetakLaporanSenadaTest|PrintLaporanTabelTest" \
  --testsuite Feature
→ 84 test / 378 assertion, 3 gagal (semuanya pre-existing, sama di HEAD):
  1. KasOpeningRekapTest::test_sesi_sebelumnya_tidak_rolling_tidak_jadi_acuan
  2. KasOpeningRekapTest::test_rekap_a4_menampilkan_rantai_rekonsiliasi_lengkap
  3. KasSesiTest::test_tutup_paksa_administrator_wajib_alasan_dan_tercatat_kasir_lain_ditolak
```

`KelolaKasFilterTest` (baru) memeriksa: field rentang & pencarian ada di
form; rentang membatasi baris; `?tgl=` lama tetap satu hari; rentang terbalik
dirapikan; pencarian konsumen parsial; wildcard `%` tidak membocorkan semua
baris; tanggal tidak valid diabaikan tanpa 500; pencarian + rentang bisa
digabung.

`php artisan view:cache` PASS, `php -l` PASS, `git diff --check` bersih,
`npm run build` PASS (kelas Tailwind baru: `sm:divide-x`, `sm:divide-y-0`,
`sm:w-auto`, `sm:self-start`).

## Bukti visual

Diambil dari `http://localhost/grafikaorionV2/kasir/kelola-kas`
(sesi login Administrator, viewport desktop 1440 px dan HP 390×844 @2x):

- `docs/reviews/assets/kelola-kas-filter/desktop-blok-2kolom.png` — sesi +
  laporan dalam satu kartu dua kolom dengan pembatas.
- `docs/reviews/assets/kelola-kas-filter/desktop-filter.png` — baris filter
  baru (Dari/Sampai tanggal, Nama konsumen, Metode, Terapkan).
- `docs/reviews/assets/kelola-kas-filter/desktop-filter-terisi.png` — filter
  aktif `20 Okt – 19 Nov 2026` + konsumen "Wi"; judul dan subjudul tabel ikut
  menyesuaikan.
- `docs/reviews/assets/kelola-kas-filter/mobile-390.png` — HP 390 px: blok
  menumpuk satu kolom, tombol Buka Laporan lebar penuh.
- `docs/reviews/assets/kelola-kas-filter/mobile-390-filter.png` — HP: filter
  bertumpuk (tanggal, konsumen, metode, Terapkan).

Batasan jujur: keadaan "belum ada sesi aktif" (kolom kiri "Belum dibuka")
belum difoto karena sesi kasir di instance lokal sedang terbuka dan tidak
ditutup demi data; tampilannya diverifikasi lewat Feature test (render tanpa
baris `kas_harian` tetap 200 dan memuat kedua kolom).

## Risiko & catatan

- Angka rekap (Tunai/Debit/Transfer dan Kas Tidak Terduga) mengikuti filter,
  sama seperti filter metode sebelumnya. Subjudul sudah menyebutkan ini.
- Pencarian hanya nama konsumen; pencarian nomor nota bisa ditambahkan bila
  diminta (kolomnya sudah tersedia di tabel).
- Format tampilan tanggal pada input mengikuti locale browser
  (mis. `mm/dd/yyyy`), nilai yang dikirim tetap `YYYY-MM-DD`.

## Status

- Produksi: tidak disentuh.
- Merge ke `main`/deploy: tidak dilakukan.
- Database produksi/`.env`/sertifikat/backup: tidak diakses.
