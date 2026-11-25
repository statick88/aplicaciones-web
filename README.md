<p align="center">
  <img src="semana2/logo-uniandes.png" alt="UNIANDES — Universidad Regional Autónoma de los Andes" width="320">
</p>

# Aplicaciones Web — Portafolio de aprendizaje

Repositorio académico de **Aplicaciones Web**, Carrera de Software de **UNIANDES**, organizado por semanas. Reúne las prácticas, el código, las pruebas y las evidencias desarrolladas durante la asignatura.

La ruta de aprendizaje se basa en el **Sílabo de Aplicaciones Web**, período **octubre de 2026 – marzo de 2027**. La planificación y las entregas realizadas se presentan por separado: un tema previsto no implica que su implementación ya esté terminada.

## Datos de la asignatura

| Dato | Información del sílabo |
|---|---|
| Universidad | Universidad Regional Autónoma de los Andes — UNIANDES |
| Facultad / carrera | Sistemas Mercantiles / Software |
| Sede y modalidad | Matriz Ambato / En línea |
| Código | `SOF06AW` |
| Período académico | VI — Octubre de 2026 a marzo de 2027 |
| Docente | Ing. Luis Antonio Llerena Ocaña, PhD |
| Créditos / horas | 3 créditos / 144 horas |
| Distribución | 32 horas de contacto con el docente, 16 de prácticas y 96 de aprendizaje autónomo |

**Objetivo general:** desarrollar aplicaciones informáticas en entornos web con criterios de diseño, usabilidad y eficacia para resolver problemas utilizando la filosofía del software libre.

## Planificación semanal

Síntesis de la sección **4. Contenidos de la asignatura** del sílabo, páginas impresas **4–7**. La columna de trabajo describe el enfoque previsto; no establece tareas adicionales ni sustituye las consignas del docente.

| Semana | Unidad | Contenidos planificados | Trabajo previsto |
|---|---|---|---|
| **1** | **1. Introducción a la creación de páginas dinámicas con PHP** | 1.1 Introducción a páginas dinámicas con PHP. 1.2 Características y variables de .NET. | Reconocer el procesamiento del lado del servidor y trabajar con variables en una página dinámica. |
| **2** | **1. Introducción a la creación de páginas dinámicas con PHP** | 1.3 Tipos de datos simples y variables de variables. 1.4 Envío de variables por formulario; métodos GET y POST. | Utilizar tipos de datos y procesar información enviada mediante formularios. |
| **3** | **2. Acceso a datos con PHP y MySQL** | 2.1 Acceso a datos con PHP y MySQL. 2.2 Estructuras de control y funciones. | Conectar PHP con la base de datos y organizar el procesamiento mediante condiciones, ciclos y funciones. |
| **4** | **2. Acceso a datos con PHP y MySQL** | 2.3 Cadenas de caracteres, expresiones regulares y arreglos. 2.4 Programación orientada a objetos. **Evaluación parcial I**. | Manipular y organizar datos, aplicar clases y objetos e integrar los contenidos del primer parcial. |
| **5** | **3. Mantenimiento de tablas** | 3.1 Mantenimiento de tablas. 3.2 Conexión con base de datos: INSERT y SELECT. | Registrar y consultar información persistida desde la aplicación web. |
| **6** | **3. Mantenimiento de tablas** | 3.3 Conexión con MySQL: UPDATE y DELETE. 3.4 Variables de sesión y cookies. | Actualizar y eliminar registros; utilizar sesiones y cookies para mantener estado. |
| **7** | **4. Ajax en PHP** | 4.1 Ajax en PHP. 4.2 Web 2.0 y el objeto XMLHttp. | Incorporar comunicación asíncrona entre el navegador y PHP. |
| **8** | **4. Ajax en PHP** | 4.3 JavaScript avanzado y jQuery. 4.4 Gestión de jQuery con procesamiento de formularios. **Evaluación parcial II**. | Procesar formularios con interacción dinámica e integrar los contenidos del segundo parcial. |
| **9** | **Evaluación final** | Evaluación final de la asignatura. | Preparar la evaluación acumulativa de los contenidos del curso. |

> **Nota de fidelidad a la fuente:** la mención a «Características y Variables de .NET» en Semana 1 se conserva porque aparece así en el sílabo. No se reemplaza silenciosamente por PHP ni se presenta como una práctica .NET ya implementada.

## Trabajo desarrollado

| Entrega | Implementación disponible | Documentación |
|---|---|---|
| **Semana 1** | Web personal con HTML/CSS y calculadora PHP: operaciones aritméticas, validación, entorno Docker y pruebas automatizadas. | [Ver práctica y ejecución](semana1/README.md) |
| **Semana 2** | CRUD de clientes con POO, contrato de repositorio, adaptadores en memoria y MariaDB, formularios, validación, Docker Compose, pruebas unitarias y E2E, y capturas de SQLTools con identidad UNIANDES. | [Ver práctica, consultas SQL y evidencias](semana2/README.md) |
| **Semana 3** | CRUD web en C# con ASP.NET Core MVC, 12 campos, MySQL, Docker Compose, UTF-8, 44 pruebas unitarias y E2E contra una base aislada. | [Ver ejecución, arquitectura y evidencias](semana3/README.md) |
| **Semana 4** | Sistema de biblioteca con scaffolding de Laravel: 5 entidades de máximo 5 atributos cada una, relacionadas entre sí, CRUD completo con validación, MySQL, Docker Compose y 23 pruebas automatizadas. | [Ver ejecución, arquitectura y evidencias](semana4/README.md) |
| **Semana 5** | CRUD de clientes con Angular 22.3.0, interfaces, servicios y componentes; API PHP, autenticación por sesión, formularios reactivos, MariaDB y Docker Compose. Node 26 y pnpm; pruebas, demo y capturas con identidad UNIANDES. | [Ver ejecución, pruebas y capturas](seman5/README.md) |
| **Semanas 6–8** | Contenidos planificados; todavía no hay entregas semanales independientes en este repositorio. | Consultar la planificación anterior. |
| **Semana 9** | Evaluación final prevista en el sílabo; no se registra aquí una evaluación realizada. | Según las instrucciones del docente. |

**Relación entre planificación y práctica:** la consigna desarrollada en Semana 2 pide crear el objeto `Cliente` y sus métodos CRUD usando POO. Por eso la implementación ya utiliza POO y operaciones de base de datos que el sílabo profundiza en las semanas 3–6. La Semana 3 utiliza C# y MySQL según su consigna; la Semana 4 aplica scaffolding con Laravel y la Semana 5 integra Angular con una API PHP basada en el ejemplo del docente. Las semanas 6–8 conservan su estado planificado.

## Navegar y ejecutar las prácticas

```text
.
├── README.md       # Presentación, planificación y avance del curso
├── semana1/        # Web personal y calculadora PHP
├── semana2/        # CRUD de clientes con PHP y MariaDB
├── semana3/        # CRUD con C#, ASP.NET Core y MySQL
├── semana4/        # Biblioteca con scaffolding de Laravel
└── seman5/         # Clientes con Angular y autenticación PHP
    ├── README.md   # Inicio rápido, cuenta local, pruebas y capturas
    ├── frontend/   # Interfaces, servicios y componentes Angular
    ├── backend/    # API PHP y sesiones
    ├── database/   # Esquema de clientes y usuarios
    ├── docs/       # Cuatro capturas y demo breve
    └── tests/      # Pruebas HTTP y colección manual
```

Cada práctica tiene su propio README y entorno Docker. **Las semanas 1 y 2 comparten el puerto web `8080`: ejecutarlas por separado con su configuración predeterminada.**

| Entrega | Acceso web | Otros puertos locales |
|---|---|---|
| Semana 3 | `http://localhost:8081` | MySQL: `3307` |
| Semana 4 | `http://localhost:8082` | MySQL: `3308` |
| Semana 5 | `http://localhost:8086` | API PHP: `8085`; MariaDB sin puerto publicado |

Para ejecutar la entrega más reciente desde la raíz del repositorio:

```bash
cd seman5
docker compose up -d --build --wait
```

Después, [crear una cuenta local](seman5/README.md#crear-una-cuenta-local) y abrir `http://localhost:8086`. Consultar sus [capturas](seman5/README.md#capturas) y [demo](seman5/docs/demo.webm).

Los entornos son de desarrollo académico local, no de producción. `docker compose down` detiene Semana 5 sin eliminar su volumen de datos.

## Evaluaciones

- **Semana 4:** evaluación parcial I.
- **Semana 8:** evaluación parcial II.
- **Semana 9:** evaluación final, escrita y acumulativa.

Según la sección **5. Forma de evaluación**, cada parcial combina la evaluación frecuente y la evaluación parcial, ambas sobre 10 puntos. Para aprobar se requiere aprobar la evaluación final con al menos **7/10** y alcanzar como mínimo **21 puntos** en la suma de los dos parciales y la evaluación final. El sílabo contempla una evaluación de recuperación escrita y acumulativa, sobre 9 puntos, con mínimo de 7.

Las fechas concretas y las consignas de cada entrega deben consultarse en el aula virtual; no se deducen del período académico.

## Fuente y autor

- **Fuente de planificación:** `Silabo_Aplicaciones_Web-1.pdf`, proporcionado por el estudiante desde su carpeta Downloads. Se consultaron los datos informativos y las secciones 2, 4 y 5; el PDF no se incorpora al repositorio.
- **Institución:** [UNIANDES](https://www.uniandes.edu.ec/).
- **Identidad visual:** se reutiliza el logo institucional incluido en la práctica de Semana 2.
- **Autor del portafolio:** Diego Saavedra.
