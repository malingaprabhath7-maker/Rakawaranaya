#!/usr/bin/env bash
# Prepares the app inside a GitHub Codespace (runs once, when the codespace is created)
set -e
[ -f .env ] || cp .env.example .env
composer install --no-interaction --no-progress
npm install --no-audit --no-fund
npm run build
php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --seed --force
echo "Rakawarana is ready."
