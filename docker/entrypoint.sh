#!/bin/sh
# Make sure Apache can write uploaded images, even when the project folder is
# bind-mounted from the host (docker-compose.yml mounts it for live editing).
set -e
mkdir -p /var/www/html/uploads/packages /var/www/html/uploads/profiles
chown -R www-data:www-data /var/www/html/uploads 2>/dev/null || chmod -R a+rwX /var/www/html/uploads 2>/dev/null || true
exec docker-php-entrypoint "$@"
