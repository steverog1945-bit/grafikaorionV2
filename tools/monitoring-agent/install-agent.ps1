<#
.SYNOPSIS
  Pasang ORION Monitor Agent (ERP) sebagai Scheduled Task SYSTEM.

.CARA PAKAI
  1. Buat token di server ERP: php artisan monitoring-cetak:create-agent <id_mesin> --nama=PC-PRODUKSI-XX
  2. Klik kanan PowerShell -> Run as administrator
  3. .\install-agent.ps1 -Token <token> -Printers '<NAMA PERSIS WMI>'
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

Write-Host '=== ORION Monitor Agent - Instalasi ===' -ForegroundColor Cyan

if (-not $Token) { $Token = Read-Host 'Token agent dari server ERP' }
if ($Printers.Count -eq 0) {
    Get-WmiObject -Class Win32_Printer | Select-Object Name, DriverName, PortName | Format-Table -AutoSize
    $p = Read-Host 'Nama printer Windows PERSIS, pisahkan dengan koma'
    $Printers = $p -split ',' | ForEach-Object { $_.Trim() } | Where-Object { $_ }
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

$acl = Get-Acl $path
$acl.SetAccessRuleProtection($true, $false) | Out-Null
$acl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule('SYSTEM', 'FullControl', 'Allow')))
$acl.AddAccessRule((New-Object System.Security.AccessControl.FileSystemAccessRule('BUILTIN\Administrators', 'FullControl', 'Allow')))
Set-Acl $path $acl

$taskPath = Join-Path $InstallDir 'OrionMonitorAgent.ps1'
schtasks /create /tn $TaskName /tr "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File `"$taskPath`"" /sc onstart /ru SYSTEM /rl HIGHEST /f | Out-Null
schtasks /run /tn $TaskName | Out-Null
Start-Sleep -Seconds 3

Write-Host "Scheduled Task '$TaskName' terdaftar." -ForegroundColor Green
Write-Host "Log agent: $env:ProgramData\OrionMonitor\agent.log"
