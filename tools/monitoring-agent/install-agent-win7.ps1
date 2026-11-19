<#
.SYNOPSIS
  OrionMonitor Agent - Installer (Windows 7 / PowerShell 2.0+)
  Jalankan sebagai Administrator di PC mesin.

.CARA PAKAI (USB)
  1. Copy folder monitoring-agent ke PC (mis. C:\monitoring-agent)
  2. Buka PowerShell sebagai Administrator
  3. Set-ExecutionPolicy RemoteSigned -Scope CurrentUser   (sekali saja, bila perlu)
  4. .\install-agent-win7.ps1 -Token <token> -Printers '<NAMA PERSIS WMI>'

  Cek daftar printer (Win7): Get-WmiObject Win32_Printer | Select-Object Name, DriverName, PortName

  Config file: C:\OrionMonitor\agent.config (plain key=value; dipanggil OrionMonitorAgent.ps1).
  Token di dapat dari server ERP (php artisan monitoring-cetak:create-agent <id> --nama=PC-XX)
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

Write-Host '=== ORION Monitor Agent - Instalasi (Win7/PS2.0) ===' -ForegroundColor Cyan

if (-not $Token) { $Token = Read-Host 'Token agent dari server ERP' }
if ($Printers.Count -eq 0) {
    Write-Host 'Printer belum diisi. Cek daftar printer via WMI:'
    Get-WmiObject -Class Win32_Printer | Select-Object Name, DriverName, PortName | Format-Table -AutoSize
    $inputPrinters = Read-Host 'Nama printer Windows PERSIS, pisahkan dengan koma'
    $Printers = $inputPrinters -split ',' | ForEach-Object { $_.Trim() } | Where-Object { $_ }
}
if ($Printers.Count -eq 0) { throw 'Minimal satu printer harus diisi.' }

if (-not (Test-Path $InstallDir)) { New-Item -ItemType Directory -Path $InstallDir -Force | Out-Null }

$path = Join-Path $InstallDir 'agent.config'
$content  = "Server=$Server`r`n"
$content += "Token=$Token`r`n"
$content += "Printers=" + ($Printers -join ',') + "`r`n"
$content += "AgentPath=/api/monitoring-cetak/agent`r`n"
$content += "PollSeconds=2`r`n"
$content += "HeartbeatSeconds=30`r`n"
Set-Content -Path $path -Value $content -Encoding ASCII
Copy-Item (Join-Path $PSScriptRoot 'OrionMonitorAgent.ps1') (Join-Path $InstallDir 'OrionMonitorAgent.ps1') -Force

# ACL: hanya SYSTEM & Administrators
$acl = Get-Acl $path
$acl.SetAccessRuleProtection($true, $false) | Out-Null
$ruleSystem = New-Object System.Security.AccessControl.FileSystemAccessRule('SYSTEM', 'FullControl', 'Allow')
$ruleAdmin  = New-Object System.Security.AccessControl.FileSystemAccessRule('BUILTIN\Administrators', 'FullControl', 'Allow')
$acl.AddAccessRule($ruleSystem) | Out-Null
$acl.AddAccessRule($ruleAdmin)  | Out-Null
Set-Acl $path $acl

$taskPath = Join-Path $InstallDir 'OrionMonitorAgent.ps1'
schtasks /create /tn $TaskName /tr "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$taskPath`"" /sc onstart /ru SYSTEM /rl HIGHEST /f | Out-Null
schtasks /run /tn $TaskName | Out-Null
Start-Sleep -Seconds 3

Write-Host ''
Write-Host "Scheduled Task '$TaskName' terdaftar." -ForegroundColor Green
Write-Host "Log agent: $env:ProgramData\OrionMonitor\agent.log"
