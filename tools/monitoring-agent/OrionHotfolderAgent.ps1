<#
.SYNOPSIS
  ORION Monitor Agent (Hotfolder RIP) - pantau *file print* yang masuk ke
  hotfolder RIP (mis. Onyx / Flexi / Fiery), bukan Windows spooler.

.DESCRIPTION
  Mesin yang menerima pekerjaan lewat hotfolder (bukan driver printer Windows
  langsung) tidak terlihat oleh Get-PrintJob. Agent ini memantau folder
  hotfolder RIP; event yang dikirim ke ERP:

    - started   : file BARU muncul di hotfolder (job masuk ke antrean RIP)
    - completed : file HILANG dari hotfolder (diambil/diproses RIP selesai)
    - heartbeat : sinyal hidup tiap N detik saat scan

  Konfigurasi: agent.config.json di folder yang sama:
  {
    "Server": "http://192.168.1.26",          # base URL ERP (tanpa path)
    "Token": "<token dari create-agent>",      # token ERP /api/monitoring-cetak/agent
    "AgentPath": "/api/monitoring-cetak/agent",
    "Printers": ["FLORA OUTDOOR"],             # nama mesin (label di dashboard)
    "HotPath": "C:\\RIP\\IN",                  # hotfolder lokal atau UNC (butuh akses)
    "PollSeconds": 3,
    "HeartbeatSeconds": 30,
    "SettleSeconds": 15                        # file harus stabil sebelum dianggap "started"
  }

.CATATAN
  Agent ini didesain untuk hotfolder yang "file kosong dihapus saat selesai".
  Kalau hotfolder Anda men-KARANTINA file olahan (bukan menghapus), pakai
  pola: file baru = started; file PINDAH ke folder olahan = completed.
#>
[CmdletBinding()]
param(
    [string]$ConfigPath = (Join-Path $PSScriptRoot 'agent.config.json')
)

$ErrorActionPreference = 'Stop'
$LogDir = Join-Path $env:ProgramData 'OrionMonitor'
$LogFile = Join-Path $LogDir 'hotfolder-agent.log'

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
    foreach ($key in 'Server', 'Token', 'Printers', 'HotPath') {
        if (-not $cfg.$key) { throw "Config kurang: $key" }
    }
    $cfg.Server = $cfg.Server.TrimEnd('/')
    if (-not $cfg.AgentPath) { $cfg | Add-Member -NotePropertyName AgentPath -NotePropertyValue '/api/monitoring-cetak/agent' }
    if (-not $cfg.PollSeconds) { $cfg | Add-Member -NotePropertyName PollSeconds -NotePropertyValue 3 }
    if (-not $cfg.HeartbeatSeconds) { $cfg | Add-Member -NotePropertyName HeartbeatSeconds -NotePropertyValue 30 }
    if (-not $cfg.SettleSeconds) { $cfg | Add-Member -NotePropertyName SettleSeconds -NotePropertyValue 15 }
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

if (-not (Test-Path $script:Cfg.HotPath)) {
    Write-Log "FATAL: HotPath tidak ada: $($script:Cfg.HotPath)"
    exit 1
}

Write-Log "Hotfolder agent mulai. HotPath=$($script:Cfg.HotPath) Printers=$($script:Cfg.Printers -join ', ')"

# Verifikasi server & token
try {
    Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/hello" @{}
    Write-Log 'Hello OK - token tervalidasi server.'
} catch {
    Write-Log "FATAL: server/token ditolak: $($_.Exception.Message)"
    exit 1
}

# state: path -> @{ size; firstSeen; reported }
$state = @{}
$terakhirHeartbeat = [datetime]::MinValue

while ($true) {
    try {
        $sekarang = Get-Date

        foreach ($printer in $script:Cfg.Printers) {
            $files = Get-ChildItem -Path $script:Cfg.HotPath -File -ErrorAction Stop |
                Where-Object { $_.Extension -match '\.(ps|pcl|prn|eps|pdf|jpg|jpeg|tif|tiff|cdr)$' -or $_.Length -gt 0 }

            foreach ($f in $files) {
                $key = $printer + '|' + $f.FullName
                if (-not $state.ContainsKey($key)) {
                    $state[$key] = @{ size = $f.Length; firstSeen = $sekarang; reported = $false }
                }
                $s = $state[$key]
                # Stabil = ukuran tidak berubah sejak pertama dilihat
                if ($f.Length -ne $s.size) {
                    $s.size = $f.Length; $s.firstSeen = $sekarang
                }
                $umur = ($sekarang - $s.firstSeen).TotalSeconds
                if (-not $s.reported -and $umur -ge [double]$script:Cfg.SettleSeconds) {
                    Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/event" @{
                        event      = 'started'
                        printer    = $printer
                        doc_name   = $f.Name
                        pages_total = 0
                        pages_done  = 0
                        bytes_total = $f.Length
                        bytes_done  = 0
                        detail     = @{ hotpath = $script:Cfg.HotPath; size = $f.Length; firstSeen = $s.firstSeen.ToString('s') }
                    }
                    $s.reported = $true
                    Write-Log "started: $printer $($f.Name) ($($f.Length) bytes)"
                }
            }

            # File hilang setelah sebelumnya dilaporkan = selesai/diproses
            foreach ($key in @($state.Keys)) {
                if (-not $key.StartsWith("$printer|")) { continue }
                $path = ($key -split '\|', 2)[1]
                if (-not (Test-Path $path)) {
                    $s = $state[$key]
                    if ($s.reported) {
                        Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/event" @{
                            event      = 'completed'
                            printer    = $printer
                            doc_name   = (Split-Path $path -Leaf)
                            pages_total = 0
                            pages_done  = 0
                            detail     = @{ hotpath = $script:Cfg.HotPath }
                        }
                        Write-Log "completed: $printer $((Split-Path $path -Leaf))"
                    }
                    $state.Remove($key)
                }
            }
        }

        # Heartbeat
        if (($sekarang - $terakhirHeartbeat).TotalSeconds -ge $script:Cfg.HeartbeatSeconds) {
            Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/ping" @{}
            $terakhirHeartbeat = $sekarang
        }

        Start-Sleep -Seconds $script:Cfg.PollSeconds
    } catch {
        Write-Log "ERROR: $($_.Exception.Message)"
        Start-Sleep -Seconds 10
    }
}
