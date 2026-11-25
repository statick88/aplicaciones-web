<?php
declare(strict_types=1);
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function responder(int $codigo, array $datos): never
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

set_exception_handler(function (Throwable $error): never {
    // Do not expose connection details, SQL or personal data.
    error_log('API error: ' . get_class($error));
    responder(500, ['mensaje' => 'No se pudo completar la operación. Intentá nuevamente.']);
});

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('UNIANDES_SESSION');
$cookie = [
    'path' => '/',
    'secure' => getenv('COOKIE_SECURE') === '1',
    'httponly' => true,
    'samesite' => 'Lax',
];
session_set_cookie_params($cookie);
session_start();

function token(): void
{
    global $cookie;
    $_SESSION['xsrf'] ??= bin2hex(random_bytes(32));
    setcookie('XSRF-TOKEN', $_SESSION['xsrf'], array_merge($cookie, ['httponly' => false]));
}

function limpiarSesion(): void
{
    global $cookie;
    $_SESSION = [];
    session_destroy();
    setcookie(session_name(), '', array_merge($cookie, ['expires' => 1]));
    setcookie('XSRF-TOKEN', '', array_merge($cookie, ['expires' => 1, 'httponly' => false]));
}

function usuario(): ?array
{
    $ttl = max(1, (int) (getenv('SESSION_TTL') ?: 1800));
    if (isset($_SESSION['usuario']) && time() - ($_SESSION['actividad'] ?? 0) >= $ttl) {
        limpiarSesion();
        return null;
    }
    if (isset($_SESSION['usuario'])) {
        $_SESSION['actividad'] = time();
        return $_SESSION['usuario'];
    }
    return null;
}

function autenticar(): array
{
    $user = usuario();
    if ($user === null) {
        responder(401, ['mensaje' => 'Tu sesión terminó. Iniciá sesión nuevamente.']);
    }
    return $user;
}

function validarXsrf(): void
{
    $recibido = $_SERVER['HTTP_X_XSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['xsrf']) || !hash_equals($_SESSION['xsrf'], $recibido)) {
        responder(403, ['mensaje' => 'La solicitud no es válida. Recargá la página.']);
    }
}

function datos(): array
{
    try {
        $body = json_decode(file_get_contents('php://input'), true, 16, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        responder(400, ['mensaje' => 'El cuerpo debe ser JSON válido.']);
    }
    if (!is_array($body) || array_is_list($body)) {
        responder(400, ['mensaje' => 'Se esperaba un objeto JSON.']);
    }
    return $body;
}
