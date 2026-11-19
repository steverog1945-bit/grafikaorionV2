<#
.SYNOPSIS
  ORION Monitor Agent (ERP) - Win7/PowerShell 2.0 compatible.
  Memantau antrean cetak Windows (spooler) via WMI dan melapor ke ERP via HTTP LAN.

.DESCRIPTION
  Dipasang sebagai Windows Scheduled Task (SYSTEM) di PC produksi (Windows 7
  SP1 / PowerShell 2.0). Di Windows 7, cmdlet Get-PrintJob tidak tersedia;
  agent ini membaca Win32_PrintJob via [System.Management.ManagementObjectSearcher].

  Agent kompatibel dengan Windows 7, 8, 10, 11 (WMI tersedia di semua).
  Event yang dikirim:
    - started   : job BARU muncul di spooler
    - completed : job HILANG dari spooler
    - heartbeat : sinyal hidup tiap N detik
#>
[CmdletBinding()]
param(
    [string]$ConfigPath = $null
)

$ErrorActionPreference = 'Stop'
if (-not $ConfigPath) {
    $ConfigPath = Join-Path $env:SystemDrive 'OrionMonitor\agent.config.json'
}
$LogDir = Join-Path $env:ProgramData 'OrionMonitor'
$LogFile = Join-Path $LogDir 'agent.log'

function Write-Log([string]$msg) {
    try {
        if (-not (Test-Path $LogDir)) { New-Item -ItemType Directory -Path $LogDir -Force | Out-Null }
        if ((Test-Path $LogFile) -and ((Get-Item $LogFile).Length -gt 5MB)) {
            Move-Item $LogFile ($LogFile + '.1') -Force
        }
        $line = '{0} | {1}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $msg
        $line | Out-File -FilePath $LogFile -Append -Encoding UTF8
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
    if (-not $cfg.AgentPath) { $cfg | Add-Member -NotePropertyName AgentPath -NotePropertyValue '/api/monitoring-cetak/agent' }
    $cfg.AgentPath = $cfg.AgentPath.TrimEnd('/')
    return $cfg
}

function Post-Json([string]$url, [hashtable]$body) {
    $headers = @{ 'X-Agent-Token' = $script:Cfg.Token; 'Content-Type' = 'application/json' }
    $json = $body | ConvertTo-Json -Compress -Depth 6
    $req = [System.Net.HttpWebRequest]::Create($url)
    $req.Method = 'POST'
    $req.ContentType = 'application/json'
    $req.Timeout = 10000
    foreach ($k in $headers.Keys) { [void]$req.Headers.Add($k, $headers[$k]) }
    $bytes = [System.Text.Encoding]::UTF8.GetBytes($json)
    $req.ContentLength = $bytes.Length
    $stream = $req.GetRequestStream()
    $stream.Write($bytes, 0, $bytes.Length)
    $stream.Close()
    $resp = $req.GetResponse()
    $resp.Close()
}

function Get-PrintJobs([string]$PrinterName) {
    $jobs = @()
    try {
        $jobs = Get-WmiObject -Query "SELECT * FROM Win32_PrintJob WHERE Name LIKE '$PrinterName%'"
    } catch {
        try {
            $jobs = Get-WmiObject -Class Win32_PrintJob -ErrorAction Stop | Where-Object { $_.Name -like "$PrinterName*" }
        } catch {
        }
    }
    return $jobs
}

try {
    $script:Cfg = Read-Config
} catch {
    Write-Log "FATAL: $($_.Exception.Message)"
    exit 1
}

Write-Log "Agent mulai (Win7-compat). Server=$($script:Cfg.Server) Path=$($script:Cfg.AgentPath) Printers=$($script:Cfg.Printers -join ', ')"

# Verifikasi server & token
try {
    Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/hello" @{}
    Write-Log 'Hello OK - token tervalidasi server.'
} catch {
    Write-Log "FATAL: server/token ditolak: $($_.Exception.Message)"
    exit 1
}

$jobs = @{}
$terakhirHeartbeat = [datetime]::MinValue
$gagalBeruntun = 0

while ($true) {
    try {
        $sekarang = Get-Date

        foreach ($printer in $script:Cfg.Printers) {
            $current = @(Get-PrintJobs $printer)

            $currentMap = @{}
            foreach ($j in $current) {
                $jobId = $j.JobId
                if (-not $jobId) { $jobId = $j.Name }
                $key = "$printer|$jobId"
                $currentMap[$key] = $j

                if (-not $jobs.ContainsKey($key)) {
                    $pages = 0
                    try { $pages = [int]$j.TotalPages } catch { }
                    $sizeBytes = 0
                    try { $sizeBytes = [long]$j.Size } catch { }
                    Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/event" @{
                        event      = 'started'
                        printer    = $printer
                        doc_name   = "$($j.Document)"
                        pages_total = $pages
                        pages_done  = 0
                        bytes_total = $sizeBytes
                        bytes_done  = 0
                        detail     = @{ submitted = "$($j.TimeSubmitted)"; job_id = $jobId; status = "$($j.StatusMask)" }
                    }
                    $jobs[$key] = @{ Doc = "$($j.Document)"; Pages = $pages }
                    Write-Log "started: $printer [$jobId] $($j.Document) ($pages hal)"
                }
            }

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
        Start-Sleep -Seconds ([Math]::Min(30, 2 * $gagalBeruntun))
    }
}
