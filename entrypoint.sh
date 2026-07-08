#!/bin/bash
set -e
cd /app
mkdir -p /app/storage/logs
echo "[$(date)] Starting entrypoint..." > /app/storage/logs/startup.log
if [ ! -f /app/.env ]; then
    cp /app/.env.example /app/.env 2>/dev/null || true
fi
echo "[$(date)] Running migrations..." >> /app/storage/logs/startup.log
php artisan migrate --force 2>&1 | tee -a /app/storage/logs/startup.log || true
echo "[$(date)] Starting server on port ${PORT:-8080}..." >> /app/storage/logs/startup.log
php artisan serve --host=0.0.0.0 --port=${PORT:-8080} 2>&1 | tee -a /app/storage/logs/startup.log
