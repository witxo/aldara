#!/bin/bash
# Deploy script for aldara.ivema.es
# Usage: ./deploy.sh
# Run from /opt/aldara (the git repo root)

set -e

REPO_DIR="/opt/aldara"
WEB_DIR="/var/www/vhosts/ivema.es/aldara"

echo "=== Pulling latest code ==="
cd "$REPO_DIR"
git pull origin main

echo "=== Syncing to web root ==="
rsync -a --delete \
  --exclude={'vendor/','node_modules/','.env','.git','storage/'} \
  "$REPO_DIR/backend/" "$WEB_DIR/"

echo "=== Installing PHP dependencies ==="
cd "$WEB_DIR"
/opt/plesk/php/8.3/bin/php composer.phar install --no-dev --no-interaction

echo "=== Clearing Laravel caches ==="
/opt/plesk/php/8.3/bin/php artisan route:clear
/opt/plesk/php/8.3/bin/php artisan config:clear
/opt/plesk/php/8.3/bin/php artisan cache:clear
/opt/plesk/php/8.3/bin/php artisan view:clear

echo "=== Done ==="