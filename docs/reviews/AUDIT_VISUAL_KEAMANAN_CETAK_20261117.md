# Audit Visual, Keamanan UI & Konsistensi Cetak — 17 November 2026

Tanggal: 17 November 2026
Branch: `feat/min-luas-hybrid-t4a` (target push: `sync/xampp-hybrid-t4a-20261116`) — sama dengan audit BOM sebelumnya.
Bukti: staging Laravel terisolasi `http://127.0.0.1:8217` (DB sintetis `orion_pwa_staging_pr21`, port 3307, fixtures `StagingFixtureSeeder` + user Administrator sintetis `adminfix`). Semua screenshot tersimpan di `docs/reviews/assets/audit-visual-20261117/`. Produksi tidak disentuh.

---

## 1. Keluhan owner yang harus dijawab: "print laporan tidak senada dengan print laporan lainnya"

Temuan root-cause (3 keluarga kop + 5 varian kop total) & perbaikannya:

| Sebelumnya | Sesudah |
|---|---|
| 21 laporan memakai `partials.print-report-header` (kop flex, logo h-14, judul 17px, garis 1px) **tapi tidak satu pun punya baris "Dicetak pada … oleh …"** — footer itu hanya ada di Evaluasi Master dengan format sendiri | Partial baru `partials/print-report-footer.blade.php` ("Dicetak pada {d/m/Y H:i} oleh {username}") dipakai **semua 23 halaman cetak**; footer Evaluasi Master diganti partial yang sama |
| `setting_harga/cetak` punya kop duplikat manual (logo h-10, judul 13/14px, tabel 10/12.5px, padding cetak `0 !important`) | Pakai `print-report-header` + footer; tabel ikut `var(--fs-tabel)`; skala huruf & skema padding 28px 32px senada; gap `main padding:0` dihapus |
| `laporan/restock` **tidak bisa dicetak rapi sama sekali** (Ctrl+P = halaman layar + sidebar; tanpa kop) | Tambah `@media print` boilerplate + kop bersama + footer + tombol Cetak (pola `x-button secondary` seperti laporan Order) |
| `laporan/kebocoran-tagihan` mencetak kartu layar ber-rounded + badge warna amber, bukan tabel polos | Blok print-only `x-laporan.print-table` berisi Nota/Tanggal/Konsumen/Pekerjaan/Sumber (kolom persis laporan lain); kartu & section AI hanya di layar |
| `laporan/evaluasi-master` — `.eval-table th` warna palet lama terang, **tanpa dark override** | Kepala tabel memakai token `#f5f5f5/#171717` + override `html.dark` (kepala gelap, teks `#e5e7eb`) — diverifikasi dark screenshot |
| `print_closing` memakai `#1a231f` + `font-weight:600` (6 lokasi) | Diseragamkan ke token `#171717` + bobot 500 |
| Keseluruhan: 20 file menyalin boilerplate `@media print { aside… }` persis sama (dibiarkan — didokumentasikan, bukan bug) | — |

Bukti: `before-setting-harga-cetak.png` → `after-setting-harga-cetak-light.png` + dark `after-dark-setting-harga-cetak.png`; `before-restock.png` → `after-restock-full-opacity.png` (tombol Cetak baru + tombol aksi memakai aksen tema); `after-evaluasi-master-light.png` + `after-dark-evaluasi-master.png` (kepaka tabel gelap di mode gelap); `acuan-laporan-order.png` (gaya acuan aplikasi, tidak diubah); `artifact-*-blank-saat-animasi.png` (bukti tangkapan di tengah animasi fade-up — konten asli ada).

**Catatan penting:** footer baru bersifat `hidden print:block` — tidak mengubah tampilan layar; terverifikasi oleh 20 asersi `CetakLaporanSenadaTest` (semua 200 OK, memuat kop bersama + "Dicetak pada").

## 2. Audit Halaman/Blade — temuan & penanganan

Ditemukan oleh sweep visual/komponen (137 baris `<style>` privat di `audit-owner/index`, orphan `kasir/open_order.blade.php`, `kedatangan-bahan/form.blade.php`, `penjualan/page-header.blade.php`, 13 duplikat mati `resources/views/layouts/*.blade.php`, sistem tabel privat lain). **Tidak dihapus sekarang** — penghapusan file adalah keputusan owner; dicatat sebagai pekerjaan lanjutan.

Yang diperbaiki sekarang:

1. **Evaluasi Master dark mode** — tabel kepala tetap putih di mode gelap (pecah). Diperbaiki + diverifikasi screenshot.
2. **Jersey Pesanan Baru (`jersey/orders/create`) dark mode** — input `background-color:#ffffff` & `#f8fafc` hardcode + focus ring `#006C7B` (aksen lama); sekarang `var(--surface-primary/secondary)`, `var(--text-primary)`, `var(--border-primary/secondary)`, ring `var(--brand-sidebar-accent)` — diverifikasi `after-dark-jersey-create.png` (form gelap, seluruh input gelap).
3. **Restock tombol hitam `bg-slate-900`** (di luar tema) → `bg-[var(--brand-sidebar-accent,#4F46E5)] text-white hover:brightness-110` = persis kelas `x-button` primary. Computed style: `rgb(232,137,29)` = #E8891D (orion-classic).
4. **Kebocoran Tagihan** — versi cetak polos terpasang; layar tidak berubah.
5. **Kepyalan kelas arbitrary-value baru**: class `bg-[var(--brand-sidebar-accent,#E8891D)]` yang saya tulis awalnya TIDAK ter-build di CSS hasil (`getComputedStyle` = `rgba(0,0,0,0)` = bening, tombol putih di atas putih) → diselaraskan ke string kelas yang sudah ter-compile + `npm run build` (app-AtIf8mzi.css) diverifikasi computed = #E8891D. **Semua sesi: kelas baru harus lewat Vite build.**

## 3. Audit Motion — temuan & penanganan

- **`dashboard.blade.php`** — kedip "updated at" memakai WAAPI `Element.animate()` yang TIDAK terhenti oleh kill-switch `prefers-reduced-motion` (CSS tidak menghentikan WAAPI). Sekarang di-gate dengan `matchMedia('(prefers-reduced-motion: reduce)')`.
- `@keyframes shake` (penjualan/create) tanpa guard — tertutup kill-switch global, dibiarkan.
- Toast/confirm: `Toast` global (app.blade.php) vs 6 `Swal.mixin` inline tersebar (1800–3000ms berbeda) & `data-konfirmasi` global vs puluhan Swal manual — **inkonsistensi serius tapi lintas puluhan file; perlu migrasi bertahap, bukan tambalan mendadak** (di bawah, bagian "Pekerjaan lanjutan").
- `.shimmer`/`.spin` tidak pernah dipakai (CSS mati); `window.deleteConfirm` mati di app.js.

## 4. Audit Keamanan UI — temuan & penanganan

CSRF/method spoofing: **SEHAT** (34 form GET tanpa token memang tidak butuh; semua form tulis terverifikasi `@csrf`+`@method` benar; `throttle.write` 180/menit di route grup; login `throttle:login` 5/mnt + CAPTCHA setelah 3 gagal = aktif & berlapis). `CheckMenuAccess` fail-closed; route publik read-only + ter-throttle; `/storage/{folder}` traversal diblok.

Diperbaiki sekarang:
1. **Polling GET tanpa batas** — endpoint `/sinyal`, `/kasir/check-pembayaran`, `/dashboard/api/kpi`, `/spk/cek-antrian-baru`, `/produksi/cek-antrian-baru`, `/collecting/cek-antrian-baru` tidak tersentuh limiter (ThrottleWriteRequests sengaja hanya menghitung POST/PUT/PATCH/DELETE). Sekarang semuanya terpasang `throttle:polling` (120/menit per user — kasir yang bekerja sah tidak akan tersentuh).
2. **`POST /logout` tanpa middleware auth** → ditambah `auth`.
3. **Limiter `tulis` mati** di AppServiceProvider dihapus (implementasi riil ada di middleware; dokumentasi dicatat di kode).
4. **`formatRupiah`/`cleanRupiah` dideklarasikan ulang** di `kasir/form_pembayaran` & `dashboard` (timpa global `rupiah-format-script` senyap) → deklarasi ganda dihapus (global dipakai).
5. **`{!! $post->isi_html !!}`** di postingan/show & publik/baca — masih bergantung pada sanitasi saat simpan (HtmlBersih). Dibiarkan (tidak ada bukti sanitizer lepas) + **catat sebagai pekerjaan keamanan lanjutan** (sebaiknya escape ulang saat render atau tambahkan sanitasi di reader).
6. **`resume-table.blade.php:{{!!}} $value !!}`** — generik; belum ada pemanggil berbahaya (18 pemanggil mengirim `number_format`); **jangan diubah terburu-buru** — footgun, dicatat.

Tidak ada risiko TINGGI yang ditemukan. Root keamanan yang tersisa terkait rate-limiter login di lingkungan uji sudah known pre-existing (lihat bagian 6).

## 5. Perbaikan fixture & staging

- `StagingFixtureSeeder` rusak sejak migrasi `konsumen.id_unique` (NOT NULL) — ditambah UUID per baris fixture.
- Pemetaan role di fixture tidak cocok dengan tabel `roles` saat ini (`admin` fixture = id_role 1 "Operator Produksi"); user Administrator sintetis khusus staging dibuat lewat tinker (`adminfix`), hanya di DB `orion_pwa_staging_pr21`.
- `spk.finishing` view hilang = **kode mati tanpa route** (`SpkController::finishing/simpanFinishing` tidak direferensikan `routes/web.php`), bukan 500 runtime. Dihapus? Belum — dicatat.
- `kedatangan-bahan/form.blade.php` orphan; `penjualan/page-header.blade.php` orphan; `kasir/open_order.blade.php` orphan (halaman indigo di luar tema); 13 `layouts/*.blade.php` duplikat mati.

## 6. Yang TIDAK diubah & alasan

- **Desain print SPK/Quotation/Jersey/Pembelian** (kop topbar hitam spk-print-style, palet lama quotation #2A3731, pembelian continuous monospace Courier) — dokumen kerja/eksternal; mengubah palet mengubah dokumen bisnis; perlu keputusan owner terpisah.
- **Boilerplate `@media print` di 20 file** — fungsional; konsolidasi ke partial adalah refactor "bersih", bukan bug.
- **`#94a3b8` print** di pembelian/evaluasi — kontras; tetap ada di banyak tempat; sweep warna cetak terpisah.
- **CSS `.shimmer/.spin` mati**; **kumpulan orphan**; **sistem tabel privat audit-owner** (137 baris) & analisis/kedatangan-bahan/jersey orders show/menu-editor — migrasi ke `x-table` belum dilakukan (risiko visual pada halaman paling kompleks; butuh sesi khusus).
- **Header tercetak sidebar saat dark mode**? Tidak ada — print-table memaksa `#fff`.
- **28 pre-existing test failures** (mis. DesainHanyaLihatProduksi, JerseyOrderSimpanGanda, LoginRateLimit, Backup) — diverifikasi identik dengan/tanpa perubahan (git stash + rerun bersih); domain campuran; di luar lingkup audit ini.

## 7. Validasi

| Uji | Hasil |
|---|---|
| `npm run build` | PASS (6.03s, app-AtIf8mzi.css 246 kB / 39,7 kB gzip, JS 154 kB) |
| PHPUnit `CetakLaporanSenadaTest` (baru, 20 data) | PASS 20/20 — semua laporan memuat kop bersama + footer bersama; setting-harga memakai partial (dh-kop dihilangkan) |
| PHPUnit `PrintLaporanTabelTest` + `KembalianTest` + `LaporanSinkronJurnalTest` + `AnalisisEksekutifTest` + `CetakLaporanSenadaTest` | PASS (50 test; 1 gagal = DesainHanyaLihatProduksi, terverifikasi pre-existing identik tanpa perubahan) |
| PHPUnit suite Feature penuh | 1506 tests; 7 error + 21 failure — **identik dengan baseline** (1486 tests sebelumnya, +20 test baru saya); nol regresi |
| `php -l` routes/web.php, AppServiceProvider.php | PASS |
| Computed style tombol aksen (restock) | `rgb(232,137,29)` = #E8891D |
| Screenshot staging (light/dark, 12 file) | `docs/reviews/assets/audit-visual-20261117/` |
| Dark mode form Jersey create | `after-dark-jersey-create.png` — input gelap, teks terbaca |
| DOM kop+footer staging (restock, setting-harga, evaluasi, kebocoran, jurnal, kas, audit-owner) | 7/7 = `true` (kop + footer bersama) |
| PHPUnit suite Feature penuh (rerun) | **1506 tests; 7 error + 21 failure = IDENTIK dengan baseline pre-existing** (diverifikasi via stash & rerun bersih) → nol regresi; suite sebelumnya sempat terkontaminasi oleh tabrakan RefreshDatabase yang saya jalankan paralel (584 error artefak, bukan regresi) — dijalankan ulang bersih |

## 8. Pekerjaan lanjutan (butuh keputusan owner / sesi terpisah)

1. Migrasi toast/confirm inline → global (`Toast`, `data-konfirmasi`) — ~15 file, perlu checklist per modul.
2. Migrasi tabel privat ke `x-table`: audit-owner (prioritas tertinggi — 137 baris, 67 hex), evaluasi-master (sudah di-token sebagian), analisis, kedatangan-bahan/create, jersey orders/show, menu-editor, master/pendukung, postingan/_daftar.
3. Hapus file orphan setelah ACC owner: `kasir/open_order.blade.php`, `kedatangan-bahan/form.blade.php`, `penjualan/page-header.blade.php`, 13 `layouts/*.blade.php`, `SpkController::finishing/simpanFinishing` (kode mati).
4. Palet legacy di dokumen cetak eksternal (quotation/pembelian/spk) — keputusan palet dokumen bisnis owner.
5. `{!! $post->isi_html !!}` — escape/sanitize di sisi render untuk `/baca/{slug}` (publik).
6. Tema staging `orion-classic` (`#E8891D` aksen di atas putih untuk teks putih pada tombol primary) — kontras putih-di-atas-oranye ~2.9:1, gagal WCAG AA; pertimbangkan gelapnya aksen atau teks gelap pada tema owner.

## Produksi & rilis

- Produksi `C:\xampp\htdocs\grafikaorion`, `.env`, `oriondb`: **TIDAK DISENTUH**.
- Merge ke `main` / deploy: **TIDAK DILAKUKAN**.
