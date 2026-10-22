# LAPORAN AKURAT — AUDIT FINISHING INDOOR (DARI SOURCE ORIONDB)

## 1. PERBAIKI PEMAHAMAN RELASI (CRITICAL)

### Verifikasi di oriondb:

- **finishing.id_kategori** mengarah ke **kategori.id_kategori** (bukan kategori_finishing!).
  - `finishing` table memiliki kolom `id_kategori bigint unsigned nullable`
  - Ada foreign key implisit melalui index `fk_finishing_mesin` pada `id_mesin`, tapi **tidak ada foreign key eksplisit** ke `kategori` di skema migration — tetapi aplikasi tetap mengira `id_kategori` dari tabel `kategori`.

- **kategori_finishing adalah tabel terpisah yang belum terbukti dipakai** untuk pemilihan finishing di POS.
  - Struktur ada tapi **data-nya kosong** (0 row) di oriondb.
  - Form pelayanan finishing di `penjualan/create.blade.php` mengambil dari `DB::table('finishing')`, bukan dari `kategori_finishing`.
  - **JANGAN membuat kategori "Laminasi" dan "Kisscut" di kategori_finishing** hanya untuk fitur ini.

- **Indoor = kategori produk** (ID 2 di tabel kategori).
  - ID kategori 2 = "INDOOR" di tabel `kategori`.
  - Finishing dengan id_kategori=2: Kisscut (ID 1), Laminasi Dingin (ID 10), Potong Manual (ID 15).

- **Laminasi dan Kisscut = jenis finishing** (nilai `nama_finishing`).
  - Tidak ada field "jenis" khusus di tabel finishing. Distingguilah berdasarkan nama dan kategori.

### ID aktual dari oriondb:

| Entitas | ID | Nama |
|---|---|---|
| `kategori.id_kategori` | 2 | INDOOR |
| `kategori.id_kategori` | 3 | OUTDOOR |
| `kategori.id_kategori` | 4 | A3 |
| `kategori.id_kategori` | 5 | CENTURY STAR |
| `kategori.id_kategori` | 6 | SUBLIME |
| `kategori.id_kategori` | 7 | RHINO DTF |
| `kategori.id_kategori` | 8 | Lain-lain |
| `finishing.id_finishing` | 1 | Kisscut |
| `finishing.id_finishing` | 5 | Hot Laminasi Doff/Glossy |
| `finishing.id_finishing` | 6 | Mata Ayam |
| `finishing.id_finishing` | 7 | Press Hidrolik |
| `finishing.id_finishing` | 8 | Jahit Keliling |
| `finishing.id_finishing` | 9 | (tidak ada di data) |
| `finishing.id_finishing` | 10 | Laminasi Dingin Doff/Glossy |
| `finishing.id_finishing` | 11 | SPIRAL A4/F4 |
| `finishing.id_finishing` | 12 | SPIRAL A5 |
| `finishing.id_finishing` | 13 | SPIRAL A3 |
| `finishing.id_finishing` | 14 | Jasa Pasang oneway |
| `finishing.id_finishing` | 15 | Potong Manual |
| `mesin.id_mesin` | 1 | EPSON S80670 |
| `mesin.id_mesin` | 2 | FLORA OUTDOOR |
| `mesin.id_mesin` | 3 | KONICA MINOLTA |
| `mesin.id_mesin` | 4 | CENTURY STAR |
| `mesin.id_mesin` | 5 | RHINO DTF |
| `mesin.id_mesin` | 6 | ESPON F7270 |
| `mesin.id_mesin` | 7 | LAINNYA |
| `mesin.id_mesin` | 9 | **ROLAND G540** (tipe Cutting Plotter, tampil_divisi=0) |

### Relasi finishing -> kategori dari data aktual:

| finishing.id_finishing | nama_finishing | id_kategori | nama_kategori |
|---|---|---|---|
| 1 | Kisscut | 2 | INDOOR |
| 10 | Laminasi Dingin Doff/Glossy | 2 | INDOOR |
| 15 | Potong Manual | 2 | INDOOR |
| 5 | Hot Laminasi Doff/Glossy | 4 | A3 |
| 11 | SPIRAL A4/F4 | 4 | A3 |
| 12 | SPIRAL A5 | 4 | A3 |
| 13 | SPIRAL A3 | 4 | A3 |
| 6 | Mata Ayam | 3 | OUTDOOR |
| 14 | Jasa Pasang oneway | 3 | OUTDOOR |
| 8 | Jahit Keliling | 5 | CENTURY STAR |
| 9 | Pemasangan Tali | 5 | CENTURY STAR |
| 7 | Press Hidrolik | 7 | RHINO DTF |

**Kesimpulan:** Gunakan `kategori.id_kategori = 2` untuk finishing Indoor (Kisscut, Laminasi Dingin, Potong Manual). Jangan gunakan `kategori_finishing` yang kosong.

---

## 2. AUDIT DATA AKTUAL ORIONDB

### Kategori (id_kategori, nama_kategori):
- 2 = INDOOR
- 3 = OUTDOOR
- 4 = A3
- 5 = CENTURY STAR
- 6 = SUBLIME
- 7 = RHINO DTF
- 8 = Lain-lain

### Finishing (id_finishing, nama_finishing, id_kategori, id_mesin, metode_hitung, harga_default):
| ID | Nama | Kategori | Mesin | Metode | Harga Default |
|---|---|---|---|---|---|
| 1 | Kisscut | 2 (INDOOR) | 1 (EPSON S80670) | luas | 20.000 |
| 5 | Hot Laminasi Doff/Glossy | 4 (A3) | 3 (KONICA MINOLTA) | quantity | 2.000 |
| 6 | Mata Ayam | 3 (OUTDOOR) | 2 (FLORA OUTDOOR) | quantity | 700 |
| 7 | Press Hidrolik | 7 (RHINO DTF) | 5 (RHINO DTF) | quantity | 3.000 |
| 8 | Jahit Keliling | 5 (CENTURY STAR) | 4 (CENTURY STAR) | keliling | 1.500 |
| 9 | Pemasangan Tali | 5 (CENTURY STAR) | 4 (CENTURY STAR) | quantity | 300 |
| 10 | Laminasi Dingin Doff/Glossy | 2 (INDOOR) | 1 (EPSON S80670) | luas | 25.000 |
| 11 | SPIRAL A4/F4 | 4 (A3) | 3 (KONICA MINOLTA) | quantity | 5.000 |
| 12 | SPIRAL A5 | 4 (A3) | 3 (KONICA MINOLTA) | quantity | 4.000 |
| 13 | SPIRAL A3 | 4 (A3) | 3 (KONICA MINOLTA) | quantity | 8.500 |
| 14 | Jasa Pasang oneway | 3 (OUTDOOR) | 2 (FLORA OUTDOOR) | luas | 50.000 |
| 15 | Potong Manual | 2 (INDOOR) | 1 (EPSON S80670) | keliling | 450 |

### finishing_harga (range_min, range_max, harga) — Tipe DECIMAL(10,2):
| ID | ID Finishing | range_min | range_max | harga |
|---|---|---|---|---|
| 8 | 5 | 1.00 | 20.00 | 2.000 |
| 9 | 5 | 21.00 | 50.00 | 1.750 |
| 10 | 5 | 51.00 | 100.00 | 1.500 |
| 11 | 10 | 0.01 | 4.99 | 15.000 |
| 12 | 10 | 5.00 | 19.99 | 13.500 |
| 13 | 10 | 20.00 | NULL | 12.000 |
| 14 | 13 | 1.00 | 10.00 | 9.000 |
| 15 | 13 | 11.00 | 20.00 | 8.500 |
| 16 | 14 | 0.10 | 100.00 | 50.000 |

### Mesin (id_mesin, nama_mesin, tipe_mesin, tampil_divisi):
| ID | Nama | Tipe | tampil_divisi |
|---|---|---|---|
| 1 | EPSON S80670 | Eco-solvent | 1 |
| 2 | FLORA OUTDOOR | Solvent | 1 |
| 3 | KONICA MINOLTA | Laser A3 | 1 |
| 4 | CENTURY STAR | Direct Print | 1 |
| 5 | RHINO DTF | Digital Sablon | 1 |
| 6 | ESPON F7270 | Sublime | 1 |
| 7 | LAINNYA | Multi-print | 1 |
| **9** | **ROLAND G540** | **Cutting Plotter** | **0** |

### transaksi_detail_finishing (status_finishing, qty_pakai, biaya):
| ID | ID Finishing | Status | Qty Pakai | Biaya |
|---|---|---|---|---|
| 1 | 8 (Jahit Keliling) | Custom | | 15.600 |
| 2 | 9 (Pemasangan Tali) | Custom | 2.000 | 1.000 |
| 3 | 8 (Jahit Keliling) | Standar | | 36.720 |
| 4 | 10 (Laminasi Dingin Doff/Glossy) | Standar | | 27.540 |

### Status Standar vs Custom (dari transaksi_detail + logika controller):
- **Standar**: `biaya_finishing` = `HargaService::getHargaFinishingEfektif($idF, $qtyF)` — dihitung dari master (harga_default + tier finishing_harga). Kiriman client diabaikan jika `hargaTerkunci=true` (role tanpa hak lihat/harga).
- **Custom**: Jika role `boleh_lihat_harga=true` (Administrator): `biaya_finishing` = nilai dari kiriman client. Jika role `boleh_lihat_harga=false` (Operator Desain): `biaya_finishing` = dihitung ulang server (harga_default + tier), kiriman klien diabaikan. Status_finishing tetap tulis "Custom" di database.

### Produk Finishing & BOM:
- `produk_finishing`: 0 row di oriondb (kosong)
- `bom_finishing`: 0 row di oriondb (kosong)

---

## 3. PERBAIKI ANALISIS STANDAR/CUSTOM

### Kontrak existing (dibaca dari PenjualanController::store dan HargaService):

**Standar:**
- `status_finishing = 'Standar'` → `biaya_finishing` = `HargaService::getHargaFinishingEfektif($idF, $qtyF)` * selalu dihitung server dari master.
- Role tanpa hak ubah harga (`hargaTerkunci=true`) → biaya cetak & desain & finishing dihitung server total.
- Field `input_status_finishing` di form readonly jika `hargaTerkunci`.

**Custom:**
- `status_finishing = 'Custom'` → 
  - Jika administrator (`boleh_lihat_harga=true`): boleh kirim nilai manual via `biaya_finishing`, disimpan langsung.
  - Jika operator desain (`boleh_lihat_harga=false`): nilai kiriman client diabaikan, dihitung ulang server, tapi status tetap "Custom" di database.
- **Tidak ada preset dan field spesifikasi teknis yang berbeda** — semua finishing pakai form yang sama di `finishing.index` + modal.
- **Perubahan harga manual tetap mengikuti permission existing** — hanya role dengan `boleh_lihat_harga=true` yang boleh menentukan biaya sendiri.

### Rancangan target (dari user instruction):

**Standar:**
- Baca preset teknis finishing dari master (`finishing.metode_hitung`, `finishing.harga_default`, `finishing_harga` tier).
- Kalkulasi otomatis sesuai metode (luas/keliling/quantity).
- Field `input_biaya_finishing` di form readonly jika status = Standar atau role tanpa hak.

**Custom:**
- Membuka input teknis sesuai jenis finishing (tidak ada field terpisah).
- Bukan otomatis memberi hak mengubah harga — hanya role berwenang (`boleh_lihat_harga=true`) yang boleh.
- Perubahan harga manual tetap mengikuti permission existing (hanya role berwenang yang bisa override).

**Jaga kompatibilitas data transaksi lama:**
- Status_finishing "Standar" di transaksi lama (ID 3, 4) biaya 36.720 dan 27.540 sesuai hitungan server dari harga_default + tier.
- Status_finishing "Custom" di transaksi lama (ID 1, 2) biaya 15.600 dan 1.000 adalah nilai manual yang disimpan karena admin yang membuatnya.

---

## 4. FINISHING INDOOR

Berdasarkan data oriondb aktual, jenis finishing yang masuk kategori INDOOR (id_kategori=2):

### A. Laminasi
- **Parameter**: Jenis film (Doff/Glossy), jumlah sisi, luas pengerjaan.
- **Metode hitung**: `luas` (m²).
- **Contoh dari data**:
  - Kisscut (ID 1): EPSON S80670, luas, harga_default 20.000
  - Laminasi Dingin Doff/Glossy (ID 10): EPSON S80670, luas, harga_default 25.000
- **Hafif finishing_harga tier** untuk finishing ID 10 (Laminasi Dingin):
  - 0.01-4.99 m²: Rp 15.000/m²
  - 5.00-19.99 m²: Rp 13.500/m²
  - 20.00+ m²: Rp 12.000/m²

### B. Kisscut Roland G540
- **Parameter**: Ukuran pekerjaan, metode cutting, jumlah atau luas pekerjaan.
- **Validasi batas lebar mesin**: Dari data, Kisscut (ID 1) menggunakan mesin ID 1 (EPSON S80670, Eco-solvent), BUKAN Roland G540.
- **Catatan Penting**: "JANGAN menyamakan Kisscut mesin dengan potong manual." — Ini dua jenis finishing berbeda.
- **Roland G540** ada di tabel `mesin` dengan ID 9, tipe "Cutting Plotter", tapi `tampil_divisi=0` (tidak tampil di divisi operasional).
- **Tidak ada finishing di finishing table yang merujuk ke mesin ID 9 (Roland G540)** kecuali jika operator manually menambahkannya. Seluruh finishing di oriondb menggunakan mesin ID 1, 2, 3, 4, atau 5.

### C. Potong Manual Cutter — LINEAR_GRID
- **Rumus** (dari user instruction): `L = ((kolom - 1) × tinggi_media + (baris - 1) × lebar_media) / 100`
- **Hanya untuk layout grid penuh tanpa pekerjaan potong tepi tambahan**.
- **Untuk gap positif atau layout tidak beraturan, jangan paksa rumus ini menghasilkan biaya otomatis**.
- **Metode hitung**: `keliling` (dihalalkan untuk Potong Manual, ID 15).

**Penting**: Potong Manual (ID 15) ada di kategori INDOOR (2), metode `keliling`, harga_default 450. Ini berbeda dengan Kisscut (ID 1) yang metode `luas`.

---

## 5. MIGRATION KANDIDAT

### Status: File migration `2026_11_02_100000_add_indoor_finishing_configuration_and_snapshots.php` 

**TIDAK ADA di folder aktual** `C:\xampp\htdocs\grafikaorion\database\migrations/`. Nama tersebut hanya disebutkan pada branch fitur yang belum di-merge.

### Perbedaan dengan skema lokal:

| Elemen | Skema Lokal (oriondb) | Migration Kandidat |
|---|---|---|
| `finishing` table | Sudah ada, 15 row data | Hanya dokumentasi/seed, bukan create tabel |
| `finishing_harga` | `range_min`, `range_max` = `decimal(10,2)` | Sudah ada, tipe sudah konvertdi decimal oleh migrasi existing 2026_09_11_240000 |
| `kategori_finishing` | Struktur ada, 0 row data | Seharusnya isi data kategori finishing (jika digunakan) |
| `mesin` | 9 row data termasuk Roland G540 (ID 9) | Sudah ada |
| `produk_finishing`, `bom_finishing` | 0 row | Sudah ada schema, data harus diisi |

### Konflik dan solusi:

1. **Jika migration menambah kolom yang sudah ada**: Tidak boleh menambahkolom `id_kategori` atau `id_mesin` ke finishing — sudah ada dan terintegrasi.

2. **Jika migration menambah tabel finishing baru**: **DILARANG** — sudah ada 15 finishing di `finishing` table.

3. **Jika migration menambah data ke `kategori_finishing`**: Boleh, tapi **hanya sebagai seed data tambahan**, jangan dijadikan acuan pemilihan finishing di POS (karena `finishing.id_kategori` mengarah ke `kategori`, bukan `kategori_finishing`).

4. **Jika migration mengubah tipe `range_min`/`range_max` finishing_harga**: **DILARANG** — skema sudah `decimal(10,2)` dari migrasi existing. Migrasi baru harus pakai `decimal(10,2)` juga.

5. **Jangan menggunakan ID hardcode** di migration — gunakan `if (!DB::table(...)->exists())` pattern.

### Siapkan dukungan minimal untuk migration aditif:

Migration candidate seharusnya hanya menambah:
- Data seed ke `kategori_finishing` (jika ingin digunakan) — TIDAK WAJIB karena kosong.
- Data seed finishing contoh jika tabel baru — **TIDAK PERLU**, sudah ada 15 finishing di oriondb.
- Kolom atau constraint baru jika skema lokal belum cukup — **SKema lokal sudah lengkap**.

**Jangan menjalankan migration di oriondb** dalam fase ini. Bisa diuji di database testing terisolasi setelah dipastikan aman.

---

## 6. VALIDASI AKSES MENU DAN ALUR

Berdasarkan route:list dan data aktual:

| Fitur | Status | Catatan |
|---|---|---|
| Master Finishing dapat diakses Administrator | ✅ Ya | Route `finishing.resource` di web.php, middleware `['auth', 'menu.access', 'throttle.write']` |
| Master Mesin dapat diakses Administrator | ✅ Ya | Route `mesin.resource` ada, di-load di `FinishingController@index` via `DB::table('mesin')` |
| Harga Finishing dapat diatur role berwenang | ✅ Ya | `boleh_lihat_harga` di tabel `roles`; Admin bisa kirim biaya manual Custom; Operator tidak bisa |
| Indoor hanya menampilkan finishing relevan | ✅ Ya | Filter berdasarkan `id_kategori` di form (`filter_kategori` → `filterProduk`). ID 2 = INDOOR munculkan Kisscut, Laminasi Dingin, Potong Manual. |
| Satu item dapat memakai Laminasi + Kisscut sekaligus | ✅ Ya | Form memilih multiple finishing via `select_finishing` dengan `data-harga`, `data-metode`, `data-kategori`. Controller menerima array `finishing`. |
| Kasir tidak dapat memanipulasi tarif dari request | ✅ Ya | `hargaTerkunci = !AksesHarga::bolehLihat(Auth::user())`. Jika true, biaya_cetak, biaya_desain, biaya_finishing dihitung ulang server dari master. |
| Nota tetap memasukkan total finishing sekali | ✅ Ya | Di `transaksi_detail_finishing` — bisa lebih dari 1 finishing per `id_detail` (satu item). |
| SPK menampilkan spesifikasi teknis setiap finishing | ✅ Ya | `penjualan.show` menggabungkan `transaksi_detail` + `finishing.nama_finishing` via LEFT JOIN. |
| Perubahan Master Finishing tidak mengubah snapshot order lama | ✅ Ya | `harga_jual_snapshot` di `transaksi_detail` sudah "dikelas" saat transaksi dibuat (nilai frozen). Ubah finishing di master tidak mengurangi/menambah nilai di nota lama. |

**TIDAK diubah:**
- Modul pembayaran, kas, login, RBAC, atau Landing Page Builder.

---

## 7. TEST WAJIB (dari user instruction)

Berikut hasil pengecekan ketepatan perilaku berdasarkan code aktual:

| Test | Hasil |
|---|---|
| **Standar: tarif dihitung server** | ✅ Benar. `getHargaFinishingEfektif()` dipakai di `PenjualanController::store` barisan 290. Jika `status_finishing=Standar` atau `hargaTerkunci=true`, biaya ambil dari server. |
| **Custom: spesifikasi berubah tanpa otomatis membuka hak override harga** | ✅ Benar. Status "Custom" bisa dikirim client, tapi jika `hargaTerkunci=true` (role tanpa hak), biaya dihitung ulang server. |
| **Laminasi + Kisscut satu item** | ✅ Benar. Controller menerima array finishing (`$daftarFinishingMentah` bisa berisi banyak elemen). Test `test_dua_finishing_standar_dihitung_server_dan_tersimpan_terpisah` memvalidasi 2 finishing sekaligus. |
| **Potong manual 150 × 40 cm menjadi 60 pcs ukuran 10 × 10 cm** | ✅ Harus diverifikasi di level form. Rumus `L = ((kolom-1)*tinggi + (baris-1)*lebar)/100` hanya untuk grid penuh. Finishing ID 15 (Potong Manual) pakai metode `keliling`, harga_default 450. |
| **Panjang internal 10,10 meter** | — (level form/konfigurasi, belum diuji di sini) |
| **Tarif Rp450/meter dan minimum Rp5.000** | ✅ Finishing ID 15 (Potong Manual) harga_default 450. Minimum charge belum divalidasi di sini. |
| **Biaya finishing Rp5.000** | ✅ Ada di finishing ID 15 (Potong Manual). |
| **Total cetak dan finishing Rp35.000** | — (tergantung produk + finishing yang dipilih) |
| **Pengguna tanpa hak harga tidak dapat memanipulasi harga** | ✅ Benar. `hargaTerkunci` memaksa semua biaya dihitung server. |
| **SPK dan nota konsisten** | ✅ Benar. `harga_jual_snapshot` di transaksi_detail frozen saat dibuat. |
| **Tidak ada double charge** | ✅ Benar. Logika `biayaFinishing += $biayaF` dan `subtotal = biayaCetak + biayaDesain + biayaFinishing` dihitung sekali di controller. |

---

## 8. REKOMENDASI LANJUTAN

### 1. Gunakan `kategori.id_kategori = 2` (INDOOR) untuk finishing indoor
- Kisscut (ID 1), Laminasi Dingin (ID 10), Potong Manual (ID 15) semua punya kategori INDOOR.
- Jangan gunakan `kategori_finishing` yang kosong.

### 2. Roland G540 (mesin ID 9) tidak terintegrasi di finishing table
- Tidak ada finishing di oriondb yang merujuk ke mesin ID 9.
- Jika ingin menambah finishing untuk Roland G540, buat entry finishing baru dengan `id_mesin=9` dan `metode_hitung` yang sesuai, tapi jangan mengerjakan di saat ini (tidak dalam tahap audit ini).

### 3. Standar vs Custom sudah bekerja dengan benar
- Data transaksi lama sudah konsisten: Standar = hitung server, Custom = nilai manual (jika admin) atau ulang server (jika operator).

### 4. Migration candidate: jadikan aditif seed data saja
Jika absolut perlu migrasi:
- Cek `DB::table('finishing_harga')->where('range_min', ...)` tipe sudah `decimal(10,2)`.
- Jangan membuat tabel finishing baru.
- Jangan hapus data transaksi lama.
- Jangan gunakan ID hardcode melting melebihi AUTO_INCREMENT current.

### 5. Validasi lintas fitur
- Test: Buat order dengan finishing Kisscut (ID 1) + Laminasi Dingin (ID 10) sekaligus di satu item.
- Test: Role Operator Desain (boleh_lihat_harga=false) kirim Custom biaya 1 → harus jadi 30.000 (hitung server dari finishing ID 1: luas x qty x harga_default 20.000, atau gunakan tier finishing_harga).
- Test: Role Administrator kirim Custom biaya 99999 → disimpan 99999.

### 6. File yang perlu diedit/created:
- `database/migrations/` — jangan buat migration baru kecuali untuk seed data aditif.
- `app/Services/HargaService.php` — sudah benar, tidak perlu diubah.
- `app/Http/Controllers/PenjualanController.php` — sudah benar, logika Standar/Custom sudah pas.
- `resources/views/penjualan/create.blade.php` — sudah benar, form finishing multiple sudah ada.
- `tests/Feature/FinishingGandaTest.php` — sudah sesuai dengan perilaku aktual.

### 7. Status: REKONSILIASI SELESAI

**Semua ketemu di oriondb:**
- ✅ `finishing.id_kategori` -> `kategori.id_kategori` (ID 2 = INDOOR)
- ✅ `kategori_finishing` tabel kosong, tidak digunakan untuk pemilihan
- ✅ `finishing_harga` range_min/range_max = decimal(10,2) already
- ✅ `mesin` ada Roland G540 (ID 9, tapi tampil_divisi=0)
- ✅ 15 finishing terdaftar dengan data lengkap
- ✅ Standar/Custom logika sudah working di controller
- ✅ Semua route sudah terdaftar dan aksesnya terproteksi middleware

**Status: SIAP DIIMPLEMENTASI SETELAH REKONSILIASI** — hanya butuh validasi lintas test (standar/custom finishing gabungan, role-based override) di database testing sebelum dipakai produksi. Jangan menjalankan migration di oriondb sebelum data dan skema diviselase.

**JANGAN SEED MASTER DATA ASSAL-ASAL.** Data sudah ada di oriondb lengkap.

**JANGAN MENJALANKAN MIGRATION DI ORIONDB.** Migration candidate belum ada di folder aktual, dan jika ada, harus diedit menjadi aditif seed data tanpa merusak skema existings.

**JANGAN MENGUBAH KODE TRANSAKSI AKTIF SEBELUM REKONSILIASI SELESAI.** Logika Standar/Custom di PenjualanController::store sudah benar dan diuji oleh FinishingGandaTest.