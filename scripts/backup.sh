#!/usr/bin/env bash
# ==============================================================================
# DevLoop — Automated Database Backup Script with Rotation
# Run via cron (e.g. daily at 2:00 AM)
# Cron entry: 0 2 * * * /var/www/devloop/scripts/backup.sh >> /var/log/devloop-backup.log 2>&1
# ==============================================================================

set -euo pipefail

BACKUP_DIR="/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
RETENTION_DAYS=14

mkdir -p "$BACKUP_DIR"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Starting automated database backup..."

cd /var/www/devloop

# Export database dump from MySQL container
BACKUP_FILE="${BACKUP_DIR}/devloop_backup_${TIMESTAMP}.sql.gz"

docker compose exec -T mysql mysqldump -u root -p"$(grep DB_PASSWORD .env | cut -d '=' -f2)" devloop | gzip > "$BACKUP_FILE"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backup created: ${BACKUP_FILE} ($(du -h "$BACKUP_FILE" | cut -f1))"

# Prune backups older than 14 days
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Pruning backups older than ${RETENTION_DAYS} days..."
find "$BACKUP_DIR" -name "devloop_backup_*.sql.gz" -type f -mtime "+${RETENTION_DAYS}" -delete

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backup routine finished successfully."
