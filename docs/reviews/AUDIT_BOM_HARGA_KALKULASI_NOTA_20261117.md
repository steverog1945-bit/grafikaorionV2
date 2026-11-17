# Audit BOM, Harga & Kalkulasi Nota — Temuan dan Perbaikan

Tanggal: 17 November 2026
Branch: `feat/min-luas-hybrid-t4a` (target push: `sync/xampp-hybrid-t4a-20261116`)
Lingkup: audit end-to-end BOM (bahan baku/stok/konversi), harga & margin (tier/setting_harga/minimum luas), dan kalkulasi total nota (POS/kasir/koreksi/jurnal).

---

## 1. Metode

Tiga agent eksplorasi paralel memetakan:

1. **BOM & stok** — `BomCalculationService`, `StokReversalService`, konversi satuan, kartu stok, pengurangan/pengembalian bahan di semua alur (POS, edit-nota, batal, produksi).
2. **Harga & margin** — `setting_harga`, harga tier, `metode_hitung` (quantity/ukuran/lajur), minimum luas hybrid, HPP snapshot, margin laporan.
3. **Kalkulasi total** — store → prosesBayar → koreksi → cetak → jurnal, termasuk diskon, uang muka, susulan, dan pembatalan.

Setiap temuan diklasifikasi: (a) bug nyata berdampak uang/stok → **diperbaiki**; (b) desain sadar yang terdokumentasi di kode/komentar → **dilewati**.

---

## 2. Perbaikan yang dilakukan (8 kode + 1 hardening turunan)

### 2.1 Koreksi nota membuang diskon (`PenjualanController::updateItemNota`)
`total_bayar` baru dihitung dari subtotal mentah tanpa mengurangi `transaksi.diskon`. Nota yang didiskon kasir (total sah 70.000 dari 100.000) kembali ke harga penuh saat dikoreksi — tanpa peringatan. Sekarang diskon tetap dikurangkan dari: jurnal (`AccountingService::sesuaikanTotalNota` menerima total net), status pembayaran, `total_bayar`, guard "total < terbayar", dan response `total_baru`.

### 2.2 Apply koreksi nota penjualan-langsung selalu ditolak (`PenjualanController::applyApprovedEdit`)
Guard `NotaEditService::bolehEdit` versi edit-selisih menolak item berstatus `Selesai/Cetak` — padahal item Penjualan & Jasa memang `Selesai` sejak awal tanpa lewat produksi, sehingga alur apply (pembatalan penuh yang sudah disetujui Administrator) SELALU gagal dengan "Ada item yang sudah masuk produksi" tepat setelah approval. Sekarang guard item sendiri (ada yang Selesai diproduksi → tolak; sudah diambil → tolak) berjalan dulu, lalu `bolehEdit($transaksi, null, false, $adaProduksi)` dipanggil dengan pengecualian aturan Selesai/Cetak milik alur edit-selisih; aturan kunci Lunas/pemilik/hari tetap berlaku.

### 2.3 Status Lunas dengan selisih pembulatan (`KasirController::prosesBayar`)
Perbandingan `==` mentah membuat nota dengan selisih miliaran desimal (mis. 0.004 dari pembagian m²) berstatus Belum Bayar padahal terbayar penuh. Kini toleransi +0.004 selaras `StatusPembayaran`.

### 2.4 Cetak opsi memasukkan baris Batal & harga tier salah basis (`KasirController::printOpsi`)
Baris berstatus `Batal` ikut tercetak di nota; dan `harga_tier` memakai qty pcs untuk produk metode `ukuran` (harusnya qty_hitung m²). Keduanya diperbaiki.

### 2.5 Laporan breakdown memasukkan baris Batal (`LaporanController::order`)
Divisi & produk breakdown menghitung baris `Batal` sebagai omzet. Kini dikecualikan.

### 2.6 BOM silent fallback (`BomCalculationService::hitungQtyPakai`)
Rumus BOM tak dikenal diam-diam dihitung 1:1. Perilaku dipertahankan (terdokumentasi disengaja) tetapi kini `Log::warning` untuk observability.

### 2.7 HPP snapshot tidak dibulatkan (`PenjualanCheckoutService::pakaiBahanItem`, `QuotationController`)
`hpp_snapshot` ditulis mentah (banyak desimal) ke kolom `decimal(15,2)` — selisih miliaran dengan layanan lain. Kini `round(..., 2)`.

### 2.8 `Produk::$fillable` + fix 500 `itemBerubah()`
`min_luas_total`, `kelompok_minimum`, `spk_lengkap` ditambahkan ke fillable; akses `$item['file_siap_cetak']` tanpa fallback menyebabkan 500 saat edit nota (kini `?? 0`).

---

## 3. Perawatan suite test (prasyarat validasi)

- Migrasi 2026-11-11 membuat `konsumen.id_unique` NOT NULL; 79 file fixture test masih insert tanpa kolom itu → seluruh suite Feature error massal (baseline 452 kegagalan). Sweep satu-baris (anchor `('konsumen')->insert`) menambahkan `'id_unique' => Str::uuid()` pada 95 insert di 79 file; lint PHP 0 gagal.
- Assertion `KembalianTest` `[ 8.000 ]` = chip harga satuan format nota LAMA (commit ad73d0fd); diperbarui ke format nota sekarang (`<td class="rinci r">80.000</td>`).
- Test baru `tests/Feature/KoreksiNotaDiskonTest.php`: (1) koreksi mempertahankan diskon (100.000 − 30.000 = 70.000); (2) guard terbayar memakai total net (422 bila total net < terbayar). **PASS.**

---

## 4. Hasil validasi

| Uji | Status |
|---|---|
| `php -l` seluruh file berubah | PASS (0 gagal) |
| Batch 22 suite (Kasir/Penjualan/MinimumLuas/Stok/Koreksi/Quotation/Kembalian/EditNota) | PASS — 175 test, 1054 asersi |
| Suite Feature penuh | 1486 test; 7 error + 21 failure — **identik dengan / tanpa perubahan** (verifikasi stash): 0 regresi |
| Baseline kegagalan lama | 452 → 28 (semua pre-existing, sebelumnya tersembunyi balik error `id_unique`) |
| DB produksi / `.env` / XAMPP 3306 | TIDAK DISENTUH (uji hanya MariaDB sintetis port 3307 `percetakan_erp_testing`) |

Sisa 28 masalah pre-existing (bukan dari audit ini): `BackupTest` (header dump "-- MariaDB dump" + retensi), `SecAuditTest`/`LoginRateLimitTest` (rate limiter login tidak aktif di lingkungan uji), `JerseyOrderHttpTest`/`SimpanGanda`, `KasOpeningRekap`/`KasSesi`, `QueryCountTest` riwayat, `DaftarHargaCetakSelisihRehearsal`, `DashboardRingkasanCollecting`, `FormatAngkaTest`, `DesainHanyaLihatProduksi`, `MatriksRuteKeamanan`, `KeranjangHapusGate`, `UploadBerkasDesain`, `AksesApiHarga`, `PembayaranParalel`. Domain campuran; dapat dilanjutkan bila diminta.

## 5. Temuan yang sengaja TIDAK diubah (desain sadar)

- Floor `max($qtyHitung, 1)` di sisi server (terdokumentasi).
- Selisih pembulatan surcharge client vs server (server yang dipakai).
- Jersey tanpa konversi satuan (sengaja, unit pcs).
- Kunci kelompok minimum server (`label|min_luas|tarif_acuan`) vs client (label saja).

## 6. Risiko & batas

- Perbaikan koreksi nota mengubah angka yang dikirim ke jurnal (total net, bukan subtotal mentah) — nota lama yang sudah pernah dikoreksi dengan angka lama tidak ditulis ulang.
- Rate limiter login & backup masih NOT FIXED (pre-existing, di luar lingkup audit ini).
- Tidak ada tes Laravel produksi, uji visual, atau deploy: build frontend bukan bukti siap produksi.

## 7. Produksi & rilis

- Produksi `C:\xampp\htdocs\grafikaorion`, `.env`, `oriondb`: **TIDAK DISENTUH**.
- Merge ke `main` / deploy: **TIDAK DILAKUKAN** (menunggu arahan owner soal strategi branch).
