#!/bin/sh
set -e

echo "===> Laravel starting..."

php artisan package:discover --ansi || true
php artisan storage:link || true

php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

apache2-foreground