#!/bin/bash
set -e

DOCKER_DIR="$(cd "$(dirname "$0")/.." && pwd)"
COMMANDS_DIR="$(cd "$(dirname "$0")" && pwd)"

green() { printf "\033[32m✓ %s\033[0m\n" "$1"; }
red()   { printf "\033[31m✗ %s\033[0m\n" "$1"; }
info()  { printf "\033[36m→ %s\033[0m\n" "$1"; }

PROJECT_ROOT="$(cd "$DOCKER_DIR/.." && pwd)"

echo ""
echo "Setting up Canalizador (fresh)..."
echo ""

# 1. Stop & clean
info "Stopping containers and removing volumes..."
docker compose -f "$DOCKER_DIR/docker-compose.yml" down --volumes --remove-orphans --rmi local 2>/dev/null || true
green "Containers, volumes and images removed"

# 1b. Clean storage (regenerable artifacts) and Laravel caches
info "Cleaning storage artifacts and Laravel caches..."
find "$PROJECT_ROOT/storage/app" -mindepth 1 -maxdepth 1 \
    ! -name '.gitignore' \
    ! -name 'private' \
    ! -name 'public' \
    -exec rm -rf {} +
find "$PROJECT_ROOT/storage/framework/cache" -mindepth 1 ! -name '.gitignore' -exec rm -rf {} + 2>/dev/null || true
find "$PROJECT_ROOT/storage/framework/sessions" -mindepth 1 ! -name '.gitignore' -exec rm -rf {} + 2>/dev/null || true
find "$PROJECT_ROOT/storage/framework/views" -mindepth 1 ! -name '.gitignore' -exec rm -rf {} + 2>/dev/null || true
find "$PROJECT_ROOT/storage/framework/testing" -mindepth 1 ! -name '.gitignore' -exec rm -rf {} + 2>/dev/null || true
find "$PROJECT_ROOT/storage/logs" -mindepth 1 ! -name '.gitignore' -exec rm -rf {} + 2>/dev/null || true
find "$PROJECT_ROOT/storage/pail" -mindepth 1 ! -name '.gitignore' -exec rm -rf {} + 2>/dev/null || true
find "$PROJECT_ROOT/bootstrap/cache" -mindepth 1 ! -name '.gitignore' -exec rm -rf {} + 2>/dev/null || true
green "Storage artifacts and caches cleaned"

# 2. Build (no cache)
info "Building Docker images (no cache)..."
docker compose -f "$DOCKER_DIR/docker-compose.yml" build --no-cache
green "Docker images built"

# 3. Up
info "Starting containers..."
docker compose -f "$DOCKER_DIR/docker-compose.yml" up -d
green "Containers started"

# 4. Install dependencies
info "Installing Composer dependencies..."
docker exec php_canalizador sh -c "cd /code && composer install --no-interaction"
green "Composer dependencies installed"

# 5. Wait for services
info "Waiting for MySQL..."
until docker exec php_canalizador php -r "new PDO('mysql:host=mysql_canalizador;port=3306', 'root', getenv('MYSQL_PASSWORD') ?: 'root');" 2>/dev/null; do
    sleep 2
done
green "MySQL ready"

info "Waiting for RabbitMQ..."
until docker exec rabbitmq_canalizador rabbitmq-diagnostics -q ping 2>/dev/null; do
    sleep 2
done
green "RabbitMQ ready"

# 6. Migrations
info "Wiping database..."
"$COMMANDS_DIR/artisan.sh" db:wipe --force
green "Database wiped"

info "Running migrations..."
"$COMMANDS_DIR/artisan.sh" migrate --force
green "Migrations completed"

# 7. RabbitMQ setup
info "Declaring RabbitMQ queues..."
"$COMMANDS_DIR/artisan.sh" rabbitmq:setup
green "RabbitMQ queues declared"

# 7b. Clear Laravel runtime caches
info "Clearing Laravel runtime caches..."
"$COMMANDS_DIR/artisan.sh" optimize:clear
green "Runtime caches cleared"

# 8. Health checks
info "Running health checks..."

if docker exec php_canalizador php artisan db:monitor --databases=mysql 2>/dev/null; then
    green "DB connection OK"
else
    red "DB connection FAILED"
fi

if docker exec rabbitmq_canalizador rabbitmqctl list_queues --quiet 2>/dev/null | grep -q "video\.\|clip\."; then
    green "RabbitMQ queues OK"
else
    red "RabbitMQ queues FAILED"
fi

echo ""
green "Setup completed!"
