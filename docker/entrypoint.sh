#!/bin/sh
set -e

STORAGE_PATH="/app/storage"
mkdir -p "$STORAGE_PATH/framework/cache" "$STORAGE_PATH/framework/sessions" "$STORAGE_PATH/framework/views"
chown -R www-data:www-data "$STORAGE_PATH"

if [ -z "$APP_KEY" ]; then
    echo "No APP_KEY set. Generating temporary key..."
    php artisan key:generate --ansi
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /app/docker/supervisor/supervisor.conf
