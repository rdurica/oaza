#!/bin/sh
set -e

cd /app/src

# Start Vite when a frontend toolchain is present
if [ -d "node_modules" ] && [ -f "package.json" ]; then
    echo "Starting Vite dev server..."
    npm run dev &
fi

echo "Starting FrankenPHP..."
exec frankenphp run --config /etc/caddy/Caddyfile
