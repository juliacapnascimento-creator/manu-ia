#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY must be configured in the hosting environment." >&2
    exit 1
fi

if [ -n "${RENDER_EXTERNAL_URL:-}" ] && [ "${APP_KEY#base64:}" = "$APP_KEY" ]; then
    APP_KEY="base64:$APP_KEY"
    export APP_KEY
fi

if [ -n "${DATABASE_URL:-}" ] || [ -n "${DB_HOST:-}" ]; then
    php artisan migrate --force
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
exec /usr/bin/supervisord -n -c /etc/supervisord.conf
