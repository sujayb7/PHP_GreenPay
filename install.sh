#!/usr/bin/env bash
# One-step setup for GreenPay on Linux, macOS, WSL or GitHub Codespaces.
# Needs PHP 8.1+ with the pdo_sqlite extension. Installs it with apt or brew when missing, then creates the database.
set -euo pipefail
cd "$(dirname "$0")"
export XDEBUG_MODE=off

have_php() { command -v php >/dev/null 2>&1 && php -m 2>/dev/null | grep -qi '^pdo_sqlite$'; }

if ! have_php; then
  echo "PHP with pdo_sqlite not found; installing it..."
  if command -v apt-get >/dev/null 2>&1; then
    SUDO=""; [ "$(id -u)" -ne 0 ] && SUDO="sudo"
    $SUDO apt-get update -y && $SUDO apt-get install -y php-cli php-sqlite3
  elif command -v brew >/dev/null 2>&1; then
    brew install php
  else
    echo "Please install PHP 8.1+ with pdo_sqlite (https://www.php.net/downloads), then run ./install.sh again." >&2
    exit 1
  fi
fi

php -r 'exit(PHP_VERSION_ID >= 80100 ? 0 : 1);' || { echo "PHP 8.1 or newer is required (found $(php -r 'echo PHP_VERSION;'))." >&2; exit 1; }
mkdir -p data logs
php scripts/init_db.php

cat <<'MSG'

GreenPay is installed. Start it with:   ./run.sh      then open http://127.0.0.1:8080

Demo sign-ins:
  admin  / GreenAdmin!2026
  alice  / alice-wallet-42
  bob    / bob-saves-77
MSG
