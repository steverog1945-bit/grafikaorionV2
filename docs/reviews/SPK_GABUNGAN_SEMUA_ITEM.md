# SPK Gabungan — Semua Item dalam Satu Cetak

Tanggal: 20 November 2026
Modul: Jersey (order jersey) & SPK Percetakan (nota kasir)

## Masalah

SPK dicetak **satu per item**. Order/nota dengan banyak item harus dibuka dan
dicetak berulang kali:

- Jersey: tombol SPK ada di tiap kartu barang → `/jersey/{order}/lines/{line}/spk`
- Percetakan: tombol cetak ada di tiap baris antrean → `/spk/{id_detail}/print/thermal`

## Yang ditambahkan

Satu tombol/tautan yang membuka **satu dokumen berisi semua item**, masing-masing
tetap satu halaman (page break antar item). Jalur cetak per item tidak diubah.

| Modul | Route baru | Nama route | Controller |
|---|---|---|---|
| Jersey | `GET /jersey/{order}/spk` | `jersey.orders.spk-semua` | `JerseyOrderController::printSpkSemua()` |
| Percetakan | `GET /spk/nota/{id}/print/{format?}` | `spk.print-nota` | `SpkController::printNota()` |

Titik masuk:

- Detail order jersey: tombol **Cetak SPK Semua (n)** di header (hanya bila barang > 1).
- Detail transaksi (`/penjualan/{id}`): tombol **SPK Semua Item (n)** — hanya untuk
  nota `Aktif`, item > 1, dan role yang berhak membuka menu SPK (`Izin::bolehPath(..., 'spk')`).
- Antrean SPK (`/spk`): tautan kecil **semua item** di bawah nomor nota tiap baris.

## Struktur view (satu sumber tampilan)

| Sebelum | Sesudah |
|---|---|
| `jersey/orders/spk-print.blade.php` (dokumen + isi) | `jersey/orders/_spk-sheet.blade.php` (isi) + `spk-print.blade.php` + `spk-print-semua.blade.php` |
| `spk/print.blade.php` (dokumen + isi + QR) | `spk/_spk-sheet.blade.php` (isi) + `spk/_qr-script.blade.php` (QR) + `print.blade.php` + `print-semua.blade.php` |

Alasan pemisahan: cetak tunggal dan cetak gabungan memakai markup identik, jadi
tidak ada dua versi SPK yang bisa berbeda sendiri saat diperbaiki nanti.

QR pembayaran tiap item kini memakai id unik `qr-spk-{id_detail}` supaya tidak
bertabrakan saat beberapa SPK berada di satu halaman dokumen.

## Bukti visual (browser nyata, dev lokal)

- `docs/reviews/assets/jersey-spk-semua.png` — `/jersey/37/spk`: 2 halaman SPK
  (`JO-20261119-001-151`, `JO-20261119-001-152`).
- `docs/reviews/assets/spk-nota-semua.png` — `/spk/nota/630/print/normal`: 3 SPK
  (`#764`, `#765`, `#767`) + 3 QR ter-render.

## Validasi

| Uji | Hasil |
|---|---|
| `php -l` SpkController, JerseyOrderController, routes/web.php | PASS |
| `php artisan view:cache` | PASS |
| `php artisan route:list --name=spk` | route `spk.print-nota` & `jersey.orders.spk-semua` terdaftar |
| `SpkLengkapTest, SpkFilterTanggalTest, BatalSpkHppTest, AksesLengkapDanQrTest, AlurKeamananTest, MatriksRuteKeamananTest` | 46 test / 183 assertion — PASS |
| `--filter "JerseyOrder|Jersey"` | 97 test, 4 error + 7 gagal — **sama persis dengan baseline HEAD** (pre-existing) |
| Cetak per item (`/spk/764/print/normal`, `/jersey/37/lines/151/spk`) | PASS, tampilan tidak berubah |

Tidak diuji: hasil fisik printer thermal (perlu perangkat keras), dan cetak
gabungan untuk nota `Dibatalkan` (sengaja 404 — tidak boleh ada SPK baru).

## Catatan

- Format cetak gabungan mengikuti parameter `format` yang sama (`thermal`
  default, `normal`, `continuous`).
- Nota `Dibatalkan` tidak punya SPK aktif → route mengembalikan 404.
- Produksi tidak disentuh; perubahan hanya di view, dua method controller, dan route.
