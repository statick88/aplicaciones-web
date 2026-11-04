#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
for user in root appuser; do
  DB_USER="$user" DB_PASS=appsecret docker compose config --format json |
    python -c 'import json,sys
c=json.load(sys.stdin)
web=c["services"]["web"]["environment"]
db=c["services"]["db"]["environment"]
assert db.get("APP_DB_USER") == web["DB_USER"], "MariaDB must receive the configured application user"
assert db.get("APP_DB_PASS") == web["DB_PASS"], "MariaDB must receive the configured application password"
assert db.get("MARIADB_USER") != "root", "The official image rejects MARIADB_USER=root"
print("PASS: matching app credentials for", web["DB_USER"])'
done
test -f docker/initdb/02-usuario.sh
