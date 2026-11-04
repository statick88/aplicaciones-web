-- Esquema de la base `sexto`: tabla de clientes (CRUD de la Semana 2).
-- Se ejecuta automáticamente en el primer arranque de mariadb (docker-entrypoint-initdb.d).
CREATE TABLE IF NOT EXISTS clientes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    telefono VARCHAR(30) NULL,
    PRIMARY KEY (id),
    KEY idx_clientes_correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
