<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

startSession();
$csrf = $_SESSION['csrf'];
$json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
$input = $_POST;
try {
    $service = new RentalService(database());
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $resource = is_string($_GET['resource'] ?? null) ? $_GET['resource'] : 'vehiculos';
        $result = ['data' => $service->list($resource)];
        $status = 200;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_string($input['csrf'] ?? null) || !hash_equals($csrf, $input['csrf'])) {
            throw new RentalError('Formulario vencido. Recargue la página e intente nuevamente.', 403);
        }
        $action = is_string($input['action'] ?? null) ? $input['action'] : '';
        $result = $service->execute($action, $input);
        $status = $result['status'];
        $_SESSION['message'] = 'Operación realizada correctamente.';
    } else {
        header('Allow: GET, POST');
        throw new RentalError('Método no permitido.', 405);
    }
} catch (Throwable $error) {
    $status = $error instanceof RentalError ? $error->status : 503;
    $result = ['error' => $error instanceof RentalError ? $error->getMessage()
        : 'No fue posible completar la operación. No se guardaron cambios.'];
    if (!$error instanceof RentalError) {
        error_log('Rental operation failed: ' . $error->getCode());
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$json) {
        $_SESSION['error'] = $result['error'];
        unset($input['csrf']);
        $_SESSION['old'] = $input;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$json) {
    $action = is_string($input['action'] ?? null) ? $input['action'] : '';
    $tab = str_contains($action, 'vehiculo') ? 'vehiculos'
        : (str_contains($action, 'cliente') ? 'clientes' : 'alquileres');
    header('Location: /?tab=' . $tab, true, 303);
    exit;
}
session_write_close();
http_response_code($status);
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');
echo json_encode([...$result, 'csrf' => $csrf], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
