#!/bin/sh
# =====================================================================
# Cuenta de aplicación distinta de `root` (opcional).
#
# Lo ejecuta el entrypoint oficial de mysql en la primera
# inicialización del volumen. `root` ya quedó provisionado con
# MYSQL_ROOT_PASSWORD; este script solo actúa si DB_USER != root.
#
# Mismo criterio que semana2/docker/initdb/02-usuario.sh.
# =====================================================================
if [ "${DB_USER:-root}" != root ]; then
    app_user=$(printf '%s' "$DB_USER" | sed "s/'/''/g")
    app_password=$(printf '%s' "$DB_PASS" | sed "s/'/''/g")
    app_database=$(printf '%s' "$MYSQL_DATABASE" | sed 's/`/``/g')
    docker_process_sql <<SQL
SET SESSION sql_mode = 'NO_BACKSLASH_ESCAPES';
CREATE USER IF NOT EXISTS '$app_user'@'%' IDENTIFIED BY '$app_password';
GRANT ALL PRIVILEGES ON \`$app_database\`.* TO '$app_user'@'%';
SQL
    unset app_user app_password app_database
fi
