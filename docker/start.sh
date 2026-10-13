#!/bin/sh
set -e
cd /var/www/html

# Render tells us which port to use
PORT="${PORT:-10000}"
sed -ri "s/Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ -z "$APP_KEY" ]; then
  echo "WARNING: APP_KEY is not set. Generating a temporary key; set APP_KEY in the dashboard so data stays readable after a restart."
  export APP_KEY="$(php artisan key:generate --show)"
fi

if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
  touch database/database.sqlite
  chown www-data:www-data database/database.sqlite
fi

php artisan migrate --force
php artisan rakawarana:reset-demo || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage database

# run the scheduler (daily digest, demo reset) in the background
su -s /bin/sh www-data -c "php artisan schedule:work" > /dev/null 2>&1 &

exec apache2-foreground
