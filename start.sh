#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

if ! command -v php >/dev/null 2>&1; then
  echo "Installing PHP CLI + PDO SQLite/MySQL extensions..."
  sudo apt-get update -qq
  sudo apt-get install -y -qq php-cli php-sqlite3 php-mysql php-mbstring php-xml
fi

PORT="${PORT:-3000}"
exec php -S "0.0.0.0:${PORT}" -t public router.php
