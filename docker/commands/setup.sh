#!/bin/bash
set -e

DOCKER_DIR="$(cd "$(dirname "$0")/.." && pwd)"
COMMANDS_DIR="$(cd "$(dirname "$0")" && pwd)"

green() { printf "\033[32m✓ %s\033[0m\n" "$1"; }
red()   { printf "\033[31m✗ %s\033[0m\n" "$1"; }
info()  { printf "\033[36m→ %s\033[0m\n" "$1"; }

echo ""
echo "Setting up Canalizador..."
echo ""

# 1. Build
info "Building Docker images..."
docker compose -f "$DOCKER_DIR/docker-compose.yml" build
green "Docker images built"

# 2. Up
info "Starting containers..."
docker compose -f "$DOCKER_DIR/docker-compose.yml" up -d
green "Containers started"

# 3. Wait for services
info "Waiting for MySQL..."
until docker exec php_canalizador php -r "new PDO('mysql:host=mysql_canalizador;port=3306', 'root', getenv('MYSQL_PASSWORD') ?: 'root');" 2>/dev/null; do
    sleep 2
done
green "MySQL ready"

info "Waiting for RabbitMQ..."
until docker exec php_canalizador php -r "new \PhpAmqpLib\Connection\AMQPStreamConnection('rabbitmq_canalizador', 5672, 'guest', 'guest');" 2>/dev/null; do
    sleep 2
done
green "RabbitMQ ready"

# 4. Migrations
info "Running migrations..."
"$COMMANDS_DIR/artisan.sh" migrate --force
green "Migrations completed"

# 5. RabbitMQ setup
info "Declaring RabbitMQ queues..."
"$COMMANDS_DIR/artisan.sh" rabbitmq:setup
green "RabbitMQ queues declared"

# 6. Health checks
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
