#!/usr/bin/env bash
# Disposable API integration check. It never targets the normal seman5 project.
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
project="${COMPOSE_PROJECT_NAME:-seman5-check-$RANDOM-$$}"
api_port="${API_PORT:-28085}"
compose=(docker compose --env-file /dev/null -p "$project" -f "$root/compose.yaml")

case "$project" in
  seman5-check-*) ;;
  *)
    echo "Refusing project '$project': use the seman5-check- test prefix." >&2
    exit 2
    ;;
esac

cleanup() {
  "${compose[@]}" down --volumes --remove-orphans >/dev/null 2>&1 || true
}
trap cleanup EXIT

API_PORT="$api_port" SESSION_TTL=3 COOKIE_SECURE=0 "${compose[@]}" config -q
API_PORT="$api_port" SESSION_TTL=3 COOKIE_SECURE=0 "${compose[@]}" up --build -d --wait api
printf '%s\n' 'Public-Test-Fixture-2026!' | \
  "${compose[@]}" exec -T api php crear-usuario.php integration@example.test Integration
API_URL="http://127.0.0.1:$api_port" TEST_TTL=3 python3 "$root/tests/api_test.py"
echo "API integration checks passed in disposable project $project."
