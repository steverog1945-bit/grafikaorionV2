# Audit P0 — Dependensi, SPK Divisi, Localhost 500, dan Perintah Audit Data Produksi

Tanggal: 17 November 2026
Branch: `feat/min-luas-hybrid-t4a` → push ke `sync/xampp-hybrid-t4a-20261116`
Konteks: kelanjutan audit Visual/Cetak & BOM. Produksi tidak disentuh — audit produksi di bawah INI HANYA PERINTAH SQL TEKS (baca, tanpa tulis) untuk dijalankan owner di phpMyAdmin; bukan dieksekusi oleh agent.

---

## A. Dependensi — hasil audit (tidak mengubah apa pun)

### composer audit: **0 vulnerability** (bersih)

### npm audit: **11 vulnerability** (1 low, 1 moderate, 7 high, 2 critical)

| Paket | Versi | Dipakai | Risiko ringkas |
|---|---|---|---|
| `axios` | ^1.11.0 | **RUNTIME** — `resources/js/bootstrap.js` (`window.axios`) — Laravel scaffold default | ±38 advisory (SSRF NO_PROXY bypass, prototype-pollution gadget, credential/header injection, DoS). Fix tersedia via `npm audit fix` (semver-patch). **Belum diterapkan.** |
| `vite` | ^7.0.7 | build tooling (dev server) — output build tetap dipakai | ±7 advisory (path traversal `.map`, `server.fs.deny` bypass, WebSocket file read, NTLMv2 hash, editor CLI). Fix tersedia. Hanya butuh dev server hidup → risiko produksi rendah. |
| `source-map-js` | 1.0.0–1.2.1 | transitive (build) | event-loop DoS — build-time saja. |
| `shell-quote` via `concurrently` | 9.2.1 | transitive (build) | command injection — build-time saja. |

Semua fix tersedia melalui `npm audit fix` (non-major kemungkinan besar). **Tidak dijalankan per keputusan owner (audit & lapor saja).** Rekomendasi: jalankan `npm audit fix` + `npm run build` + suite test sesudah owner menyetujui — estimasi < 15 menit.

---

## B. Localhost XAMPP `C:\xampp\htdocs\grafikaorionV2` = 500 (sejak 2026-11-07 08:05, 740 kegagalan)

**Bukan bug SPK/Badge — aplikasi tidak bisa membuka koneksi MySQL sama sekali.**

Log (`storage/logs/laravel.log`, dibaca read-only):
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: NO)
(Host: 127.0.0.1, Port: 3306, Database: laravel)
```
- Laravel fallback ke DB `laravel` & user `root` tanpa password — artinya **`.env` tidak terpakai** (DB_* hilang/kosong/tidak terbaca), bukan sekadar password salah.
- `mysqld` 3306 hidup; login TCP root ditolak → owner yang perlu membereskan kredensial (kredensial bukan wewenang/izin agent; `.env` produksi/dev tidak boleh dibaca agent).
- `bootstrap/cache/` hanya packages.php/services.php (tidak ada config cache); `.env` **tidak terlacak git**; `.env.example` ada.
- Semua pekerjaan audit ini berjalan di DB sintetis 3307 & staging :8217; nol sentuhan ke 3306 / `.env`.

**Langkah owner (pilih satu):**
1. Perbaiki `C:\xampp\htdocs\grafikaorionV2\.env` di komputer Anda: `APP_KEY` terisi, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=grafikaorionv2`, `DB_USERNAME=<user Anda>`, `DB_PASSWORD=<password>` → restart Apache → laporkan hasilnya.
2. ATAU arahkan ke DB sintetis 3307 `percetakan_erp_testing` (kosong, boleh untuk uji) — aplikasi hidup tapi datanya bukan data nyata.
3. ATAU salin `.env` dari `C:\xampp\htdocs\grafikaorion\.env` bila Anda memang ingin lokal meniru produksi — hanya Anda sendiri yang boleh melakukannya (agent tidak boleh membaca `.env` produksi).

Setelah hidup: cek lagi halaman `/spk`, badge divisi, dan Tunggakan Aktif — baru bisa dinilai apakah masih ada temuan UI.

---

## C. Analisis badge di SPK/Produksi (temuan + rekomendasi)

Tiga mekanisme menghitung "pekerjaan antre" dengan definisi BERBEDA:

| Mekanisme | Memfilter apa | Tanggal | status_order | status_nota |
|---|---|---|---|---|
| **Tab divisi** (`DivisiService::daftar(m.i), ['Antre'])`) — badge "3"/"1" di tab mesin | status_spk = 'Antre', `status_nota != 'Dibatalkan'` | khususnya **DIABAIKAN** (semua tanggal, termasuk masa depan) | **DIABAIKAN** (termasuk belum "Diterima Kasir") | != Dibatalkan |
| **Lonceng** (`NotifikasiService spk-antre`) | status_spk = 'Antre', `status_nota='Aktif'`, `status_order='Diterima Kasir'` | **DIABAIKAN** (desain sengaja) | = Diterima Kasir | = Aktif |
| **Banner amber "N SPK masih antre di luar rentang"** (`$tertinggal`) | status_spk = 'Antre', `Aktif` + `Diterima Kasir`, TGL_ORDER di luar rentang aktif | luar rentang aktif | = Diterima Kasir | = Aktif |
| Tabel SPK (default) | Antre + **Selesai**, rentang hari ini, `Aktif + Diterima Kasir` | hari ini | = Diterima Kasir | = Aktif |

Konsekuensi yang terlihat oleh user: tab divisi bisa menampilkan 3 & 1, sementara tabel utama / Tunggakan Aktif menampilkan lebih sedikit (atau kosong bila filter divisi aktif / rentang tanggal berbeda / item belum "Diterima Kasir"). Produk tanpa mesin ikut terhitung di tab "Tanpa Divisi" (id 0).

**Rekomendasi (menunggu keputusan owner — tidak diubah sepihak):**

1. Samakan definisi tab divisi dengan lonceng: `Antre + Aktif + Diterima Kasir`, tanpa filter tanggal (badge = pekerjaan yang benar-benar menunggu).
2. Atau buat tab = tabel persis (hari ini, Antre+Selesai) — "badge divisi" jadi hanya pembagi tampilan tabel.
3. Atau dokumentasikan saja (status quo) — tidak diubah.

Tindakan pasti yang bisa saya lakukan tanpa keputusan owner: hanya #B/#C dokumentasi ini; **perubahan semantik badge sengaja TIDAK dilakukan** sampai dipilih.

---

## D. Audit data produksi — diskon hilang saat koreksi (P0 #1)

**Konteks:** sebelum fix, `PenjualanController::updateItemNota()` menulis `total_bayar = subtotal mentah` tanpa mengurangi `transaksi.diskon`, dan jurnal/status sudah disesuaikan. Nota yang pernah dikoreksi lewat alur "koreksi" berpotensi punya `total_bayar` lebih besar dari seharusnya (`subtotal − diskon`).

**Status akses:** agent mencoba koneksi read-only produksi → ditolak (root TCP tanpa password, .env tidak terbaca). **Jadi agent TIDAK bisa membuktikan berapa baris terdampak.** Di bawah perintah untuk dijalankan **owner sendiri** (phpMyAdmin → database produksi → tab SQL):

```sql
-- AMAN: hanya membaca. Tidak menulis apa pun.
SELECT
    t.id_transaksi,
    t.nomor_nota,
    t.diskon,
    t.total_bayar,
    SUM(td.subtotal)                 AS sum_subtotal_items,
    SUM(td.subtotal) - t.diskon      AS total_seharusnya
FROM transaksi t
JOIN transaksi_detail td ON td.id_transaksi = t.id_transaksi
WHERE t.diskon > 0
  AND t.status_nota <> 'Dibatalkan'
GROUP BY t.id_transaksi, t.nomor_nota, t.diskon, t.total_bayar
HAVING ABS(t.total_bayar - (SUM(td.subtotal) - t.diskon)) > 0.50
ORDER BY t.id_transaksi DESC;
```

Catatan tafsir:
- Setiap baris hasil = kandidat nota yang pernah dikoreksi dengan total terlalu besar; **bisa juga false-positive** (nota yang memang pernah disesuaikan manual di luar koreksi) — tinjau manual: nilai `total_bayar > sum_subtotal − diskon` harus bisa dijelaskan; kalau tidak, nota itu terdampak bug.
- Untuk konfirmasi jurnal: selisih yang sama HARUS tercermin di `jurnal_umum` (akun 4000 / akun pendapatan) untuk `no_referensi = nomor_nota` — jurnal yang mencatat pendapatan sebesar subtotal penuh (bukan net) adalah bukti audit. Contoh:

```sql
SELECT no_referensi, kode_akun, SUM(debet) AS total_debet, COUNT(*) AS baris
FROM jurnal_umum
WHERE no_referensi IN (SELECT nomor_nota FROM transaksi WHERE diskon > 0)
  AND kode_akun = '4000'
GROUP BY no_referensi, kode_akun
ORDER BY no_referensi;
```
- Setelah daftar nota terdampak diverifikasi, opsi perbaikan = **entri jurnal koreksi** (Dr Piutang/Beban, Cr Selisih Koreksi) + penyesuaian `total_bayar` per nota — keputusan owner, tidak dieksekusi agent tanpa keputusan tertulis.

---

## E. Ringkasan tindakan P0 yang sudah dilakukan & status

| Butir | Status |
|---|---|
| #2 Tes keamanan/ACL merah (LoginRateLimit/SecAudit/DesainHanyaLihatProduksi) | **SELESAI** — 17/17 hijau, commit `813dc5e` → sync `553b470c` (push) |
| #3 `{!! $post->isi_html !!}` publik disanitasi saat render | **SELESAI** — render ulang `HtmlBersih::bersihkan` + test regresi `SecAuditTest::test_isi_postingan_disanitasi_ketik_render_walau_isi_tersimpan_nakal` |
| #4 composer/npm audit | **LAPOR** — composer 0; npm 11 (axios butuh update) — belum diterapkan |
| #1 Audit produksi diskon nota | **TERBLOKIR** — akses ditolak; perintah SQL disiapkan (bagian D) untuk owner |
| Localhost 500 (bukan bagian P0, ditemukan saat mengecek bug SPK) | **TERIDENTIFIKASI** — root cause DB access denied sejak 2026-11-07; langkah perbaikan di bagian B |
| Badge divisi vs SPK (bukan bagian P0) | **TERDOKUMENTASI** — bagian C; menunggu keputusan owner |

Produksi: TIDAK DISENTUH. Merge/deploy: TIDAK DILAKUKAN.
