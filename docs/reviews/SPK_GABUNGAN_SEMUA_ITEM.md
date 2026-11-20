# SPK Gabungan — Daftar Kerja Semua Item (Satu Lembar)

Tanggal: 20 November 2026
Modul: Jersey (order jersey) & SPK Percetakan (nota kasir)

## Masalah

SPK dicetak **satu per item**, jadi nota dengan banyak item menghabiskan
kertas: 1 lembar per item. Padahal barcode SPK mana pun membuka **seluruh
item nota** di Monitoring Kasir, sehingga identitas item tidak perlu
dibuktikan lewat satu halaman penuh per item.

## Yang dibuat

Tombol/tautan **Daftar SPK (n)** membuka **satu lembar daftar kerja**:

- 1 barcode nota (sekali scan → semua item nota tampil)
- identitas nota/order (nomor, tanggal, konsumen, jumlah item)
- tabel semua item: No. SPK, nama file/barang, produk, ukuran, qty,
  finishing, catatan, status
- daftar nama & nomor punggung (khusus Jersey) tetap dicetak di bawahnya
  karena informasi itu tidak terbaca dengan scan

SPK per item **tidak diubah**: tombol `SPK` di tiap kartu barang / baris
antrean tetap mencetak halaman lengkap (gambar desain, estimasi bahan) bila
memang perlu ditempel di barang.

| Modul | Route | Nama route | Controller |
|---|---|---|---|
| Jersey | `GET /jersey/{order}/spk` | `jersey.orders.spk-semua` | `JerseyOrderController::printSpkSemua()` |
| Percetakan | `GET /spk/nota/{id}/print/{format?}` | `spk.print-nota` | `SpkController::printNota()` |

## Supaya tidak ada dua tombol untuk pekerjaan yang sama

Tombol/tautan "Daftar SPK" **hanya muncul bila item > 1**:

- Detail order Jersey: `$order->lines->count() > 1`
- Detail transaksi: `count($items) > 1` dan nota `Aktif` dan role berhak menu SPK
- Antrean `/spk`: tautan kecil `daftar SPK` di bawah nomor nota, hanya untuk
  nota yang jumlah itemnya > 1 (dihitung sekali per halaman lewat
  `$jumlahItemNota`, tanpa query per baris)

Nota/order 1 item → hanya tombol SPK biasa.

## Bukti visual (browser nyata, dev lokal)

- `docs/reviews/assets/jersey-daftar-spk.png` — `/jersey/37/spk`: 2 barang,
  11 pcs, 9 nama pemain → **1 lembar** (sebelumnya 2 lembar penuh).
- `docs/reviews/assets/spk-daftar-nota.png` — `/spk/nota/630/print/normal`:
  3 item dalam **1 lembar** (sebelumnya 3 lembar penuh).

## Validasi

| Uji | Hasil |
|---|---|
| `php -l` SpkController, JerseyOrderController, routes/web.php | PASS |
| `php artisan view:cache` | PASS |
| `SpkLengkapTest, SpkFilterTanggalTest, BatalSpkHppTest, AksesLengkapDanQrTest, AlurKeamananTest, MatriksRuteKeamananTest` | 46 test / 183 assertion — PASS |
| `--filter "JerseyOrder|Jersey"` | 4 error + 7 gagal, **sama persis dengan baseline HEAD** (pre-existing) |
| Cetak per item (`/spk/764/print/normal`, `/jersey/37/lines/151/spk`) | PASS, tidak berubah |

Tidak diuji: hasil fisik printer (perlu perangkat keras) dan cetak thermal untuk
daftar (kolom otomatis dipersempit, tetapi tidak diuji di kertas 80mm).

## Struktur view

| Sebelum | Sesudah |
|---|---|
| `jersey/orders/spk-print.blade.php` (dokumen + isi) | `jersey/orders/_spk-sheet.blade.php` + `spk-print.blade.php` (per item) + `spk-print-semua.blade.php` (daftar) |
| `spk/print.blade.php` (dokumen + isi + QR) | `spk/_spk-sheet.blade.php` + `spk/_qr-script.blade.php` + `print.blade.php` (per item) + `print-semua.blade.php` (daftar) |

QR per item memakai id unik `qr-spk-{id_detail}`; dokumen daftar memakai
`qr-spk-nota` (satu barcode per dokumen).
