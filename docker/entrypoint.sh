#!/bin/sh
set -eu

PORT="${PORT:-80}"

sed -i "0,/^Listen /s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

cd /var/www/html

php artisan package:discover --ansi
php artisan storage:link || true
php artisan migrate --force

exec apache2-foreground
