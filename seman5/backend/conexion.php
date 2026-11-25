<?php
declare(strict_types=1);

// Adapted from lleroc/Sexto_OM26, Semana 5/backend/conexion.php.
final class Conexion
{
    public static function conectar(): mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $db = new mysqli(
            getenv('DB_HOST') ?: 'db',
            getenv('DB_USER') ?: 'clientes',
            getenv('DB_PASS') ?: '',
            getenv('DB_NAME') ?: 'clientes'
        );
        $db->set_charset('utf8mb4');
        return $db;
    }
}
