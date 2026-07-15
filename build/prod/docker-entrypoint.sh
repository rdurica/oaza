#!/bin/bash
set -euo pipefail

echo "Oaza - Starting..."

wait_for() {
    local host="$1" port="$2" name="$3" max=30 i=0
    while ! nc -z "$host" "$port" 2>/dev/null; do
        i=$((i + 1))
        if [ "$i" -ge "$max" ]; then
            echo "ERROR: $name ($host:$port) not reachable after ${max}s - aborting" >&2
            exit 1
        fi
        echo "Waiting for $name ($host:$port)... ($i/$max)"
        sleep 1
    done
    echo "$name ($host:$port) is ready"
}

# Wait for database when configured (MariaDB default port 3306).
if [ -n "${DB_HOST:-}" ]; then
    wait_for "$DB_HOST" "${DB_PORT:-3306}" "Database"
elif [ -n "${DATABASE_URL:-}" ]; then
    DB_HOST=$(echo "$DATABASE_URL" | sed -n 's/.*@\([^:]*\).*/\1/p')
    DB_PORT=$(echo "$DATABASE_URL" | sed -n 's/.*:\([0-9]*\)\/.*/\1/p')
    DB_HOST=${DB_HOST:-localhost}
    DB_PORT=${DB_PORT:-3306}
    wait_for "$DB_HOST" "$DB_PORT" "Database"
fi

if [ -n "${REDIS_HOST:-}" ]; then
    wait_for "$REDIS_HOST" "${REDIS_PORT:-6379}" "Redis"
fi

echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/caddy/Caddyfile
