# AUDIT TOTAL ERP PERCETAKAN

Tanggal: 2026-09-30. Dasar: kode di `main`, database `oriondb` (nyata), `percetakan_erp_testing` (uji), server Apache/MySQL lokal.
Pelengkap: `docs/AUDIT_FASE0.md` (baseline), `anti-slop/audit-001-2026-09-30.md` (12 temuan gaya tulisan/UI).
Legenda: **SELESAI** = diperbaiki + dites. **TERBUKA** = butuh keputusan/tindakan pemilik. **KEEP** = benar, jangan diubah. **UNKNOWN** = tidak bisa dibuktikan dari sini.

## 1. Ringkasan eksekutif
- 7 temuan kritis/tinggi ditutup (rahasia bocor, over-bayar Jersey, stok kembali dobel, nota dobel di POS, eskalasi hak akses, unggahan HTML, FK hilang).
- Alur keuangan yang bolong ditutup: uang nota batal (jadi saldo deposit), serah terima nota belum lunas (wajib alasan), maker-checker pada persetujuan.
- Tes: 433 lulus / 1.997 assertion.
- Yang masih berisiko tinggi **bukan kode**, tetapi operasional: backup otomatis tidak pernah berjalan, MySQL terbuka ke LAN, tidak ada salinan di luar server.

## 2. Matriks cakupan permintaan awal
| Permintaan | Status | Bukti |
|---|---|---|
| Menu Jasa tidak bisa edit/simpan | SELESAI | prefix `jasa-harga`, `finishing-harga`, `setting-harga-tier` belum di `menu_access` (403 fail-closed); dipetakan |
| Jasa Jahit Borongan tidak bisa edit/simpan | SELESAI | `jasa-jahit => [68]`; format qty diperbaiki |
| Landing login gaya opencode | SELESAI (tampilan main dipertahankan sesuai perintah) | `login.blade.php` |
| Sidebar seperti gambar acuan | SELESAI | `layouts/app.blade.php` |
| Logout tidak mengingat halaman terakhir; login ke Dashboard | SELESAI | halaman tujuan lama dibersihkan |
| Database, FK, index | SELESAI | 35 dari ~116 FK ada; 114 dipulihkan via migrasi korektif |
| Stok, reversal, kartu stok | SELESAI + KEEP | snapshot, consume-once; rantai kartu stok konsisten, tidak ada stok negatif |
| Akuntansi | SELESAI | semua jurnal lewat `AccountingService`; debet = kredit; kredit nota batal |
| POS | SELESAI | `sekali.jalan` (anti nota dobel), audit pembayaran |
| Order Jersey (60 pemain, ukuran kustom) | SELESAI | tes HTTP; ukuran dinamis dari master |
| Keamanan (auth, otorisasi, upload, header) | SELESAI | bagian 4 |
| Audit trail | SEBAGIAN | pembayaran, master, 15 model; celah di bagian 6 |
| Concurrency | KEEP | `lockForUpdate` + cek status di dalam transaksi |
| Performa | TERBUKA ringan | bagian 7 |
| Backup/recovery | TERBUKA | restore teruji; penjadwal belum terpasang |
| LAN produksi | TERBUKA | bagian 8 |
| Frontend/keyboard | SEBAGIAN | bagian 9 |
| Tes | SELESAI | bagian 10 |
| Bahasa/UI "tidak generik" | AUDIT SELESAI, eksekusi menunggu pilihan | `audit-001` |
| Pemakaian skill | dipakai | antislop audit, laravel-best-practices |

## 3. Peta alur bisnis
| Alur | Pemicu | Data dibaca | Data ditulis | Stok | Jurnal | Audit | Pelaku | Titik gagal |
|---|---|---|---|---|---|---|---|---|
| Penjualan POS | Kasir simpan nota | produk, harga, BOM | transaksi, detail, snapshot bahan | kurang saat produksi | Dr Piutang/Cr Pendapatan (+HPP) | ya | Kasir | kirim ulang (ditutup `sekali.jalan`) |
| Pembayaran | Kasir bayar | transaksi | pembayaran_riwayat, deposit (bila Deposit) | - | Dr Kas/Bank, Cr Piutang | ya | Kasir | butuh kas aktif milik sendiri |
| Produksi/SPK | Operator | detail | status_spk, kartu_stok | ya | HPP | ya | Operator | selesai lalu batal (dijaga) |
| Collecting | Serah barang | transaksi | pengambilan | - | - | ya | Kasir | belum lunas: wajib alasan |
| Batal nota | Ajukan + setujui | transaksi | status, stok, jurnal balik, deposit kredit | dikembalikan (snapshot) | balik + Dr Piutang/Cr Hutang Deposit | ya | Kasir ajukan, Admin setuju | uang menggantung (ditutup) |
| Edit nota | Permintaan + terapkan | nota_edit_request | nota lama batal | dikembalikan | balik + kredit | ya | Kasir/Admin | sama dengan batal |
| Pembelian/kedatangan | Gudang | bahan, supplier | pembelian_bahan, kartu_stok | naik | Dr Persediaan/Cr Kas/Hutang | ya | Gudang | `id_kedatangan` NOT NULL vs SET NULL (TERBUKA) |
| Order Jersey | Kasir jersey | master jersey | order, line, item, pembayaran | per bahan | JerseyAccountingService | ya | Kasir | over-bayar (ditutup) |
| Deposit | Setoran/pemakaian | konsumen_deposit | ledger + jurnal | - | Dr Kas/Cr Hutang Deposit | ya | Kasir | belum ada pengembalian tunai (TERBUKA) |
| Kas kecil | Pengajuan | - | kas_kecil | - | ya | ya | Kasir/Admin | maker-checker |
| Tutup kas/periode | Admin | jurnal | periode | - | - | ya | Admin | `PeriodeAkuntansiTertutupException` |

## 4. Keamanan
| # | Temuan | Status |
|---|---|---|
| S1 | `.env`/rahasia terbaca via web | SELESAI |
| S2 | Eskalasi hak lewat Data User (`Izin::bolehKelolaRole/User/berikanRole`) | SELESAI |
| S3 | Unggahan HTML menyamar `.pdf` (XSS tersimpan) | SELESAI (`BerkasAman` + `.htaccess`) |
| S4 | Pesan galat teknis bocor ke pengguna | SELESAI (`PesanError`) |
| S5 | Rute tanpa pemetaan menu | KEEP (fail-closed) |
| S6 | Header keamanan/CSP | SELESAI |
| S7 | Login: rate limit, sesi | KEEP |
| S8 | Berkas unggahan dilayani tanpa login (~20 tempat) | TERBUKA: butuh disk privat + rute unduh berizin |
| S9 | MySQL mendengar `0.0.0.0:3306`, akun root tanpa sandi | TERBUKA: `bind-address=127.0.0.1`; akun aplikasi khusus |
| S10 | `APP_ENV=local`, `LOG_LEVEL=debug` | TERBUKA: ubah setelah data uji dibersihkan |
| S11 | Rotasi `APP_KEY` | TERBUKA (memutus sesi/data terenkripsi lama; jadwalkan) |

## 5. Alur yang tidak sesuai ERP profesional (sudah dibenahi)
1. Uang nota batal menggantung -> saldo deposit + jurnal reklasifikasi.
2. Barang belum lunas keluar tanpa jejak -> konfirmasi + alasan (server), tercatat di audit.
3. Pembuat dan penyetuju bisa orang sama -> maker-checker di `ApprovalService`.
4. Harga POS ditimpa tanpa jejak -> price list vs harga dipakai dicatat.
5. Master (bank, golongan) bisa dihapus meski dipakai -> diblokir.

Belum: pengembalian uang tunai dari deposit; penyeragaman metode bayar (`Debit` hanya kasir, `QRIS` hanya Jersey).

## 6. Audit trail: celah tersisa
Sudah: pembayaran POS, master utama, 15 model. Celah kecil (RENDAH): sebagian jalur status SPK/produksi lewat `DB::table` belum memanggil `AuditLogger`, dan `StockOpname` belum diperiksa tuntas. Perbaikan: panggil `AuditLogger::master`/`log` di jalur itu; tanpa perubahan skema.

## 7. Performa
- 49 `paginate()` dari 168 `->get()` di controller. Kandidat: `Jersey/JerseyOrderController:53` (semua konsumen ke dropdown), `KonsumenTimelineController` (semua nota satu konsumen). Risiko sedang saat data ribuan. Perbaikan: pencarian AJAX/pagination.
- OPcache mati, `date.timezone=Europe/Berlin` di php.ini (koneksi DB dikunci `+07:00`, data aman). Perbaikan konfigurasi, bukan kode.
- `EXPLAIN` pada data produksi tidak dilakukan: UNKNOWN untuk query lambat.

## 8. LAN/produksi
`php artisan produksi:cek` (hanya baca): 0 gagal, 7 waspada (penjadwal, backup tanpa salinan luar, bind MySQL, root tanpa sandi, APP_ENV, LOG_LEVEL, OPcache).
**Backup otomatis tidak pernah berjalan.** Pasang: PowerShell Administrator, lalu `powershell -ExecutionPolicy Bypass -File scripts\pasang-penjadwal.ps1`. Restore sudah teruji (105 tabel, 2.687 baris identik).

## 9. Frontend
Klik ganda pada form biasa (bukan POS) bergantung pada penjagaan server: aman, tetapi pengguna bisa melihat pesan "sudah diproses". Rekomendasi (RENDAH): nonaktifkan tombol submit setelah klik secara global. Navigasi keyboard: UNKNOWN (belum diuji manual).

## 10. Matriks tes
| Domain | Tes | Celah |
|---|---|---|
| Keamanan/akses | matriks rute, eskalasi hak, upload, login | - |
| POS/pembayaran | sekali jalan, audit, harga | pembayaran paralel sungguhan (butuh 2 proses) |
| Stok | snapshot, reversal ganda, konversi | multi-gudang tidak ada (desain) |
| Akuntansi | jurnal seimbang, kredit nota batal | tutup periode + koreksi |
| Jersey | HTTP 60 pemain, ukuran, nomor | - |
| Collecting | 4 kasus belum lunas | - |
| Backup | restore diuji manual | belum ada tes otomatis |

## 11. Matriks risiko akhir
| Kelas | Item |
|---|---|
| **MUST FIX** (operasional, pemilik) | pasang penjadwal backup; `bind-address`; salinan backup di luar server |
| **SHOULD FIX** | disk privat untuk unggahan (S8); akun DB khusus; APP_ENV/LOG_LEVEL produksi; pengembalian tunai deposit; keputusan stok per gudang; penyeragaman metode bayar; audit jalur SPK/opname |
| **DEFER** | rotasi APP_KEY; pagination dropdown konsumen; migrasi bertanggal 2026-10 yang terurut di masa depan; `count()+1` referensi pembayaran (dijaga lock nota) |
| **KEEP** | validasi ukuran Jersey dinamis, reversal snapshot, penomoran atomik, fail-closed menu, void via approval, migrasi "duplikat" yang dibungkus `hasTable` |

## 12. Roadmap
| Fase | Isi | Status |
|---|---|---|
| 0 | Baseline, backup, git tag | SELESAI |
| 1 | Rahasia, hak akses, upload, FK, jasa/jahit | SELESAI |
| 2 | Integritas alur: stok, Jersey, POS, batal nota, collecting, maker-checker | SELESAI |
| 3 | Audit trail master + pembayaran | SELESAI (celah kecil bagian 6) |
| 4 | Operasional: penjadwal, bind MySQL, salinan luar | MENUNGGU PEMILIK |
| 5 | Penyimpanan unggahan privat (`BerkasOrderController`, view yang memakai `asset('storage')`; regresi: tampilan gambar) | DIREKOMENDASIKAN |
| 6 | Deposit: pengembalian tunai; metode bayar seragam (migrasi enum + layar; tes akuntansi) | DIREKOMENDASIKAN |
| 7 | Antislop `audit-001` (nomor 1, 2, 10 dulu) | MENUNGGU PILIHAN |
| 8 | Performa data besar + stok per gudang | DITUNDA sampai ada data/keputusan |

## 13. Keputusan yang masih di tangan pemilik
1. Jalankan `pasang-penjadwal.ps1`. 2. `bind-address=127.0.0.1` di `my.ini`. 3. Lokasi salinan backup luar server. 4. Nomor `audit-001` yang dikerjakan. 5. Perlu pengembalian tunai deposit? 6. Stok per gudang atau tetap global? 7. `pembelian_bahan.id_kedatangan`: NOT NULL atau SET NULL? 8. Hapus worktree lama.
