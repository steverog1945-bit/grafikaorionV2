<#
.SYNOPSIS
  ORION Monitor Agent (ERP) - kompatibel Windows 7 / PowerShell 2.0 / .NET 3.5

.DESCRIPTION
  Pantau spooler Windows (Win32_PrintJob) dan laporkan started/completed ke ERP.
  Tanpa menggunakan ConvertFrom-Json / ConvertTo-Json (tidak ada di PS 2.0).
  Config pakai format key=value sederhana; event dikirim sebagai JSON rakit manual.

  Kirim log ke: <Server><AgentPath>/hello | /event | /ping
#>
[CmdletBinding()]
param(
    [string]$ConfigPath = $null
)

$ErrorActionPreference = 'Stop'
if (-not $ConfigPath) { $ConfigPath = Join-Path $env:SystemDrive 'OrionMonitor\agent.config' }

$LogDir = Join-Path $env:ProgramData 'OrionMonitor'
$LogFile = Join-Path $LogDir 'agent.log'

function Write-Log([string]$msg) {
    try {
        if (-not (Test-Path $LogDir)) { New-Item -ItemType Directory -Path $LogDir -Force | Out-Null }
        if ((Test-Path $LogFile) -and ((Get-Item $LogFile).Length -gt 5MB)) {
            Move-Item $LogFile ($LogFile + '.1') -Force
        }
        $line = '{0} | {1}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $msg
        Add-Content -Path $LogFile -Value $line -Encoding UTF8
    } catch { }
}

function Read-Config {
    if (-not (Test-Path $ConfigPath)) { throw "Config tidak ditemukan: $ConfigPath" }
    $raw = Get-Content $ConfigPath
    $cfg = @{}
    foreach ($l in $raw) {
        $l = ($l -replace '\s+$','')
        if ($l -eq '' -or $l.StartsWith('#')) { continue }
        $i = $l.IndexOf('=')
        if ($i -lt 1) { continue }
        $key = $l.Substring(0, $i).Trim()
        $val = $l.Substring($i + 1).Trim()
        $cfg[$key] = $val
    }
    foreach ($k in 'Server','Token','Printers') {
        if (-not $cfg.ContainsKey($k)) { throw "Config kurang: $k" }
    }
    $cfg.Server = $cfg.Server.TrimEnd('/')
    if (-not $cfg.ContainsKey('PollSeconds')) { $cfg.PollSeconds = '2' }
    if (-not $cfg.ContainsKey('HeartbeatSeconds')) { $cfg.HeartbeatSeconds = '30' }
    if (-not $cfg.ContainsKey('AgentPath')) { $cfg.AgentPath = '/api/monitoring-cetak/agent' }
    $cfg.AgentPath = $cfg.AgentPath.TrimEnd('/')
    # Printers, dipisah dengan koma atau pipe; spasi di sekitar dipotong
    $printers = @()
    foreach ($p in ($cfg.Printers -split '[,|]')) {
        $t = $p.Trim()
        if ($t) { $printers += $t }
    }
    $cfg.Printers = $printers
    return $cfg
}

function Json-Escape([string]$s) {
    if ($null -eq $s) { return '' }
    return $s.Replace('\','\\').Replace('"','\"').Replace("`r",'').Replace("`n",'')
}

function Json-Obj($kv) {
    $b = ''
    foreach ($k in $kv.Keys) {
        $v = $kv[$k]
        if ($v -is [int] -or $v -is [long] -or $v -is [double]) {
            $b += ('"{0}":{1},' -f $k, $v)
        } else {
            $b += ('"{0}":"{1}",' -f $k, (Json-Escape "$v"))
        }
    }
    return '{' + $b.TrimEnd(',') + '}'
}

function Post-Json([string]$url, [string]$json) {
    $req = [System.Net.HttpWebRequest]::Create($url)
    $req.Method = 'POST'
    $req.ContentType = 'application/json'
    $req.Timeout = 10000
    $req.Headers.Add('X-Agent-Token', $script:Cfg.Token)
    $bytes = [System.Text.Encoding]::UTF8.GetBytes($json)
    $req.ContentLength = $bytes.Length
    $s = $req.GetRequestStream()
    $s.Write($bytes, 0, $bytes.Length)
    $s.Close()
    $resp = $req.GetResponse()
    $resp.Close()
}

function Get-PrintJobs([string]$PrinterName) {
    try {
        $jobs = @()
        $all = Get-WmiObject -Query 'SELECT * FROM Win32_PrintJob'
        if ($all) {
            foreach ($j in $all) {
                if ($j.Name -like "$PrinterName*") { $jobs += $j }
            }
        }
        return $jobs
    } catch {
        return @()
    }
}

try { $script:Cfg = Read-Config } catch {
    Write-Log "FATAL: $($_.Exception.Message)"
    exit 1
}

Write-Log "Agent mulai (PS2.0/Win7). Server=$($script:Cfg.Server) Path=$($script:Cfg.AgentPath) Printers=$($script:Cfg.Printers -join ', ')"

# Verifikasi server & token
try {
    Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/hello" '{}'
    Write-Log 'Hello OK - token tervalidasi server.'
} catch {
    Write-Log "FATAL: server/token ditolak: $($_.Exception.Message)"
    exit 1
}

$pollSec = [int]$script:Cfg.PollSeconds
if ($pollSec -lt 1) { $pollSec = 2 }
$heartbeatSec = [int]$script:Cfg.HeartbeatSeconds
if ($heartbeatSec -lt 1) { $heartbeatSec = 30 }

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
                    $doc = Json-Escape ([string]$j.Document)
                    $evt = (
                        '{"event":"started","printer":"' + $printer + '","doc_name":"' + $doc + '","pages_total":' + $pages + ',"pages_done":0,"bytes_total":' + $sizeBytes + ',"bytes_done":0,"detail":{"job_id":"' + ($jobId -replace '"','\"') + '"}}'
                    )
                    try { Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/event" $evt } catch { Write-Log 'POST event started gagal' }
                    $jobs[$key] = @{ Doc = [string]$j.Document; Pages = $pages }
                    Write-Log "started: $printer [$jobId] $($j.Document) ($pages hal)"
                }
            }

            foreach ($key in @($jobs.Keys)) {
                if (-not $currentMap.ContainsKey($key) -and $key.StartsWith("$printer|")) {
                    $info = $jobs[$key]
                    $doc = Json-Escape ([string]$info.Doc)
                    $pages = [int]$info.Pages
                    $evt = '{"event":"completed","printer":"' + $printer + '","doc_name":"' + $doc + '","pages_total":' + $pages + ',"pages_done":' + $pages + '}'
                    try { Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/event" $evt } catch { }
                    Write-Log "completed: $printer $($info.Doc)"
                    $jobs.Remove($key)
                }
            }
        }

        if (($sekarang - $terakhirHeartbeat).TotalSeconds -ge $heartbeatSec) {
            try { Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/ping" '{}' } catch { }
            $terakhirHeartbeat = $sekarang
        }

        $gagalBeruntun = 0
        Start-Sleep -Seconds $pollSec
    } catch {
        $gagalBeruntun++
        Write-Log "ERROR ($gagalBeruntun): $($_.Exception.Message)"
        Start-Sleep -Seconds 10
    }
}
