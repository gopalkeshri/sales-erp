#!/usr/bin/env bash

# ==============================================================================
# Sales ERP - Automated Deployment Script for Hostinger
# ==============================================================================
# Usage:
#   1. SSH / Terminal: bash deploy.sh
#   2. Hostinger hPanel Post-Deployment Command: bash deploy.sh
#   3. GitHub Actions / Webhook Auto Deploy
# ==============================================================================

set -e # Exit immediately if a command exits with a non-zero status

# Text formatting
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}====================================================${NC}"
echo -e "${BLUE}   Starting Sales ERP Hostinger Deployment          ${NC}"
echo -e "${BLUE}====================================================${NC}"

# 1. Determine PHP Binary (Hostinger supports standard php or specific version paths)
if command -v php >/dev/null 2>&1; then
    PHP_BIN="php"
elif [ -f "/opt/alt/php84/usr/bin/php" ]; then
    PHP_BIN="/opt/alt/php84/usr/bin/php"
elif [ -f "/opt/alt/php83/usr/bin/php" ]; then
    PHP_BIN="/opt/alt/php83/usr/bin/php"
elif [ -f "/usr/bin/php8.4" ]; then
    PHP_BIN="/usr/bin/php8.4"
elif [ -f "/usr/bin/php8.3" ]; then
    PHP_BIN="/usr/bin/php8.3"
else
    PHP_BIN="php"
fi

echo -e "${YELLOW}Using PHP binary:${NC} $($PHP_BIN -v | head -n 1)"

# 2. Determine Composer Binary
if command -v composer >/dev/null 2>&1; then
    COMPOSER_BIN="composer"
elif [ -f "composer.phar" ]; then
    COMPOSER_BIN="$PHP_BIN composer.phar"
else
    echo -e "${YELLOW}Downloading local composer.phar...${NC}"
    $PHP_BIN -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    $PHP_BIN composer-setup.php --quiet
    $PHP_BIN -r "unlink('composer-setup.php');"
    COMPOSER_BIN="$PHP_BIN composer.phar"
fi

# 3. Enter Maintenance Mode (Optional, gracefully handles traffic during deploy)
echo -e "${YELLOW}--> Putting application into maintenance mode...${NC}"
$PHP_BIN artisan down --render="errors::503" --retry=60 || true

# 4. Pull Latest Code from Git (if inside a git repository)
if [ -d ".git" ]; then
    echo -e "${YELLOW}--> Pulling latest changes from Git repository...${NC}"
    git fetch --all --prune
    git reset --hard origin/main || git pull origin main
fi

# 5. Ensure .env exists
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}--> Creating .env from .env.example...${NC}"
    cp .env.example .env
    $PHP_BIN artisan key:generate --force
fi

# 6. Install / Update Composer Dependencies
echo -e "${YELLOW}--> Installing Composer dependencies (No Dev, Optimized)...${NC}"
$COMPOSER_BIN install --no-dev --prefer-dist --no-interaction --optimize-autoloader --ignore-platform-reqs

# 7. Run Database Migrations
echo -e "${YELLOW}--> Running database migrations...${NC}"
$PHP_BIN artisan migrate --force

# 8. Clear and Rebuild Laravel Caches
echo -e "${YELLOW}--> Optimizing application cache & routes...${NC}"
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache

# 9. Create Storage Symlink
echo -e "${YELLOW}--> Verifying storage symlink...${NC}"
$PHP_BIN artisan storage:link || true

# 10. Set Correct Permissions for Hostinger Shared Hosting
echo -e "${YELLOW}--> Setting file & directory permissions...${NC}"
chmod -R 775 storage bootstrap/cache || true

# 11. Exit Maintenance Mode
echo -e "${YELLOW}--> Bringing application back online...${NC}"
$PHP_BIN artisan up

echo -e "${GREEN}====================================================${NC}"
echo -e "${GREEN}   Sales ERP Deployment Completed Successfully!   ${NC}"
echo -e "${GREEN}====================================================${NC}"
