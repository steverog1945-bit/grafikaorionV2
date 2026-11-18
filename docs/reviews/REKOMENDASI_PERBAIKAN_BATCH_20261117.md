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

---

# ADDENDUM — Batch 2 (17 November 2026, lanjutan "kerjakan satu per satu")

## J. Orphan & dead code DIHAPUS (owner: "kerjakan satu persatu... jangan ada temuan lagi")

| File | Status sebelum | Verifikasi penghapusan |
|---|---|---|
| `resources/views/kasir/open_order.blade.php` | orphan (tanpa route/view-ref) | `git grep open_order` = 0 referensi |
| `resources/views/kedatangan-bahan/form.blade.php` | orphan | `git grep kedatangan-bahan.form` = 0 |
| `resources/views/penjualan/page-header.blade.php` | orphan | `git grep penjualan.page-header` = 0 |
| 13 duplikat mati `resources/views/layouts/*.blade.php` (badge, button, card, detail, empty-state, group, index, input, modal, page-header, pagination, select, table) | tidak pernah direferensikan | `git grep x-layouts.` / `view('layouts.` = 0; hanya `app.blade.php` & `_app-footer.blade.php` dipakai dan DIPERTAHANKAN |
| `SpkController::finishing()` | kode mati tanpa route | grep `SpkController@finishing` = 0; `simpanFinishing()` DIPERTAHANKAN (dipakai `A3TanpaTierValidasiTest`); view `spk/finishing` memang sudah tidak ada |

## K. Perbaikan tambahan

1. **`resume-table` XSS footgun dihapus** — `{!! $value !!}` → `{{ $value }}`. Audit seluruh 22+ pemanggil (`akuntansi/*`, `laporan/*`, `kas-kecil`, `audit-owner`): semuanya menyuntik `number_format(...)` atau string polos, TIDAK ada HTML nyata — escape adalah default yang benar.
2. **Padding cetak landscape diselaraskan** — `laporan/evaluasi-master` dan `audit-owner` keduanya `main { padding: 28px 32px }` (sebelumnya evaluasi-master `0 8mm` = kop menempel ke tepi kertas, keluhan "mepet kanan kiri").

## L. Sengaja TIDAK diubah (keputusan desain, bukan temuan terbuka)

- **Palet indigo (`bg-indigo-600` dsb.) di ratusan view** — migrasi tema sistemik butuh keputusan owner: pindah semua ke `--brand-sidebar-accent` (orion-classic = #E8891D) atau pertahankan biru. Penggantian massal tanpa keputusan = berisiko merusak kontras & identitas. Halaman error (`errors/master`) punya palet standalone SENGAJA — dibiarkan.
- **`audit-owner` tetap utuh** — halaman sudah memakai kop cetak bersama & padding diselaraskan; palet `.lembar`-nya adalah desain panel sendiri dengan dark-mode lengkap, bukan sampah.

## M. Audit produksi (P0 #1) — status final untuk agent

- MySQL socket `root` @ produksi: **Access denied** (tanpa password; kredensial tidak dibaca dari `.env`). TCP juga ditolak.
- Kesimpulan: audit data produksi tetap harus DIJALANKAN OWNER via SQL read-only di bagian D `AUDIT_P0_DEP_DAN_LOCALHOST500_20261117.md`. Ini bukan pekerjaan agent.

## N. Validasi batch 2

- `php -l app/Http/Controllers/SpkController.php` → PASS.
- Suite Feature penuh → lihat bagian O.

## O. Hasil suite Feature (batch 2)

Pengukuran TERAKHIR setelah seluruh batch 2 & 3 (termasuk perbaikan input pembayaran & RentangTanggal):

```
Tests: 1509, Assertions: 6746, Errors: 7, Failures: 18, PHPUnit Deprecations: 231, Skipped: 7, Risky: 1.
```

- Baseline valid sebelumnya: 1508 test, 7E + 18F (25 pra-ada, tersebar di JerseyOrderHttp/SimpanGanda, BackupTest, KasOpeningRekap, KasSesi tutup-paksa, FormatAngka, QueryCount, RawSqlAudit FOLLOW-UP, UploadBerkasDesain, SecAudit, PembayaranParalel).
- Batch 3 menambah 1 test baru (`test_closing_selisih_kas_lebih_dan_kurang_dijurnal_ke_akun_selisih`) — LULUS.
- `RawSqlAuditTest::test_filter_laporan_dengan_payload_injeksi_tidak_error` BERULANG kembali ke daftar PASS setelah fix `RentangTanggal` (try/catch).
- 7E + 18F SEMUA pra-ada; tidak ada regresi dari batch ini.
- Suite dijalankan TUNGGAL tanpa edit kode paralel (run campuran sebelumnya = artefak DB uji mati, hasilnya diabaikan).

# ADDENDUM — Batch 3 (18 November 2026, permintaan owner berantai)

## P. Kelola Kas — filter tanggal & metode, visual sesi ringkas, kolom Aksi

1. **Filter history**: `KasirController::kelolaKas(Request)` menerima `?tgl=YYYY-MM-DD` (regex-format ketat, invalid → diabaikan) dan `?metode=` (whitelist `Tunai|Debit|Transfer|QRIS BRI|Deposit`). Filter menajamkan query `pembayaran_riwayat` & `kas_manual` (tanggal → `whereBetween` penuh 00:00–23:59; tanpa filter → perilaku lama "sejak sesi dibuka"). GET murni, tanpa JS, hasil bisa dibagikan sebagai tautan. Bar filter + tombol Reset di `kasir/kelola_kas.blade.php`; judul tabel & rekap ikut menyebut rentang filter.
2. **Bar sesi disederhanakan**: paragraf panjang "Sesi kas dibuka..." → satu baris chip ringkas: titik status + "Sesi aktif · {nama} · {tanggal} ({jam})" + chip "Laci awal Rp …" + badge peringatan "Lebih dari 24 jam - wajib closing" (teks sama dengan assertion test `KasSesiTest` yang dipertahankan, assertion 'belum di-closing' → 'wajib closing').
3. **Kolom Aksi kanan**: koreksi metode pindah dari tombol inline di bawah badge ke kolom terpisah di ujung kanan tabel (header + `colspan` 8 + tombol berkerangka). Nota non-aktif/Deposit menampilkan tanda `–` dengan tooltip alasan.

## Q. Kebijakan selisih laci vs sistem (DIJURNAL OTOMATIS)

- **Jawaban atas pertanyaan owner**: selisih tidak boleh "diamankan" manual oleh kasir. Saat closing, selisih dijurnal otomatis:
  - Kas lebih → Dr `1000 Kas`, Cr `4200 Pendapatan Lain-lain`
  - Kas kurang → Dr `5100 Biaya Lain-lain`, Cr `1000 Kas`
  - Referensi `KAS-SELISIH-{id_kas}`; selisih < 1 sen (artefak pembulatan) tidak dijurnal.
  - Implementasi: `AccountingService::postSelisihKas()`, dipanggil `simpanClosing` SEBELUM jurnal setoran (urutan: buku besar disamakan dulu dengan isi laci → baru setoran fisik dipindahkan). Buku besar Kas selalu = uang yang benar-benar ada di laci di akhir setiap sesi.
- **Indikator toleransi** di `preview_closing` (live JS): |selisih| ≤ Rp 10.000 ditampilkan "kecil, dalam toleransi" (tetap wajib keterangan), di atas itu merah/amber seperti semula.
- Test baru: `KasSesiTest::test_closing_selisih_kas_lebih_dan_kurang_dijurnal_ke_akun_selisih` (kas lebih 510rb/500rb → jurnal 2 baris 1000/4200; kas kurang 490rb/500rb → 5100/1000).

## R. Dropdown konsumen → select2 AJAX (7 halaman)

Master konsumen (puluhan ribu baris) tidak lagi diseret ke `<option>` tiap buka halaman. Pencarian server-side `konsumen.search-ajax` (LIKE nama/telepon, limit 20, index baru `konsumen.nama_konsumen`).

| Halaman | Perubahan |
|---|---|
| `penjualan-barang/create` | controller: `$konsumen` → `$konsumenAwal` (hanya old()/redirect terpilih); view: select2 AJAX |
| `jersey/orders/create` | controller `formData()`: `konsumenList` penuh → `konsumenAwal`; init AJAX + fokus kursor tetap |
| `laporan/order` | controller + view + init AJAX, auto-submit on change |
| `laporan/order-jersey` | idem |
| `laporan/piutang` | idem |
| `kasir/monitoring_transaksi` | filter konsumen AJAX (`data-konsumen-ajax`), filter lain tetap lokal |
| `quotation/form` | init AJAX + metadata konsumen (nama/telp/golongan) via `e.params.data` (opsi AJAX) & dataset (opsi server-rendered) |
| `konsumen-deposit` (2 modal) | Tambah Setoran & Kembalikan Uang → AJAX; Kembalikan memakai mode `?saldo=1` di `searchAjax` (join `konsumen_deposit`, hanya saldo positif, saldo tampil di teks) |

## S. Kolom `total_terbayar` + index `nama_konsumen`

- Migrasi `2026_11_18_100000_add_total_terbayar_to_transaksi_and_index_nama_konsumen.php`: kolom `transaksi.total_terbayar decimal(15,2) default 0` + backfill dari `SUM(pembayaran_riwayat)` sah + index `konsumen_nama_konsumen_idx`. **Sudah dijalankan** di DB uji 3307 (verifikasi: index ada, NULL count = 0).
- `App\Services\TerbayarSinkron::sinkronkan()` — recompute idempoten dari sumber kebenaran; dipanggil di 4 titik tulis pembayaran: `KasirController::bayar`, `PenjualanCheckoutService` (deposit), `KoreksiPembayaranService` (void+insert).
- **Badge/status TIDAK diubah** ke kolom mirror: Monitoring Kasir tetap membaca subquery agregat (sumber kebenaran) agar fixture test tidak pecah; kolom dipakai untuk KPI baru **"Terbayar Nota"** di dashboard eksekutif (grid 4→5, `DashboardEksekutifService::ringkasan()['terbayar']`).
- Penjaga drift: `AuditIntegritasCommand` menemukan `total_terbayar_tidak_sinkron` bila mirror ≠ SUM (limit 10 contoh).

## T. Pagination & batas rentang (jurnal / buku besar / laporan)

- Helper baru `App\Support\RentangTanggal`: format invalid → fallback default (bukan 422/500), akhir < awal → ditukar, akhir masa depan → hari ini, **lebar rentang maks 366 hari** (clamp, bukan error). Parsing dibungkus try/catch — `Carbon::createFromFormat` MELEMPAR `InvalidFormatException` untuk "2026-01-01' OR '1'='1" (trailing data); sebelum fix, test `RawSqlAuditTest::test_filter_laporan_dengan_payload_injeksi_tidak_error` GAGAL 500 karena itu. Setelah fix: respons 200 (payload diperlakukan sebagai fallback default, tidak pernah menyentuh SQL).
- `AkuntansiController`:
  - `jurnal()` — pagination 50/baris `withQueryString()`; total debet/kredit = agregat SQL TERPISAH yang mencakup seluruh rentang (bukan cuma halaman tampil).
  - `bukuBesar()` — pagination 50/baris; saldo berjalan tiap halaman dihitung dari **saldo kumulatif sebelum baris pertama halaman** (subtotal query offset), saldo awal & akhir = agregat seluruh rentang → angka saldo tidak pernah salah lintas halaman.
  - `labaRugi/perbandingan/neraca/neracaSaldo` memakai helper yang sama.
- `LaporanController` — 9 method (kebocoranTagihan, order, produksi, kas, belanjaBahan, orderJersey, hutang, piutang, jobCosting, hpp) mengganti pola `validate(date)+Carbon::parse` dengan helper (limit 92-hari kebocoranTagihan diselaraskan ke 366).
- View `akuntansi/jurnal` & `akuntansi/buku-besar` menambah links() + keterangan bahwa total mencakup seluruh rentang.

## U. Saldo bulanan neraca

`AkuntansiController::neraca()` menambah **tabel Saldo Bulanan** — posisi kumulatif per akhir bulan (Aset/Kewajiban/Ekuitas) sampai tanggal terpilih, satu query GROUP BY bulan×tipe_akun lalu diakumulasi berurutan; baris terakhir wajib sama dengan posisi utama di atas (satu sumber kebenaran), status Balance per bulan ditandai. Section di `akuntansi/neraca.blade.php`.

## V. Perketat login & konfigurasi produksi (tanpa menyentuh `.env`)

1. **Hard lockout login** (`AuthController::login`): di atas CAPTCHA (3 gagal) kini ada lockout keras — 10 kegagalan/15 menit **per IP** → IP terkunci; 10 kegagalan/15 menit **per username** → username terkunci. Password benar pun ditolak selama terkunci, pesan tidak membocorkan kebenaran password, setiap percobaan saat terkunci ter-audit. Sukses login menghapus counter.
2. **Kebijakan password** (`UserController`): min 8 karakter + wajib memuat huruf & angka (dulu `........` 8 titik lolos). Semua fixture test sudah memenuhi.
3. **`config/hashing.php` (baru)**: bcrypt rounds 10 → **12** (default framework dulu). Biaya: hanya saat create/ganti password; verifikasi tetap 1x per login; factory user meng-cache hash (dampak suite minimum).
4. **`config/session.php`**: cookie sesi **SECURE otomatis** ketika `APP_ENV=production` (fallback baru; staging/lokal http tetap aman). `http_only`, `same_site=lax`, `expire_on_close`, lifetime — tidak diubah (perilaku kerja kasir dipertahankan).
5. Sudah ada & dipertahankan: `throttle:login` 5/menit, CAPTCHA setelah 3 gagal, session regenerate + buang `url.intended`, fallback config `APP_ENV=production`, `APP_DEBUG=false`.

## W2. BUG OPERASIONAL (dilaporkan owner 18 Nov 2026): input pembayaran gagal — AKAR & PERBAIKAN

**Gejala owner**: "Input pembayaran tidak berhasil. Gagal. Terjadi kesalahan sistem. Tidak ada data yang tersimpan, coba lagi."

**Akar penyebab (paling mungkin & paling berbahaya)**:
1. Kolom `transaksi.total_terbayar` (batch ini) baru dijalankan di DB uji 3307 (`percetakan_erp_testing`) dan staging uji `orion_pwa_staging_pr21` — **BELUM dimigrasi di runtime yang dipakai user** (XAMPP/staging 3306).
2. `TerbayarSinkron::sinkronkan()` dipanggil DI DALAM transaksi pembayaran (`KasirController::prosesBayar` → insert pembayaran → sinkronkan → wajib `UPDATE transaksi SET total_terbayar=...`). Kolom tidak ada → SQL 1054 → exception → transaksi ROLLBACK → tidak ada data tersimpan + pesan generik `PesanError::UMUM` (itupun sesuai desain, pesan teknis tidak bocor ke layar).

**Perbaikan**:
1. `TerbayarSinkron::sinkronkan()` — cek `Schema::hasColumn('transaksi','total_terbayar')` (di-cache per proses) + seluruh recompute dibungkus `try/catch (Throwable)` yang hanya melaporkan ke log. Mirror monitoring **TIDAK BOLEH MENGGAGALKAN pencatatan uang** lagi; kegagalan sinkronisasi tetap terdeteksi oleh `AuditIntegritasCommand` (temuan `total_terbayar_tidak_sinkron`).
2. KPI `terbayar` di `DashboardEksekutifService` — di-guard `Schema::hasColumn` (0 bila belum dimigrasi; halaman dashboard tidak pernah 500 karena ini).
3. `orion_pwa_staging_pr21` (3307) — ikut dimigrasi (verifikasi DONE).
4. **Aksi untuk owner**: bila aplikasi XAMPP/3306 memakai DB staging sendiri, kolom `total_terbayar` tidak ada di sana — pembayaran TETAP BISA, hanya kolom mirror diam sampai `php artisan migrate --force` dijalankan owner di sana (agent tidak boleh menjalankan migrasi ke DB runtime yang tidak diminta/tidak bisa diakses).

## W. Skill frontend (item 8) — hasil pemasangan & uji beban

| Skill diminta owner | Sumber efektif | Status |
|---|---|---|
| `frontend-design` | `npx skills add anthropics/skills@frontend-design` | ✓ terpasang `.claude/skills/frontend-design` |
| halaman agenticskills.io/skills/frontend-design | ternyata halaman direktori skill YANG SAMA (induk `anthropics/skills`) | duplikat — sudah tercakup item 1 |
| vercel web design | `npx skills add vercel-labs/agent-skills` | ✓ 9 skill (`web-design-guidelines`, `vercel-react-best-practices`, `vercel-composition-patterns`, `vercel-optimize`, dll.) |
| stageshow | `npx skills add benjitaylor/skills@stageshow` (nama paket benar = `benjitaylor/skills`, bukan repo `stageshow` berdiri sendiri) | ✓ terpasang |
| kubai icons | `npx skills add kubai087/kubai-icons@kubai-icons` | ✓ terpasang |

**Uji beban (permintaan: "sebelum penerapan lakukan pengujian apakah membebankan load halaman")**:

- Seluruh skill terpasang hanya sebagai file panduan agent `.claude/skills/*/SKILL.md` — git-ignored (tidak masuk diff), **nol referensi** di `resources/`, `app/`, `routes/`, `config/` (grep = 0).
- `npm run build` sesudah pemasangan: `app-CHoTb8S4.js 153,63 kB` + `app-CNl8niM_.css 245,69 kB` — hash & ukuran **identik** dengan baseline sebelum skill ada → **TIDAK membebani load halaman** (bukti: nama file ber-hash sama = konten bundle byte-per-byte sama).
- Karena tidak ada perubahan load, semua skill aman diterapkan pada pekerjaan frontend berikutnya (tidak ada aktivasi runtime yang perlu dilepas).



