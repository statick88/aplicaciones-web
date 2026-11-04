<?php
/**
 * Vista del CRUD de clientes.
 *
 * @var list<Cliente> $clientes clientes a listar
 * @var list<string> $errores mensajes de error de validación
 * @var array<string, mixed> $datos datos del formulario (vacío = alta nueva)
 * @var string $accion crear|actualizar
 */

$tituloFormulario = $accion === 'actualizar' ? 'Guardar cambios' : 'Registrar cliente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes — Semana 2</title>
    <link rel="icon" href="favicon.png" type="image/png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="panel">
    <header class="encabezado">
        <img class="encabezado__logo" src="logo-uniandes.png" alt="Universidad de los Andes" width="220" height="79">
        <div>
            <h1>Gestión de clientes</h1>
            <p class="subtitulo">CRUD en POO — Aplicaciones Web, Semana 2</p>
        </div>
    </header>

    <?php if ($errores !== []): ?>
        <ul class="errores" role="alert">
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form class="formulario" method="post" action="index.php" novalidate>
        <input type="hidden" name="accion" value="<?= htmlspecialchars($accion, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) ($datos['id'] ?? 0) ?>">

        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ingrese su nombre"
                   value="<?= htmlspecialchars((string) ($datos['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="campo">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com"
                   value="<?= htmlspecialchars((string) ($datos['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="campo">
            <label for="telefono">Teléfono <span class="opcional">(opcional)</span></label>
            <input type="text" id="telefono" name="telefono" placeholder="0987654321"
                   value="<?= htmlspecialchars((string) ($datos['telefono'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <label class="casilla">
            <input type="checkbox" id="activo" name="activo" value="1" <?= !empty($datos['activo']) ? 'checked' : '' ?>>
            Cliente activo
        </label>

        <div class="acciones">
            <button type="submit" class="boton boton--primario"><?= $tituloFormulario ?></button>
            <?php if ($accion === 'actualizar'): ?>
                <a class="boton boton--secundario" href="index.php">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <h2>Clientes registrados (<?= count($clientes) ?>)</h2>
    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($clientes === []): ?>
            <tr><td colspan="6" class="vacio">No hay clientes registrados.</td></tr>
        <?php endif; ?>
        <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?= (int) $cliente->id ?></td>
                <td><?= htmlspecialchars($cliente->nombre, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($cliente->correo, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($cliente->telefono ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <span class="estado <?= $cliente->activo ? 'estado--activo' : 'estado--inactivo' ?>">
                        <?= $cliente->activo ? 'Activo' : 'Inactivo' ?>
                    </span>
                </td>
                <td class="acciones-fila">
                    <a href="index.php?editar=<?= (int) $cliente->id ?>">Editar</a>
                    <form method="post" action="index.php">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" value="<?= (int) $cliente->id ?>">
                        <button type="submit" class="enlace-peligro">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
