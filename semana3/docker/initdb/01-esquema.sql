-- =====================================================================
-- Esquema de la base `semana3`: tabla de clientes (CRUD en C#/.NET).
--
-- Se ejecuta automáticamente SOLO en el primer arranque de un volumen
-- nuevo (docker-entrypoint-initdb.d). Sobre un volumen existente no se
-- vuelve a ejecutar, igual que en la Semana 2.
--
-- La tabla cumple la consigna de "mínimo 10 campos": aquí hay
-- 12 campos de datos además de la clave `id`.
-- =====================================================================
CREATE TABLE IF NOT EXISTS clientes (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    cedula           VARCHAR(15)   NOT NULL,
    nombre           VARCHAR(100)  NOT NULL,
    apellido         VARCHAR(100)  NOT NULL,
    correo           VARCHAR(150)  NOT NULL,
    telefono         VARCHAR(30)   NULL,
    direccion        VARCHAR(200)  NULL,
    ciudad           VARCHAR(80)   NULL,
    fecha_nacimiento DATE          NULL,
    tipo_cliente     VARCHAR(20)   NOT NULL DEFAULT 'Natural',
    limite_credito   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    activo           TINYINT(1)    NOT NULL DEFAULT 1,
    observaciones    VARCHAR(500)  NULL,
    PRIMARY KEY (id),
    -- Unicidad a nivel de base: es la garantía definitiva de que dos
    -- registros no puedan compartir cédula ni correo, aunque la
    -- validación de la aplicación falle.
    UNIQUE KEY uq_clientes_cedula (cedula),
    UNIQUE KEY uq_clientes_correo (correo),
    KEY idx_clientes_apellido (apellido),
    KEY idx_clientes_ciudad (ciudad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
