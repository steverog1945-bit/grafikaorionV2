# ORION GRAFIKA — PWA Mobile Screen Inventory & Coverage

**Checkpoint:** 26 Oktober 2026. **Branch:** `integration/pwa-mobile-all-modules`. **Status:** IMPLEMENTASI BERJALAN, bukan bukti PASS atau siap produksi.

## Metode dan batasan

- Inventaris dihasilkan deterministik dari seluruh file `resources/views/**/*.blade.php` pada branch integrasi. Semua file dicatat; jumlah nama view **tidak sama dengan jumlah route aktif**.
- `SOURCE MOBILE (UNTESTED)` berarti memiliki CSS+class Blade spesifik dari pekerjaan lama atau commit baru; **belum** berarti screenshot, fungsi, role, atau desktop lulus.
- `GENERIC ONLY / UI TODO` berarti hanya fondasi responsif umum atau belum ada penyesuaian spesifik yang diverifikasi.
- `PARTIAL / NOT ROUTE` berarti komponen/partial/layout, perlu diverifikasi melalui layar yang memakai komponen tersebut.
- `PRINT VIEW / PRINT TEST DUE` berarti tampilan print/ekspor; belum diuji preview cetak. Perlu koreksi klasifikasi manual jika ada file non-print.
- **Route, menu, role dan hak akses perlu dipetakan terhadap `routes/web.php`, konfigurasi route, dan RBAC runtime** sebelum cakupan diklaim 100%. Jangan menyimpulkan sebuah view publik/aktif hanya dari path.
- `PASS` hanya boleh diberikan sesudah tes yang benar-benar dijalankan dengan bukti dan SHA. Browser Laravel, device iOS/Android, PHPUnit, RBAC/IDOR, PWA cache, dan desktop parity **NOT TESTED** pada checkpoint ini.

## Ringkasan status dari source

| Kategori | Jumlah file |
|---|---:|
| GENERIC ONLY / UI TODO | 112 |
| PARTIAL / NOT ROUTE | 63 |
| SOURCE MOBILE (UNTESTED) | 10 |
| PRINT VIEW / PRINT TEST DUE | 9 |
| **Total file view** | **194** |

## Matriks seluruh file view

| File Blade | Grup | Status source | Route/Role & test |
|---|---|---|---|
| `resources/views/accounting-period/index.blade.php` | accounting-period | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/akses/_matriks.blade.php` | akses | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/akses/index.blade.php` | akses | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/akses/user.blade.php` | akses | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/akuntansi/_subnav.blade.php` | akuntansi | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/akuntansi/buku-besar.blade.php` | akuntansi | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/akuntansi/jurnal.blade.php` | akuntansi | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/akuntansi/laba-rugi.blade.php` | akuntansi | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/akuntansi/neraca-saldo.blade.php` | akuntansi | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/akuntansi/neraca.blade.php` | akuntansi | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/akuntansi/perbandingan.blade.php` | akuntansi | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/analisis/biaya-tinta.blade.php` | analisis | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/analisis/dashboard.blade.php` | analisis | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/analisis/pemakaian-bahan.blade.php` | analisis | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/analisis/profitabilitas.blade.php` | analisis | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/approval/index.blade.php` | approval | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/audit-log/index.blade.php` | audit-log | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/audit-owner/index.blade.php` | audit-owner | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/backup/index.blade.php` | backup | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/berkas-order/show.blade.php` | berkas-order | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/collecting/index.blade.php` | collecting | SOURCE MOBILE (UNTESTED) | NOT VERIFIED |
| `resources/views/components/badge.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/button.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/card.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/empty-state.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/form/group.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/form/input.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/form/select.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/laporan/print-table.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/laporan/resume-table.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/modal.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/page-header.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/polling-notifikasi.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/table.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/components/table/pagination.blade.php` | components | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/dashboard-desain.blade.php` | dashboard-desain.blade.php | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/dashboard-gudang.blade.php` | dashboard-gudang.blade.php | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/dashboard-produksi.blade.php` | dashboard-produksi.blade.php | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/dashboard.blade.php` | dashboard.blade.php | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/finishing/index.blade.php` | finishing | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/jasa-jahit/index.blade.php` | jasa-jahit | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/jersey/keuangan/index.blade.php` | jersey | SOURCE MOBILE (UNTESTED) | NOT VERIFIED |
| `resources/views/jersey/master/_filter-toolbar.blade.php` | jersey | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/jersey/master/_row-harga.blade.php` | jersey | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/jersey/master/_row-simple.blade.php` | jersey | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/jersey/master/_row-size.blade.php` | jersey | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/jersey/master/index.blade.php` | jersey | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/jersey/orders/_csv-pemain-card-buttons.blade.php` | jersey | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/jersey/orders/_csv-pemain-hasil-panel.blade.php` | jersey | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/jersey/orders/_csv-pemain-tools.blade.php` | jersey | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/jersey/orders/_line-card-fields.blade.php` | jersey | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/jersey/orders/_player-row-fields.blade.php` | jersey | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/jersey/orders/_size-helper.blade.php` | jersey | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/jersey/orders/create.blade.php` | jersey | SOURCE MOBILE (UNTESTED) | NOT VERIFIED |
| `resources/views/jersey/orders/edit-lines.blade.php` | jersey | SOURCE MOBILE (UNTESTED) | NOT VERIFIED |
| `resources/views/jersey/orders/index.blade.php` | jersey | SOURCE MOBILE (UNTESTED) | NOT VERIFIED |
| `resources/views/jersey/orders/print.blade.php` | jersey | PRINT VIEW / PRINT TEST DUE | NOT VERIFIED |
| `resources/views/jersey/orders/show.blade.php` | jersey | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/jersey/orders/spk-print.blade.php` | jersey | PRINT VIEW / PRINT TEST DUE | NOT VERIFIED |
| `resources/views/kartu_stok/detail.blade.php` | kartu_stok | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/kartu_stok/index.blade.php` | kartu_stok | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/kas-kecil/index.blade.php` | kas-kecil | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/kasir/form_pembayaran.blade.php` | kasir | SOURCE MOBILE (UNTESTED) | NOT VERIFIED |
| `resources/views/kasir/kelola_kas.blade.php` | kasir | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/kasir/monitoring_transaksi.blade.php` | kasir | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/kasir/open_order.blade.php` | kasir | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/kasir/preview_closing.blade.php` | kasir | PRINT VIEW / PRINT TEST DUE | NOT VERIFIED |
| `resources/views/kasir/print_closing.blade.php` | kasir | PRINT VIEW / PRINT TEST DUE | NOT VERIFIED |
| `resources/views/kasir/print_nota.blade.php` | kasir | PRINT VIEW / PRINT TEST DUE | NOT VERIFIED |
| `resources/views/kasir/waiting_list.blade.php` | kasir | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/kedatangan-bahan/create.blade.php` | kedatangan-bahan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/kedatangan-bahan/form.blade.php` | kedatangan-bahan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/kedatangan-bahan/index.blade.php` | kedatangan-bahan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/konsumen-deposit/index.blade.php` | konsumen-deposit | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/konsumen/timeline.blade.php` | konsumen | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/landing-page-public.blade.php` | landing-page-public.blade.php | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/belanja-bahan.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/biaya-desain.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/evaluasi-master.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/hpp.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/hutang.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/job-costing.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/kas.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/order-jersey.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/order.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/piutang.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/produksi.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/laporan/stok-bahan.blade.php` | laporan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/layouts/app.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/badge.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/button.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/card.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/detail.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/empty-state.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/group.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/index.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/input.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/modal.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/page-header.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/pagination.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/select.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/layouts/table.blade.php` | layouts | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/login.blade.php` | login.blade.php | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/akun/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/bahan/index.blade.php` | master | SOURCE MOBILE (UNTESTED) | NOT VERIFIED |
| `resources/views/master/bank/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/golongan/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/gudang/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/harga-promo/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/harga_siap_cetak/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/jasa/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/jenis-bahan/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/karyawan/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/kategori-kas-kecil/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/kategori/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/konsumen/_field.blade.php` | master | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/master/konsumen/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/merk/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/mesin/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/pendukung/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/platform-belanja/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/produk/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/satuan/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/setting_harga/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/supplier/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/master/user/index.blade.php` | master | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/masuk.blade.php` | masuk.blade.php | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/partials/command-palette.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/dashboard-info-terbaru.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/dashboard-tab.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/divisi-tabs.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/image-compress-script.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/modal-pelunasan-hutang.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/print-header.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/print-report-header.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/print-report-table.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/print-style.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/quick-picker.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/rupiah-format-script.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/spk-print-style.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/supplier-baru-modal.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/partials/tooltip-script.blade.php` | partials | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/pembelian-bahan/create.blade.php` | pembelian-bahan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pembelian-bahan/edit.blade.php` | pembelian-bahan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pembelian-bahan/index.blade.php` | pembelian-bahan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pembelian-bahan/print.blade.php` | pembelian-bahan | PRINT VIEW / PRINT TEST DUE | NOT VERIFIED |
| `resources/views/pembelian-bahan/show.blade.php` | pembelian-bahan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pengaturan/aplikasi.blade.php` | pengaturan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pengaturan/desainer.blade.php` | pengaturan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pengaturan/jam-kerja.blade.php` | pengaturan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pengaturan/landing-page-builder.blade.php` | pengaturan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pengaturan/landing-page-preview-iframe.blade.php` | pengaturan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pengaturan/landing-page-preview.blade.php` | pengaturan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pengaturan/menu-editor.blade.php` | pengaturan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/pengaturan/partials/footer-render.blade.php` | pengaturan | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/pengaturan/partials/renderer-styles.blade.php` | pengaturan | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/pengaturan/partials/scroll-reveal.blade.php` | pengaturan | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/pengaturan/partials/section-render.blade.php` | pengaturan | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/pengaturan/partials/smooth-scroll.blade.php` | pengaturan | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/pengaturan/tema.blade.php` | pengaturan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/penjualan-barang/create.blade.php` | penjualan-barang | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/penjualan/create.blade.php` | penjualan | SOURCE MOBILE (UNTESTED) | NOT VERIFIED |
| `resources/views/penjualan/edit-approval.blade.php` | penjualan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/penjualan/page-header.blade.php` | penjualan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/penjualan/riwayat.blade.php` | penjualan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/penjualan/show.blade.php` | penjualan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/postingan/_daftar.blade.php` | postingan | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/postingan/form.blade.php` | postingan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/postingan/index.blade.php` | postingan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/postingan/show.blade.php` | postingan | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/produksi/index.blade.php` | produksi | SOURCE MOBILE (UNTESTED) | NOT VERIFIED |
| `resources/views/publik/_kartu.blade.php` | publik | PARTIAL / NOT ROUTE | NOT VERIFIED |
| `resources/views/publik/baca.blade.php` | publik | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/publik/katalog-produk.blade.php` | publik | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/publik/layout-artikel.blade.php` | publik | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/publik/layout-masuk.blade.php` | publik | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/publik/layout.blade.php` | publik | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/purchase-order/form.blade.php` | purchase-order | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/purchase-order/index.blade.php` | purchase-order | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/purchase-request/form.blade.php` | purchase-request | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/purchase-request/index.blade.php` | purchase-request | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/qc/create.blade.php` | qc | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/qc/index.blade.php` | qc | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/quotation/form.blade.php` | quotation | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/quotation/index.blade.php` | quotation | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/quotation/print.blade.php` | quotation | PRINT VIEW / PRINT TEST DUE | NOT VERIFIED |
| `resources/views/spk/index.blade.php` | spk | SOURCE MOBILE (UNTESTED) | NOT VERIFIED |
| `resources/views/spk/print-lengkap.blade.php` | spk | PRINT VIEW / PRINT TEST DUE | NOT VERIFIED |
| `resources/views/spk/print.blade.php` | spk | PRINT VIEW / PRINT TEST DUE | NOT VERIFIED |
| `resources/views/stock-opname/create.blade.php` | stock-opname | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/stock-opname/edit.blade.php` | stock-opname | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/stock-opname/index.blade.php` | stock-opname | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/waste/create.blade.php` | waste | GENERIC ONLY / UI TODO | NOT VERIFIED |
| `resources/views/waste/index.blade.php` | waste | GENERIC ONLY / UI TODO | NOT VERIFIED |

## Urutan eksekusi OpenCode — tanpa prompt lanjutan

1. Dari daftar `GENERIC ONLY`, kelompokkan berdasarkan menu/route aktif. Implementasikan setiap halaman create/edit/detail/index dan state error/empty dengan CSS scoped, tanpa mengubah handler/controller/RBAC.
2. Lanjut urutan kasir tambahan → stok/logistik/pembelian → semua master → keuangan/laporan → dashboard/setting → sisi menu yang masih tersisa. Collecting serta Jersey list/create/edit/keuangan sudah memiliki implementasi source, namun butuh validasi visual dan form.
3. Map route+menu+role untuk setiap view layar dari definisi yang nyata; catat `NOT APPLICABLE` hanya setelah alasan sah (partial/print). Update setiap baris dari `GENERIC ONLY` ke `SOURCE MOBILE` setelah file kode benar-benar berubah. Jangan otomatis menyatakan PASS.
4. Setelah semua layar tercakup, jalankan build/lint, browser render dari Blade asli pada staging aman, screenshot viewport dan regresi desktop, alur transaksi dan IDOR pada DB sintetis. Catat kegagalan dan tes tidak tersedia secara eksplisit.
5. Buat release/rollback checklist operator, lalu tunggu keputusan owner GO/NO-GO sebelum merge `main`/deploy ke ERP produksi.

**Larangan:** jangan membaca `.env`, menyentuh `oriondb`, direktori ERP produksi, kredensial, Apache/SSL, backup, scheduler, atau membuat transaksi uji pada data asli.
