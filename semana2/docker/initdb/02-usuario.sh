#!/bin/sh
# Sourced by the official MariaDB entrypoint on first initialization only.
# Root is already provisioned by MARIADB_ROOT_PASSWORD.
if [ "${APP_DB_USER:-root}" != root ]; then
    app_user=$(printf '%s' "$APP_DB_USER" | sed "s/'/''/g")
    app_password=$(printf '%s' "$APP_DB_PASS" | sed "s/'/''/g")
    app_database=$(printf '%s' "$MARIADB_DATABASE" | sed 's/`/``/g')
    docker_process_sql <<SQL
SET SESSION sql_mode = 'NO_BACKSLASH_ESCAPES';
CREATE USER IF NOT EXISTS '$app_user'@'%' IDENTIFIED BY '$app_password';
GRANT ALL PRIVILEGES ON \`$app_database\`.* TO '$app_user'@'%';
SQL
    unset app_user app_password app_database
fi
