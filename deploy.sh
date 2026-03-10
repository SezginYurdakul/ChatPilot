#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

COMPOSE_FILES=(-f docker-compose.yml -f docker-compose.prod.yml)

echo "[1/5] Pull latest code"
git pull --ff-only

echo "[2/5] Build and start containers"
docker compose "${COMPOSE_FILES[@]}" up -d --build

echo "[3/5] Run database migrations"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan migrate --force

echo "[4/5] Refresh caches"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan optimize:clear
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan config:cache
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan route:cache
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan view:cache

echo "[5/5] Health check"
curl -fsS "http://localhost:${NGINX_PORT:-8090}/api/health" >/dev/null
echo "Deploy OK"
