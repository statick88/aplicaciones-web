<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/cliente.php';

autenticar();
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
    header('Allow: GET, POST, PUT, DELETE');
    responder(405, ['mensaje' => 'Método no permitido.']);
}
if ($method !== 'GET') {
    validarXsrf();
}
$id = 0;
if (isset($_GET['id'])) {
    $raw = $_GET['id'];
    if (!is_string($raw) || !preg_match('/^[1-9][0-9]{0,9}$/D', $raw) || (float) $raw > 4294967295) {
        responder(400, ['mensaje' => 'Identificador inválido.']);
    }
    $id = (int) $raw;
}
if (in_array($method, ['PUT', 'DELETE'], true) && $id === 0) {
    responder(400, ['mensaje' => 'Se requiere un identificador.']);
}
$cliente = new Cliente(Conexion::conectar());
if ($id && $cliente->buscar($id) === null) {
    responder(404, ['mensaje' => 'El cliente no existe.']);
}
if ($method === 'GET') {
    responder(200, $id ? $cliente->buscar($id) : $cliente->listar());
}
if ($method === 'DELETE') {
    $cliente->eliminar($id);
    responder(200, ['estado' => true]);
}
$input = datos();
$clean = [];
foreach (['nombre' => 80, 'apellido' => 80, 'correo' => 160, 'telefono' => 25] as $field => $max) {
    if (!is_string($input[$field] ?? null)) {
        responder(400, ['mensaje' => 'Completá todos los campos.']);
    }
    $value = trim($input[$field]);
    if ($value === '' || strlen($value) > $max) {
        responder(400, ['mensaje' => 'Revisá la longitud de los campos.']);
    }
    $clean[$field] = $value;
}
if (!filter_var($clean['correo'], FILTER_VALIDATE_EMAIL)
    || !preg_match('/^[+0-9() .-]{7,25}$/D', $clean['telefono'])) {
    responder(400, ['mensaje' => 'El correo o el teléfono no son válidos.']);
}
$target = $method === 'POST' ? 0 : $id;
if ($cliente->correoExiste($clean['correo'], $target)) {
    responder(409, ['mensaje' => 'Ya existe un cliente con ese correo.']);
}
try {
    $saved = $cliente->guardar($clean, $target);
} catch (mysqli_sql_exception $error) {
    if ($error->getCode() === 1062) {
        responder(409, ['mensaje' => 'Ya existe un cliente con ese correo.']);
    }
    throw $error;
}
responder($method === 'POST' ? 201 : 200, ['estado' => true, 'id' => $saved]);
