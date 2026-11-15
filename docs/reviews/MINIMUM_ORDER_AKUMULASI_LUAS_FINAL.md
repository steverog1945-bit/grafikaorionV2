# Minimum Order Metode Luas — Akumulasi Kelompok (spec owner 2026-11-15)

Branch: `feat/min-luas-hybrid-t4a` · tanggal kerja: 2026-11-15
Scope: PHASE A–F sesuai spesifikasi "Pengembangan Fitur Minimum Order pada Metode Perhitungan Luas".

## PHASE A — Audit implementasi yang sudah berjalan (temuan)

- Akumulasi per kelompok + roll-up selisih **sudah ada** (HYBRID T4-A, commit sebelumnya): `PenjualanCheckoutService::validasiItems()` mengumpulkan `$minLuasGroups` per `kelompok_minimum` (fallback per produk), total aktual dibulatkan 4 desimal, selisih `MinimumLuas::selisih()`, ditagih pada **baris item terbesar** kelompok (bukan baris terpisah), ditandai `is_minimum_order=1` + catatan "Termasuk Minimum Order {kelompok}: selisih X m²" di `catatan_produksi`. Snapshot tersimpan (`subtotal`, `harga_jual_snapshot`), `is_minimum_order` ada di `transaksi_detail` (migrasi `2026_11_10_110000`).
- Frontend POS sudah memuat SweetAlert2 global (`layouts/app.blade.php`) dan menampilkan baris surcharge di kartu keranjang (`renderCart()`).
- **Minimum PENAGIHAN** (konsep 2) = lantai Daftar Harga: `range_min` terkecil tier (`HargaService::luas_min_daftar`) — di bawahnya harga 0 → POS memblokir Tambah dengan SweetAlert "Di bawah minimum daftar harga", server menolak "Harga belum diatur". Kolom `produk.min_luas_tagih`/`mode_luas` ada di DB tapi 0 data & 0 referensi kode → tidak disentuh.
- Edit & susulan memakai jalur validasi yang sama (`validasiItems` + `$itemsLama`/`$itemBerubah`).

## PHASE B/C — Kompatibilitas kelompok + integrasi minimum penagihan

**Kunci kompatibilitas baru** (`MinimumLuas::kunciKelompok(label, minLuas, tarifAcuan)`): item hanya dijumlahkan bila label kelompok + `min_luas_total` + **tarif acuan** (harga per m² pada qty acuan `max(min_luas_total, 1)`, cache per produk) sama. Item dengan basis harga/konfigurasi minimum berbeda jatuh ke kunci berbeda → terakumulasi terpisah, masing-masing memenuhi minimumnya sendiri (spec §2).

Matematika kelompok diekstrak ke fungsi murni `MinimumLuas::rencanaKelompok()` (satu tempat, diunit-test); blok post-loop `validasiItems()` kini memakainya.

**Dua konsep dipisahkan** (spec §3, komentar baru di service): Minimum Order (`min_luas_total` + `kelompok_minimum`, hanya produk berkonfigurasi) vs Minimum Penagihan Daftar Harga (`range_min` tier terkecil, dijaga `HargaService`, tidak diubah). Produk tanpa konfigurasi minimum order: seluruh mekanisme lama berjalan apa adanya.

## PHASE D — Peringatan SweetAlert + rincian kalkulasi

- `grupMinKeranjang()`: akumulasi luas per kelompok minimum di keranjang (mirror preview server).
- `peringatanKelompokMinOrder()`: SweetAlert "Minimum Order Belum Terpenuhi" **non-blocking** dengan pesan sesuai spec (total aktual, minimum, kekurangan, "Sistem akan menghitung harga berdasarkan minimum order"). Dipanggil setelah item ditambah/diubah (`tambahKeKeranjang`) dan setelah item dihapus (`hapusItem`); **dedup via signature** (label|total|min) — tidak diulang selama angka tidak berubah, hilang saat minimum terpenuhi (signature direset).
- Panel **rincian kalkulasi** `#minimum_rincian` di keranjang (spec §6): per kelompok — Total luas aktual, Minimum order, Kekurangan, Luas ditagihkan, Tarif/m², Total harga, + status ("Minimum terpenuhi - perhitungan memakai luas aktual" / "Harga memakai minimum order"). Preview saja; angka final dihitung server saat disimpan.
- `tambahKeKeranjang` ukuran: blokir lama (dengan relax kelompok+kategori basi) diganti **auto-naik** panjang/lebar ke minimum (sama dengan Pass 0 server), menghormati eksempsi produk sama ≥2 baris; minimum QTY (pcs) tetap ditolak. `cekMinOrder()` diberi short-circuit eksempsi.

## PHASE E — Automated test

- **Unit (berjalan, exit 0):** `tests/Unit/MinimumLuasKelompokTest.php` — 11 tes fungsi murni: skenario spec 1-5 (0,4 tunggal → selisih 0,6; 0,4+0,7 → tanpa; 0,3+0,2 → 0,5; tepat; melebihi), 8/9 (ubah/hapus item → rencana dihitung ulang), 12 (qty>1 sekali hitung), 13 (presisi 4 desimal, noise float 0,1+0,2+0,7 tidak menimbulkan selisih palsu), 10/11 (`kunciKelompok` memisahkan tarif/minimum/label beda, format angka tidak mengubah kunci). Suite Unit penuh: **37 tes / 138 assertion OK**.
- **Feature (dibuat, butuh DB testing — NOT TESTED, lihat hambatan):** `tests/Feature/MinimumLuasAkumulasiTest.php` — akumulasi lintas-produk sekelompok (contoh spec 1 & 2), tarif beda di label sama → terpisah, qty 2+2, dua kelompok tidak saling menolong, minimum penagihan daftar harga tetap mengikat untuk produk tanpa konfigurasi minimum (422 di bawah range_min, OK di atasnya).
- **Rekonsiliasi test basi:** `Tahap3OutdoorMinTest::test_0_6_plus_0_3_*` diperbarui ke keputusan eksempsi 2026-11-14 (produk sama ≥2 baris → minimum ditiadakan, semua baris luas aktual).

## PHASE F — Audit regresi

- Verifikasi server CLI dengan produk nyata (harness baca-saja, tanpa tulis DB): akumulasi #1+#18 FLS280 (0,4+0,7 tanpa selisih; 0,3+0,2 → selisih 0,5 × 20.000 digulung ke #1, `is_minimum_order` hanya di baris #1), eksempsi #1 dua baris tetap ditiadakan, floor #2 (50×100 → 120×200) & #22 (60×60 → 100×100) dengan payload `koreksi_minimum` utuh, produk tanpa config (#3) tak tersentuh, isolasi lintas kelompok (selisih FLS280 tidak tertolong TC/TC110; #193 floor 110×200 menyerap minimumnya sendiri) — **13/13 PASS**.
- JS fake-DOM: **13/13 PASS** (grup, selisih, popup non-blocking, dedup, reset signature, qty, tanpa-config). 26/26 blok `<script>` `node --check` OK. Render POS CLI OK (478.611 byte, panel & helper ada di HTML).
- `php -l` semua file terubah OK. Kasir/menulis DB/produksi/desktop tidak tersentuh.

## Hambatan

- **NOT TESTED (BLOCKED):** seluruh suite Feature (termasuk `MinimumLuasAkumulasiTest`) tidak bisa dijalankan — database `percetakan_erp_testing` tidak dapat dibuat: akun aplikasi `grafikaorionv2` hanya ber-hak pada `grafikaorionv2.*` (SHOW GRANTS diverifikasi; root tanpa password ditolak MySQL). Perlu owner membuat skema testing atau memberi grant; setelah itu `php vendor/bin/phpunit --testsuite Feature`.
- Uji klik-through browser (popup SweetAlert & panel rincian di layar sungguhan) belum dilakukan — preview konsistensi diverifikasi via fake-DOM; angka final tetap otoritatif dari server.

## Keputusan tafsir yang bisa diveto owner

1. Kompatibilitas tarif diukur pada qty acuan `max(min_luas_total, 1)` m² per produk — tier berbeda di bawah/atas acuan tidak memecah kelompok.
2. Kunci sisi layar (preview) hanya label+minimum; dimensi tarif hanya di kunci server. Pada data nyata (FLS280 tarif seragam 20.000) keduanya identik.
3. Eksempsi 2026-11-14 (produk sama ≥2 baris → minimum batal) tetap berlaku dan baris itu keluar dari kelompok minimum.
