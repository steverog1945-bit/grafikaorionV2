<#
.SYNOPSIS
  Pasang ORION Monitor Agent (ERP) sebagai Scheduled Task SYSTEM - mulai otomatis saat boot.
  Jalankan PowerShell SEBAGAI ADMINISTRATOR di setiap PC produksi.

.CARA PAKAI
  1. Buat token di server ERP: php artisan monitoring-cetak:create-agent <id_mesin> --nama=PC-PRODUKSI-XX
  2. Klik kanan PowerShell -> Run as administrator
  3. .\install-agent.ps1
     (atau: .\install-agent.ps1 -Server http://<host-erp> -Token <token> -Printers 'PRINTER1','PRINTER2')

.CATATAN
  Untuk ERP Orion Grafika pakai AgentPath = /api/monitoring-cetak/agent (default).
  Untuk standalone monitor lama, isi: -AgentPath /api/agent
#>
#Requires -RunAsAdministrator
[CmdletBinding()]
param(
    [string]$Server = '',
    [string]$Token = '',
    [string[]]$Printers = @(),
    [string]$AgentPath = '/api/monitoring-cetak/agent',
    [string]$InstallDir = (Join-Path $env:ProgramFiles 'OrionMonitor')
)

$ErrorActionPreference = 'Stop'
Write-Host '=== ORION Monitor Agent (ERP) - Instalasi ===' -ForegroundColor Cyan

if (-not $Server) {
    $Server = Read-Host 'URL server ERP (mis. http://192.168.1.26)'
    if (-not $Server) { $Server = 'http://192.168.1.26' }
}
if (-not $Token) {
    $Token = Read-Host 'Token agent (dari php artisan monitoring-cetak:create-agent)'
}
if ($Printers.Count -eq 0) {
    $inputPrinters = Read-Host 'Nama printer Windows, pisahkan dengan koma (cek: Get-Printer)'
    $Printers = $inputPrinters -split ',' | ForEach-Object { $_.Trim() } | Where-Object { $_ }
}
if ($Printers.Count -eq 0) { throw 'Minimal satu printer harus diisi.' }

# Folder instalasi
if (-not (Test-Path $InstallDir)) { New-Item -ItemType Directory -Path $InstallDir -Force | Out-Null }
Copy-Item (Join-Path $PSScriptRoot 'OrionMonitorAgent.ps1') (Join-Path $InstallDir 'OrionMonitorAgent.ps1') -Force

$config = [ordered]@{
    Server     = $Server.TrimEnd('/')
    Token      = $Token
    Printers   = $Printers
    AgentPath  = "/$($AgentPath.Trim('/'))"
    PollSeconds = 2
    HeartbeatSeconds = 30
}
$configPath = Join-Path $InstallDir 'agent.config.json'
$config | ConvertTo-Json | Set-Content $configPath -Encoding UTF8

# ACL: hanya SYSTEM & Administrators yang boleh baca file config (berisi token)
$acl = Get-Acl $configPath
$acl.SetAccessRuleProtection($true, $false) | Out-Null
$ruleSystem = New-Object System.Security.AccessControl.FileSystemAccessRule('SYSTEM', 'FullControl', 'Allow')
$ruleAdmins = New-Object System.Security.AccessControl.FileSystemAccessRule('BUILTIN\Administrators', 'FullControl', 'Allow')
$acl.AddAccessRule($ruleSystem) | Out-Null
$acl.AddAccessRule($ruleAdmins) | Out-Null
Set-Acl $configPath $acl

# Scheduled Task SYSTEM, mulai saat boot, restart otomatis bila gagal
$taskName = 'OrionMonitorAgent'
$existing = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existing) {
    Stop-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

$action = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument "-NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$InstallDir\OrionMonitorAgent.ps1`""
$trigger = New-ScheduledTaskTrigger -AtStartup
$principal = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest
$settings = New-ScheduledTaskSettingsSet -RestartCount 999 -RestartInterval (New-TimeSpan -Minutes 1) -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -ExecutionTimeLimit ([TimeSpan]::Zero)

Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Settings $settings | Out-Null
Start-ScheduledTask -TaskName $taskName
Start-Sleep -Seconds 3

$state = (Get-ScheduledTask -TaskName $taskName).State
Write-Host ''
Write-Host "Scheduled Task '$taskName' terdaftar. Status: $state" -ForegroundColor Green
Write-Host "Log agent: $env:ProgramData\OrionMonitor\agent.log"
Write-Host ''
Write-Host "Verifikasi: buka http://<host-erp>/monitoring-cetak - mesin harus tampil CETAK/SIAGA dalam <1 menit." -ForegroundColor Yellow
