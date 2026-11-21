# Koreksi Konversi Lanyard & NCR

Tanggal: 21 November 2026
Branch: `feat/min-luas-hybrid-t4a`
Bersifat **koreksi data master**, bukan perubahan logika aplikasi.

## Latar

Pemeriksaan read-only terhadap konversi bahan baku menemukan dua nilai yang
tidak sesuai satuan fisiknya. Tinta Flora (44/136/137/138) dikonfirmasi owner
dikemas dalam **jerigen 5 L**, jadi `nilai_konversi = 5000 ml` sudah benar untuk
satu satuan stok dan **tidak diubah**.

## 1. LANYARD 20mm — id 56, kode LNY

| | Sebelum | Sesudah |
|---|---|---|
| `id_satuan_hitung` | 11 `pcs` | 14 `m` |
| `nilai_konversi` | 2 (m²/roll) | 100 (meter/roll) |
| `panjang_roll` | 100 m | tidak diubah |
| `lebar_roll` | 0,02 m | tidak diubah |
| BOM `Q*1,1` (produk 189/190) | tidak diubah | tidak diubah |

`2` berasal dari `0,02 m × 100 m = 2 m²/roll`, tapi BOM `Q*1,1` menghasilkan
**meter**, bukan m². Akibatnya 25 lanyard dihitung `27,5 / 2 = 13,75 roll`
(≈ 55 m per item). Dengan konversi 100 meter/roll, 25 item menjadi
`27,5 / 100 = 0,275 roll` — sesuai kebiasaan percetakan.

Bukti data lama: order `ORG-20261109-054` menyimpan `qty_pakai = 25,0000`
(bukan 13,75). BOM `Q*1,1` baru dibuat 18 November 2026, jadi histori dan
konfigurasi saat ini sebelumnya tidak konsisten.

## 2. NCR paper — id 149, kode NCR

| | Sebelum | Sesudah |
|---|---|---|
| `id_satuan_hitung` | 11 `pcs` | 18 `lembar` |
| `nilai_konversi` | 1 (1 pcs = 1 rim) | 500 (1 rim = 500 lembar) |

Sebelumnya 25 nota NCR mengurangi **25 rim**. Semua bahan `rim` lain di
database (AC260, AC210, BONTAX, AP120, AP150, A3 VINIL/HVS A3/A4, dll.)
memakai 500 lembar/rim, jadi 25 nota kini mengurangi **0,05 rim**.

Tidak diubah: `harga_rata_rata` masih 0 (butuh harga supplier) dan rumus BOM
`Q` pada produk 191/192 ("NCR rangkap 2/3"). Perlu keputusan terpisah apakah
rangkap 2/3 seharusnya `Q*2` / `Q*3`.

## Validasi hitung (sebelum migrasi dijalankan ke DB dev)

| Order | Bahan | Hitung lama | Hitung baru |
|---|---|---|---|
| 25 lanyard (produk 190) | LNY | 13,75 roll | **0,275 roll** |
| 25 NCR (produk 191) | NCR | 25 rim | **0,05 rim** |

## Yang sengaja tidak disentuh

- Tinta Flora: 5 L/jerigen → `nilai_konversi = 5000` tetap.
- BOM produk 189/190/191/192.
- Master harga, `harga_rata_rata` NCR, finishing, SPK, jurnal, route, kode aplikasi.
- Data historis `transaksi_detail_bahan_pakai` / `kartu_stok` lama (tidak ditulis ulang).

## Eksekusi

Migrasi `2026_11_21_101000_koreksi_konversi_lanyard_ncr.php` hanya mengubah
dua baris bila nilai lamanya masih persis seperti di atas; `down()` mengembalikan
nilai semula. Agen **tidak menjalankan** migrasi ke DB dev. Operator menjalankan:

```
php artisan migrate --path=database/migrations/2026_11_21_101000_koreksi_konversi_lanyard_ncr.php
```

Verifikasi:

```
php artisan tinker
>>> DB::table('bahan_baku')->whereIn('id_bahan',[56,149])->get(['id_bahan','kode_bahan','id_satuan_hitung','nilai_konversi'])
// LNY: 14 / 100 ; NCR: 18 / 500
```
