<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/conexion.php';
$email = $argv[1] ?? '';
$name = $argv[2] ?? 'Estudiante';
$password = rtrim(stream_get_contents(STDIN), "\r\n");
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 160
    || trim($name) === '' || strlen($name) > 80 || strlen($password) < 12) {
    fwrite(STDERR, "Correo/nombre inválido o contraseña de menos de 12 caracteres.\n");
    exit(1);
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$db = Conexion::conectar();
$query = $db->prepare('INSERT INTO usuarios (nombre, correo, password_hash) VALUES (?, ?, ?)');
$query->bind_param('sss', $name, $email, $hash);
try {
    $query->execute();
    echo "Usuario creado.\n";
} catch (mysqli_sql_exception) {
    fwrite(STDERR, "No se pudo crear el usuario. Revisá si el correo ya existe.\n");
    exit(1);
}
