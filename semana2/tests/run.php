<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/ValidadorClientes.php';
require_once __DIR__ . '/../interfaces/RepositorioClientes.php';
require_once __DIR__ . '/../repositories/ClienteRepositorioMemoria.php';

/**
 * Tests unitarios del CRUD de Cliente (sin base de datos).
 * El repositorio en memoria es el adaptador de prueba del contrato RepositorioClientes.
 */
$tests = [
    'cliente conserva sus datos' => function (): bool {
        $cliente = new Cliente(1, 'Ana Pérez', 'ana@correo.com');

        return $cliente->id === 1
            && $cliente->nombre === 'Ana Pérez'
            && $cliente->correo === 'ana@correo.com'
            && $cliente->activo === true
            && $cliente->telefono === null;
    },
    'cliente acepta teléfono y estado inactivo' => function (): bool {
        $cliente = new Cliente(2, 'Luis Torres', 'luis@correo.com', activo: false, telefono: '0987654321');

        return $cliente->activo === false && $cliente->telefono === '0987654321';
    },
    'el repositorio en memoria cumple la interfaz RepositorioClientes' => function (): bool {
        return (new ClienteRepositorioMemoria()) instanceof RepositorioClientes;
    },
    'registrar y consultar un cliente' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $repo->registrar(new Cliente(1, 'Ana Pérez', 'ana@correo.com'));

        $cliente = $repo->consultar(1);

        return $cliente instanceof Cliente && $cliente->nombre === 'Ana Pérez' && $cliente->correo === 'ana@correo.com';
    },
    'consultar un id inexistente devuelve null' => function (): bool {
        $repo = new ClienteRepositorioMemoria();

        return $repo->consultar(99) === null;
    },
    'listar devuelve todos los clientes en orden de registro' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $repo->registrar(new Cliente(1, 'Ana Pérez', 'ana@correo.com'));
        $repo->registrar(new Cliente(2, 'Luis Torres', 'luis@correo.com'));
        $repo->registrar(new Cliente(3, 'Carla Gómez', 'carla@correo.com'));

        $lista = $repo->listar();

        return count($lista) === 3
            && $lista[0]->id === 1
            && $lista[1]->id === 2
            && $lista[2]->id === 3;
    },
    'actualizar modifica los datos del cliente' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $repo->registrar(new Cliente(1, 'Ana Pérez', 'ana@correo.com'));

        $resultado = $repo->actualizar(new Cliente(1, 'Ana P. Revíes', 'ana.perez@correo.com', activo: false, telefono: '0991112222'));
        $cliente = $repo->consultar(1);

        return $resultado === true
            && $cliente !== null
            && $cliente->nombre === 'Ana P. Revíes'
            && $cliente->correo === 'ana.perez@correo.com'
            && $cliente->activo === false
            && $cliente->telefono === '0991112222';
    },
    'actualizar un id inexistente devuelve false' => function (): bool {
        $repo = new ClienteRepositorioMemoria();

        return $repo->actualizar(new Cliente(404, 'Fantasma', 'fantasma@correo.com')) === false;
    },
    'eliminar quita el cliente' => function (): bool {
        $repo = new ClienteRepositorioMemoria();
        $repo->registrar(new Cliente(1, 'Ana Pérez', 'ana@correo.com'));

        $resultado = $repo->eliminar(1);

        return $resultado === true && $repo->consultar(1) === null && $repo->listar() === [];
    },
    'eliminar un id inexistente devuelve false' => function (): bool {
        $repo = new ClienteRepositorioMemoria();

        return $repo->eliminar(404) === false;
    },
    'validador rechaza un correo inválido' => function (): bool {
        $validador = new ValidadorClientes();

        $errores = $validador->validar('Ana Pérez', 'ana@correo');

        return $errores !== [] && in_array('El correo electrónico no es válido.', $errores, true);
    },
    'validador rechaza un nombre vacío' => function (): bool {
        $validador = new ValidadorClientes();

        $errores = $validador->validar('   ', 'ana@correo.com');

        return $errores !== [];
    },
    'validador acepta datos válidos' => function (): bool {
        $validador = new ValidadorClientes();

        return $validador->validar('Ana Pérez', 'ana@correo.com') === [];
    },
];

$failures = [];

foreach ($tests as $name => $test) {
    try {
        if (!$test()) {
            $failures[] = $name;
            fwrite(STDERR, "FAIL: {$name}\n");
            continue;
        }

        fwrite(STDOUT, "PASS: {$name}\n");
    } catch (Throwable $exception) {
        $failures[] = $name;
        fwrite(STDERR, "ERROR: {$name} — {$exception->getMessage()}\n");
    }
}

fwrite(STDOUT, sprintf("\n%d tests, %d failures.\n", count($tests), count($failures)));
exit($failures === [] ? 0 : 1);
