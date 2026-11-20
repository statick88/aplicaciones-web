SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS Vehiculos (
    vehiculo_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(80) NOT NULL,
    modelo VARCHAR(80) NOT NULL,
    `año` SMALLINT UNSIGNED NOT NULL,
    disponible BOOLEAN NOT NULL DEFAULT TRUE,
    CHECK (`año` BETWEEN 1886 AND 2100),
    CHECK (disponible IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Clientes (
    cliente_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    licencia VARCHAR(40) NOT NULL UNIQUE,
    telefono VARCHAR(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Alquileres (
    alquiler_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehiculo_id INT UNSIGNED NOT NULL,
    cliente_id INT UNSIGNED NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_devolucion DATE NULL,
    vehiculo_activo INT UNSIGNED GENERATED ALWAYS AS
        (CASE WHEN fecha_devolucion IS NULL THEN vehiculo_id ELSE NULL END) STORED,
    UNIQUE KEY un_alquiler_activo (vehiculo_activo),
    FOREIGN KEY (vehiculo_id) REFERENCES Vehiculos (vehiculo_id) ON DELETE RESTRICT,
    FOREIGN KEY (cliente_id) REFERENCES Clientes (cliente_id) ON DELETE RESTRICT,
    CHECK (fecha_devolucion IS NULL OR fecha_devolucion >= fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
