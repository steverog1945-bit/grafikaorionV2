# Acuan Estimasi Kain Jersey (untuk Diverifikasi di Lapangan)

Angka di bawah sudah terisi di Master Data > Jersey > Estimasi Bahan. Sumbernya
angka yang Anda input sendiri (dipulihkan dari backup 13 Sep 2026), lalu
dilengkapi/dirapikan dengan tren ukuran. **Ini acuan awal, bukan hasil ukur
riil** - verifikasi dulu sebelum go-live.

## Estimasi per pcs (meter kain / gram)

| Ukuran | Body (jersey) | Lengan Pendek | Lengan Panjang | Rib (kerah) | Celana |
|---|---|---|---|---|---|
| S | 0,712 m / 140 g | 0,18 m / 55 g | 0,30 m / 95 g | 0,03 m / 5 g | 0,84 m / 110 g |
| M | 0,730 m / 160 g | 0,20 m / 60 g | 0,33 m / 105 g | 0,03 m / 5 g | 0,89 m / 120 g |
| L | 0,750 m / 180 g | 0,22 m / 65 g | 0,36 m / 125 g | 0,03 m / 5 g | 1,00 m / 135 g |
| XL | 0,860 m / 200 g | 0,24 m / 70 g | 0,39 m / 125 g | 0,03 m / 5 g | 1,05 m / 150 g |
| 2XL | 0,920 m / 230 g | 0,26 m / 80 g | 0,43 m / 140 g | 0,03 m / 5 g | 1,10 m / 170 g |
| 3XL | 0,960 m / 260 g | 0,28 m / 90 g | 0,47 m / 155 g | 0,04 m / 5 g | 1,15 m / 190 g |
| 4XL | 1,000 m / 290 g | 0,31 m / 100 g | 0,51 m / 170 g | 0,04 m / 5 g | 1,20 m / 210 g |
| 5XL | 1,040 m / 320 g | 0,34 m / 110 g | 0,55 m / 185 g | 0,04 m / 5 g | 1,25 m / 230 g |

Lengan pendek dan panjang diambil dari matriks Tipe Lengan; Body, Rib, dan
Celana dari matriks Komponen. Hanya bagian yang dipesan yang ikut dihitung:
jersey lengan pendek tidak memuat lengan panjang maupun celana, dan celana
tidak memuat body/lengan/kerah.

## Yang saya koreksi/isi (semua bisa diubah di Master)

- Meter 4XL dan 5XL yang kosong diisi mengikuti tren ukuran sebelumnya.
- Celana: angka lama datar 1,00 m untuk L-2XL lalu melonjak 1,30 m di 3XL.
  Dirapikan menjadi naik bertahap 0,05 m per ukuran.
- Lengan panjang 3XL yang turun ke 0,48 m dinaikkan mengikuti tren (0,70 m).
- Gram tidak diubah sama sekali.
- Tipe Kerah masih kosong; kerah sudah terwakili komponen Rib. Isi tipe kerah
  di Master hanya bila memang ada kain kerah terpisah yang beda per model.

## Cara verifikasi riil (disarankan, sekali saja per tipe)

1. Ambil 1 pola jadi untuk tiap ukuran (minimal S, L, XL, 3XL; sisanya
   mengikuti selisih).
2. Ukur panjang kain terpakai per bagian (body, lengan, rib, celana) pada
   lebar kain yang biasa dipakai, tambah kira-kira 3-5% untuk sisa potong.
3. Timbang kain terpakai untuk mengisi gram.
4. Ubah angka di Master Data > Jersey > Estimasi Bahan bila selisih dengan
   tabel di atas lebih dari sekitar 5%.

Catatan: estimasi hanya perkiraan (bukan pemotongan stok). Angka riil dicatat
terpisah lewat Waste/Stok Opname.
