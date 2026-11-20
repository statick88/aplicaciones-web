# Evaluación Parcial 1 — Alquiler de vehículos

Sistema académico para gestionar vehículos, clientes, alquileres y devoluciones. Su objetivo es registrar y consultar la operación de alquiler, conservar el historial y evitar que un vehículo tenga dos alquileres activos simultáneamente.

## Tecnologías y requisitos

- PHP 8.5 con Apache.
- MySQL 8.4.
- Docker Engine y Docker Compose.
- Un navegador web para la interfaz.

El archivo Compose declara el nombre de proyecto estable `evaluacion`; por ello el espacio de nombres y el volumen de datos no dependen del nombre de esta carpeta.

## Inicio rápido

Desde la raíz del repositorio:

```bash
cd evaluacion/evaluacion-parcial-1
docker compose config
docker compose up -d --build --wait
docker compose ps
curl -i http://127.0.0.1:8083/
```

Abra `http://127.0.0.1:8083/` en el navegador. Para detener los servicios sin borrar los datos:

```bash
docker compose down
```

El esquema se inicializa al crear un volumen nuevo. Si ya existe una base de datos de este proyecto y hace falta aplicar el esquema idempotente sin borrar datos, ejecute:

```bash
docker compose exec -T mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysql --default-character-set=utf8mb4 -uroot evaluacion' < database/schema.sql
```

## Uso

1. En **Vehículos**, registre marca, modelo, año y disponibilidad; edite o elimine solo vehículos sin historial de alquileres.
2. En **Clientes**, registre nombre, apellido, licencia única y teléfono; edite o elimine solo clientes sin historial.
3. En **Alquileres**, seleccione un vehículo disponible, un cliente y una fecha de inicio.
4. Consulte el historial de alquileres y registre la devolución con una fecha igual o posterior al inicio.
5. Compruebe las eliminaciones válidas con una entidad sin historial; una entidad referenciada por un alquiler, incluso devuelto, se rechaza para preservar el historial.

Las validaciones muestran un mensaje comprensible y, ante un formulario rechazado, conservan sus valores. La interfaz dispone de enlace para saltar al contenido principal, foco de teclado y tablas desplazables en pantallas estrechas.

## Modelo de datos

| Tabla | Campos principales |
| --- | --- |
| `Vehiculos` | `vehiculo_id`, `marca`, `modelo`, `año`, `disponible` |
| `Clientes` | `cliente_id`, `nombre`, `apellido`, `licencia`, `telefono` |
| `Alquileres` | `alquiler_id`, `vehiculo_id`, `cliente_id`, `fecha_inicio`, `fecha_devolucion` |

`Alquileres.vehiculo_activo` es una columna generada con un índice único para impedir más de un alquiler activo por vehículo. Las claves foráneas usan restricción de borrado para conservar el historial.

## Evidencia comprobada

La comprobación local aislada registró estos resultados:

- 16 casos funcionales obligatorios (`T01`–`T16`) y una comprobación de concurrencia (`C01`): aprobados.
- 10 comprobaciones HTTP de regresión: aprobadas.
- 10 comprobaciones de navegador (`B01`–`B10`): aprobadas.
- 2 contratos de apoyo: aprobados.

El detalle sanitizado de entradas, resultados obtenidos y estados está en [docs/evidencias/resultados-auditoria.json](docs/evidencias/resultados-auditoria.json). El informe con la matriz y alcance está en [docs/INFORME_AUDITORIA_FINAL.md](docs/INFORME_AUDITORIA_FINAL.md).

### Capturas verificadas

| Vista | Captura |
| --- | --- |
| Registro y listado de vehículos | [Vehículos](docs/capturas/vehiculos.png) |
| Registro y listado de clientes | [Clientes](docs/capturas/clientes.png) |
| Alquiler activo | [Alquiler activo](docs/capturas/alquiler-activo.png) |
| Devolución registrada | [Devolución](docs/capturas/devolucion.png) |
| Vista móvil | [Vista móvil](docs/capturas/vista-movil.png) |

### Comprobación manual de la interfaz

1. Inicie los servicios y abra la dirección local indicada arriba.
2. Registre y edite un vehículo; confirme que el listado refleja los cambios.
3. Registre y edite un cliente con una licencia única.
4. Cree un alquiler y confirme que el vehículo queda como no disponible; registre la devolución y confirme que vuelve a estar disponible.
5. Intente registrar una licencia duplicada y confirme el mensaje y la conservación de los valores enviados.
6. Use `Tab` y `Enter` sobre el enlace de salto, y reduzca el navegador a 390 px para revisar los tres módulos.

## Alcance y límites conocidos

Es un entorno académico local: no incluye autenticación ni despliegue público. Los servicios se exponen en loopback; la base de datos no requiere un puerto de host para el funcionamiento comprobado. Los datos de demostración de la auditoría son ficticios.

Los guiones de comprobación automatizada se conservaron solo de forma local por una restricción previa del usuario; no se incluyen como material publicado ni se afirma que un clon nuevo pueda ejecutarlos. El JSON sanitizado y las capturas permiten inspeccionar la evidencia publicada.

Repositorio canónico: <https://github.com/statick88/aplicaciones-web>
