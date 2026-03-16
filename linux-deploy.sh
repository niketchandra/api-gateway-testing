#!/usr/bin/env bash

set -euo pipefail

NO_CACHE=false
SKIP_MIGRATIONS=false

print_help() {
  cat <<'EOF'
Usage: ./linux-deploy.sh [options]

Options:
  --no-cache         Build Docker images without cache
  --skip-migrations  Skip Laravel migration and seeder steps
  -h, --help         Show this help message
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --no-cache)
      NO_CACHE=true
      shift
      ;;
    --skip-migrations)
      SKIP_MIGRATIONS=true
      shift
      ;;
    -h|--help)
      print_help
      exit 0
      ;;
    *)
      echo "Unknown option: $1"
      print_help
      exit 1
      ;;
  esac
done

if docker compose version >/dev/null 2>&1; then
  COMPOSE_CMD=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE_CMD=(docker-compose)
else
  echo "Docker Compose is not installed. Install Docker Compose and try again."
  exit 1
fi

COMPOSE_FILES=(-f docker-compose.yml -f docker-compose-kong.yml)

echo "[1/5] Docker down"
"${COMPOSE_CMD[@]}" "${COMPOSE_FILES[@]}" down --remove-orphans

echo "[2/5] Docker build"
if [[ "$NO_CACHE" == true ]]; then
  "${COMPOSE_CMD[@]}" "${COMPOSE_FILES[@]}" build --no-cache
else
  "${COMPOSE_CMD[@]}" "${COMPOSE_FILES[@]}" build
fi

echo "[3/5] Docker deploy"
"${COMPOSE_CMD[@]}" "${COMPOSE_FILES[@]}" up -d

"${COMPOSE_CMD[@]}" "${COMPOSE_FILES[@]}" exec -T api sh -lc "grep -q '^APP_KEY=base64:' /app/.env || php artisan key:generate --force"

if [[ "$SKIP_MIGRATIONS" == false ]]; then
  echo "[4/5] Run migration"
  "${COMPOSE_CMD[@]}" "${COMPOSE_FILES[@]}" exec -T api php artisan migrate --force

  echo "[5/5] Run seeder"
  "${COMPOSE_CMD[@]}" "${COMPOSE_FILES[@]}" exec -T api php artisan db:seed --force
else
  echo "[4/5] Skip migration"
  echo "[5/5] Skip seeder"
fi

echo "Final service status"
"${COMPOSE_CMD[@]}" "${COMPOSE_FILES[@]}" ps

echo "Deployment completed successfully."
