#!/bin/sh
set -eu

PORT="${PORT:-80}"

sed -i "0,/^Listen /s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:.*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

cd /var/www/html

if [ -f /etc/secrets/aiven-ca.pem ]; then
  cp /etc/secrets/aiven-ca.pem /usr/local/share/aiven-ca.pem
  chmod 644 /usr/local/share/aiven-ca.pem
  export MYSQL_ATTR_SSL_CA=/usr/local/share/aiven-ca.pem
fi

export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"

php artisan package:discover --ansi
php artisan storage:link || true
php artisan migrate --force

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  php artisan db:seed --force &
fi

exec apache2-foreground
