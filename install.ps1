# Windows setup: needs PHP 8.1+ on PATH with pdo_sqlite enabled (https://windows.php.net/download).
$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot
if (-not (Get-Command php -ErrorAction SilentlyContinue)) { throw 'PHP not found. Install PHP 8.1+ (winget install PHP.PHP.8.4) and enable extension=pdo_sqlite in php.ini.' }
if (-not ((php -m) -match '^pdo_sqlite$')) { throw 'Enable extension=pdo_sqlite in php.ini, then run .\install.ps1 again.' }
New-Item -ItemType Directory -Force data, logs | Out-Null
php scripts/init_db.php
Write-Host "`nStart it with:  php -S 127.0.0.1:8080 -t public   then open http://127.0.0.1:8080"
Write-Host "Demo sign-ins: admin / GreenAdmin!2026, alice / alice-wallet-42, bob / bob-saves-77"
