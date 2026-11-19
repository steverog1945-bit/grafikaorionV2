<#
.SYNOPSIS
  OrionMonitor Agent - Installer (Windows 7 / 10 / 11, PowerShell 2.0+)
  Jalankan sebagai Administrator di PC mesin.

.CARA PAKAI (USB)
  1. Copy folder monitoring-agent ini ke PC (mis. C:\monitoring-agent)
  2. Buka PowerShell sebagai Administrator
  3. Set-ExecutionPolicy RemoteSigned -Scope CurrentUser   (sekali saja)
  4. .\install-agent-win7.ps1

  Default membaca config dari share script:
    Server   = http://192.168.1.26/grafikaorionV2   (base URL ERP - bisa diganti)
    AgentPath = /api/monitoring-cetak/agent
  Token diambil dari interaktif prompt bila tidak diisi - ATAU langsung:
    .\install-agent-win7.ps1 -Token "xxx" -Printers "EPSON SC-..."

  Task terdaftar sebagai SYSTEM di startup.
#>
[CmdletBinding()]
param(
    [string]$Server = 'http://192.168.1.26/grafikaorionV2',
    [string]$Token = '',
    [string[]]$Printers = @(),
    [string]$InstallDir = (Join-Path $env:SystemDrive 'OrionMonitor'),
    [string]$TaskName = 'OrionMonitorAgent'
)

$ErrorActionPreference = 'Stop'

Write-Host '=== ORION Monitor Agent - Instalasi (Win7-compat) ===' -ForegroundColor Cyan

if (-not $Token) { $Token = Read-Host 'Token agent dari server ERP' }
if ($Printers.Count -eq 0) {
    Write-Host 'Printer tidak diisi. Cek daftar printer via WMI:'
    Get-WmiObject -Class Win32_Printer | Select-Object Name, DriverName, PortName | Format-Table -AutoSize
    $inputPrinters = Read-Host 'Nama printer Windows PERSIS, pisahkan dengan koma'
    $Printers = $inputPrinters -split ',' | ForEach-Object { $_.Trim() } | Where-Object { $_ }
}
if ($Printers.Count -eq 0) { throw 'Minimal satu printer harus diisi.' }

if (-not (Test-Path $InstallDir)) { New-Item -ItemType Directory -Path $InstallDir -Force | Out-Null }

# Ambil config contoh, isi dari parameter
$AgentPath = '/api/monitoring-cetak/agent'
$config = [ordered]@{
    Server       = $Server.TrimEnd('/')
    Token        = $Token
    Printers     = @($Printers)
    AgentPath    = $AgentPath
    PollSeconds  = 2
    HeartbeatSeconds = 30
}
$configPath = Join-Path $InstallDir 'agent.config.json'
$config | ConvertTo-Json | Set-Content -Path $configPath -Encoding UTF8

# ACL: hanya SYSTEM & Administrators
$acl = Get-Acl $configPath
$acl.SetAccessRuleProtection($true, $false) | Out-Null
$ruleSystem = New-Object System.Security.AccessControl.FileSystemAccessRule('SYSTEM', 'FullControl', 'Allow')
$ruleAdmin  = New-Object System.Security.AccessControl.FileSystemAccessRule('BUILTIN\Administrators', 'FullControl', 'Allow')
$acl.AddAccessRule($ruleSystem) | Out-Null
$acl.AddAccessRule($ruleAdmin)  | Out-Null
Set-Acl $configPath $acl

Copy-Item (Join-Path $PSScriptRoot 'OrionMonitorAgent.ps1') (Join-Path $InstallDir 'OrionMonitorAgent.ps1') -Force

# Scheduled Task via schtasks (kompatibel Win7, tanpa Register-ScheduledTask)
$taskPath = Join-Path $InstallDir 'OrionMonitorAgent.ps1'
schtasks /create /tn $TaskName /tr "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$taskPath`"" /sc onstart /ru SYSTEM /rl HIGHEST /f | Out-Null
schtasks /run /tn $TaskName | Out-Null
Start-Sleep -Seconds 3

$state = (schtasks /query /tn $TaskName /fo CSV /v | ConvertFrom-Csv).'Status'
Write-Host ''
Write-Host "Scheduled Task '$TaskName' terdaftar. Status: $state" -ForegroundColor Green
Write-Host "Log agent: $env:ProgramData\OrionMonitor\agent.log"
