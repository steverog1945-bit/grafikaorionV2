# Minimum Order FLS280 — Pengecualian Mockup Styrofoam

Tanggal: 21 November 2026
Branch: `feat/min-luas-hybrid-t4a`
Keputusan owner: **Opsi A — pengecualian mockup**

## Masalah

Produk FLS280 punya `min_luas_total = 1.0000` per kelompok `FLS280`. Sejak
aturan hard-block 15 November 2026, kelompok yang hanya berisi **satu item**
di bawah 1 m² ditolak saat simpan (layar + server). Akibatnya mockup styrofoam
berukuran kecil tidak bisa dijual lagi, padahal nota lama
`ORG-20261106-030` (FLS280 60×40 cm + finishing Styrofoam) pernah dibuat
dengan total Rp 28.400.

Finishing `Styrofoam Mockup` (`id_finishing = 20`) memakai `metode_hitung =
luas` dan tarif Rp 35.000/m². Qty finishing selalu mengikuti luas cetak
aktual (`PenjualanCheckoutService::validasiItems`, cabang `'luas'`), sehingga
tidak ada cara memasukkan luas styrofoam yang lebih kecil dari luas cetak.

## Keputusan

1. **Satu item FLS280 di bawah 1 m² boleh disimpan bila memakai finishing
   Styrofoam.** Deteksi berbasis nama master finishing yang memuat
   `styrofoam` (bukan id hardcode), sehingga berlaku di database lain.
2. **Penagihan tidak berubah:**
   - cetak tetap ditagih minimum kelompok 1 m² (selisih digulung ke baris
     itu, `biaya_minimum` + `is_minimum_order = 1`);
   - styrofoam tetap ditagih luas aktual.
3. Finishing lain **tidak** membuka pengecualian (mencegah bypass minimum
   dengan menambahkan finishing murah).

## Perubahan kode

| Berkas | Perubahan |
|---|---|
| `app/Services/PenjualanCheckoutService.php` | Helper `barisMockupStyrofoam()`; flag `mockup_styrofoam` per baris kelompok minimum; hard-block 1 item dilewati hanya untuk baris mockup |
| `resources/views/penjualan/create.blade.php` | Helper `barisMockupStyrofoam()`; hard-block `simpanTransaksi()` melewati baris mockup; teks peringatan menyebut pengecualian dan penagihan minimum |
| `tests/Feature/MinimumLuasAkumulasiTest.php` | 3 skenario baru (mockup diterima, tanpa styrofoam ditolak, finishing lain ditolak) |

Tidak ada perubahan pada perhitungan harga, tarif, stok, atau alur desktop.

## Validasi

Lingkungan: database uji terisolasi `percetakan_erp_testing` @
`127.0.0.1:3307` (RefreshDatabase), bukan database dev/produksi.

| Uji | Hasil |
|---|---|
| `MinimumLuasAkumulasiTest` (9 tes) | PASS — 52 assertion |
| `MinimumLuasRekonsiliasiTest` + `Tahap3OutdoorMinTest` + unit `MinimumLuas*` (32 tes) | PASS — 165 assertion |
| `php -l` kedua berkas | PASS |
| `php artisan view:cache` | PASS |
| Render `/penjualan/kasir` | 200, helper + teks pengecualian ada di HTML |

Skenario mockup yang diuji: FLS280 60×40 cm (0,24 m²), harga 20.000/m²,
styrofoam 35.000/m² →

- `biaya_cetak` = 20.000 (0,24 m² aktual 4.800 + selisih 0,76 m² × 20.000 =
  15.200)
- `biaya_minimum` = 15.200, `is_minimum_order` = 1
- `biaya_finishing` = 8.400 (0,24 m² × 35.000)
- subtotal/total nota = 28.400 — identik dengan nota `ORG-20261106-030`

## Batasan / belum dikerjakan

- Aturan lantai `min_panjang`/`min_lebar` tidak berubah; pengecualian ini
  hanya untuk blokir kelompok minimum luas.
- Dua celah yang disebut di dokumen ini (stok/HPP styrofoam dan `qty_pakai`
  finishing `luas` yang NULL) **sudah ditutup** pada 21 November 2026.
  Lihat `FINISHING_BAHAN_TERPAKAI.md` untuk rancangan, uji, dan satu data
  yang masih perlu diisi operator (ukuran lembar styrofoam).
