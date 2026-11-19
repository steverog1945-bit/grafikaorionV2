<#
.SYNOPSIS
  ORION Monitor Agent (ERP) - memantau antrean cetak Windows (spooler) dan melapor
  ke ERP Orion Grafika via HTTP LAN.

.DESCRIPTION
  Dipasang sebagai Windows Scheduled Task (SYSTEM) di PC yang men-drive printer:
  EPSON S80670, FLORA OUTDOOR, CENTURY STAR, RHINO DTF, Roland GR-540, dst.

  Endpoint tujuan: <Server><AgentPath>/hello | /event | /ping
    - Standalone monitor lama: AgentPath =/api/agent
    - Modul Monitoring Cetak di ERP: AgentPath = /api/monitoring-cetak/agent

  Event yang dikirim:
    - started   : job BARU muncul di spooler (nama dokumen, ukuran total, total halaman bila ada)
    - completed : job HILANG dari spooler (dianggap terkirim/selesai dialihkan ke RIP/mesin)
    - heartbeat : sinyal hidup tiap N detik (meng-update "agent online" di dashboard)

  Keterbatasan yang disengaja (v1):
    - Progress halaman REAL-TIME di tengah cetak tidak selalu tersedia dari spooler.
      Dashboard menampilkan bar berjalan tanpa persen pada kasus itu.
    - Job RIP yang dibuffer penuh lalu dicetak berjam-jam tidak lagi terlihat di spooler.
#>
[CmdletBinding()]
param(
    [string]$ConfigPath = (Join-Path $PSScriptRoot 'agent.config.json')
)

$ErrorActionPreference = 'Stop'
$LogDir = Join-Path $env:ProgramData 'OrionMonitor'
$LogFile = Join-Path $LogDir 'agent.log'

function Write-Log([string]$msg) {
    try {
        if (-not (Test-Path $LogDir)) { New-Item -ItemType Directory -Path $LogDir -Force | Out-Null }
        if ((Test-Path $LogFile) -and ((Get-Item $LogFile).Length -gt 5MB)) {
            Move-Item $LogFile ($LogFile + '.1') -Force
        }
        $line = '{0} | {1}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $msg
        Add-Content -Path $LogFile -Value $line
    } catch { }
}

function Read-Config {
    if (-not (Test-Path $ConfigPath)) { throw "Config tidak ditemukan: $ConfigPath" }
    $cfg = Get-Content $ConfigPath -Raw | ConvertFrom-Json
    foreach ($key in 'Server', 'Token', 'Printers') {
        if (-not $cfg.$key) { throw "Config kurang: $key" }
    }
    $cfg.Server = $cfg.Server.TrimEnd('/')
    if (-not $cfg.PollSeconds) { $cfg | Add-Member -NotePropertyName PollSeconds -NotePropertyValue 2 }
    if (-not $cfg.HeartbeatSeconds) { $cfg | Add-Member -NotePropertyName HeartbeatSeconds -NotePropertyValue 30 }
    if (-not $cfg.AgentPath) { $cfg | Add-Member -NotePropertyName AgentPath -NotePropertyValue '/api/agent' }
    $cfg.AgentPath = $cfg.AgentPath.TrimEnd('/')
    return $cfg
}

function Post-Json([string]$url, [hashtable]$body) {
    $headers = @{ 'X-Agent-Token' = $script:Cfg.Token; 'Content-Type' = 'application/json' }
    $json = $body | ConvertTo-Json -Compress -Depth 6
    Invoke-RestMethod -Uri $url -Method Post -Headers $headers -Body $json -TimeoutSec 10 | Out-Null
}

try {
    $script:Cfg = Read-Config
} catch {
    Write-Log "FATAL: $($_.Exception.Message)"
    exit 1
}

Write-Log "Agent mulai. Server=$($script:Cfg.Server) Path=$($script:Cfg.AgentPath) Printers=$($script:Cfg.Printers -join ', ')"

# Verifikasi server & token sekali di awal - gagal cepat dengan log jelas
try {
    Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/hello" @{}
    Write-Log 'Hello OK - token tervalidasi server.'
} catch {
    Write-Log "FATAL: server/token ditolak: $($_.Exception.Message)"
    exit 1
}

$jobs = @{}                 # key: "printer|jobId" -> @{ Doc; Pages }
$terakhirHeartbeat = [datetime]::MinValue
$gagalBeruntun = 0

while ($true) {
    try {
        $sekarang = Get-Date

        foreach ($printer in $script:Cfg.Printers) {
            try {
                $current = Get-PrintJob -PrinterName $printer -ErrorAction Stop
            } catch {
                Write-Log "PERINGATAN: tidak bisa baca spooler '$printer': $($_.Exception.Message)"
                continue
            }

            $currentMap = @{}
            foreach ($j in @($current)) {
                $key = "$printer|$($j.ID)"
                $currentMap[$key] = $j

                if (-not $jobs.ContainsKey($key)) {
                    $pages = 0
                    try { $pages = [int]($j.TotalPages) } catch { }
                    $sizeBytes = 0
                    try { $sizeBytes = [long]($j.Size) } catch { }
                    Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/event" @{
                        event      = 'started'
                        printer    = $printer
                        doc_name   = "$($j.DocumentName)"
                        pages_total = $pages
                        pages_done  = 0
                        bytes_total = $sizeBytes
                        bytes_done  = 0
                        detail     = @{ submitted = "$($j.SubmittedTime)"; job_id = $j.ID; status = "$($j.JobStatus)" }
                    }
                    $jobs[$key] = @{ Doc = "$($j.DocumentName)"; Pages = $pages }
                    Write-Log "started: $printer [$($j.ID)] $($j.DocumentName) ($pages hal)"
                }
            }

            # Job yang hilang dari spooler = selesai/dibatalkan -> laporkan completed
            foreach ($key in @($jobs.Keys)) {
                if (-not $currentMap.ContainsKey($key) -and $key.StartsWith("$printer|")) {
                    $info = $jobs[$key]
                    Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/event" @{
                        event      = 'completed'
                        printer    = $printer
                        doc_name   = $info.Doc
                        pages_total = $info.Pages
                        pages_done  = $info.Pages
                    }
                    Write-Log "completed: $printer $($info.Doc)"
                    $jobs.Remove($key)
                }
            }
        }

        # Heartbeat
        if (($sekarang - $terakhirHeartbeat).TotalSeconds -ge $script:Cfg.HeartbeatSeconds) {
            Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/ping" @{}
            $terakhirHeartbeat = $sekarang
        }

        $gagalBeruntun = 0
        Start-Sleep -Seconds $script:Cfg.PollSeconds
    } catch {
        $gagalBeruntun++
        Write-Log "ERROR ($gagalBeruntun): $($_.Exception.Message)"
        # Backoff saat server ERP tidak terjangkau
        Start-Sleep -Seconds ([Math]::Min(30, 2 * $gagalBeruntun))
    }
}
