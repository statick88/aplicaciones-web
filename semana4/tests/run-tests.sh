#!/usr/bin/env bash
# =====================================================================
# Semana 4 — ejecución de la suite de pruebas
#
# Uso:  bash tests/run-tests.sh [argumentos de phpunit]
#
# 1. Crea (si no existe) la base de pruebas `semana4_test` en el
#    contenedor de MySQL y le da permisos al usuario de la aplicación.
# 2. Corre PHPUnit dentro del contenedor de la aplicación, donde sí
#    existe la extensión pdo_mysql (el host no la tiene).
# =====================================================================
set -euo pipefail

cd "$(dirname "$0")/.."

# MYSQL_PWD evita el aviso de "password on the command line" y sigue
# mostrando los errores reales de MySQL si algo falla.
docker compose exec -T -e MYSQL_PWD=root_dev mysql mysql -uroot -e "
    CREATE DATABASE IF NOT EXISTS semana4_test
        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    GRANT ALL PRIVILEGES ON semana4_test.* TO 'semana4'@'%';
    FLUSH PRIVILEGES;"

docker compose exec -T app vendor/bin/phpunit "$@"
