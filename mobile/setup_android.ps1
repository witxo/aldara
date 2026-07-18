# Run this script after installing Flutter SDK
# It generates the Android platform files needed to build the APK

$mobileDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $mobileDir

Write-Host "=== CheckIn Mobile - Android Setup ===" -ForegroundColor Cyan

# 1. Check flutter
$flutter = Get-Command flutter -ErrorAction SilentlyContinue
if (-not $flutter) {
    Write-Host "ERROR: Flutter not found. Install from https://docs.flutter.dev/get-started/install/windows" -ForegroundColor Red
    exit 1
}

Write-Host "Flutter found: $(flutter --version | Select-Object -First 1)" -ForegroundColor Green

# 2. Backup custom files that flutter create might overwrite
$backupDir = Join-Path $mobileDir "android_backup"
if (Test-Path $mobileDir\android) {
    Write-Host "Backing up custom Android files..." -ForegroundColor Yellow
    if (-not (Test-Path $backupDir)) { New-Item -ItemType Directory -Path $backupDir -Force | Out-Null }
    Copy-Item "$mobileDir\android\app\src\main\AndroidManifest.xml" "$backupDir\AndroidManifest.xml" -Force
    Copy-Item "$mobileDir\android\app\build.gradle" "$backupDir\app_build.gradle" -Force
    Copy-Item "$mobileDir\android\build.gradle" "$backupDir\build.gradle" -Force
    Remove-Item "$mobileDir\android" -Recurse -Force
}

# 3. Generate fresh Android platform files
Write-Host "Generating Android platform files..." -ForegroundColor Cyan
flutter create --platforms android --org com.checkin --project-name checkin_mobile .

# 4. Restore custom files (AndroidManifest with camera permission, build.gradle with minSdk 21)
if (Test-Path $backupDir) {
    Write-Host "Restoring custom configurations (camera permission, minSdk 21)..." -ForegroundColor Yellow
    if (Test-Path "$backupDir\AndroidManifest.xml") {
        Copy-Item "$backupDir\AndroidManifest.xml" "$mobileDir\android\app\src\main\AndroidManifest.xml" -Force
    }
    if (Test-Path "$backupDir\app_build.gradle") {
        Copy-Item "$backupDir\app_build.gradle" "$mobileDir\android\app\build.gradle" -Force
    }
    if (Test-Path "$backupDir\build.gradle") {
        Copy-Item "$backupDir\build.gradle" "$mobileDir\android\build.gradle" -Force
    }
    Remove-Item $backupDir -Recurse -Force
}

# 5. Install dependencies
Write-Host "Installing Flutter dependencies..." -ForegroundColor Cyan
flutter pub get

# 6. Verify setup
Write-Host "`n=== Running flutter doctor ===" -ForegroundColor Cyan
flutter doctor

Write-Host "`n=== Android Setup Complete ===" -ForegroundColor Green
Write-Host "Next step: flutter build apk --debug" -ForegroundColor Green
