# Pemulihan tabel InnoDB yang "doesn't exist in engine" (error 1932)
# untuk database percetakan_erp. Jalankan skrip ini dari PowerShell:
#   cd C:\xampp\htdocs\percetakan-app\backups\db_recovery_20260923
#   powershell -ExecutionPolicy Bypass -File .\run_recovery.ps1
#
# Prosedur per tabel (sudah teruji sukses di tabel "roles"):
#   1. DROP TABLE (hanya hapus entri dictionary, file .ibd fisik TIDAK tersentuh)
#   2. Rename file .ibd asli -> .ibd.orig (amankan dulu)
#   3. CREATE TABLE baru yang kosong (dari skema hasil baca dictionary)
#   4. DISCARD TABLESPACE tabel kosong itu (akan otomatis hapus .ibd kosongnya)
#   5. Rename .ibd.orig -> .ibd (pasang lagi data asli)
#   6. IMPORT TABLESPACE (pasang data asli ke tabel)

$ErrorActionPreference = "Stop"
$mysql = "C:\xampp\mysql\bin\mysql.exe"
$dataDir = "C:\xampp\mysql\data\percetakan_erp"
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

Write-Host "=== STEP 1: DROP TABLE (dictionary only, 70 tabel) ===" -ForegroundColor Cyan
Get-Content "$scriptDir\01_drop.sql" -Raw | & $mysql -u root
Write-Host "Selesai DROP." -ForegroundColor Green

Write-Host "`n=== STEP 2: Amankan file .ibd asli (rename -> .ibd.orig) ===" -ForegroundColor Cyan
$tables = Get-Content "$scriptDir\table_list.txt" | Where-Object { $_.Trim() -ne "" }
foreach ($t in $tables) {
    $src = Join-Path $dataDir "$t.ibd"
    $dst = Join-Path $dataDir "$t.ibd.orig"
    if (Test-Path $src) {
        Move-Item -Path $src -Destination $dst -Force
    } else {
        Write-Warning "File tidak ditemukan (skip): $src"
    }
}
Write-Host "Selesai rename ke .orig." -ForegroundColor Green

Write-Host "`n=== STEP 3: CREATE TABLE kosong (skema dari dictionary) ===" -ForegroundColor Cyan
Get-Content "$scriptDir\02_create.sql" -Raw | & $mysql -u root
Write-Host "Selesai CREATE." -ForegroundColor Green

Write-Host "`n=== STEP 4: DISCARD TABLESPACE (buang tablespace kosong) ===" -ForegroundColor Cyan
Get-Content "$scriptDir\03_discard.sql" -Raw | & $mysql -u root
Write-Host "Selesai DISCARD." -ForegroundColor Green

Write-Host "`n=== STEP 5: Pasang kembali file .ibd asli ===" -ForegroundColor Cyan
foreach ($t in $tables) {
    $src = Join-Path $dataDir "$t.ibd.orig"
    $dst = Join-Path $dataDir "$t.ibd"
    if (Test-Path $src) {
        Move-Item -Path $src -Destination $dst -Force
    } else {
        Write-Warning "File .orig tidak ditemukan (skip): $src"
    }
}
Write-Host "Selesai rename balik." -ForegroundColor Green

Write-Host "`n=== STEP 6: IMPORT TABLESPACE (pasang data asli) ===" -ForegroundColor Cyan
Get-Content "$scriptDir\04_import.sql" -Raw | & $mysql -u root
Write-Host "Selesai IMPORT." -ForegroundColor Green

Write-Host "`n=== VERIFIKASI: hitung baris tiap tabel ===" -ForegroundColor Cyan
foreach ($t in $tables) {
    $result = & $mysql -u root -N -e "SELECT COUNT(*) FROM percetakan_erp.``$t``;" 2>&1
    Write-Host "$t : $result"
}

Write-Host "`n=== SELESAI. Cek di atas apakah ada tabel yang masih error/0 baris yang seharusnya berisi data. ===" -ForegroundColor Yellow
