# Finishing Memotong Bahan: Styrofoam Masuk Stok & HPP

Tanggal: 21 November 2026
Branch: `feat/min-luas-hybrid-t4a`
Lanjutan dari `MINIMUM_ORDER_MOCKUP_STYROFOAM.md` (keputusan Opsi A).

## Celah yang ditutup

| Celah | Sebelum | Sesudah |
|---|---|---|
| Luas finishing tidak tercatat | `transaksi_detail_finishing.qty_pakai` selalu `NULL` untuk metode `luas` | `qty_pakai` = luas/meter yang dipakai (0,24 m² untuk mockup 60×40) |
| Stok styrofoam tidak terpotong | Bahan 150 punya 0 baris `kartu_stok`, 0 baris `bahan_pakai` | Order memotong stok, menulis `kartu_stok OUT` + snapshot pemakaian |
| HPP tidak memuat styrofoam | `hpp_snapshot` hanya FRONTLITE dari BOM produk | HPP = BOM + bahan finishing (0,24 m² × 0,5 lembar/m² × 65.000 = 7.800) |

## Rancangan

Tabel `finishing` diberi dua kolom aditif (migrasi
`2026_11_21_100000_finishing_bahan_terpakai.php`):

- `id_bahan` — bahan baku yang dipotong finishing ini, **opsional**.
  Finishing jasa (jahit, laminasi) dibiarkan kosong.
- `qty_bahan_per_satuan` — satuan STOK bahan terpakai per 1 satuan HITUNG
  finishing (m² untuk `luas`, meter untuk `keliling`, pcs untuk `quantity`).

Master **Produk & BOM → Finishing** mendapat dua input tersebut (tambah & edit).
Migrasi menautkan finishing bernama "styrofoam" ke bahan baku berkode
`STYROFOAM`, tetapi **tidak menebak faktornya** — ukuran lembar adalah data
fisik yang hanya diketahui operator.

Alur checkout (`PenjualanCheckoutService::tulisFinishingItem`):

1. Simpan rincian finishing seperti biasa, sekarang dengan `qty_pakai`.
2. Jika finishing punya `id_bahan` **dan** `qty_bahan_per_satuan > 0`:
   `qtyBahan = qty_hitung × faktor`, potong `bahan_baku.stok` dengan
   `lockForUpdate()`, tulis `kartu_stok OUT`, dan tulis snapshot
   `transaksi_detail_bahan_pakai`.
3. HPP bahan finishing dijumlahkan ke `hpp_snapshot` baris item (BOM + finishing).
4. Reversal (`StokReversalService`) mengembalikan stok persis dari snapshot
   dan menghapus snapshotnya (consume-once), termasuk baris bahan finishing.

Jalur "item tidak berubah" saat koreksi nota memanggil `tulisFinishingItem`
dengan `$potongStok = false`, supaya stok tidak dipotong dua kali.
`SpkController::simpanFinishing` (rincian finishing di SPK) sengaja tidak
diubah — ia tidak mencatat pemakaian bahan sama sekali, seperti sebelumnya.

## Perubahan berkas

- `database/migrations/2026_11_21_100000_finishing_bahan_terpakai.php`
- `app/Models/Finishing.php` — fillable untuk dua kolom baru
- `app/Http/Controllers/FinishingController.php` — validasi + daftar bahan di form
- `resources/views/finishing/index.blade.php` — input tambah/edit + JS
- `app/Services/PenjualanCheckoutService.php` — `qty_pakai` luas/keliling,
  `qty_hitung`, pemotongan stok + HPP finishing
- `tests/Feature/FinishingBahanTerpakaiTest.php` — 3 skenario baru

## Validasi

Database uji terisolasi `percetakan_erp_testing` @ `127.0.0.1:3307`,
RefreshDatabase — **bukan** database dev/produksi.

| Uji | Hasil |
|---|---|
| `FinishingBahanTerpakaiTest` (3 tes) | PASS — 22 assertion |
| Regresi checkout/finishing/minimum/stok/HPP (95 tes) | PASS — 608 assertion |
| Regresi API/SPK/nota/thermal (62 tes) | PASS — 338 assertion |
| `php -l` 5 berkas PHP + `php artisan view:cache` | PASS |
| Render `/finishing` di browser | 200; input tambah & edit hadir |

Skenario uji: FLS280 60×40 cm (0,24 m²) + finishing Styrofoam 35.000/m²,
faktor 0,5 lembar/m², harga bahan 65.000/lembar →

- stok 10 → 9,88 lembar; `kartu_stok OUT` 0,12 lembar
- `hpp_snapshot` 8.040 (240 FRONTLITE + 7.800 styrofoam)
- `qty_pakai` finishing 0,24 m²; `biaya` 8.400
- reversal mengembalikan stok ke 10 dan snapshot hilang; panggilan kedua tidak mengembalikan lagi

## Konfirmasi owner & kondisi lapangan 21 November 2026

- Ukuran fisik 1 lembar styrofoam: **100 × 200 cm = 2 m²** → faktor
  `qty_bahan_per_satuan = 0,5 lembar per m²`. Faktor diisi otomatis oleh
  migrasi `2026_11_21_100100_finishing_styrofoam_faktor.php` (hanya baris yang
  masih kosong; konfigurasi manual admin tidak ditimpa).
- Order `ORG-20261121-002` (id_transaksi 668, 80×40 cm = 0,32 m², 11.200
  finishing, stok saat order belum di-adjust) diputuskan **TIDAK dikoreksi**.
  Fitur mulai berlaku untuk order baru setelah migrasi dijalankan.
- Data dev saat pemeriksaan: stok 2 lembar dari `ADJUSTMENT` 09:32
  "Recheck Stock"; `qty_pakai` finishing masih NULL, belum ada `kartu_stok OUT`
  maupun snapshot bahan 150; `hpp_snapshot` 4.505,53 baru FRONTLITE + tinta.
- Contoh order baru 0,32 m² dengan faktor 0,5: pakai **0,16 lembar**, sisa
  **1,84 lembar**, HPP bertambah **10.400** → **14.905,53**. Tagihan tetap
  11.200. Order uji 60×40 cm (0,24 m²) memotong 0,12 lembar.

## Yang masih perlu input operator

Tidak ada angka yang perlu ditebak lagi. Operator hanya perlu **menjalankan
dua migrasi** di lingkungan dev/sintetis (agen tidak menjalankan migrasi yang
mengubah DB):

```
php artisan migrate --path=database/migrations/2026_11_21_100000_finishing_bahan_terpakai.php
php artisan migrate --path=database/migrations/2026_11_21_100100_finishing_styrofoam_faktor.php
```

Verifikasi singkat setelah migrasi:

```
php artisan tinker
>>> DB::table('finishing')->where('nama_finishing','like','%tyrofoam%')->get(['id_finishing','id_bahan','qty_bahan_per_satuan'])
// harus: id_bahan = 150, qty_bahan_per_satuan = 0.5000
```

Bila ingin mengubah ukuran lembar di kemudian hari, cukup edit field
**Qty Bahan / Satuan Hitung** di master Finishing — tidak perlu migrasi baru.

## Batasan lain

- Tagihan styrofoam tetap luas aktual (keputusan Opsi A); sisa potongan diasumsikan bisa dipakai ulang.
- `min_panjang`/`min_lebar` dan aturan hard-block minimum tidak berubah.
- Tidak ada perubahan business logic harga, tarif, perhitungan, atau jurnal selain penambahan HPP bahan finishing yang memang belum tercatat.
