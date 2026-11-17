# Perbaikan Rekomendasi P0/P1/P2 — Batch 17 November 2026 (lanjutan)

Tanggal: 17 November 2026
Branch: `feat/min-luas-hybrid-t4a` → push ke `sync/xampp-hybrid-t4a-20261116`
Konteks: kelanjutan dari jawaban rekomendasi pasca-audit (pertanyaan "apa yang bisa kamu rekomendasikan"). Semua perubahan diverifikasi di instance lokal XAMPP & lewat PHPUnit (DB sintetis 3307). Produksi tidak disentuh.

---

## A. Silent failure (kegagalan yang ditelan diam-diam) — diperbaiki

| Lokasi | Dulu | Sekarang |
|---|---|---|
| `PenjualanCheckoutService` (`tgl_order`) | Format salah / tanggal masa depan → diabaikan tanpa jejak | `report()` + `session('warning')` tampil ke pengguna ("Tanggal order … diabaikan — nota dicatat dengan tanggal hari ini."); aman di channel API (`hasSession()`) |
| `AccountingService::reverseByReferensi` / `reversePenjualan` | Tidak ada baris jurnal → return tanpa apa pun ("Koreksi berhasil" padahal tidak membalik apa pun) | `report()` dengan no_referensi/nota — wajib tercatat |
| `dashboard.blade.php` (`fetchKpi`) | Gagal total tanpa jejak; operator mengira angka segar | `console.warn` + label "Gagal memuat" di baris status agar pengguna sadar data belum segar |
| `components/polling-notifikasi.blade.php` | `.catch(() => {})` penuh | `console.warn` + atribut `title` pada lonceng setelah 3 kegagalan berturut-turut ("Polling gagal N× — muat ulang/masuk ulang") |

## B. Toast inline → Toast global (6 titik, 5 file)

`Swal.mixin` toast dengan timer 1800/2000/2200/2400/3000 berbeda dihapus; semua memanggil `Toast` global layouts (top-end, 3000ms) — perilaku seragam di seluruh aplikasi:

- `penjualan/create` (3×), `penjualan-barang/create`, `jersey/master`, `postingan/form`.

Catatan: "Toast global" memakai `const Toast` script-level klasik (konvensi kode yang sudah ada — bukan `window.Toast`).

## C. Token aksen seragam (tema)

Tombol konfirmasi Swal `'#4f46e5'` (kasir/waiting_list, jersey/master ×2) → `confirmButtonColor: 'var(--brand-sidebar-accent, #E8891D)'` (ikut tema aktif, sendiri berubah bila tema diganti).

## D. Dead code dihapus

- `.shimmer`, `.spin` + keyframes `motion-shimmer`/`motion-spin` di `resources/css/motion.css` — tidak pernah dipakai view mana pun.
- `window.deleteConfirm` di `resources/js/app.js` — 0 pemanggil (semua master memakai pola `data-konfirmasi`).
- Duplikasi lokal `escHtml()` (master/konsumen, penjualan/create) — global `window.escHtml` (layouts) dipakai.

## E. Tabel Restock → `x-table`

`laporan/restock` diganti ke komponen `x-table bare` (header tanpa uppercase sendiri, ukuran/pemisah mengikuti `.modern-table`), thead `bg-slate-50`, baris `hover:bg-slate-50`, `divide-slate-100` — senada dengan laporan lain. Print behavior & tombol baru tidak berubah.

## F. Master Pendukung di-rapihkan — TAPI orphan

`master/pendukung/index.blade.php` ditulis ulang sepenuhnya: `x-page-header`, `x-card`, `x-table bare`, `x-button` (aksen tema), fokus ring indigo dipertahankan, emoji mojibake (`�sT�`) diganti ikon FA. **Namun:** halaman ini tidak punya route apa pun (controller `MasterController@…` tidak direferensikan `routes/web.php`) — jadi ia **dead code**, bukan hanya menyimpang gaya. Perbaikan tetap berguna bila suatu saat di-wire, tapi kandidat terbaik = dihapus (perlu ACC owner) atau di-route terpisah.

## G. Yang TIDAK dikerjakan (jujur, dengan alasan)

- **`audit-owner/index.blade.php`** (376 baris, 13 `<table>`, 137 baris `<style>` privat, 67 hex, palet biru sendiri + salinan override dark sendiri) — redesign penuh, memerlukan sesi khusus & verifikasi visual bertahap; bukan pekerjaan satu commit.
- **Migrasi konfirmasi tangan → `data-konfirmasi` global** — puluhan pemanggilan dengan `.then()` jQuery & logika kontekstual; konversi massal berisiko memecah perilaku kasir; direkomendasikan menyicil per modul, bukan satu sapu.
- **`{!! $post->isi_html !!}`** — sudah diperbaiki sesi sebelumnya (sanitasi saat render).
- **Orphan & dead code lain** (`kasir/open_order`, `kedatangan-bahan/form`, `penjualan/page-header`, 13 `layouts/*.blade.php`, `SpkController::finishing/simpanFinishing`) — menunggu ACC hapus owner.
- **Palet legacy cetakan eksternal** (quotation/pembelian/SPK) — keputusan palet dokumen bisnis owner.
- **Tema `orion-classic` kontras** (putih di atas `#E8891D` ≈ 2.9:1) — keputusan tema owner.
- **Audit data produksi diskon** (#1) — akses DB produksi ditolak untuk agent; SQL read-only disiapkan di `docs/reviews/AUDIT_P0_DEP_DAN_LOCALHOST500_20261117.md`; owner yang menjalankan.
- **`master/pendukung`** — fungsi membersihkan gaya, tapi pagination tidak ada (halaman mati).
- **postingan/show "tanpa @media print"** — audit keliru: `.cetak-kop` & aturan print postingan SUDAH didefinisikan di `resources/css/app.css` (`@media print` global `.no-print`); tidak perlu perubahan.

## H. Validasi

| Uji | Hasil |
|---|---|
| `npm run build` | PASS (5.75s, app-CHoTb8S4.js 153,6 kB) |
| PHPUnit batch alur disentuh (Penjualan/Koreksi/OrderLifecycle/Approval/Notifkasi/Kembalian/DivisiTab) | PASS 91/91 |
| Suite Feature penuh | lihat bagian I |
| Halaman live dicek via browser (GET): `/laporan/restock`, `/dashboard` | 200 OK, bukan halaman error |
| `master/pendukung` | 404 — halaman orphan (tidak ada route); dibiarkan (laporan) |

## I. Hasil suite Feature penuh

1508 test, 6736 asersi → **7 errors + 18 failures** — IDENTIK dengan baseline pre-existing (7+21) **dikurangi tepat 3 kegagalan yang diperbaiki sesi ini** (LoginRateLimit, SecAudit login 5-percobaan, DesainHanyaLihatProduksi) → **nol regresi; 3 masalah lama sembuh**. 1 test "risky" = `SecAuditTest::test_isi_postingan_disanitasi…` (PHPUnit menandai output buffer yang terbuka di jalur render — bukan kegagalan; 13/13 assert hijau).

## Produksi & rilis

- Produksi: TIDAK DISENTUH.
- Merge/deploy: TIDAK DILAKUKAN.
