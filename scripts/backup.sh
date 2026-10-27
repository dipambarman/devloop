#!/usr/bin/env bash
# ==============================================================================
# DevLoop — Production Database Backup & Rotation Runner
# Usage: ./scripts/backup.sh [--keep=7]
# ==============================================================================

set -euo pipefail

APP_DIR="/var/www/devloop"
KEEP_DAYS="${1:---keep=7}"

echo "==> [$(date -Iseconds)] Initiating automated database backup..."

if [ -f "docker-compose.yml" ]; then
    docker compose exec -T app php artisan db:backup "$KEEP_DAYS"
elif [ -d "$APP_DIR" ] && [ -f "$APP_DIR/docker-compose.yml" ]; then
    cd "$APP_DIR"
    docker compose exec -T app php artisan db:backup "$KEEP_DAYS"
else
    php artisan db:backup "$KEEP_DAYS"
fi

echo "==> [$(date -Iseconds)] Database backup workflow complete."
