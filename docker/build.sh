#!/bin/bash
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

echo "==> Syncing packages into build context..."
# This repo, and a system-metrics checkout next to it (override with SYSTEM_METRICS_PATH).
PACKAGE_PATH="$(cd "$SCRIPT_DIR/.." && pwd)"
SYSTEM_METRICS_PATH="${SYSTEM_METRICS_PATH:-$PACKAGE_PATH/../system-metrics}"

rsync -a --delete --exclude=vendor --exclude=node_modules --exclude=.git --exclude=docker \
    "$PACKAGE_PATH/" ./laravel-health/

rsync -a --delete --exclude=vendor --exclude=node_modules --exclude=.git \
    "$SYSTEM_METRICS_PATH/" ./system-metrics/

echo "==> Building Docker image..."
docker compose build --no-cache

echo "==> Starting container with limits (2 CPUs, 512MB RAM)..."
docker compose up -d

echo "==> Waiting for server to start..."
sleep 3

echo "==> Testing endpoints..."
echo ""
echo "--- Liveness ---"
curl -s http://localhost:8877/health | python3 -m json.tool 2>/dev/null || curl -s http://localhost:8877/health
echo ""
echo "--- Metrics JSON ---"
curl -s "http://localhost:8877/health/metrics/json?token=test-token" | python3 -m json.tool 2>/dev/null | head -60
echo ""
echo "--- Prometheus ---"
curl -s "http://localhost:8877/health/metrics?token=test-token" | head -40
echo ""
echo "==> Container running at http://localhost:8877"
echo "==> Dashboard at http://localhost:8877/health/ui?token=test-token"
