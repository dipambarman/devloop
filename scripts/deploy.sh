#!/usr/bin/env bash
# ==============================================================================
# DevLoop — Production Deployment & Update Script
# Usage: ./scripts/deploy.sh
# ==============================================================================

set -euo pipefail

APP_DIR="/var/www/devloop"

echo "==> Deploying DevLoop..."
cd "$APP_DIR"

# 1. Enter maintenance mode
echo "==> Enabling maintenance mode..."
docker compose exec -T app php artisan down --render="errors::503" || true

# 2. Pull latest code
echo "==> Fetching latest changes from Git..."
git fetch origin main
git reset --hard origin/main

# 3. Rebuild and restart containers
echo "==> Building and updating Docker containers..."
docker compose build --pull app
docker compose up -d --remove-orphans

# 4. Wait for MySQL to be healthy
echo "==> Waiting for database connection..."
docker compose exec -T app php artisan db:monitor || sleep 5

# 5. Run database migrations
echo "==> Running database migrations..."
docker compose exec -T app php artisan migrate --force

# 6. Rebuild production caches
echo "==> Optimizing configuration, routes, and views..."
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache
docker compose exec -T app php artisan event:cache

# 7. Restart background queue workers
echo "==> Restarting background queue workers..."
docker compose restart queue

# 8. Exit maintenance mode
echo "==> Bringing application back online..."
docker compose exec -T app php artisan up

echo "==> 🚀 Deployment finished successfully!"
