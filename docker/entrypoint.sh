#!/bin/sh
set -e

# Wait for database to be ready — use PHP instead of nc (netcat may not be installed)
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database ($DB_HOST:$DB_PORT)..."
    for i in $(seq 1 30); do
        php -r "
            try {
                new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USERNAME}', '${DB_PASSWORD}');
                exit(0);
            } catch (\PDOException \$e) {
                exit(1);
            }
        " 2>/dev/null && break
        echo "  Attempt $i/30 — database not ready, waiting..."
        sleep 2
    done
    echo "Database is ready."
fi

# Run migrations (only in production — local uses artisan directly)
if [ "${APP_ENV}" = "production" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --no-interaction
fi

# Create storage link if not exists
php artisan storage:link --no-interaction 2>/dev/null || true

# Clear and rebuild cache
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true

# Start queue worker in background (no --daemon flag — removed in Laravel 10+)
if [ "${QUEUE_WORKER}" = "true" ]; then
    echo "Starting queue worker..."
    php artisan queue:work &
fi

# Execute the main command
exec "$@"
