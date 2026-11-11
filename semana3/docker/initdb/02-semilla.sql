-- =====================================================================
-- Semilla de demostración (opcional).
--
-- Se ejecuta después de 01-esquema.sql y solo en un volumen nuevo.
-- Aporta dos filas para que el listado no arranque vacío en la
-- demostración; la prueba E2E NO depende de este archivo ni de la
-- tabla vacía: crea y borra sus propios registros con correo y cédula
-- propios, así que es indiferente si aquí hay datos.
-- =====================================================================
INSERT INTO clientes
    (cedula, nombre, apellido, correo, telefono, direccion, ciudad,
     fecha_nacimiento, tipo_cliente, limite_credito, activo, observaciones)
VALUES
    ('0102030405', 'Ana',   'Pérez',  'ana.perez@example.com',   '0987654321',
     'Av. Universitaria 123', 'Ambato', '1995-04-12', 'Natural', 1500.00, 1,
     'Cliente de demostración creado por el script de inicialización.'),
    ('0912345678', 'Luis',  'Torres', 'luis.torres@example.com', '0991112233',
     'Calle Bolívar 45',      'Riobamba', '1988-11-30', 'Jurídica', 7500.50, 1,
     'Cuenta corporativa con límite de crédito aprobado.');
