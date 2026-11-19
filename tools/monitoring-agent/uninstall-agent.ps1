<#
.SYNOPSIS
  Hapus ORION Monitor Agent dari PC ini. Jalankan sebagai Administrator.
  Data ERP tidak disentuh.
#>
#Requires -RunAsAdministrator
[CmdletBinding()]
param(
    [switch]$KeepConfig
)

$ErrorActionPreference = 'Stop'
$taskName = 'OrionMonitorAgent'
$installDir = Join-Path $env:SystemDrive 'OrionMonitor'

$existing = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existing) {
    Stop-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
    Write-Host "Scheduled task '$taskName' dihapus."
} else {
    Write-Host 'Scheduled task tidak ditemukan (mungkin belum pernah dipasang).'
}

if (Test-Path $installDir) {
    if ($KeepConfig) {
        Remove-Item (Join-Path $installDir 'OrionMonitorAgent.ps1') -Force -ErrorAction SilentlyContinue
        Write-Host 'File agent dihapus; agent.config.json DIPERTAHANKAN (-KeepConfig).'
    } else {
        Remove-Item $installDir -Recurse -Force
        Write-Host "Folder $installDir dihapus."
    }
}

Write-Host 'Selesai. Cabut juga token agent-nya di server ERP (php artisan monitoring-cetak:create-agent hanya membuat; rotasi via menu/admin bila diperlukan).' -ForegroundColor Yellow
