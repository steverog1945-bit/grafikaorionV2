# Guard Pembayaran Transfer Wajib Rekening Tujuan — 21 November 2026

## Task

Menelusuri temuan "pembayaran transfer hari ini tanpa menyertakan rekening bank tujuan" dan memasang guard agar tidak terulang. Lingkup: source Laravel di `C:\xampp\htdocs\grafikaorionV2`. Produksi, `.env`, kredensial, database `oriondb`, server/backup produksi tidak diakses. Tidak ada merge/deploy.

## Akar masalah (hasil audit source)

Kondisi ini **bukan celah tak sengaja**, melainkan perilaku yang memang ditulis di beberapa lapis:

| Lapis | Temuan |
|---|---|
| Migration `2026_09_10_060000_add_bank_to_pembayaran_riwayat.php` | `id_bank` sengaja nullable (`nullOnDelete`) karena Tunai tidak relevan dan data lama tidak punya info bank. Tidak ada CHECK constraint untuk `metode = 'Transfer'`. |
| `KasirController::prosesBayar()` (`:441-447`) | Validasi bersyarat: `if ($metode === 'Transfer' && $request->filled('id_bank'))`. Request tanpa field / `id_bank=''` / `0` lolos dan tersimpan dengan `id_bank = NULL`. Tidak ada `$request->validate()`. |
| `form_pembayaran.blade.php` | Hint eksplisit: *"Boleh dikosongkan - transfer dicatat tanpa keterangan rekening."* Saat Master Bank kosong, input `id_bank` tidak dirender sama sekali. `toggleBankCard()` mengizinkan klik ulang membatalkan pilihan. |
| `StagingFixtureSeeder.php`, `IsiDataDemoCommand.php` | Menulis Transfer dengan `id_bank = null` secara eksplisit (fixture) atau bila Master Bank kosong (demo). |
| `KoreksiPembayaranService.php` (`:95-104`) | **Sudah ketat**: koreksi ke Transfer wajib `idBankBaru`. Tes `KoreksiMetodePembayaranTest::test_transfer_wajib_memilih_bank` sudah menjaganya. |
| API | Read-only, tidak ada POST — bukan jalur penyebab. |

Dampaknya: identitas rekening tujuan hilang dari `pembayaran_riwayat`, nota cetak, respons API, dan `AuditLogger`, walaupun jurnal Bank dan `kas_harian.total_transfer` tetap terisi.

## Perubahan

1. **`app/Http/Controllers/Kasir/KasirController.php`** — guard server sebagai otoritas:
   - `Transfer` tanpa `id_bank` (field absen, string kosong, atau `<= 0`) → exception bisnis `Pembayaran Transfer wajib memilih rekening bank tujuan.` (diteruskan apa adanya oleh `PesanError`, transaksi di-rollback).
   - `id_bank` tetap diverifikasi terdaftar di `bank_perusahaan` (id palsu ditolak).
   - Metode lain (Tunai, Debit, QRIS BRI, Deposit) tidak berubah.

2. **`resources/views/kasir/form_pembayaran.blade.php`** — sinkronisasi UI:
   - Hint dan pesan Master Bank kosong tidak lagi membenarkan Transfer tanpa rekening.
   - Helper `getBankTujuan()` / `setBankTujuan()` menangani input yang tidak dirender saat Master Bank kosong (sebelumnya berisiko `TypeError` di jalur QRIS).
   - Guard submit klien menampilkan SweetAlert bila Transfer dipilih tanpa rekening; server tetap memvalidasi ulang.

3. **`database/seeders/StagingFixtureSeeder.php`** — fixture Transfer DP memakai rekening sintetis (dibuat bila Master Bank kosong).

4. **`app/Console/Commands/IsiDataDemoCommand.php`** — Transfer diturunkan ke Tunai bila Master Bank kosong, sehingga demo tidak melanggar invariant.

5. **`tests/TestCase.php`** — helper `bankUji()` untuk menyediakan rekening pada test Transfer.

6. **Test yang memakai `/kasir/proses-bayar` dengan Transfer** (`AlurPenuhOrderTest`, `KasirUbahHargaJurnalTest`, `NotaKoreksiCetakTest`, `PengeluaranPemilikTest`, `RekonsiliasiTotalNotaSusulanTest`, `RollbackAtomikTest`) disesuaikan memakai `bankUji()`.

7. **`tests/Feature/TransferWajibRekeningTest.php`** (baru) — 3 tes regresi:
   - Transfer tanpa field / `''` / `0` ditolak, tanpa jejak pembayaran, jurnal, `total_transfer`, atau perubahan status nota.
   - Transfer dengan rekening terdaftar diterima dan `id_bank` tersimpan.
   - `id_bank` tidak terdaftar ditolak.

Desain guard sengaja ditempatkan di controller (bukan CHECK constraint DB): kolom `id_bank` harus tetap nullable untuk metode lain dan baris lama, serta lingkungan XAMPP/MariaDB tidak menjamin penegakan CHECK constraint. Jalur koreksi sudah lebih dulu ketat dan tidak diubah.

## Validasi

- `git diff --check` — PASS (tanpa whitespace error).
- `php -l` pada 11 file PHP/Blade yang diubah — PASS, tidak ada syntax error.
- `DB_PORT=3307 vendor/bin/phpunit --filter TransferWajibRekeningTest` — **PASS**: 3 tests, 18 assertions (deprecation PHPUnit bawaan saja).
- Test terkait (7 file, termasuk `KoreksiMetodePembayaranTest`) — **PASS**: 39 tests, 275 assertions.
- `--testsuite Feature` penuh — **FAIL baseline**: 1564 tests, 7 errors, 20 failures, 1 risky. Semua kegagalan berada di modul yang tidak disentuh perubahan ini (Jersey, backup, format angka, KelolaKas/KasOpening, QueryCount N+1, upload SVG, JS keranjang, dan isolation guard `DROP TABLE`). Error `PembayaranParalelTest` terjadi di `setUp()` (duplikat konsumen `u-1`) sebelum request pembayaran, jadi tidak berkaitan dengan guard. Perbaikan baseline tersebut di luar scope tugas ini.
- Migrasi/DB produksi: **NOT TESTED / TIDAK DILAKUKAN**. Semua test memakai `percetakan_erp_testing` pada `127.0.0.1:3307` (di-pin oleh `tests/TestCase.php`).

## Status

Implementasi dan validasi terarah selesai. Data historis yang sudah terlanjur tercatat tanpa rekening **tidak dikoreksi** di sini (membutuhkan GO terpisah dan identifikasi nota dari operator). Tidak ada merge, deploy, force-push, atau perubahan PR.
