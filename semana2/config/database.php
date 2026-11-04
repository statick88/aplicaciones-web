<?php

declare(strict_types=1);

/**
 * Conexión MySQL parametrizada por variables de entorno (ver compose.yaml).
 * Repara el em-dash del archivo original: `$conexion —> set_charset(...)`.
 */
$host = getenv('DB_HOST') ?: 'db';
$puerto = (int) (getenv('DB_PORT') ?: '3306');
$base = getenv('DB_NAME') ?: 'sexto';
$usuario = getenv('DB_USER') ?: 'root';
$clave = getenv('DB_PASS') ?: 'root';

try {
    $conexion = new mysqli($host, $usuario, $clave, $base, $puerto);
} catch (mysqli_sql_exception $exception) {
    http_response_code(503);
    die('Error al conectar con la base de datos: ' . $exception->getMessage());
}

$conexion->set_charset('utf8mb4');
