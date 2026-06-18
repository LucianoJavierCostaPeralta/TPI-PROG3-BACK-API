#!/usr/bin/env sh
set -e

export PORT="${PORT:-10000}"

if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
  export DB_URL="$DATABASE_URL"
fi

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is required. Set it in Render with: php artisan key:generate --show"
  exit 1
fi

php artisan config:clear --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction
php artisan migrate --force --no-interaction
php artisan config:cache --no-interaction
php artisan view:cache --no-interaction

exec php artisan serve --host=0.0.0.0 --port="$PORT"
