<#
.SYNOPSIS
  ORION Monitor Agent (Hotfolder RIP) - Windows 7 / PowerShell 2.0
  Pantau file yang masuk ke hotfolder RIP (Flexi/Onyx/dll.) dan laporkan started/completed ke ERP.

.DESCRIPTION
  Tidak menggunakan ConvertFrom-Json. Config: <InstallDir>\agent.config
    Server=http://192.168.1.26/grafikaorionV2
    Token=<token mesin>
    Printers=FLORA OUTDOOR
    AgentPath=/api/monitoring-cetak/agent
    HotPath=C:\RIP\IN        <-- folder hotfolder RIP (survey ke operator RIP)
    PollSeconds=3
    HeartbeatSeconds=30
    SettleSeconds=15
#>
[CmdletBinding()]
param(
    [string]$ConfigPath = $null
)

$ErrorActionPreference = 'Stop'
if (-not $ConfigPath) { $ConfigPath = Join-Path $env:SystemDrive 'OrionMonitor\agent.config' }

$LogDir = Join-Path $env:ProgramData 'OrionMonitor'
$LogFile = Join-Path $LogDir 'hotfolder-agent.log'

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
    foreach ($k in 'Server','Token','Printers','HotPath') {
        if (-not $cfg.ContainsKey($k)) { throw "Config kurang: $k" }
    }
    $cfg.Server = $cfg.Server.TrimEnd('/')
    if (-not $cfg.ContainsKey('AgentPath')) { $cfg.AgentPath = '/api/monitoring-cetak/agent' }
    if (-not $cfg.ContainsKey('PollSeconds')) { $cfg.PollSeconds = '3' }
    if (-not $cfg.ContainsKey('HeartbeatSeconds')) { $cfg.HeartbeatSeconds = '30' }
    if (-not $cfg.ContainsKey('SettleSeconds')) { $cfg.SettleSeconds = '15' }
    $cfg.AgentPath = $cfg.AgentPath.TrimEnd('/')
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

try { $script:Cfg = Read-Config } catch {
    Write-Log "FATAL: $($_.Exception.Message)"
    exit 1
}

if (-not $script:Cfg.HotPath) {
    Write-Log "FATAL: HotPath kosong di config"
    exit 1
}
$hotPath = $script:Cfg.HotPath
if ($hotPath.StartsWith('"') -and $hotPath.EndsWith('"')) { $hotPath = $hotPath.Trim('"') }
if (-not (Test-Path $hotPath)) {
    Write-Log "FATAL: HotPath tidak ada: $hotPath"
    exit 1
}

Write-Log "Hotfolder agent mulai. HotPath=$hotPath Printers=$($script:Cfg.Printers -join ', ')"

try {
    Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/hello" '{}'
    Write-Log 'Hello OK - token tervalidasi server.'
} catch {
    Write-Log "FATAL: server/token ditolak: $($_.Exception.Message)"
    exit 1
}

$state = @{}          # key = "printer|fullpath" -> @{ size; firstSeen; reported }
$terakhirHeartbeat = [datetime]::MinValue
$pollSec = [int]$script:Cfg.PollSeconds
if ($pollSec -lt 1) { $pollSec = 3 }
$heartbeatSec = [int]$script:Cfg.HeartbeatSeconds
if ($heartbeatSec -lt 1) { $heartbeatSec = 30 }
$settleSec = [int]$script:Cfg.SettleSeconds
if ($settleSec -lt 1) { $settleSec = 15 }

while ($true) {
    try {
        $sekarang = Get-Date

        foreach ($printer in $script:Cfg.Printers) {
            $files = Get-ChildItem -Path $hotPath -File -ErrorAction SilentlyContinue |
                Where-Object { $_.Length -gt 0 }

            foreach ($f in $files) {
                $key = "$printer|$($f.FullName)"
                if (-not $state.ContainsKey($key)) {
                    $state[$key] = @{ size = $f.Length; firstSeen = $sekarang; reported = $false }
                }
                $s = $state[$key]
                if ($f.Length -ne $s.size) {
                    $s.size = $f.Length
                    $s.firstSeen = $sekarang
                }
                $umur = ($sekarang - $s.firstSeen).TotalSeconds
                if (-not $s.reported -and $umur -ge $settleSec) {
                    $doc = Json-Escape $f.Name
                    $evt = "{`"event`":`"started`",`"printer`":`"$printer`",`"doc_name`":`"$doc`",`"pages_total`":0,`"pages_done`":0,`"bytes_total`":$($f.Length),`"bytes_done`":0,`"detail`":{}}"
                    try { Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/event" $evt } catch { Write-Log 'POST started gagal' }
                    $s.reported = $true
                    Write-Log "started: $printer $($f.Name) ($($f.Length) bytes)"
                }
            }

            # File yang hilang setelah sebelumnya dilaporkan selesai
            foreach ($key in @($state.Keys)) {
                $parts = $key -split '\|', 2
                if ($parts[0] -ne $printer) { continue }
                $path = $parts[1]
                if (-not (Test-Path $path)) {
                    $s = $state[$key]
                    if ($s.reported) {
                        $doc = Split-Path $path -Leaf
                        $docName = Split-Path $path -Leaf
                        $escdoc = Json-Escape $docName
                        $evt = "{`"event`":`"completed`",`"printer`":`"$printer`",`"doc_name`":`"$escdoc`",`"pages_total`":0,`"pages_done`":0}"
                        try { Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/event" $evt } catch { }
                        Write-Log "completed: $printer $escdoc"
                    }
                    $state.Remove($key)
                }
            }
        }

        if (($sekarang - $terakhirHeartbeat).TotalSeconds -ge $heartbeatSec) {
            try { Post-Json "$($script:Cfg.Server)$($script:Cfg.AgentPath)/ping" '{}' } catch { }
            $terakhirHeartbeat = $sekarang
        }

        Start-Sleep -Seconds $pollSec
    } catch {
        Write-Log "ERROR: $($_.Exception.Message)"
        Start-Sleep -Seconds 10
    }
}

Write-Log 'Agent berhenti tanpa alasan.'
