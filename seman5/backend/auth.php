<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    $user = usuario();
    // An expired session was destroyed: start a fresh anonymous session for login.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    token();
    responder(200, ['usuario' => $user]);
}
if (!in_array($method, ['POST', 'DELETE'], true)) {
    header('Allow: GET, POST, DELETE');
    responder(405, ['mensaje' => 'Método no permitido.']);
}
validarXsrf();
if ($method === 'DELETE') {
    limpiarSesion();
    responder(200, ['estado' => true]);
}

$input = datos();
if (!is_string($input['correo'] ?? null) || !is_string($input['password'] ?? null)
    || !filter_var($input['correo'], FILTER_VALIDATE_EMAIL) || $input['password'] === '') {
    responder(400, ['mensaje' => 'Ingresá un correo y una contraseña válidos.']);
}
// Limit repeated attempts per session; after the cooldown a user can retry.
$attempts = $_SESSION['intentos'] ?? 0;
$last = $_SESSION['ultimo_intento'] ?? 0;
if ($attempts >= 5 && time() - $last < 60) {
    header('Retry-After: 60');
    responder(429, ['mensaje' => 'Demasiados intentos. Esperá un minuto y volvé a intentar.']);
}
if (time() - $last >= 60) {
    $_SESSION['intentos'] = 0;
}
$db = Conexion::conectar();
$query = $db->prepare('SELECT id, nombre, correo, password_hash FROM usuarios WHERE correo = ?');
$email = trim($input['correo']);
$query->bind_param('s', $email);
$query->execute();
$user = $query->get_result()->fetch_assoc();
if ($user === null || !password_verify($input['password'], $user['password_hash'])) {
    $_SESSION['intentos'] = ($_SESSION['intentos'] ?? 0) + 1;
    $_SESSION['ultimo_intento'] = time();
    responder(401, ['mensaje' => 'Correo o contraseña incorrectos.']);
}
session_regenerate_id(true);
unset($user['password_hash']);
$user['id'] = (int) $user['id'];
$_SESSION = ['usuario' => $user, 'actividad' => time(), 'xsrf' => bin2hex(random_bytes(32))];
token();
responder(200, ['usuario' => $user]);
