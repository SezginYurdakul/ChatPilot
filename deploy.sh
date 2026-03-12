#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

COMPOSE_FILES=(-f docker-compose.yml -f docker-compose.prod.yml)

echo "[1/6] Pull latest code"
git pull --ff-only

echo "[2/6] Build and start containers"
docker compose "${COMPOSE_FILES[@]}" up -d --build

echo "[3/6] Sync public assets into shared volume"
docker compose "${COMPOSE_FILES[@]}" cp public/chatpilot-widget.js app:/var/www/html/public/chatpilot-widget.js

echo "[4/6] Run database migrations"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan migrate --force

echo "[5/6] Refresh caches (app + queue)"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan optimize:clear
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan config:cache
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan route:cache
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan view:cache
docker compose "${COMPOSE_FILES[@]}" exec -T queue php artisan config:clear
docker compose "${COMPOSE_FILES[@]}" exec -T queue php artisan config:cache
docker compose "${COMPOSE_FILES[@]}" restart queue

echo "[6/6] Health check"
curl -fsS "http://localhost:${NGINX_PORT:-8090}/api/health" >/dev/null
echo "Deploy OK"

