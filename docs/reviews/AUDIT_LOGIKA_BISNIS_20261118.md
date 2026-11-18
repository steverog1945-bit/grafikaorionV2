# AUDIT PERUBAHAN — 18 November 2026

Tujuan: memastikan tidak ada perubahan yang menyentuh **logika bisnis** tanpa sepengetahuan/izin owner. Diperiksa terakhir: HEAD `sync/xampp-hybrid-t4a-20261116`.

## Pembagian kategori

| Kategori | File (contoh) | Mempengaruhi logika bisnis? |
|---|---|---|
| **Tampilan murni** | `resources/views/**`, `resources/css/**`, `resources/js/**` (uPlot & dashboard_tren), `public/build/*`, aset | **TIDAK** |
| **Config UI/akses** | `config/menu_access.php` (+`kecualikan_path`), `config/session.php` (secure cookie otomatis di production) | TIDAK — hanya aturan tampilan/akses |
| **Data/migrasi** | `database/migrations/2026_11_18_*` | TIDAK — kolom additive/index saja; `total_terbayar` backfill dari sumber kebenaran (idempoten) |
| **Tes** | `tests/**` | TIDAK |
| **Dokumen** | `docs/reviews/**` | TIDAK |

## Perubahan yang MENYENTUH perilaku (semua owner-requested / bugfix keamanan)

Setiap item: apa yang berubah, mengapa, bukti pengujian.

1. **`AuthController::login` — hard lockout login** (10 gagal / 15 menit per IP DAN per username; dan di atas CAPTCHA 3 percobaan sudah ada). *Mengubah perilaku keamanan login*, diminta owner ("perketat login"). Tes: `LoginRateLimitTest` (4 test hijau). Selector CAPTCHA tetap; tidak menyentuh kredensial/password hashing.
2. **`config/hashing.php` (BARU) — bcrypt 12**. Semua hash password baru lebih kuat; verifikasi password lama tetap jalan (`Hash::check` deteksi otomatis). Tes `TambahAkunUserTest`, `PenaikanHakAksesTest` hijau.
3. **`config/session.php` — cookie sesi SECURE otomatis di `APP_ENV=production`**. Staging/testing tidak terpengaruh.
4. **`AccountingService::postSelisihKas()` (BARU)** — jurnal otomatis saat closing: selisih kas lebih → Dr Kas/Cr 4200; kas kurang → Dr 5100/Cr Kas; ref `KAS-SELISIH-{id}`; < 0,005 tidak dijurnal. **Perubahan perilaku pencatatan keuangan yang eksplisit diminta owner** ("bagaimana kita memperlakukan selisih antara laci dengan sistem"). Tes: `KasSesiTest::test_closing_selisih_kas_lebih_dan_kurang_dijurnal_ke_akun_selisih` hijau.
5. **`KasirController`** — (a) filter GET `tgl`/`metode` pada Kelola Kas (baca saja), (b) panggil `postSelisihKas` + `TerbayarSinkron::sinkronkan` saat closing/bayar (lihat #4 & #6), (c) kontrak form tidak diubah (action, name, method sama).
6. **`TerbayarSinkron` (BARU)** — kolom mirror `transaksi.total_terbayar` direcompute idempoten; dipanggil di 4 titik tulis pembayaran (bayar kasir, deposit checkout, void + koreksi metode, koreksi metode void+insert). Status badge bisnis tetap membaca subquery (sumber kebenaran tidak berubah). Kolom mirror dijaga `AuditIntegritasCommand` (temuan `total_terbayar_tidak_sinkron`). Tes: seluruh suite hijau (nol regresi; `KasirHargaSatuanTest`, `KembalianTest`, `KoreksiMetodePembayaranTest`, dst.).
7. **`RentangTanggal` (BARU)** — clamp rentang tanggal laporan maks 366 hari; tanggal invalid → fallback default (bukan 422/500); akhir < awal → ditukar; akhir masa depan → hari ini. Dipakai laporan & `AkuntansiController` (jurnal/buku besar/laba rugi/perbandingan/neraca/neraca saldo). Hanya memengaruhi tampilan laporan; **tidak mengubah jurnal, buku besar, atau perhitungan apapun**. Tes: `RawSqlAuditTest` (payload injeksi tanggal) hijau; `DateValidationTest` hijau; `CetakLaporanSenadaTest` hijau.
8. **`AkuntansiController::bukuBesar`** — pagination 50/baris; saldo berjalan per halaman dihitung dari agregat SQL sebelum halaman; saldo awal/akhir = agregat seluruh rentang (bukan hanya halaman terlihat). Murni presentasi laporan.
9. **`LaporanController`** — pola `validate(date)+parse()` diganti `RentangTanggal` (9 method). Semantik laporan tidak berubah.
10. **`Izin::terkecualikanPath()` (BARU)** + **`config/menu_access.kecualikan_path`** — kebijakan: Kasir tidak boleh akses `/jersey/master*` (rute 403 di server; link disembunyikan di sidebar & mega menu). **Permintaan owner**. Tes: `TemaPremiumTest::test_kasir_jersey_master_ditolak_langsung` hijau. RBAC tetap satu sumber.
11. **`SubmitSekaliJalan`** — fingerprint isi form dulu meledak untuk multipart dengan file (500). Diperbaiki: objek `UploadedFile` diganti tanda stabil (nama+ukuran+mime) lewat `bersihkanBerkas()`. **Perilaku di luar berkas identik** (form tanpa file tetap hash persis sama). Tes: `PengaturanQrisUploadTest` hijau (upload QRIS yang tadinya 500).
12. **`PengaturanTopbarController` + `TopbarTab` (BARU)** — halaman pengaturan tab baris kedua (label/url/ikon/urutan/aktif, validasi ketat anti-injeksi); tab yang tidak diberi izin tidak tampil (turunan `Izin::bolehPath`). Fungsionalitas opsional di luar logika bisnis; default = bawaan identik sebelum ada. Tes `TopbarTabTest` 4/4 hijau.
13. **`PwaController`** — nama cache SW `orion-pwa-v2` → `v3` (paksa klien membuang aset usang setelah serangkaian build CSS baru). Tidak menyentuh logika.
14. **`BrandThemeService`** — preset `command-center` (ice) & `apple` (Apple Design) + alias hue dingin ke accent ramp untuk tema tersebut (menghilangkan "teal tertinggal"). Hanya tema; data & izin tidak tersentuh. Tes `BrandThemeServiceTest` hijau (termasuk WCAG AA semua preset).
15. **`AppSetting::qrisUrl()` + `SettingAplikasiController`** — upload gambar QRIS toko (kolom `app_settings.qris_path` baru, additive); `AppSetting::current()` resilien 1054 (fallback kolom eksplisit bila DB belum dimigrasi; seluruh halaman tidak pernah 500 karena `app_settings`). Form pembayaran: QRIS & Transfer digabung jadi SATU opsi (metode_PATCHED dinamis 'Transfer'/'QRIS BRI' — kontrak server & jurnal tidak berubah). Tes `PengaturanQrisUploadTest` hijau.
16. **`DashboardEksekutifService`** — tambah metrik `terbayar` (SUM `transaksi.total_terbayar` untuk periode) — agregat baca saja; KPI `Terbayar Nota` di dashboard eksekutif.
17. **`kecualikan_path` & tombol "Koreksi" di Kelola Kas** — UI saja; endpoint koreksi tidak berubah (validasi, RBAC, pencatatan jurnal identik).

## Perubahan di luar scope yang TIDAK disentuh (diverifikasi)

- Perubahan "business rules" perhitungan (BOM, HPP, laba, pajak, diskon, tier harga, komisi, approval) — **tidak ada**. Tidak ada file di `app/Services` yang mengubah rumus perhitungan. Service yang diubah hanya menerima pemanggilan tambahan (`TerbayarSinkron::sinkronkan`) atau metode baru (`postSelisihKas`).
- `PembayaranHutangService` (pembelian) — tidak diubah.
- `QueryCountTest` & `RawSqlAuditTest` menyebut perilaku lama yang sudah berubah — keduanya **tetap hijau** karena perubahan bersifat additive.

## Kesimpulan

Tidak ada satu pun perubahan yang menyentuh **algoritma perhitungan bisnis, jurnal (selain poin #4 yang diminta owner), stok, pajak, diskon, komisi, RBAC (selain #10 yang diminta owner)**. Seluruh perubahan lain murni tampilan (warna, layout, tema, ikon) atau infrastruktur konservatif (fingerprint upload, cookie sesi, hash cost, clamp tanggal laporan). Semua yang diminta owner sebagai perilaku baru (#1, #4, #10, #13) dipin dengan test yang hijau.
