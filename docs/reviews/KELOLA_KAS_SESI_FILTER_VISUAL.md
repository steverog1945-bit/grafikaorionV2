# Kelola Kas Harian — Sesi + Laporan Satu Blok, Filter Rentang & Pencarian Konsumen

Tanggal: 19 November 2026
Branch: `feat/min-luas-hybrid-t4a`
Uji: MariaDB terisolasi `percetakan_erp_testing` @ `127.0.0.1:3307` (sintetis; `.env`/`oriondb`/produksi tidak disentuh)

## Permintaan owner

1. Sesi kas + pintasan "Buka Laporan" → satu blok dua kolom.
2. Filter ditambah pencarian nama konsumen.
3. Filter tanggal → rentang (dari–sampai), default **hari ini**.
4. Hindari teks panjang yang tidak perlu.

## Yang berubah

- Sesi (kiri) dan Laporan Riwayat Closing (kanan) dalam satu kartu; desktop 2 kolom
  dengan pembatas, HP satu kolom. Kas belum dibuka pun tampil ("Belum dibuka").
- Filter: `tgl_dari` + `tgl_sampai`, pencarian `konsumen` (parsial, wildcard
  di-escape, maks 60 karakter), metode tetap. Default keduanya = hari ini.
- `?tgl=` lama tetap satu hari. Rentang terbalik ditukar otomatis. Tanggal
  divalidasi sebagai tanggal nyata, bukan sekadar regex — nilai palsu
  diabaikan, bukan 500.
- Judul tabel ikut periode: "Uang Masuk Hari Ini" / "Uang Masuk 20 Okt – 19 Nov 2026".
- Rekap Tunai/Debit/Transfer dan Kas Tidak Terduga mengikuti filter (subjudul
  menuliskan filter aktif). Tidak ada perubahan rumus/closing/stok/RBAC.
- Teks UI dan komentar dirampingkan.

## Bukti uji

```
php vendor/phpunit/phpunit/phpunit --filter "KelolaKasFilterTest" --testsuite Feature
→ OK, 9 test / 45 assertion

...--filter "GerbangLaporanClosingTest|KasCetakRekapTest|KasOpeningRekapTest|KasSesiTest|KelolaKasFilterTest|KonsistensiHalamanTest|KasClosingPerDivisiTest|CetakLaporanSenadaTest|PrintLaporanTabelTest"
→ 84 test / 378 assertion; gagal 3 (semua pre-existing, sama di HEAD)
```

`KelolaKasFilterTest` menutup: field rentang/pencarian ada; rentang membatasi
baris; `?tgl=` lama satu hari; rentang terbalik dirapikan; cari parsial;
wildcard `%`; tanggal tidak valid; kombinasi; default hari ini + label
"Uang Masuk Hari Ini".

`view:cache` PASS, `php -l` PASS, `npm run build` PASS.

## Bukti visual (Laravel lokal, Administrator, 1440px & HP 390 @2x)

- `desktop-default.png` — default hari ini, heading "Uang Masuk Hari Ini".
- `desktop-blok-2kolom.png`, `desktop-filter.png`, `desktop-filter-terisi.png`
  — blok dua kolom & filter aktif (20 Okt–19 Nov, konsumen "Wi").
- `mobile-390.png`, `mobile-390-filter.png` — HP satu kolom, filter bertumpuk.

## Risiko

- Default hari ini: sesi yang dibuka kemarin dan belum ditutup harinya menampilkan
  pembayaran hari ini saja; kasir yang ingin melihat sejak buka sesi atur tanggal
  manual.
- Total mengikuti filter (seperti filter metode sebelumnya).
- Pencarian hanya nama konsumen, bukan nomor nota.
- Tampilan placeholder tanggal mengikuti locale browser; nilai terkirim `YYYY-MM-DD`.

## Status

Produksi tidak disentuh; belum merge/deploy; push ke branch
`sync/xampp-hybrid-t4a-20261116` (branch lokal feat tidak bisa fast-forward,
lihat dokumen gerbang closing).
