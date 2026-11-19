# Monitoring Cetak — Timing Cetakan Tim Produksi di Dalam ERP

Tanggal: 19 November 2026
Branch: `feat/min-luas-hybrid-t4a`
Uji: MariaDB terisolasi `percetakan_erp_testing` @ `127.0.0.1:3307` (sintetis; `.env`/`oriondb`/produksi tidak disentuh)

## Tujuan owner

Aplikasi monitoring cetak berdiri sendiri (`C:\xampp\htdocs\monitor`, Laravel 13
+ agent spooler PowerShell) bisa dipantau timing cetak tim produksi. Permintaan:
terapkan **ke dalam aplikasi ERP**, sesuai tema ERP, uji coba dulu sebelum ke produksi.

Keputusan scope (sudah dikonfirmasi owner): **port penuh termasuk API agent**, dan
monitor berdiri sendiri **digantikan** setelah modul ERP terbukti di LAN produksi.

## Apa yang dibuat

Modul `monitoring-cetak` di dalam ERP — halaman `/monitoring-cetak` plus agent API:

- **Antrean SPK hari ini** (Nota, konsumen, file, produk, mesin, qty, status
  Antre/Desain/Cetak, URGENT duluan) — query sama seperti modul SPK ERP
  (`transaksi_detail` + `transaksi/konsumen/produk/mesin`).
- **Status mesin** dari agent spooler/RIP: CETAK / SIAGA / OFFLINE / TANPA AGENT,
  progress halaman bila RIP melapor, ETA dari median histori job agent (90 hari).
- **ETA sengaja bukan dari klik operator** — timestamp `audit_logs` tidak
  mencerminkan waktu mesin berjalan.
- Kartu bertema ERP: `x-page-header`, `x-table`, `x-badge`, token slate/emerald/
  amber/rose yang sama dengan halaman lain; menu "Monitoring Cetak" di grup
  Produksi & Logistik, aktif bila menu SPK (22) aktif (view + `menu_access`).
- Polling JSON tiap 4 detik ke `monitoring-cetak.status` (middleware `polling`).
- **Akses**: menu SPK (22) untuk tim produksi, menu input-order Operator Desain
  (41 / 42) — dikonfirmasi owner 19 Nov 2026 — dan **Administrator selalu lolos**
  (bypass otomatis di `Izin::bolehPath`, tidak perlu grant).
- Tabel `monitoring_mesins/agents/events` (migration aditif, tidak ada tabel ERP
  yang diubah).
- **Fail-closed**: `/api/monitoring-cetak/agent/*` (hello/event/ping) hanya
  menjawab bila `MONITORING_CETAK_AGENT_ENABLED=true` di server; default **503**
  walau token benar. Route ini sengaja dikecualikan dari `ApiPreparationGate`
  (punya gerbang sendiri: config + header `X-Agent-Token`, hash SHA-256).
- `monitoring-cetak:seed-machines` — daftarkan mesin dari tabel ERP `mesin`
  (idempoten).
- `monitoring-cetak:create-agent {id} --nama=PC-...` — buat/rotasi token agent;
  plaintext hanya tampil sekali.

## File utama

| Bagian | File |
| --- | --- |
| Route web | `routes/web.php` (`/monitoring-cetak`, `/monitoring-cetak/status`) |
| Route agent | `routes/api.php` + `bootstrap/app.php` (alias `monitoring-cetak-agent`) |
| Halaman | `resources/views/monitoring_cetak/index.blade.php` |
| Controller | `app/Http/Controllers/MonitoringCetakController.php` dan `Api/MonitoringCetakAgentController.php` |
| Services | `MonitoringCetakQuery`, `MonitoringCetakEtaService`, `MonitoringCetakService` |
| Model | `MonitoringMachine`, `MonitoringAgent`, `MonitoringEvent` |
| Permission | `config/menu_access.php` (`'monitoring-cetak' => [22]`) |
| Navigation | `resources/views/layouts/app.blade.php` (grup Produksi & Logistik) |

## Bukti uji

`php vendor/phpunit/phpunit/phpunit --filter "MonitoringCetakTest" --testsuite Feature`
→ **9 test / 37 assertion, semua PASS**, menutup:

- halaman butuh login; ditolak 403 untuk role tanpa menu SPK;
- muat untuk tim produksi (judul, mesin, antrean);
- `seed-machines` idempoten;
- agent API **fail-closed 503** sampai `enabled` dinyalakan;
- token salah/hilang → 401; hello/event/ping berfungsi bila diaktifkan;
- ETA dihitung dari pasangan started→completed (median 900 dtk pada fixture);
- event dengan printer tak dikenal jatuh kembali ke mesin milik agent;
- JSON polling: `queue`/`counts`/`machines`/`agents_online` lengkap.

`php artisan view:cache` PASS; `php -l` semua file baru PASS.

Demo visual berjalan di instance 127.0.0.1:8899 dengan DB terisolasi 3307 + data
SINTETIS (konsumen/produk/nota/mock mesin + event agent):

- `docs/reviews/assets/monitoring-cetak/desktop-demo.png`
- `docs/reviews/assets/monitoring-cetak/desktop-antrean.png`
- `docs/reviews/assets/monitoring-cetak/mobile-390.png`
- `docs/reviews/assets/monitoring-cetak/mobile-390-queue.png`
- `demo_seed.tinker.php` — skrip tinker untuk membangun ulang data demo (hanya
  untuk DB terisolasi; token `demo-token-*` pura-pura).

## Cara coba ulang (uji coba, BUKAN produksi)

```powershell
# 1. Migration di DB terisolasi saja
$env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'; $env:DB_DATABASE='percetakan_erp_testing'; ...
php artisan migrate --force
php artisan tinker # jalankan docs/reviews/assets/monitoring-cetak/demo_seed.tinker.php
php artisan serve --port=8899
```

Login: `demoproduksi` / `demo12345` (DB sintetis) → `/monitoring-cetak`.

## Yang BELUM diverifikasi (harus sebelum go-live)

1. **Sesungguhnya di LAN produksi**: koneksi agent spooler di PC produksi
   (Get-Printer-Varian: onyx/flexi/edgeprint/jobcentro/cutter), format payload
   event agent PowerShell dari `htdocs/monitor/agent/OrionMonitorAgent.ps1`
   terhadap endpoint ERP (endpoint payload kompatibel, tapi **belum** diuji
   end-to-end dengan agent asli).
2. **Migrasi produksi** belum dijalankan; tiga tabel baru harus dibuat di DB
   produksi lewat persetujuan operator/owner.
3. **`MONITORING_CETAK_AGENT_ENABLED=true`** belum disetel di mana pun;
   tanpa itu agent mendapat 503.
4. **Deploy/venue**: monitor berdiri sendiri belum diarsipkan; ERP menggantikannya
   hanya setelah modul ERP terbukti stabil di LAN.
5. **Estimasi ETA** belum diukur dengan 30+ job nyata per mesin (baseline testing
   baru fixture sintetis).

## Langkah menuju produksi (GO operator + owner; TIDAK dilakukan di sini)

1. Backup DB produksi.
2. `php artisan migrate --force` di server produksi (additive saja).
3. `php artisan monitoring-cetak:seed-machines`; cek mesin, isi software/pc_ip/
   printer_name per PC produksi.
4. `MONITORING_CETAK_AGENT_ENABLED=true` (env config), restart php-fpm/Apache.
5. `php artisan monitoring-cetak:create-agent <id> --nama=PC-PRODUKSI-XX` per PC;
   isi `X-Agent-Token` di agent PowerShell; ubah base URL agent ke ERP.
6. Pantau 1 minggu bersamaan dengan monitor lama; baru setelah itu monitor
   berdiri sendiri diarsipkan.

## Risiko

- ETA hanya sebaik historinya; mesin yang baru dipasang agent akan menampilkan
  "belum cukup data" (bukan angka palsu).
- Polling 4 detik; di LAN lambat gunakan 6–8 detik.
- `audit_logs` ERP tidak dipakai untuk ETA (by design).
- Tanpa GO, endpoint agent tidak bereaksi — tidak ada jalur login kasir asli
  yang terdampak.

## Status

- Produksi: belum disentuh; migration belum dijalankan di sana.
- Belum merge ke `main`; push ke `sync/xampp-hybrid-t4a-20261116` (branch feat
  menunggu integrasi).
- Demo instance (127.0.0.1:8899) **dihentikan**;
  `percetakan_erp_testing` hanya data sintetis.
