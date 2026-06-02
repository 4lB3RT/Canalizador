#!/bin/bash
set -e

DOCKER_DIR="$(cd "$(dirname "$0")/.." && pwd)"
COMMANDS_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_ROOT="$(cd "$DOCKER_DIR/.." && pwd)"
COMPOSE="docker compose -f $DOCKER_DIR/dev/docker-compose.yml"

# ── palette ──────────────────────────────────────────────────────────
if [ -t 1 ]; then
    BOLD=$'\033[1m'; DIM=$'\033[2m'; RESET=$'\033[0m'
    GREEN=$'\033[32m'; RED=$'\033[31m'; CYAN=$'\033[36m'; YELLOW=$'\033[33m'
else
    BOLD=; DIM=; RESET=; GREEN=; RED=; CYAN=; YELLOW=
fi

TOTAL_STEPS=8
START_TS=$(date +%s)

# Section header, e.g. "[3/8] Starting services"
step() { printf "\n${BOLD}${CYAN}[%s/%s]${RESET} ${BOLD}%s${RESET}\n" "$1" "$TOTAL_STEPS" "$2"; }
ok()   { printf "  ${GREEN}✓${RESET} %s\n" "$1"; }
fail() { printf "  ${RED}✗${RESET} %s\n" "$1"; }
note() { printf "  ${DIM}%s${RESET}\n" "$1"; }

# run "label" cmd args...
# Runs the command with output hidden, animating a spinner next to the label.
# On success the spinner line is rewritten with a green check; on failure the
# captured output is dumped so the error is visible.
run() {
    local label="$1"; shift
    local log; log="$(mktemp)"
    local frames='⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏'

    "$@" >"$log" 2>&1 &
    local pid=$!

    if [ -t 1 ]; then
        local i=0
        while kill -0 "$pid" 2>/dev/null; do
            local f=${frames:i++%${#frames}:1}
            printf "\r  ${CYAN}%s${RESET} %s" "$f" "$label"
            sleep 0.1
        done
    fi

    if wait "$pid"; then
        printf "\r\033[K  ${GREEN}✓${RESET} %s\n" "$label"
        rm -f "$log"
        return 0
    else
        printf "\r\033[K  ${RED}✗${RESET} %s\n" "$label"
        printf "${DIM}"; sed 's/^/      /' "$log"; printf "${RESET}"
        rm -f "$log"
        return 1
    fi
}

# ── banner ───────────────────────────────────────────────────────────
# Heredoc (quoted 'EOF') keeps the ASCII art literal — no escaping needed.
printf "\n${BOLD}${CYAN}"
cat <<'EOF'
 _____         _   _
|_   _|__  ___| |_(_)_ __   __ _
  | |/ _ \/ __| __| | '_ \ / _` |
  | |  __/\__ \ |_| | | | | (_| |
  |_|\___||___/\__|_|_| |_|\__, |
                           |___/
EOF
printf "${RESET}${DIM}  ──────────────────────────────────────────${RESET}\n"
printf "  ${BOLD}Helmreel${RESET} ${DIM}·${RESET} dev environment setup\n"

# ── 1. Stop & clean ────────────────────────────────────────────────────
step 1 "Cleaning up"
run "Stopping containers & removing volumes" \
    $COMPOSE down --volumes --remove-orphans --rmi local
docker ps -a --filter "name=helmreel" -q | xargs -r docker rm -f >/dev/null 2>&1 || true
ok "Containers, volumes and images removed"

# Remove orphaned networks claiming our subnet (e.g. from previous project names)
ORPHAN_NETS=$(docker network ls --filter driver=bridge --format '{{.Name}}' | {
    while read net; do
        subnet=$(docker network inspect "$net" --format '{{range .IPAM.Config}}{{.Subnet}}{{end}}' 2>/dev/null || true)
        if [ "$subnet" = "10.7.0.0/16" ] && [ "$net" != "helmreel_helmreel" ]; then
            echo "$net"
        fi
    done
} || true)
if [ -n "$ORPHAN_NETS" ]; then
    for net in $ORPHAN_NETS; do
        attached=$(docker network inspect "$net" --format '{{range .Containers}}{{.Name}} {{end}}' 2>/dev/null || true)
        [ -n "$attached" ] && echo "$attached" | xargs docker rm -f >/dev/null 2>&1 || true
        docker network rm "$net" >/dev/null 2>&1 || fail "Could not remove network $net"
    done
    note "Removed orphaned networks: $(echo "$ORPHAN_NETS" | tr '\n' ' ')"
fi

# Clean storage (regenerable artifacts) and Laravel caches
clean_storage() {
    find "$PROJECT_ROOT/storage/app" -mindepth 1 -maxdepth 1 \
        ! -name '.gitignore' ! -name 'private' ! -name 'public' -exec rm -rf {} +
    for d in framework/cache framework/sessions framework/views framework/testing logs pail; do
        find "$PROJECT_ROOT/storage/$d" -mindepth 1 ! -name '.gitignore' -exec rm -rf {} + 2>/dev/null || true
    done
    find "$PROJECT_ROOT/bootstrap/cache" -mindepth 1 ! -name '.gitignore' -exec rm -rf {} + 2>/dev/null || true
}
run "Cleaning storage artifacts & Laravel caches" clean_storage

# ── 2. Build ────────────────────────────────────────────────────────────
step 2 "Building images"
run "docker compose build --no-cache" $COMPOSE build --no-cache

# ── 3. Start services ────────────────────────────────────────────────────
step 3 "Starting services"
run "docker compose up -d" $COMPOSE up -d
for svc in mysql rabbitmq redis; do
    ok "$svc up"
done

# ── 4. Dependencies ──────────────────────────────────────────────────────
step 4 "Installing dependencies"
run "composer install" \
    docker exec php_helmreel sh -c "cd /code && composer install --no-interaction"

# ── 5. Wait for services ─────────────────────────────────────────────────
step 5 "Waiting for services"
wait_mysql() {
    until docker exec php_helmreel php -r \
        "new PDO('mysql:host=mysql_helmreel;port=3306','root',getenv('MYSQL_ROOT_PASSWORD')?:'root');" 2>/dev/null; do
        sleep 2
    done
}
wait_rabbit() {
    until docker exec rabbitmq_helmreel rabbitmq-diagnostics -q ping 2>/dev/null; do
        sleep 2
    done
}
run "MySQL accepting connections" wait_mysql
run "RabbitMQ responding to ping" wait_rabbit

# ── 6. Restore latest production dump from GCS ───────────────────────────
# Always overwrite the local DB with the most recent prod backup so dev mirrors
# prod data on every run. The dev MySQL is treated as a disposable cache.
step 6 "Restoring database"
BACKUP_BUCKET="gs://helmreel-backups"

set -a
# shellcheck disable=SC1091
source "$DOCKER_DIR/dev/.env"
set +a

command -v gsutil >/dev/null 2>&1 || {
    fail "gsutil not found on PATH. Install the Google Cloud SDK and run 'gcloud auth login'."
    exit 1
}
if ! gsutil ls "$BACKUP_BUCKET/" >/dev/null 2>&1; then
    fail "Cannot list $BACKUP_BUCKET — gsutil is unauthenticated or lacks access."
    note "Try: gcloud auth login && gcloud config set project galvanic-camp-493316-i2"
    note "The signed-in user needs roles/storage.objectViewer on the bucket."
    exit 1
fi

# Pick the lexicographically latest mysql-*.sql.gz (UTC timestamp in the name,
# so string sort == chronological order).
LATEST="$(gsutil ls "$BACKUP_BUCKET/mysql-*.sql.gz" 2>/dev/null | sort | tail -1)"
[ -n "$LATEST" ] || { fail "No dumps found under $BACKUP_BUCKET"; exit 1; }
note "Latest dump: $(basename "$LATEST")"

# Drop the dev DB first; the dump's own CREATE DATABASE + USE recreate it.
# Dev and prod share the DB name ("helmreel"), so the dump imports as-is.
restore_db() {
    docker exec -i mysql_helmreel \
        sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" -e 'DROP DATABASE IF EXISTS \`${MYSQL_DATABASE}\`'" </dev/null
    gsutil -q cp "$LATEST" - | gunzip \
        | docker exec -i mysql_helmreel sh -c "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\""
}
run "Importing $(basename "$LATEST") into $MYSQL_DATABASE" restore_db

run "Running pending migrations" "$COMMANDS_DIR/artisan.sh" migrate --force

# ── 7. App wiring ────────────────────────────────────────────────────────
step 7 "Configuring application"
run "Declaring RabbitMQ queues" "$COMMANDS_DIR/artisan.sh" rabbitmq:setup
run "Clearing runtime caches" "$COMMANDS_DIR/artisan.sh" optimize:clear

# ── 8. Health checks ─────────────────────────────────────────────────────
step 8 "Health checks"
if docker exec php_helmreel php artisan db:monitor --databases=mysql >/dev/null 2>&1; then
    ok "Database connection"
else
    fail "Database connection"
fi
if docker exec rabbitmq_helmreel rabbitmqctl list_queues --quiet 2>/dev/null | grep -q "video\.\|clip\."; then
    ok "RabbitMQ queues"
else
    fail "RabbitMQ queues"
fi

# ── summary ──────────────────────────────────────────────────────────────
ELAPSED=$(( $(date +%s) - START_TS ))
printf "\n${BOLD}${GREEN}✔ Setup completed${RESET} ${DIM}in %dm%02ds${RESET}\n" $((ELAPSED/60)) $((ELAPSED%60))
printf "  ${DIM}App:${RESET} ${BOLD}http://localhost:${NGINX_PORT:-8010}${RESET}\n\n"
