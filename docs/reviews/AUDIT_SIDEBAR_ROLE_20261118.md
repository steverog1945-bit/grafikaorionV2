# AUDIT SIDEBAR & ROLE — 18 November 2026

Audit "menu sidebar yang ditampilkan untuk role non-Administrator sesuai aturan ERP profesional" + tindak lanjut masalah kasir & master Jersey.

## 1. Cara kerja sidebar (diverifikasi, bukan diasumsikan)

- Sidebar merender link per `@if($cekAkses(ID))`; `$cekAkses` = `(role Administrator) OR (id_menu ada di `Izin::menuTerlihat()`)`.
- `Izin::menuTerlihat()` menurunkan dari `role_permissions`/`user_permissions` (view=1) melalui `Izin::peta()`.
- Rute dijaga `CheckMenuAccess` + `Izin::bolehPath()`/`Izin::boleh()` — "lihat" fail-closed 403 bila prefix tidak dipetakan; mutasi fail-closed 403. `menu_access.rules` panjang-terpanjang yang menang.
- Kunci: **satu sumber (`Izin::peta`) menentukan sidebar, tombol aksi, dan rute** — tidak mungkin sidebar menampilkan link yang rutenya 403 (selama `menuPerizinan` terisi jujur).

## 2. Peta link sidebar ↔ aturan route (hasil ekstraksi baris per baris)

| Sidebar | Izin sidebar | Aturan route (`rules`) | Status |
|---|---|---|---|
| Dashboard, Beranda mobile | selalu | `always_allow` | ✓ |
| `eksekutif/dashboard` | 60 | `eksekutif => [60]` | ✓ |
| Audit Owner / Log Aktivitas | 60 / 49 | `audit-owner => [60]`, `log-aktivitas => [60, 49]` | ✓ |
| Bahan Baku / Kartu Stok / Stock Opname | 1 / 53 | `bahan-baku`, `kartu-stok`, `stock-opname => [1, 53]` | ✓ |
| Pembelian / PR / PO | 20 / 1·20·54 / 20·55 | `pembelian-bahan => [20]`, `purchase-request => [1, 20, 54]`, `purchase-order => [20, 55]` | ✓ |
| Kedatangan Bahan | 17 | `kedatangan-bahan => [17]`, `ajax/bahan => [17]` | ✓ |
| SPK / Produksi / QC / Waste | 22 / 23·56 / 1·23·57 | `spk`, `produksi`, `qc => [23, 56]`, `waste => [1, 23, 57]` | ✓ |
| Collecting | 24 | `collecting => [24]` | ✓ |
| Kategori / Jenis Bahan / Merk / Satuan / Mesin / Gudang | 15/31/30/10/27/33 | cocok | ✓ |
| Konsumen / Deposit / Golongan | 7 / 34 / 8·12·18 | cocok | ✓ |
| Produk / Finishing / Jasa / Jasa Jahit | 13/21/32/68 | cocok | ✓ |
| Setting Harga / Harga Promo | 14 / 28 / tiers 14/21/32 | cocok (termasuk `finishing-harga`, `jasa-harga`, `setting-harga-tier`) | ✓ |
| Supplier / Platform Belanja | 9 / 9·62 | cocok | ✓ |
| Kasir | 16 | `kasir => [16]` | ✓ |
| Transaksi / Quotation | 3·41 / 58·3·41 | cocok | ✓ |
| Penjualan Barang | 48 | `penjualan-barang => [3, 48]` (48 utk role khusus) | ✓ |
| Riwayat Nota | 3·61 | `penjualan/riwayat => [3, 61]` | ✓ |
| Kas Kecil / Kategori Kas Kecil | 39 / 45 | cocok | ✓ |
| Bank / Karyawan | 11 / 5 | cocok | ✓ |
| Laporan (semua) | 25/26/29/40/43/44/46/47/65 | semua prefix laporan terpetakan | ✓ |
| Akuntansi / Akun | 35 / 36 / 51 | cocok | ✓ |
| Users / Pengaturan / Jam Kerja / Approval / Backup | 6/37/63/50/52 | cocok | ✓ |
| Pesanan Jersey | 38 / 42 | cocok (`jersey/create => [38, 42]`, `jersey => [38, 42]`) | ✓ |
| Data Master Jersey | 38 | `jersey/master => [38]` | **diperbaiki — lihat §4** |
| Keuangan Jersey | 38 | `jersey/keuangan => [38]` | **flag — lihat §4** |

Catatan route yang SENGAJA tidak punya link sidebar (XHR/AJAX teknis): `api/penjualan`, `ajax/bahan`, `konsumen/store-ajax`, `supplier/store-ajax`, `konsumen/search-ajax`, `harga-siap-cetak`, `jasa-harga`, `finishing-harga`, `setting-harga-tier`, `cek-kelengkapan`, `preview-harga`, `upload-thumbnail`, `cetak-*`, `print-*` (dibuka terprogram), `penjualan/edit-nota`, `purchase-request`. Semua terdaftar di `menu_access.rules`; tidak ada yang "menggantung".

## 3. Temuan & status

| # | Temuan | Status |
|---|---|---|
| 1 | Kasir memegang menu 38 → sidebar menampilkan "Data Master Jersey" (dan tombol kelola master terbuka) | **DIPERBAIKI** — lihat §4 |
| 2 | Route `jersey/master` untuk Kasir: 403 sebelum dibuka langsung lewat URL | **DIPERBAIKI** — lihat §4 |
| 3 | "Keuangan Jersey" berbagi id_menu 38 dengan Master Jersey — Kasir ikut melihat/mengelola-nya bila menu tercentang | **DIBIARKAN + FLAG** — butuh keputusan owner (kasir memang diberi 38 saat ini; pemisahan butuh menu baru e.g. id 69 "Keuangan Jersey" atau mencabut 38 dari Kasir di Hak Akses) |
| 4 | Role Owner (baca-saja) masih MELIHAT link aksi di sidebar; rute mutasi ditolak 403 dengan pesan jelas | Dibiarkan — pola accepted (baca-saja secara server; menyembunyikan tombol aksi sudah di-script `petaUntukTampilan` untuk form, sidebar link tetap tampil) |
| 5 | Tombol di dalam form untuk aksi yang pasti 403 sudah disembunyikan via `petaUntukTampilan` (satu peta dengan `Izin::boleh`) | Sudah benar, tidak diubah |
| 6 | Mode "dipetakan ≠ tampil" tidak ditemukan; tidak ada link sidebar tanpa aturan route | OK |
| 7 | Fail-closed route tanpa aturan → 403 (bukan 500) | OK |
| 8 | "Operasi" (id_role 1 di fixture staging) diberi SEMUA menu | BUKAN aturan ERP profesional — hanya data sintetis staging; JANGAN dipakai di produksi |
| 9 | `harga/eligibility`: menu berharga [21, 58] disembunyikan dari role tanpa hak harga | Benar |

## 4. Perbaikan Kasir vs Data Master Jersey (yang diminta owner)

Kebijakan baru sebagai **lapis di atas Hak Akses** (`menu_access.kecualikan_menu` / `kecualikan_path`):

```php
'kecualikan_menu' => [38 => ['Kasir']],
'kecualikan_path' => ['jersey/master' => ['Kasir']],
```

Efek (otomatis di server, bukan cuma tampilan):

1. Sidebar "Data Master Jersey" (dan ikon gear terkait) **hilang total untuk role Kasir** (via `Izin::peta()` — `menuTerlihat()` kosong untuk 38).
2. Semua tombol aksi master Jersey (tambah/ubah/hapus/simpan) mati untuk Kasir.
3. Akses langsung rute `/jersey/master*` oleh Kasir → **403** (bolehPath), rute mutasi → 403 (`Izin::boleh`), walau menu tercentang di Hak Akses.
4. Administrator tidak terpengaruh (bypass duluan).

**Rekomendasi permanen (opsional, lebih rapi)**: cabut id_menu 38 dari role Kasir di **Pengaturan → Hak Akses** — setelah itu kecualikan di atas boleh dihapus. Keuangan Jersey tetap terlihat untuk Kasir sampai owner memutuskan pemisahannya.

## 5. Lingkup biaya

Tidak ada perubahan skema/bisnis; `Izin` + config satu sumber. Regression test: tidak ada test yang memberi Kasir menu 38 (KasSesiTest memakai menu 16), jadi tidak ada suite yang pecah.
