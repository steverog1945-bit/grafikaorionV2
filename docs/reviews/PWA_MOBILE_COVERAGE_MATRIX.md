# ORION GRAFIKA — PWA Mobile Coverage Matrix

**Checkpoint:** 26 Oktober 2026. **Branch:** `feat/pwa-mobile-completion-phase2`. **Status:** IMPLEMENTASI BERJALAN.

## Metode dan batasan

- Inventaris deterministik dari seluruh file `resources/views/**/*.blade.php`.
- `SOURCE MOBILE` = memiliki CSS+class Blade spesifik (Master Bahan, POS, SPK, Produksi) — belum berarti screenshot/fungsi/role/desktop lulus.
- `GENERIC ONLY` = hanya fondasi responsif umum (`mobile-erp.css` + `mobile-all-modules.css`) — belum ada penyesuaian spesifik.
- `PARTIAL / NOT ROUTE` = komponen/partial/layout.
- `PRINT VIEW` = tampilan print/ekspor.
- `PASS` hanya boleh diberikan sesudah tes yang benar-benar dijalankan.

## Modul yang sudah memiliki CSS mobile spesifik

| Modul | CSS File | Blade Classes | Status |
|-------|----------|---------------|--------|
| Dashboard | `mobile-dashboard.css` | `.mobile-aksi-*`, `.mobile-kas-chip` | SOURCE MOBILE (UNTESTED) |
| Master Bahan | `mobile-bahan.css` | `.mobile-bahan-*` | SOURCE MOBILE (UNTESTED) |
| POS (Kasir) | `mobile-pos.css` | `.pos-*` | SOURCE MOBILE (UNTESTED) |
| SPK | `mobile-spk.css` | `.spk-*` | SOURCE MOBILE (UNTESTED) |
| Produksi | `mobile-produksi.css` | `.produksi-*` | SOURCE MOBILE (UNTESTED) |

## Modul yang hanya memiliki fondasi generik (mobile-all-modules.css)

| Modul | View Files | Status |
|-------|-----------|--------|
| Collecting | `collecting/index.blade.php` | GENERIC ONLY |
| Jersey | `jersey/*` (index, create, edit, keuangan, pemain, ukuran) | GENERIC ONLY |
| Kasir (tambahan) | `kasir/*` (form, pembayaran, monitor, waiting, riwayat, dll.) | GENERIC ONLY |
| Stok & Logistik | `kartu_stok/*`, `stock-opname/*`, `waste/*` | GENERIC ONLY |
| Pembelian | `pembelian-bahan/*`, `kedatangan-bahan/*`, `purchase-order/*`, `purchase-request/*` | GENERIC ONLY |
| Master (lainnya) | `master/*` (mesin, kategori, jenis, produk, jasa, satuan, merk, karyawan, konsumen, golongan, bank, harga, user, role, dll.) | GENERIC ONLY |
| Keuangan | `akuntansi/*`, `kas-kecil/*`, `konsumen-deposit/*`, `analisis/*` | GENERIC ONLY |
| Laporan | `laporan/*` | GENERIC ONLY |
| Dashboard (role) | `dashboard-*.blade.php` | GENERIC ONLY |
| Pengaturan | `pengaturan/*`, `akses/*` | GENERIC ONLY |
| Seluruh layar tersisa | Semua view lainnya | GENERIC ONLY |

## CSS Files

| File | Scope | Import di mobile-ui.css |
|------|-------|------------------------|
| `mobile-dashboard.css` | Dashboard | ✅ |
| `mobile-bahan.css` | Master Bahan | ✅ |
| `mobile-modal.css` | Modal & Form | ✅ |
| `mobile-nav.css` | Navigasi bawah | ✅ |
| `mobile-erp.css` | Fondasi lintas modul | ✅ |
| `mobile-pos.css` | POS (Kasir) | ✅ |
| `mobile-spk.css` | SPK | ✅ |
| `mobile-produksi.css` | Produksi | ✅ |
| `mobile-all-modules.css` | Fondasi generik semua modul | ✅ |

## Status Ringkasan

| Kategori | Jumlah | Keterangan |
|----------|--------|------------|
| SOURCE MOBILE (spesifik) | 5 modul | Dashboard, Master Bahan, POS, SPK, Produksi |
| GENERIC ONLY (fondasi) | ~180+ files | Semua modul lainnya |
| PARTIAL / NOT ROUTE | 63 files | Komponen/partial/layout |
| PRINT VIEW | 9 files | Tampilan print/ekspor |

## Catatan

- `mobile-all-modules.css` menyediakan fondasi responsif generik (input 16px, touch target 44px, overflow-x auto, pagination 44px, modal z-index, dll.) untuk semua modul yang belum punya CSS khusus.
- Setiap modul masih memerlukan implementasi individual (CSS cards + Blade classes) untuk hasil optimal.
- Semua bukti visual adalah STATIC SOURCE-DERIVED (Laravel runtime tidak tersedia).
- PASS hanya untuk pemeriksaan yang benar-benar dijalankan.
- NOT TESTED untuk semua pemeriksaan runtime (Laravel, PHPUnit, browser, PWA, device).
