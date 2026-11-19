<#
.SYNOPSIS
  Hapus ORION Monitor Agent dari PC ini. Jalankan sebagai Administrator.
  Tidak menyentuh ERP maupun mesin.
#>
[CmdletBinding()]
param(
    [string]$TaskName = 'OrionMonitorAgent',
    [string]$InstallDir = (Join-Path $env:SystemDrive 'OrionMonitor')
)

$ErrorActionPreference = 'Stop'

schtasks /delete /tn $TaskName /f | Out-Null
Write-Host "Scheduled task '$TaskName' dihapus."

if (Test-Path $InstallDir) {
    Remove-Item $InstallDir -Recurse -Force
    Write-Host "Folder $InstallDir dihapus."
}

Write-Host 'Selesai. Cabut token di server ERP bila Anda mau menggunakannya lagi.'
