$ErrorActionPreference = "Stop"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "       MAYHEN.VN -> GOOGLE DRIVE SYNC     " -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

$sourceDir = "d:\Project\Laravel\mayhen.vn"
$driveBase = "G:\My Drive"

if (-not (Test-Path $driveBase)) {
    Write-Host "[ERROR] Google Drive is not mounted at G:\. Please check Google Drive for Desktop." -ForegroundColor Red
    exit 1
}

$parentFolder = Get-ChildItem -Path $driveBase -Directory -ErrorAction SilentlyContinue | Where-Object { $_.Name -like "*mayhen*" } | Select-Object -First 1
if (-not $parentFolder) {
    Write-Host "[ERROR] Could not find the May Hen folder in Google Drive." -ForegroundColor Red
    exit 1
}

$destDir = Join-Path $parentFolder.FullName "Project\mayhen.vn"
if (-not (Test-Path $destDir)) {
    Write-Host "[ERROR] Destination directory not found: $destDir" -ForegroundColor Red
    exit 1
}

Write-Host "--> Syncing source code to Google Drive..." -ForegroundColor Yellow
git -C $destDir fetch $sourceDir main --quiet
git -C $destDir reset --hard FETCH_HEAD --quiet

Write-Host "[SUCCESS] Synchronization completed successfully!" -ForegroundColor Green
Write-Host "Destination: $destDir" -ForegroundColor White
Write-Host "Respecting .gitignore: vendor, node_modules, .env, cache, and log files are excluded." -ForegroundColor Gray
