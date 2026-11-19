# ORION Monitor Agent (ERP) — Paket Instalasi LAN

Tanggal: 19 November 2026
Scope: tools/monitoring-agent/ (repo `percetakan-app`)
Referensi: `docs/reviews/MONITORING_CETAK_ERP.md`

## Apa ini

Agent yang dijalankan **di PC yang men-drive printer** (PC produksi / spooler).
Agent memantau spooler Windows, lalu mengirim event ke server ERP via HTTP LAN:

```
PC produksi  ──HTTP LAN──▶  ERP (http://<host-erp>/api/monitoring-cetak/agent/*)
  spooler                      hello / event / ping (token X-Agent-Token)
```

Tidak butuh internet, tidak butuh database prod di PC, tidak mengubah apa pun
di PC selain Scheduled Task + file di `Program Files\OrionMonitor`.

## Prasyarat server (operator + owner)

1. Server ERP sudah terinstal kode Monitoring Cetak (branch
   `sync/xampp-hybrid-t4a-20261116` / PR #21 + migrasi berhasil).
2. Migrasi dijalankan **sekali** di server ERP: `php artisan migrate --force`
   (additive — tidak menyentuh tabel lama).
3. Mesin sudah didaftarkan: `php artisan monitoring-cetak:seed-machines`;
   **isi** kolom `software` / `pc_ip` / `printer_name` per mesin ERP
   (default seed = `other`).
4. `MONITORING_CETAK_AGENT_ENABLED=true` di env server (termasuk restart web
   server bila PHP-FPM/Apache). Tanpa ini, endpoint agent balas **503** walau
   token benar.

## Langkah per PC produksi (administrator)

Di setiap PC yang men-drive printer:

```powershell
# 1. Buat token untuk mesin yang DIDRIVE PC ini (di server ERP):
php artisan monitoring-cetak:create-agent <monitoring_mesin_id> --nama="PC-PRODUKSI-01"

# 2. Copy folder tools\monitoring-agent\ ke PC itu (USB/network share)

# 3. Di PC itu, PowerShell SEBAGAI ADMINISTRATOR, folder tools\monitoring-agent:
.\install-agent.ps1                                # mode interaktif (tanya Server/Token/Printers)
# ATAU non-interaktif:
.\install-agent.ps1 -Server http://<host-erp> -Token <token> -Printers 'EPSON SC-S80670'
```

Verifikasi (PC mana pun di LAN, atau browser): buka
`http://<host-erp>/monitoring-cetak` — mesin harus muncul **SIAGA** dalam
<1 menit setelah task terdaftar; **CETAK** saat job muncul di spooler.

Log agent: `%ProgramData%\OrionMonitor\agent.log`
Status task: `Get-ScheduledTask -TaskName OrionMonitorAgent`

## Konfigurasi (agent.config.json)

| Key | Default | Keterangan |
| --- | --- | --- |
| Server | `http://<host-erp>` | Base URL server ERP (tanpa path) |
| Token | — | Token plaintext dari `create-agent` (hanya tampil sekali di terminal) |
| Printers | — | Nama printer Windows persis `Get-Printer` (boleh lebih dari satu) |
| AgentPath | `/api/monitoring-cetak/agent` | Untuk standalone monitor lama: `/api/agent` |
| PollSeconds | `2` | Jeda poll spooler |
| HeartbeatSeconds | `30` | Jeda `ping` (status online) |

File config berisi token; ACL file dibatasi `SYSTEM` + `Administrators`.

## Uninstall per PC

```powershell
.\uninstall-agent.ps1            # hapus task + folder
.\uninstall-agent.ps1 -KeepConfig # hanya hapus file agent, pertahankan config
```
ERP/mesin/database **tidak** disentuh.

## Rehearsal (uji coba sebelum produksi)

Bayangkan target akhir = LAN produksi. Uji coba dilakukan di environment
LOKAL yang terisolasi (127.0.0.1 / DB terisolasi `percetakan_erp_testing`
@ 3307), bukan di `oriondb` produksi:

- `php artisan serve --port=8899` dengan `MONITORING_CETAK_AGENT_ENABLED=true`
- Seed mesin + buat agent token → PC/agent tersambung → event `started`,
  `completed`, `progress` tersimpan → dashboard menampilkan mesin CETAK.
- Bukti: `docs/reviews/assets/monitoring-cetak/agent-rehearsal-live.png`
  (EPSON S80670, 14/20 hal, ETA 13:08) dan
  `rehearsal` log: hello/ping/event terbaca HTTP 200, `agent_last_seen` ter-update.

Keterbatasan yang harus dipahami: rehearsal lolos untuk jalur
REST API + dashboard + PowerShell agent. **End-to-end dengan printer fisik
belum diverifikasi** (tidak ada spooler di mesin dev); status CETAK "live"
baru nyata setelah agent dipasang di PC yang men-drive printer produksi.

## Peta rollback

1. `.\uninstall-agent.ps1` di PC produksi (agent berhenti melapor; tidak ada
   perubahan data ERP).
2. Bila perlu mengehentikan seluruh fitur: set `MONITORING_CETAK_AGENT_ENABLED=false`
   (atau hapus env) → endpoint balas 503, dashboard hanya menampilkan antrean SPK
   tanpa status live.
3. Migrasi TIDAK dikembalikan otomatis (additive); bila benar-benar perlu:
   hapus tabel `monitoring_mesins`, `monitoring_agents`, `monitoring_events`
   (tidak ada FK keluar dari tabel-tabel ini; relasi monitoring ke `mesin`
   adalah satu arah).

## Checklist per PC produksi (operasional)

- [ ] `install-agent.ps1` dijalankan sebagai Administrator di PC itu
- [ ] `agent.log` menunjukkan `Hello OK - token tervalidasi server` (atau
      `started: ...` saat job pertama), BUKAN error token/server
- [ ] Dashboard `/monitoring-cetak` menampilkan mesin sesuai (SIAGA/CETAK)
- [ ] `software` / `pc_ip` / `printer_name` mesin ERP sudah diisi
- [ ] Token sudah disalin aman (tidak ditempel di chat/HRC)
- [ ] Rotasi token bila salah salin: `php artisan monitoring-cetak:create-agent <id> --nama=PC-XX`
      (token lama otomatis digantikan; agent lama berhenti 401)
