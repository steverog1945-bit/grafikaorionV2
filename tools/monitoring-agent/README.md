# ORION Monitor Agent (ERP) — Paket Instalasi LAN

Tanggal: 19 November 2026
Scope: tools/monitoring-agent/ (repo `percetakan-app`)
Referensi: `docs/reviews/MONITORING_CETAK_ERP.md`

CATATAN PENTING: `Server` = **base URL ERP lengkap**, mis.
`http://192.168.1.26/grafikaorionV2` — bukan hanya IP. Path agent
(`AgentPath`) ditempel di belakang Server.

Instalasi default memakai `C:\OrionMonitor` (TANPA spasi di path). Git-bash /
installer versi lama memakai `Program Files` yang memutus argumen `-File`
pada Task Scheduler SYSTEM; jangan dipakai.

Dua jenis agent disertakan, pilih sesuai cara mesin menerima pekerjaan:

| File | Untuk | Cara kerja |
| --- | --- | --- |
| `OrionMonitorAgent.ps1` | Mesin cetak lewat **Windows spooler** (driver printer dipasang di PC) | `Get-PrintJob`, event started/completed + heartbeat |
| `OrionHotfolderAgent.ps1` | Mesin cetak lewat **hotfolder RIP** (Onyx/Flexi/dll; file di-drop ke folder, RIP yang mengolah) | memantau file masuk/hilang di `HotPath`, event started/completed + heartbeat |

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

Di setiap PC yang men-drive printer, langkah umum:

```powershell
# 1. Buat token untuk mesin yang DIDRIVE PC ini (di server ERP):
php artisan monitoring-cetak:create-agent <monitoring_mesin_id> --nama="PC-PRODUKSI-01"

# 2. Copy folder tools\monitoring-agent\ ke PC itu (USB/network share)

# 3. Di PC itu, PowerShell SEBAGAI ADMINISTRATOR, folder tools\monitoring-agent:
.\install-agent.ps1                                # mode interaktif (tanya Server/Token/Printers)
# ATAU non-interaktif:
.\install-agent.ps1 -Server http://<host-erp>/<subpath-erp> -Token <token> -Printers 'EPSON SC-F7200 Series Comm Driver'
```

Verifikasi (PC mana pun di LAN, atau browser): buka
`http://<host-erp>/monitoring-cetak` — mesin harus muncul **SIAGA** dalam
<1 menit setelah task terdaftar; **CETAK** saat job muncul di spooler.

Log agent: `%ProgramData%\OrionMonitor\agent.log`
Status task: `Get-ScheduledTask -TaskName OrionMonitorAgent`

## Contoh: PC 192.168.1.17 (INDOOR + KONICA)

INDOOR = EPSON SureColor S80670 (print share
`EPSON_SureColor_S80670-4_Onyx_RIPCenter_18_EPSON-INDOOR-PC` terlihat di LAN);
KONICA MINOLTA = printer Windows terpasang lokal (tidak perlu dishare). Di PC ini:

```powershell
# Cek nama PERSIS printer Windows:
Get-Printer | Format-Table Name, DriverName, PortName
# lalu tulis agent.config.json:
#   Server     = http://192.168.1.26/grafikaorionV2
#   Token      = <token mesin INDOOR-EPSON>
#   Printers   = ["EPSON SC-S80670"]    <-- NAMA PERSIS dari Get-Printer
#   AgentPath  = /api/monitoring-cetak/agent
```

Untuk KONICA MINOLTA (driver Windows): buat token kedua untuk mesin KONICA,
buat config kedua (mis. `agent.config.mi.json`) dengan
`Printers=["<NAMA PERSIS KONICA>"]`, daftarkan Scheduled Task terpisah
`OrionMonitorAgent-KONICA`. Satu agent tidak bisa memakai dua token;
satu agent boleh `Printers` lebih dari satu bila token sama untuk semua
mesin di PC itu (tidak disarankan).

## Contoh: PC 192.168.1.149 (FLORA OUTDOOR — hotfolder RIP)

FLORA OUTDOOR menerima pekerjaan lewat **hotfolder**, bukan Windows spooler,
jadi agent spooler tidak akan melihat apa pun. Gunakan `OrionHotfolderAgent.ps1`:

```powershell
.\install-agent.ps1   # dan daftarkan OrionHotfolderAgent.ps1 sebagai Scheduled Task
# ATAU buat Scheduled Task sendiri yang menjalankan OrionHotfolderAgent.ps1
# isi agent.config.json:
#   Server     = http://192.168.1.26/grafikaorionV2
#   Token      = <token mesin FLORA OUTDOOR>
#   Printers   = ["FLORA OUTDOOR"]
#   HotPath    = "C:\\RIP\\IN"   <-- GANTI dengan hotfolder RIP yang sebenarnya
#   AgentPath  = /api/monitoring-cetak/agent
```

`HotPath` harus PERSIS folder hotfolder RIP di PC itu (tanyakan ke operator
RIP; bukan folder proofing). File yang ditahan RIP saat diproses dikontrol
`SettleSeconds` (default 15 detik — file harus stabil dulu sebelum dilaporkan).

## Windows 7 / PowerShell 2.0

PC produksi (mis. .17 INDOOR/KONICA, .149 FLORA) diketahui memakai **Windows 7**.
Di Win7, cmdlet `Get-Printer`/`Get-PrintJob` tidak ada; agent versi Win7-compatible
mampu membaca `Win32_PrintJob` via WMI (`OrionMonitorAgent.ps1` kompatibel PS 2.0+),
serta `install-agent-win7.ps1` untuk registrasi task via `schtasks.exe`.

Remote-automation (WinRM, RDP, PsExec) dari server **ditolak** di tiap PC
(`5985/5986/3389 closed`), dan workgroup tanpa akun admin berarti tidak ada
jalur instalasi otomatis dari server ini. Instalasi harus dilakukan manual via
USB di setiap PC:

```powershell
# Cek printer via WMI (pengganti Get-Printer di Win7)
Get-WmiObject Win32_Printer | Select-Object Name, DriverName, PortName

# Scheduled Task sudah di-create untuk SYSTEM; folder bebas dari Program Files:
.\install-agent-win7.ps1 -Token <token mesin> -Printers '<NAMA PERSIS WMI>'
```

## Contoh: PC 192.168.18.4 (CENTURY) — BEDA SUBNET

`192.168.18.4` **tidak merespons** ping/port apa pun dari subnet ini
(192.168.1.x/.26). Artinya agent yang terpasang di sana tetap harus mengakses
ERP di `192.168.1.26`; dari sini kita tidak bisa mengetes. Yang bisa dilakukan:

1. Pasang folder ini di PC CENTURY (USB/network) — langkahnya sama seperti PC
   lain.
2. Pastikan `Test-NetConnection 192.168.1.26 -Port 80` dari PC CENTURY OK
   (koneksi jaringan antara subnet 192.168.18.x dan 192.168.1.x).
3. Jika tidak terjangkau, naikkan ke administrator jaringan — tidak bisa
   diselesaikan dari sisi aplikasi.


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
