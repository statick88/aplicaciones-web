# Informe de auditoría final — Evaluación Parcial 1

**Sistema:** gestión de alquiler de vehículos

**Repositorio canónico:** <https://github.com/statick88/aplicaciones-web>

**Commit base:** `06bb4a2a59b2d880fce7b22a5df4632a00f79f93`

**Commit de software auditado:** `15c88dd75fa7f327feff005872f2f3982c9cb930`

## 1. Resumen ejecutivo

Se comprobó el sistema académico de vehículos, clientes, alquileres y devoluciones. La evidencia local aislada contiene los 16 casos obligatorios, una comprobación de concurrencia, 10 comprobaciones HTTP de regresión, 10 comprobaciones de navegador y dos contratos de apoyo, todos aprobados.

No se confirmó ningún defecto de aplicación de severidad CRÍTICA o ALTA. El alcance no agrega pagos, facturación, reservas anticipadas ni autenticación obligatoria.

## 2. Estado inicial y trazabilidad

La ruta inicial era `evaluacion/`; se renombró a `Evaluacion Parcial 1/` y luego, por corrección expresa del nombre, a `evaluacion-parcial-1/`. Este último es el nombre definitivo. El punto de partida fue una copia limpia de la rama `main` en el commit base indicado. La rama de trabajo es `audit/evaluacion-alquileres`.

El software auditado corresponde al SHA `15c88dd75fa7f327feff005872f2f3982c9cb930`; este informe no atribuye sus resultados a un SHA futuro. El cambio de nombre conserva el nombre Compose `evaluacion`, por lo que no deriva un espacio de nombres o volumen nuevo por el nombre de carpeta.

La revisión de código confirmó PHP 8.5/Apache, MySQL 8.4, consultas preparadas, escape de salida, token de formulario, transacciones, bloqueos de vehículo, clave única para alquiler activo y claves foráneas restrictivas.

La normalización final del nombre de carpeta no modifica PHP, SQL ni CSS respecto del commit de software auditado. El nombre Compose sigue siendo `evaluacion`, con los mismos volúmenes. Una revisión independiente adicional confirmó las rutas definitivas de montaje, la configuración Compose, los dos volúmenes conservados y HTTP 200 tanto operativo como aislado. No se atribuye a esta revisión de nombre una nueva ejecución de las pruebas que modifican datos.

## 3. Cambios mínimos aplicados

| Hallazgo | Severidad | Corrección o estado |
| --- | --- | --- |
| Faltaban informe, README completo y evidencia inspeccionable | MEDIO | Se añaden esta documentación y el JSON sanitizado. |
| La comprobación local previa compartía datos y fijaba el puerto del navegador | MEDIO | Se sustituyó por ejecución aislada con proyecto, volumen y origen de navegador separados. |
| El registro de evidencia podía sobrescribir los casos anteriores al comprobar el reinicio | MEDIO | Se corrigió el manejo de evidencia y se repitieron los 17 registros. |
| Faltaba comprobación de salud HTTP en el servicio web | BAJO | Se añadió `HEALTHCHECK` al contenedor web. |

No se modificaron la lógica PHP de dominio ni el esquema para esta documentación. No hay hallazgos confirmados CRÍTICOS o ALTOS.

## 4. Matriz de requisitos y casos

Los resultados obtenidos completos están en [resultados-auditoria.json](evidencias/resultados-auditoria.json). Las entradas son ficticias; los identificadores `T01` y `T02` se refieren a los registros creados por los casos precedentes.

| Caso | Entrada | Esperado | Obtenido | Estado |
| --- | --- | --- | --- | --- |
| T01 | Toyota / Corolla / 2024 / disponible | 201 y vehículo persistido | 201, id 4 | PASS |
| T02 | Cliente Auditoría Ficticio / licencia ficticia | 201 y cliente persistido | 201, id 5 | PASS |
| T03 | vehículo T01 | GET exacto | 200, id 4, coincidencia exacta | PASS |
| T04 | cliente T02 | GET exacto | 200, id 5, coincidencia exacta | PASS |
| T05 | Corolla Auditada / 2023 | 200 y campos actualizados | 200, id 4, todos verificados | PASS |
| T06 | Cliente Auditoría Actualizado | 200 y campos actualizados | 200, id 5, todos verificados | PASS |
| T07 | inicio 2026-11-01 | 201 y alquiler persistido | 201, id 4 | PASS |
| T08 | alquiler T07 | historial con ids, inicio y devolución nula | 200, id 4, coincidencia exacta | PASS |
| T09 | vehículo ocupado T01 / 2026-11-02 | 409, sin cambios | 409, «El vehículo no está disponible.», colecciones intactas | PASS |
| T10 | devolución 2026-11-02 | 200, devolución y disponibilidad | 200, id 4, disponible 1 | PASS |
| T11 | T07 / devolución 2026-11-03 | 409, sin cambios | 409, «El alquiler ya fue devuelto.», colecciones intactas | PASS |
| T12 | licencia de T02 | 409, sin cambios | 409, mensaje de licencia/relación, colecciones intactas | PASS |
| T13 | inicio 2026-02-30; devolución 2026-10-31 | 422 para ambas fechas, sin cambios | 422 por fecha inválida y por devolución anterior | PASS |
| T14 | vehículo T01 y cliente T02 con historial | 409, sin cambios | 409 para ambos, colecciones intactas | PASS |
| T15 | token ausente e inválido | 403, sin cambios | 403 en ambos, «Formulario vencido…» | PASS |
| C01 | Toyota Corolla Concurrente, dos sesiones | 201+409 y un alquiler activo | [201, 409], 1 fila, disponible 0 | PASS |
| T16 | reinicio de ambos servicios aislados | mismas filas públicas e ids | instantánea pública igual | PASS |

T01–T16 cubren el alta, consulta, edición, alquiler, devolución, validaciones, conservación del historial, token de formulario y persistencia solicitados. C01 aporta la comprobación de concurrencia para la regla de un alquiler activo.

## 5. Pruebas ejecutadas

- Casos obligatorios: `T01`–`T16`, 16 de 16 PASS.
- Concurrencia: `C01`, 1 de 1 PASS.
- Regresión HTTP heredada: 10 de 10 PASS.
- Navegador: `B01`–`B10`, 10 de 10 PASS.
- Contratos de apoyo: 2 de 2 PASS.
- Sintaxis Bash, Python y JavaScript comprobada. ShellCheck no está instalado y no se ejecutó; no se presenta como una comprobación aprobada.

Las comprobaciones de navegador cubrieron carga del logotipo (B01), alta y edición de vehículo (B02–B03), alta y edición de cliente (B04), alquiler y disponibilidad (B05), devolución (B06), licencia duplicada con conservación del formulario (B07), teclado (B08), móvil 390×844 (B09) y escritorio 1280×900 (B10).

## 6. Evidencia de ejecución

La construcción e inicialización nuevas se ejecutaron en el proyecto aislado, desde el directorio de la evaluación:

```bash
docker compose -p evaluacion_audit_20261120 -f compose.yaml -f tests/compose.audit.yaml config
docker compose -p evaluacion_audit_20261120 -f compose.yaml -f tests/compose.audit.yaml up -d --build --wait --wait-timeout 180
docker compose -p evaluacion_audit_20261120 -f compose.yaml -f tests/compose.audit.yaml ps
```

Después del renombrado se restableció el entorno operativo con `docker compose -p evaluacion up -d --no-build --wait`, conservando su volumen. El acceso HTTP de `127.0.0.1:8083` se verificó sin modificar registros. La variante `--build` se comprobó en el proyecto aislado, no se atribuye al restablecimiento operativo. Los ayudantes de `tests/` se conservan únicamente en la copia local.

La comprobación funcional usó un proyecto aislado, con volumen propio y puerto local de prueba distinto (`8084`); no usó datos operativos. Un primer acceso HTTP aislado falló con código 7 porque la red interna de Docker impidió publicar el puerto de host. Se resolvió mediante un puente local de confianza limitado a `127.0.0.1:8084` hacia el servicio interno, sin habilitar salida de red del contenedor ni modificar otro proyecto. Es una incidencia de infraestructura de prueba, no un defecto de la aplicación.

Antes del reinicio se identificó y conservó el volumen aislado. Se reiniciaron realmente web y MySQL, y T16 comparó las colecciones públicas `vehiculos`, `clientes` y `alquileres` con la instantánea previa: coincidieron. Esta comprobación describe ese punto de control, no el estado posterior a las interacciones de navegador.

Las capturas reales se enlazan a continuación:

- [Vehículos](capturas/vehiculos.png): registro y listado de vehículos.
- [Clientes](capturas/clientes.png): registro y listado de clientes.
- [Alquiler activo](capturas/alquiler-activo.png): vehículo no disponible durante el alquiler.
- [Devolución](capturas/devolucion.png): devolución registrada y disponibilidad restaurada.
- [Vista móvil](capturas/vista-movil.png): módulos sin desbordamiento horizontal a 390 px.

## 7. Defectos y resolución

Los cuatro hallazgos de la sección 3 se clasificaron como MEDIO, MEDIO, MEDIO y BAJO. Quedaron resueltos y se confirmó nuevamente la evidencia funcional después de la corrección del registro de casos.

No hay defecto confirmado de aplicación CRÍTICO o ALTO. El primer fallo de conectividad de la infraestructura aislada quedó documentado y resuelto sin atribuirlo al código del sistema.

## 8. Riesgos pendientes

Verificación independiente final: PASS, sin bloqueos. Se contrastaron los 17 registros T01–T16/C01 y las 10 comprobaciones B01–B10 con el JSON publicado; se repitieron dos contratos locales, cuatro verificaciones de sintaxis PHP, la validación Compose y HTTP 200. Se comprobaron salud, red interna, límites y volumen conservado. No se repitieron mutaciones de datos durante esa revisión.

La rama `audit/evaluacion-alquileres` se publicó y su SHA se confirmó contra el remoto. La entrega está disponible en el [Pull Request #1](https://github.com/statick88/aplicaciones-web/pull/1). `main` permanece en `06bb4a2a59b2d880fce7b22a5df4632a00f79f93`: no se empujó directamente a esa rama ni se fusionó el PR. La aprobación y la eventual fusión siguen siendo decisiones del propietario.

No se identifican riesgos funcionales pendientes dentro del alcance académico comprobado. Las medidas de producción como autenticación obligatoria o TLS no son requisitos de esta evaluación y no forman parte de esta estimación.

## 9. Estimación académica interna

| Criterio | Estimación | Archivos y evidencia | Comprobaciones | Cumplimiento / descuento |
| --- | ---: | --- | --- | --- |
| PHP/.NET | 0.50/0.50 | `Dockerfile`, `public/index.php`, `src/RentalService.php` | Construcción, salud HTTP, T01–T16 | Completo; sin descuento técnico observado |
| Vehículos | 1.25/1.25 | `database/schema.sql`, `src/RentalService.php`, `public/index.php` | T01, T03, T05, T14; regresión de eliminación válida; B02–B03 | Completo; sin descuento observado |
| Clientes | 1.25/1.25 | `database/schema.sql`, `src/RentalService.php`, `public/index.php` | T02, T04, T06, T12, T14; eliminación válida; B04 | Completo; sin descuento observado |
| Alquileres | 1.50/1.50 | `database/schema.sql`, `src/RentalService.php`, `public/api.php` | T07–T11, C01, B05–B06 | Completo; sin descuento observado |
| Persistencia e integridad | 1.00/1.00 | `compose.yaml`, `database/schema.sql`, volumen conservado | T14, T16, C01; reinicio real de ambos servicios | Completo; sin descuento observado |
| Funcionamiento y validación | 0.75/0.75 | `src/bootstrap.php`, `src/RentalService.php`, `public/api.php`, `public/index.php` | T09, T11–T15, regresión HTTP, B07–B10 | Completo; sin descuento funcional observado |
| Repositorio y documentación | 0.75/0.75 | `README.md`, este informe, `docs/evidencias/resultados-auditoria.json`, cinco capturas | Revisión de enlaces y firmas PNG; rama publicada y PR #1 abierto | Completo en la rama de auditoría; no atribuir su contenido a `main` antes de la aprobación |
| **Total estimado condicionado** | **7.00/7.00** | Evidencia verificable del software auditado | Verificación independiente y publicación confirmadas | No equivale a una nota del docente |

Esta es una estimación INTERNA, no una rúbrica oficial ni una calificación garantizada. La revisión independiente confirmó las comprobaciones esenciales y la rama está publicada. La estimación interna no reemplaza la decisión ni la rúbrica del docente.

## 10. Recomendación de entrega

La evidencia, la verificación independiente y la publicación del PR respaldan presentar el software auditado para revisión académica. Se recomienda revisar el PR y compartir su enlace; no se fusiona sin aprobación. La calificación final corresponde exclusivamente al docente. Al cerrar las comprobaciones se detuvieron únicamente los contenedores de prueba, se conservaron ambos volúmenes y el entorno operativo continuó respondiendo HTTP 200.
