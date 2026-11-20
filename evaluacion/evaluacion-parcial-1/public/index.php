<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

function formValue(array|null $record, string $key, string $fallback = ''): string
{
    $value = $record[$key] ?? $fallback;
    return escape(is_scalar($value) ? (string) $value : $fallback);
}

function selectedValue(array|null $record, string $key, string $option, string $fallback = ''): string
{
    $value = $record[$key] ?? $fallback;
    return is_scalar($value) && (string) $value === $option ? ' selected' : '';
}

function scalarValue(array $record, string $key): string
{
    $value = $record[$key] ?? '';
    return is_scalar($value) ? (string) $value : '';
}

startSession();
$tab = is_string($_GET['tab'] ?? null) ? $_GET['tab'] : 'alquileres';
$tab = in_array($tab, ['alquileres', 'vehiculos', 'clientes'], true) ? $tab : 'alquileres';
$message = is_string($_SESSION['message'] ?? null) ? $_SESSION['message'] : null;
$error = is_string($_SESSION['error'] ?? null) ? $_SESSION['error'] : null;
$old = is_array($_SESSION['old'] ?? null) ? $_SESSION['old'] : [];
unset($_SESSION['message'], $_SESSION['error'], $_SESSION['old']);

$vehicles = $clients = $rentals = [];
$databaseError = false;
try {
    $service = new RentalService(database());
    $vehicles = $service->list('vehiculos');
    $clients = $service->list('clientes');
    $rentals = $service->list('alquileres');
} catch (Throwable $exception) {
    http_response_code(503);
    $databaseError = true;
    error_log('Rental page unavailable: ' . $exception->getCode());
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editVehicle = null;
$editClient = null;
if ($tab === 'vehiculos' && $editId) {
    foreach ($vehicles as $vehicle) {
        if ((int) $vehicle['vehiculo_id'] === $editId) {
            $editVehicle = $vehicle;
            break;
        }
    }
}
if ($tab === 'clientes' && $editId) {
    foreach ($clients as $client) {
        if ((int) $client['cliente_id'] === $editId) {
            $editClient = $client;
            break;
        }
    }
}

$vehicleForm = $editVehicle;
$clientForm = $editClient;
$editingVehicle = $editVehicle !== null;
$editingClient = $editClient !== null;
if ($old && scalarValue($old, 'action') === 'guardar_vehiculo') {
    $tab = 'vehiculos';
    if (scalarValue($old, 'vehiculo_id') !== '') {
        $vehicleForm = $old;
        $editingVehicle = true;
    } else {
        $vehicleForm = $old;
        $editingVehicle = false;
    }
}
if ($old && scalarValue($old, 'action') === 'guardar_cliente') {
    $tab = 'clientes';
    if (scalarValue($old, 'cliente_id') !== '') {
        $clientForm = $old;
        $editingClient = true;
    } else {
        $clientForm = $old;
        $editingClient = false;
    }
}

$returnDates = [];
if (scalarValue($old, 'action') === 'devolver_alquiler') {
    $rentalId = filter_var(scalarValue($old, 'alquiler_id'), FILTER_VALIDATE_INT);
    $returnDate = scalarValue($old, 'fecha_devolucion');
    if ($rentalId && $returnDate !== '') {
        $returnDates[$rentalId] = $returnDate;
    }
}

$availableVehicles = array_filter(
    $vehicles,
    static fn(array $vehicle): bool => (int) $vehicle['disponible'] === 1
);
$today = (new DateTimeImmutable('today'))->format('Y-m-d');
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Evaluación Parcial 1 | Alquiler de vehículos</title>
  <link rel="icon" href="data:,">
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<a class="skip-link" href="#main-content">Saltar al contenido principal</a>
<header class="site-header">
  <div class="shell header-content">
    <div class="institutional-identity">
      <img class="institutional-logo" src="/logo-uniandes.png" alt="Logotipo institucional de UNIANDES" width="1024" height="368">
      <div>
        <p class="eyebrow">Universidad Regional Autónoma de los Andes · Aplicaciones Web</p>
        <h1>Evaluación Parcial 1</h1>
        <p class="subtitle">Sistema de gestión de alquiler de vehículos</p>
      </div>
    </div>
    <nav class="module-nav" aria-label="Módulos">
      <?php foreach (['alquileres' => 'Alquileres', 'vehiculos' => 'Vehículos', 'clientes' => 'Clientes'] as $key => $label): ?>
        <a href="/?tab=<?= $key ?>"<?= $tab === $key ? ' aria-current="page"' : '' ?>><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>
<main id="main-content" class="shell" tabindex="-1">
  <?php if ($databaseError): ?>
    <section class="notice error" role="alert">
      <h2>Servicio no disponible</h2>
      <p>No fue posible consultar los registros. Intente nuevamente en unos momentos.</p>
    </section>
  <?php else: ?>
    <?php if ($message): ?>
      <p class="notice success" role="status"><?= escape($message) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
      <p class="notice error" role="alert"><?= escape($error) ?></p>
    <?php endif; ?>

    <?php if ($tab === 'alquileres'): ?>
      <section class="panel" aria-labelledby="rental-title">
        <h2 id="rental-title">Registrar alquiler</h2>
        <p class="module-help">Asigne un vehículo disponible a un cliente registrado e indique la fecha de inicio.</p>
        <?php if (!$availableVehicles || !$clients): ?>
          <p class="empty">
            Para registrar un alquiler necesita <?= !$availableVehicles ? 'un vehículo disponible' : '' ?><?= !$availableVehicles && !$clients ? ' y ' : '' ?><?= !$clients ? 'un cliente registrado' : '' ?>.
          </p>
          <p>
            <?php if (!$availableVehicles): ?><a href="/?tab=vehiculos">Ir a Vehículos</a><?php endif; ?>
            <?php if (!$availableVehicles && !$clients): ?> · <?php endif; ?>
            <?php if (!$clients): ?><a href="/?tab=clientes">Ir a Clientes</a><?php endif; ?>
          </p>
        <?php else: ?>
          <form action="/api.php" method="post" aria-label="Registrar alquiler">
            <p class="form-guidance"><span aria-hidden="true">*</span> Campos obligatorios.</p>
            <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
            <input type="hidden" name="action" value="crear_alquiler">
            <div class="form-grid">
              <label>
                Vehículo
                <select name="vehiculo_id" required>
                  <option value="">Seleccione un vehículo</option>
                  <?php foreach ($availableVehicles as $vehicle): ?>
                    <option value="<?= escape($vehicle['vehiculo_id']) ?>"<?= selectedValue($old, 'vehiculo_id', (string) $vehicle['vehiculo_id']) ?>>
                      <?= escape($vehicle['marca'] . ' ' . $vehicle['modelo'] . ' · ' . $vehicle['año']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>
                Cliente
                <select name="cliente_id" required>
                  <option value="">Seleccione un cliente</option>
                  <?php foreach ($clients as $client): ?>
                    <option value="<?= escape($client['cliente_id']) ?>"<?= selectedValue($old, 'cliente_id', (string) $client['cliente_id']) ?>>
                      <?= escape($client['nombre'] . ' ' . $client['apellido'] . ' · ' . $client['licencia']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>
                Fecha de inicio
                <input type="date" name="fecha_inicio" value="<?= formValue($old, 'fecha_inicio', $today) ?>" required>
              </label>
            </div>
            <button type="submit">Registrar alquiler</button>
          </form>
        <?php endif; ?>
      </section>

      <section class="panel" aria-labelledby="rental-list-title">
        <h2 id="rental-list-title">Alquileres registrados</h2>
        <p class="module-help">Consulte el historial y registre la devolución de los alquileres que siguen activos.</p>
        <?php if (!$rentals): ?>
          <p class="empty">Aún no hay alquileres registrados.</p>
        <?php else: ?>
          <div class="table-scroll" role="region" aria-label="Desplace horizontalmente para ver la tabla completa" tabindex="0">
            <table>
              <caption>Historial de alquileres registrados</caption>
              <thead>
                <tr><th>ID</th><th>Vehículo</th><th>Cliente</th><th>Inicio</th><th>Estado</th><th>Devolución</th></tr>
              </thead>
              <tbody>
                <?php foreach ($rentals as $rental): ?>
                  <tr>
                    <td><?= escape($rental['alquiler_id']) ?></td>
                    <td><?= escape($rental['marca'] . ' ' . $rental['modelo']) ?></td>
                    <td><?= escape($rental['nombre'] . ' ' . $rental['apellido']) ?></td>
                    <td><?= escape($rental['fecha_inicio']) ?></td>
                    <td>
                      <span class="status <?= $rental['fecha_devolucion'] === null ? 'active' : 'returned' ?>">
                        <?= $rental['fecha_devolucion'] === null ? 'Activo' : 'Devuelto' ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($rental['fecha_devolucion'] === null): ?>
                        <form action="/api.php" method="post" class="return-form" aria-label="Devolver alquiler <?= escape($rental['alquiler_id']) ?>">
                          <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
                          <input type="hidden" name="action" value="devolver_alquiler">
                          <input type="hidden" name="alquiler_id" value="<?= escape($rental['alquiler_id']) ?>">
                          <label>
                            Fecha de devolución
                            <input type="date" name="fecha_devolucion" value="<?= escape($returnDates[(int) $rental['alquiler_id']] ?? $today) ?>" min="<?= escape($rental['fecha_inicio']) ?>" required>
                          </label>
                          <button type="submit">Registrar devolución</button>
                        </form>
                      <?php else: ?>
                        <?= escape($rental['fecha_devolucion']) ?>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <?php if ($tab === 'vehiculos'): ?>
      <section class="panel" aria-labelledby="vehicle-form-title">
        <h2 id="vehicle-form-title"><?= $editingVehicle ? 'Editar vehículo' : 'Registrar vehículo' ?></h2>
        <p class="module-help">Mantenga actualizada la información y disponibilidad de la flota.</p>
        <form action="/api.php" method="post" aria-label="<?= $editingVehicle ? 'Editar vehículo' : 'Registrar vehículo' ?>">
          <p class="form-guidance"><span aria-hidden="true">*</span> Campos obligatorios.</p>
          <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
          <input type="hidden" name="action" value="guardar_vehiculo">
          <?php if ($editingVehicle): ?>
            <input type="hidden" name="vehiculo_id" value="<?= formValue($vehicleForm, 'vehiculo_id') ?>">
          <?php endif; ?>
          <div class="form-grid">
            <label>Marca<input name="marca" maxlength="80" value="<?= formValue($vehicleForm, 'marca') ?>" required></label>
            <label>Modelo<input name="modelo" maxlength="80" value="<?= formValue($vehicleForm, 'modelo') ?>" required></label>
            <label>Año<input type="number" name="año" min="1886" max="2100" value="<?= formValue($vehicleForm, 'año') ?>" required></label>
            <label>
              Disponible
              <select name="disponible" required>
                <option value="1"<?= selectedValue($vehicleForm, 'disponible', '1', '1') ?>>Sí</option>
                <option value="0"<?= selectedValue($vehicleForm, 'disponible', '0') ?>>No</option>
              </select>
            </label>
          </div>
          <button type="submit"><?= $editingVehicle ? 'Actualizar vehículo' : 'Guardar vehículo' ?></button>
          <?php if ($editingVehicle): ?><a class="cancel" href="/?tab=vehiculos">Cancelar edición</a><?php endif; ?>
        </form>
      </section>

      <section class="panel" aria-labelledby="vehicle-list-title">
        <h2 id="vehicle-list-title">Vehículos registrados</h2>
        <p class="module-help">Revise la disponibilidad antes de iniciar un nuevo alquiler.</p>
        <?php if (!$vehicles): ?>
          <p class="empty">Aún no hay vehículos registrados.</p>
        <?php else: ?>
          <div class="table-scroll" role="region" aria-label="Desplace horizontalmente para ver la tabla completa" tabindex="0">
            <table>
              <caption>Vehículos registrados y su disponibilidad</caption>
              <thead>
                <tr><th>ID</th><th>Marca</th><th>Modelo</th><th>Año</th><th>Disponibilidad</th><th>Acciones</th></tr>
              </thead>
              <tbody>
                <?php foreach ($vehicles as $vehicle): ?>
                  <tr>
                    <td><?= escape($vehicle['vehiculo_id']) ?></td>
                    <td><?= escape($vehicle['marca']) ?></td>
                    <td><?= escape($vehicle['modelo']) ?></td>
                    <td><?= escape($vehicle['año']) ?></td>
                    <td><span class="status <?= (int) $vehicle['disponible'] ? 'returned' : 'active' ?>"><?= (int) $vehicle['disponible'] ? 'Disponible' : 'No disponible' ?></span></td>
                    <td class="actions">
                      <a href="/?tab=vehiculos&amp;edit=<?= escape($vehicle['vehiculo_id']) ?>">Editar</a>
                      <form action="/api.php" method="post">
                        <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
                        <input type="hidden" name="action" value="eliminar_vehiculo">
                        <input type="hidden" name="vehiculo_id" value="<?= escape($vehicle['vehiculo_id']) ?>">
                        <button class="link-button" type="submit">Eliminar</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <?php if ($tab === 'clientes'): ?>
      <section class="panel" aria-labelledby="client-form-title">
        <h2 id="client-form-title"><?= $editingClient ? 'Editar cliente' : 'Registrar cliente' ?></h2>
        <p class="module-help">Registre los datos de contacto y una licencia única para cada cliente.</p>
        <form action="/api.php" method="post" aria-label="<?= $editingClient ? 'Editar cliente' : 'Registrar cliente' ?>">
          <p class="form-guidance"><span aria-hidden="true">*</span> Campos obligatorios.</p>
          <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
          <input type="hidden" name="action" value="guardar_cliente">
          <?php if ($editingClient): ?>
            <input type="hidden" name="cliente_id" value="<?= formValue($clientForm, 'cliente_id') ?>">
          <?php endif; ?>
          <div class="form-grid">
            <label>Nombre<input name="nombre" maxlength="80" value="<?= formValue($clientForm, 'nombre') ?>" required></label>
            <label>Apellido<input name="apellido" maxlength="80" value="<?= formValue($clientForm, 'apellido') ?>" required></label>
            <label>Licencia<input name="licencia" maxlength="40" value="<?= formValue($clientForm, 'licencia') ?>" required></label>
            <label>Teléfono<input type="tel" name="telefono" maxlength="25" value="<?= formValue($clientForm, 'telefono') ?>" required></label>
          </div>
          <button type="submit"><?= $editingClient ? 'Actualizar cliente' : 'Guardar cliente' ?></button>
          <?php if ($editingClient): ?><a class="cancel" href="/?tab=clientes">Cancelar edición</a><?php endif; ?>
        </form>
      </section>

      <section class="panel" aria-labelledby="client-list-title">
        <h2 id="client-list-title">Clientes registrados</h2>
        <p class="module-help">Verifique los datos de los clientes antes de asignarles un vehículo.</p>
        <?php if (!$clients): ?>
          <p class="empty">Aún no hay clientes registrados.</p>
        <?php else: ?>
          <div class="table-scroll" role="region" aria-label="Desplace horizontalmente para ver la tabla completa" tabindex="0">
            <table>
              <caption>Clientes registrados y datos de contacto</caption>
              <thead>
                <tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Licencia</th><th>Teléfono</th><th>Acciones</th></tr>
              </thead>
              <tbody>
                <?php foreach ($clients as $client): ?>
                  <tr>
                    <td><?= escape($client['cliente_id']) ?></td>
                    <td><?= escape($client['nombre']) ?></td>
                    <td><?= escape($client['apellido']) ?></td>
                    <td><?= escape($client['licencia']) ?></td>
                    <td><?= escape($client['telefono']) ?></td>
                    <td class="actions">
                      <a href="/?tab=clientes&amp;edit=<?= escape($client['cliente_id']) ?>">Editar</a>
                      <form action="/api.php" method="post">
                        <input type="hidden" name="csrf" value="<?= escape($_SESSION['csrf']) ?>">
                        <input type="hidden" name="action" value="eliminar_cliente">
                        <input type="hidden" name="cliente_id" value="<?= escape($client['cliente_id']) ?>">
                        <button class="link-button" type="submit">Eliminar</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  <?php endif; ?>
</main>
</body>
</html>
