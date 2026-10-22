# AUDIT AKHIR — MODUL FINISHING ORION GRAFIKA

## 1. STRUKTUR TABEL EXISTING (READ-ONLY)

Tabel sudah ada dan terisi struktur, tetapi data kosong di database testing (`percetakan_erp_testing`).

| Tabel | Kolom Utama | Catatan |
|---|---|---|
| `finishing` | id_finishing, nama_finishing, harga_default, id_satuan, id_kategori, id_mesin, metode_hitung (luas/keliling/quantity), keterangan, created_at, updated_at | Sudah ada lama, dipakai di master, transaksi, dan BOM. Tipe `decimal(15,2)` untuk harga_default. |
| `finishing_harga` | id_finishing_harga (PK), id_finishing (FK), range_min, range_max, harga, created_at, updated_at | Tabel tier harga per-berat/per-qty. Tipe `integer` untuk range_min/range_max, `decimal(15,2)` untuk harga. Aditif — tidak menghapus harga_default di tabel finishing. |
| `kategori_finishing` | id_kategori (PK), nama_kategori | Tabel kategori finishing. Kosong di testing. Harus diisi: Indoor, Laminasi, Kisscut (dan sebagainya). |
| `produk_finishing` | id (PK), id_produk, id_finishing, created_at, updated_at | Many-to-many antar produk dan finishing (via BOM). |
| `bom_finishing` | id_bom_finishing (PK), id_produk, id_finishing, qty, created_at, updated_at | Struktur BOM: satu produk bisa punya banyak finishing. Menghapasan finishing dicek di `FinishingController::destroy()`. |
| `transaksi_detail_finishing` | id (PK), id_detail, id_finishing, status_finishing, qty_pakai, biaya, catatan, created_at, updated_at | Detail biaya finishing di setiap transaksi (nota/SPK). Status finishing per-baris transaksi. |

Tabel tambahan: `mesin` (id_mesin, nama_mesin, tipe_mesin, created_at, updated_at, tampil_divisi) — sudah ada schema-nya tapi data kosong.

**Konflik migration existing:** Tidak ada. Semua tabel sudah ada dan skema sesuai dengan migrasi sebelumnya (2026_09_10_045900, 2026_09_11_200000, 2026_09_10_050000, 2026_09_12_030100, 2026_09_10_045901). Migrasi kandidat harus ADITIF, bukan pengganti.

---

## 2. DATA AKTUAL YANG DIAUCI

Tabel-kolom target yang harus diisi (kosong saat ini):

| Entitas | Field | Contoh Nilai | Status |
|---|---|---|---|
| `kategori_finishing` | nama_kategori | "Indoor" | BELUM ADA — harus di-seed |
| `kategori_finishing` | nama_kategori | "Laminasi" | BELUM ADA — harus di-seed |
| `kategori_finishing` | nama_kategori | "Kisscut" | BELUM ADA — harus di-seed |
| `mesin` | nama_mesin | "Roland G540" | BELUM ADA — harus di-seed |
| `mesin` | tipe_mesin | "Offset/Flatbed" | BELUM ADA — harus di-seed |
| `mesin` | tampil_divisi | 1 (true) | BELUM ADA — harus di-seed |
| `finishing` | nama_finishing | "Laminasi Indoor" | BELUM ADA — akan ter-create via form |
| `finishing` | nama_finishing | "Kisscut Standar" | BELUM ADA — akan ter-create via form |
| `finishing` | id_mesin | id_mesin Roland G540 | Akan terisi ketika mesin terdaftar |
| `finishing` | id_kategori | id_kategori Indoor/Laminasi/Kisscut | Akan terisi ketika kategori terdaftar |
| `finishing` | metode_hitung | "luas", "keliling", "quantity" | Sudah ada enum di form |
| `finishing_harga` | range_min, range_max, harga | Akan terisi per finishing per rentang qty |

**Catatan:** Data historis (transaksi finishing lama) tidak ada di testing database, jadi tidak ada risiko konflik data saat migrasi.

---

## 3. ROUTE & MENU YANG TERSedia

Semua finishing routes berada di dalam grup `['auth', 'menu.access', 'throttle.write']` — artinya akses dikontrol middleware `menu.access` berdasarkan peran pengguna.

**Routes Finishing yang Terdaftar:**

| Route | Nama Route | Method | Fungsi | Akses |
|---|---|---|---|---|
| `/finishing` | `finishing.index` | GET | Daftar finishing + filter mesin/kategori + harga tiers | Admin/staff dengan menu.access |
| `finishing.resource` | — | GET/POST (tanpa create/edit/show) | CRUD master finishing | Hanya melalui menu |
| `/finishing/{id}/harga` | `finishing.harga.store` | POST | Tambah tier harga per-berat (1 baris 1 aksi) | Admin |
| `/finishing-harga/{id}` | `finishing.harga.update` | PATCH | Perbarui tier harga | Admin |
| `/finishing-harga/{id}` | `finishing.harga.destroy` | DELETE | Hapus tier harga | Admin |
| `/api/penjualan/get-harga-finishing/{id_finishing}/{qty}` | — | GET | API ambil harga finishing per qty | Publik/terbatas |

**Menu Access:**
- Fitur finishing muncul di menu administrator jika `menu.access` middleware mengizinkan.
- Tidak ada route `financing.create`/`.edit`/`.show` di daftar resource (dilakukan melalui modal di halaman index).
- User yang bukan administrator tidak akan melihat menu finishing meskipun `auth` lolos.

**Ketersediaan Master Data untuk Admin:**
- ✅ Master Finishing: bisa diakses via `/finishing` (list) + form modal di index
- ✅ Master Mesin: bisa diakses via routes `mesin.resource` + di-load di halaman finishing index (`$mesin = DB::table('mesin')->get();`)
- ✅ Master Harga: bisa diakses via `/finishing/{id}/harga` (POST tier) dan `/finishing-harga/{id}` (PATCH/DELETE tier)
- ⚠️ Kategori Finishing: harus terdaftar di tabel `kategori_finishing` baru sebelum finishing bisa disortir/filter per kategori
- ⚠️ Mesin Roland G540: harus terdaftar di tabel `mesin` sebelum bisa dipilih di form finishing

---

## 4. PERBANDINGAN STANDAR vs CUSTOM (SPESIFIKASI TEKNIS)

**Definisi dari modul finishing yang ada:**

| Aspek | Finishing STANDAR | Finishing CUSTOM |
|---|---|---|
| **Definisi** | Jenis finishing yang sudah dikonfigurasi operator di Builder (dari section 'finishing' di halaman setting). | Finishing yang dibuat manual di form master finishing (`FinishingController::store/update`). |
| **Sumber Harga** | `harga_default` di tabel `finishing` + tier `finishing_harga` per rentang qty. | Hanya `harga_default` + tier `finishing_harga`. Tidak ada logika khusus untuk "custom". |
| **Metode Perhitungan** | `metode_hitung`: `luas` (m²), `keliling`, atau `quantity` (banyak). | Sama — enum sama untuk keduanya. Tidak ada field "custom method" tambahan. |
| **Identifikasi** | Diidentifikasi melalui `nama_finishing` dan `id_kategori`. | Diidentifikasi melalui `nama_finishing` yang disebutkan sebagai "Custom" di form/order. |
| **Override Harga** | Form kasir di `transaksi_detail_finishing` memungkinkan pengisian `biaya` manual per baris — ini adalah *override* harga, bukan status "custom" secara otomatis. | sama — biaya manual di transaksi_detail adalah override harga, bukan mengubah status finishing jadi "custom". |
| **Form Spesifikasi Teknis** | Tidak ada field form spesifikasi teknis terpisah untuk "standar" vs "custom". Semuanya satu form `finishing.index` + modal. | Tidak ada form terpisah. Perbedaan hanya di *naming* di operator saat membuat entry (misal: "Laminasi Biasa" vs "Laminasi Premium Custom"). |

**Kesimpulan:** Standar dan Custom **tidak memiliki form spesifikasi teknis yang berbeda** di level database maupun form UI. Keduanya menggunakan tabel `finishing` sama persis. Perbedaan hanya pada:
- Nama `nama_finishing` yang dipilih operator
- Pilihan `id_kategori` dan `id_mesin`
- Apakah ada tier `finishing_harga` yang diatur
- Apakah biaya di transaksi di-*override* manual

**Bedakan status spesifikasi dengan izin override harga:**
- Jika `biaya` di `transaksi_detail_finishing` diisi manual → ini adalah **override harga per-transaksi**, status finishing tetap biasa.
- Jika tidak ada biaya manual → gunakan `harga_default` + tier `finishing_harga` yang sudah ditetapkan.
- Tidak ada flag/field `is_custom` di tabel finishing atau transaksi.

---

## 5. HASIL PENAMLANAN MIGRASI KANDIDAT

Migrasi kandidat: `2026_11_02_100000_add_indoor_finishing_configuration_and_snapshots.php`

**STATUS: BELUM ADA / PERLU DIBUAT ADITIF**

Alasan:
1. File migration tidak ada di folder `database/migrations/`. Nama tersebut disebutkan dalam branch yang belum di-merge ke repo utama.
2. Skema lokal sudah tersedia (tabel sudah ada). Migrasi baru harus **aditif** (menambah column/tabel baru jika perlu) tanpa merusak tabel yang sudah ada.
3. Data yang belum ada (kategori Indoor/Laminasi/Kisscut, mesin Roland G540) harus di-seed, bukan dibuat oleh migration schema saja.

**Apa yang harus dilakukan migrasi aditif ini:**

Migration ini seharusnya menambah:
- Data awal (seed) ke tabel `kategori_finishing`: Indoor, Laminasi, Kisscut
- Data awal (seed) ke tabel `mesin`: Roland G540 beserta tipe dan tampil_divisi
- Kolom atau tabel tambahan untuk "snapshot konfigurasi" jika perlu (lihat poin 6)
- **TIDAK PERLU** membuat ulang tabel `finishing`, `finishing_harga`, `produk_finishing`, `bom_finishing`, `transaksi_detail_finishing` — sudah ada.

**Risiko jika dijalankan sebelum data di-seed:** Migrasi akan "sukses" (create table yang sudah ada = error), jadi harus dicek `Schema::hasTable()` terlebih dahulu di atas migrasi, atau migrasi hanya memasukkan data seed (tidak membuat tabel).

**Revisi yang diperlukan:**
Migration candidate harus diedit menjadi format **data seed blm tabel baru**, misal:
- `Schema::table()` atau `DB::table()` insert data ke `kategori_finishing` dan `mesin`
- Atau dibuat sebagai seeder terpisah yang dipanggil dari migration `up()`.

---

## 6. KESIAPAN IMPLEMENTASI

| Kriteria | Status | Catatan |
|---|---|---|
| Semua tabel skema sudah ada | ✅ LENGKAP | Tidak ada tabel yang missing |
| Data kategori (Indoor, Laminasi, Kisscut) | ❌ BELUM ADA | Harus di-seed terlebih dahulu |
| Data mesin (Roland G540) | ❌ BELUM ADA | Harus di-seed terlebih dahulu |
| Data finishing (sample entries) | ❌ BELUM ADA | Akan ter-create via form UI Admin |
| Data finishing_harga (sample tiers) | ❌ BELUM ADA | Akan ter-create via form UI Admin |
| Routes sudah terdaftar | ✅ LENGKAP | 6 route finishing terdaftar di web.php |
| Menu access terintegrasi | ✅ TERINTEGRASI | Melalui middleware `menu.access` |
| Tidak ada data historis yang tersentuh | ✅ AMAN | Tabel kosong di testing, migrasi aditif aman |
| Bisa dijalankan serial (bukan parallel) | ✅ AMAN | Database testing isolasi OK |

**Penilaian Akhir: MEMBUTUHKAN REKONSILIASI**

Migrasi kandidat **belum siap dijalankan** karena data referensi (kategori + mesin) masih kosong. Setelah data di-seed, migrasi bisa dibuat sebagai file data seed sederhana atau diperbaiki menambahkan baris `DB::table('kategori_finishing')->insert()` dan `DB::table('mesin')->insert()` di dalam `up()`.

**File yang perlu diubah/siapkan:**
1. `database/migrations/2026_11_02_100000_add_indoor_finishing_configuration_and_snapshots.php` — diedit menjadi aditif seed data ke `kategori_finishing` dan `mesin` (bukan create table baru).
2. Atau seeder terpisah yang dijalankan setelah migration migrations terakhir.
3. Datenya: populate `kategori_finishing` (3 row), `mesin` (1 row Roland G540).

**TIDAK diperbolehkan dalam tahap ini:**
- Menjalankan migration sebelum skema dan backup tervalidasi.
- Membuat ulang modul finishing dari nol.
- Mengubah Landing Page Builder, Login, Kasir pembayaran, transaksi historis, atau SPK existing.

---

## 7. REKOMENDASI LANJUTAN

1. **Jalankan seeder data terlebih dahulu** untuk mengisi:
   - `kategori_finishing`: Indoor, Laminasi, Kisscut
   - `mesin`: Roland G540 (tipe: Offset/Flatbed, tampil_divisi: 1)
   
2. **Setelah data ada, uji fitur**:
   - Admin login → menu finishing tampil
   - Create finishing entry dengan memilih kategori & mesin
   - Tambah tier harga finishing per rentang qty
   - Hubungkan finishing ke produk via BOM

3. **Jika perlu migrasi candidate**, ubah menjadi:
   ```php
   public function up()
   {
       // Seed kategori finishing
       if (!Schema::hasTable('kategori_finishing') || DB::table('kategori_finishing')->count() === 0) {
           DB::table('kategori_finishing')->insert([
               ['nama_kategori' => 'Indoor'],
               ['nama_kategori' => 'Laminasi'],
               ['nama_kategori' => 'Kisscut'],
           ]);
       }
       
       // Seed mesin
       if (!Schema::hasTable('mesin') || DB::table('mesin')->count() === 0) {
           DB::table('mesin')->insert([
               ['nama_mesin' => 'Roland G540', 'tipe_mesin' => 'Offset/Flatbed', 'tampil_divisi' => 1],
           ]);
       }
   }
   ```
   Lalu hapus migration tersebut jika sudah ada di atas, atau beri tanda `// already applied`.

4. **Validasi manual**: Pastikan Admin bisa melihat menu finishing, menambah finishing, menetapkan kategori/mesin, dan menambah tier harga tanpa error.

---

**Status Akhir: MEMBUTUHKAN REKONSILIASI — migration candidate perlu diedit menjadi aditif seed data, lalu dicek ulang setelah data awal di-seed.**