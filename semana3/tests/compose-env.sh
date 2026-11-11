#!/usr/bin/env bash
# =====================================================================
# Comprobación estática del compose.yaml de la Semana 3.
#
# No levanta contenedores: solo valida, con `docker compose config`,
# que las variables llegan hasta donde tienen que llegar:
#   * la web y la base reciben el MISMO usuario/contraseña/.nombre;
#   * el nombre de usuario de la aplicación no es root;
#   * la base recibe DB_USER/DB_PASS que consume el script de initdb;
#   * la web espera a que la base esté sana (service_healthy);
#   * los puertos publicados son los de la semana (8081 / 3307), que no
#     chocan con Semana 1 y 2 (8080 / 3306).
# =====================================================================
set -euo pipefail
cd "$(dirname "$0")/.."

for user in root appuser; do
  DB_USER="$user" DB_PASS=appsecret docker compose config --format json |
    python3 -c 'import json, sys

config = json.load(sys.stdin)
web = config["services"]["web"]
db = config["services"]["db"]
web_env = web["environment"]
db_env = db["environment"]

# Misma credencial en los dos extremos: si no coinciden, la web no entra.
assert db_env.get("MYSQL_ROOT_PASSWORD") == web_env["DB_PASS"], \
    "MySQL root password must equal the web DB_PASS"
assert db_env.get("MYSQL_DATABASE") == web_env["DB_NAME"], \
    "MySQL database name must equal the web DB_NAME"
assert db_env.get("DB_USER") == web_env["DB_USER"], \
    "The initdb user script must receive the configured application user"
assert db_env.get("DB_PASS") == web_env["DB_PASS"], \
    "The initdb user script must receive the configured application password"

# La imagen oficial de MySQL rechaza MYSQL_USER=root; aquí root se
# provisiona con MYSQL_ROOT_PASSWORD y el usuario de la app lo crea
# 03-usuario.sh solo si DB_USER != root.
if web_env["DB_USER"] == "root":
    assert db_env.get("DB_USER") == "root", "Root must stay explicit for the initdb script"
else:
    assert db_env["DB_USER"] != "root", "The application user must not be root"

# La web no debe arrancar antes de que la base esté sana.
dependencia = web.get("depends_on", {})
assert isinstance(dependencia, dict) and "db" in dependencia, "web must depend on db"
assert dependencia["db"].get("condition") == "service_healthy", \
    "web must wait for db with condition service_healthy"

# Puertos propios de la semana: 8081 (web) y 3307 (MySQL).
# `docker compose config` expone cada puerto como objeto con la clave
# `published` (el puerto del host) y `target` (el del contenedor).
puertos_web = [(str(p["host_ip"]), str(p["published"])) for p in web["ports"]]
puertos_db = [(str(p["host_ip"]), str(p["published"])) for p in db["ports"]]
assert puertos_web == [("127.0.0.1", "8081")], f"web must publish only 127.0.0.1:8081, got {puertos_web}"
assert puertos_db == [("127.0.0.1", "3307")], f"db must publish only 127.0.0.1:3307, got {puertos_db}"

print("PASS: matching credentials, healthy dependency and week ports for", web_env["DB_USER"])'
done

# Los scripts de inicialización tienen que existir: si falta alguno,
# el esquema no se crea en un volumen nuevo y la app falla al arrancar.
test -f docker/initdb/01-esquema.sql
test -f docker/initdb/02-semilla.sql
test -f docker/initdb/03-usuario.sh
echo "PASS: initdb scripts present (esquema, semilla, usuario)"
