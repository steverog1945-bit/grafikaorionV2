# Laporan Impor Data Riil (25 Sep 2026)

Sumber: `Bahan Baku.docx`. Dijalankan dengan `php artisan data:impor-riil` (aman diulang; `--simulasi` untuk uji coba).
Backup sebelum impor: `storage/app/backups/manual/backup_manual_20260925_003833.sql`.

## Ringkasan hasil
- 45 bahan diperbarui (39 di antaranya harga rata-rata diganti sesuai dokumen), 25 bahan baru.
- 77 produk baru beserta BOM (100 baris) dan 900+ baris tier harga untuk semua golongan (Retail, Reseller, Member Platinum, Member Foto, Sponsor, Asosiasi), Member Foto khusus foto sesuai dokumen.
- 2 jasa baru (Jasa Print Rp 35.000/m, Press Hidrolik Rp 3.000).
- Produk lama yang belum bertarif dilengkapi (DTF A3/A4/A5/Logo dari dokumen; BONTAX asumsi).
- Perbaikan: BOM STIKER CAMEL 155 (sebelumnya memakai Exilo), BOM DTF (1 m2 film per lembar -> luas sebenarnya), duplikat PAPER SUBLIME dinonaktifkan.
- Rumus BOM baru: `Q/N` (N barang per lembar) dan `Q*K` (K bahan per barang), juga di form BOM produk.

## Asumsi yang WAJIB diverifikasi
- ASUMSI isi: laminasi A3 200 lembar/roll, spiral 400 jilid/roll, PVC ID Card 100 pcs/dus, A3 rim = 500 lembar.
- PAPER SUBLIME (PPS) dinonaktifkan (duplikat); BOM produk Sublime Paper dialihkan ke SUBLIME PAPER (SB), isi roll 1,6m x 100m = 160 m2.
- ASUMSI Epson S80670: panjang roll 50 m; lebar Sunblast 1,27 m & Wallpaper 1,06 m; Luster 125cm Rp 1.875.000 (skala dari 90cm); Stiker Transparan 155cm Rp 1.600.000 (harga pasaran, tidak ada di dokumen).
- ASUMSI Flora: harga roll FLS 2.6m Rp 1.160.000 & 1.6m Rp 990.000 (interpolasi, tidak ada di dokumen); ring mata ayam 500 pcs/pack.
- ASUMSI Lain-lain: 1 dus amplop = 100 pcs, 1 pack gantungan kunci = 100 pcs; Pin 44mm Rp 1.200 & 55mm Rp 1.300 (urutan di dokumen tidak tegas). Belum diregistrasi (tanpa harga di dokumen): Figura, Karangan Bunga, Keplek, Linen A4, Map, Paperbag, SPPD, XBanner.
- Jasa Pasang Stiker sudah ada (Rp 50.000); PRINT ONLY dimasukkan sebagai Jasa Print Rp 35.000 (bukan produk terpisah).
- ASUMSI foto per lembar A3+: 2R=20, 3R=9, 4R=6, 6R=4, 10R/10Rs=2, 11R/12R/12Rs=1. Harga 2R (Rp 2.000) lebih tinggi dari 3R (Rp 1.600) sesuai urutan dokumen - mohon cek.
- ASUMSI produk A3 tanpa harga: seluruh harga = pasaran awal; kartu nama 100 kartu per box, 20 kartu per lembar A3+ (5 lembar AC 260 per box); brosur A4 2 per lembar A3+.
- Indoor: Exilo 152cm dilewati (produk STIKER EXILO 153 sudah ada); PRINT ONLY dimasukkan sebagai Jasa Print. Roll Up 60x120 dijual Rp 180.000 = harga beli stand Rp 180.000 (margin negatif).
- Outdoor: selisih "tanpa desain, file ready" Rp 18.000 vs "plus desain" Rp 20.000 belum dipecah menjadi produk terpisah (bisa dikelola lewat biaya desain).
- PERBAIKAN BOM DTF A3/A4/A5/Logo: sebelumnya 1 m2 Pet Film per lembar (HPP Rp 10.484), kini sesuai luas cetak + 10% sisa.

## Yang perlu dicek pemilik
1. Roll Up Albatros 60x120 dijual Rp 180.000 = harga beli stand Rp 180.000 (margin negatif); 80x200 margin 25%.
2. Harga foto 2R (Rp 2.000) lebih tinggi dari 3R (Rp 1.600) - sesuai urutan dokumen, mungkin tertukar.
3. Harga pasaran awal (A3 tanpa harga, Tripod Banner, Stiker Transparan 155cm, interpolasi FLS 2.6m/1.6m) adalah usulan, bukan data dokumen.
4. Golongan selain Retail/Member Foto diisi sama dengan Retail pada produk baru; sesuaikan.
5. Produk lama (FLS, Camel, Exilo, Inflex, TC, Sublime, AC) tidak diubah; sebagian belum punya harga untuk semua golongan.
6. Stok bahan baru = 0; isi lewat Kedatangan Bahan/Stok Awal. Harga toner dihitung per botol (satuan botol).
