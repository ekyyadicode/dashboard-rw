#!/usr/bin/env bash
set -e

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force
php artisan db:seed --force || true
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
