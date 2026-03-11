#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

COMPOSE_FILES=(-f docker-compose.yml -f docker-compose.dev.yml)

echo "[1/5] Build and start dev containers"
docker compose "${COMPOSE_FILES[@]}" up -d --build

echo "[2/5] Install PHP dependencies if needed"
docker compose "${COMPOSE_FILES[@]}" exec -T app composer install

echo "[3/5] Ensure app key exists"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan key:generate --force

echo "[4/5] Run migrations"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan migrate --seed --force

echo "[5/5] Health check"
curl -fsS "http://localhost:${NGINX_PORT:-8080}/api/health" >/dev/null
echo "Dev deploy OK"
