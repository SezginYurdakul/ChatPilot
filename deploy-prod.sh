#!/bin/bash

# Production Deployment Script for ChatPilot
# Domain: chatpilot.sezginyurdakul.com (via Cloudflare Tunnel → localhost:8090)

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo "Starting ChatPilot production deployment..."

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Check prerequisites
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed.${NC}"
    exit 1
fi

if ! docker compose version &> /dev/null 2>&1; then
    echo -e "${RED}Error: Docker Compose is not installed.${NC}"
    exit 1
fi

# Check .env file
if [ ! -f .env ]; then
    echo -e "${RED}Error: .env file not found!${NC}"
    echo -e "${YELLOW}Copy .env.production.example to .env and configure it:${NC}"
    echo "  cp .env.production.example .env"
    exit 1
fi

# Validate critical env vars
source_env() {
    set -a
    source .env
    set +a
}
source_env

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo -e "${YELLOW}APP_KEY not set. Generating...${NC}"
    # Will be generated after build
fi

if [ -z "$DB_PASSWORD" ] || [ "$DB_PASSWORD" = "CHANGE_ME_STRONG_PASSWORD" ]; then
    echo -e "${RED}Error: DB_PASSWORD is not set or still default!${NC}"
    echo -e "${YELLOW}Update DB_PASSWORD in .env file.${NC}"
    exit 1
fi

if [ "$APP_ENV" != "production" ]; then
    echo -e "${YELLOW}Warning: APP_ENV is not 'production' (current: ${APP_ENV:-not set})${NC}"
fi

echo -e "${GREEN}Environment check passed${NC}"

# Build
echo "Building production images..."
docker compose -f docker-compose.prod.yml build

if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Docker build failed!${NC}"
    exit 1
fi
echo -e "${GREEN}Build successful${NC}"

# Stop existing containers
echo "Stopping existing containers..."
docker compose -f docker-compose.prod.yml down 2>/dev/null || true

# Start containers
echo "Starting production containers..."
docker compose -f docker-compose.prod.yml up -d

if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Failed to start containers!${NC}"
    exit 1
fi

# Wait for postgres to be ready
echo "Waiting for PostgreSQL..."
for i in {1..30}; do
    if docker compose -f docker-compose.prod.yml exec -T postgres pg_isready -U "${DB_USERNAME:-chatpilot}" &> /dev/null; then
        echo -e "${GREEN}PostgreSQL is ready${NC}"
        break
    fi
    if [ $i -eq 30 ]; then
        echo -e "${RED}Error: PostgreSQL did not start in time${NC}"
        exit 1
    fi
    sleep 1
done

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "Generating APP_KEY..."
    docker compose -f docker-compose.prod.yml exec -T app php artisan key:generate --force
    echo -e "${GREEN}APP_KEY generated. Check .env file.${NC}"
fi

# Run migrations
echo "Running database migrations..."
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

echo -e "${GREEN}Migrations complete${NC}"

# Wait and health check
sleep 3
echo "Running health check..."
if curl -sf http://localhost:8090/api/health > /dev/null 2>&1; then
    echo -e "${GREEN}Health check passed${NC}"
else
    echo -e "${YELLOW}Warning: Health check failed. Containers might still be starting.${NC}"
    sleep 5
    if curl -sf http://localhost:8090/api/health > /dev/null 2>&1; then
        echo -e "${GREEN}Health check passed (retry)${NC}"
    else
        echo -e "${RED}Health check still failing. Check logs:${NC}"
        echo "  docker compose -f docker-compose.prod.yml logs"
    fi
fi

# Status
echo ""
echo -e "${BLUE}Container Status:${NC}"
docker ps --filter "name=chatpilot_" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo -e "${GREEN}Deployment complete!${NC}"
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo "1. Add Cloudflare Tunnel: chatpilot.sezginyurdakul.com → http://localhost:8090"
echo "2. Test: curl http://localhost:8090/api/health"
echo "3. Admin panel: https://chatpilot.sezginyurdakul.com/admin"
echo ""
echo -e "${BLUE}Useful commands:${NC}"
echo "  Logs:      docker compose -f docker-compose.prod.yml logs -f"
echo "  Restart:   docker compose -f docker-compose.prod.yml restart"
echo "  Stop:      docker compose -f docker-compose.prod.yml down"
echo "  Rebuild:   docker compose -f docker-compose.prod.yml build --no-cache"
echo "  Migrate:   docker compose -f docker-compose.prod.yml exec app php artisan migrate --force"
echo "  Tinker:    docker compose -f docker-compose.prod.yml exec app php artisan tinker"
