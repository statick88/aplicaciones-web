<?php

declare(strict_types=1);

/**
 * Front controller del CRUD de clientes.
 * POST = acciones crear/actualizar/eliminar; GET = lista o edición (?editar=id).
 * Tras una mutación exitosa aplica PRG: header('Location: index.php'); exit;
 */
class ClienteController
{
    public function __construct(
        private RepositorioClientes $repositorio,
        private ValidadorClientes $validador,
    ) {}

    public function index(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->procesarPost();
            return;
        }

        $idEditando = 0;
        if (array_key_exists('editar', $_GET)) {
            $idEditando = filter_var($_GET['editar'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($idEditando === false) {
                $this->mostrarVista(['El identificador del cliente no es válido.']);
                return;
            }
        }
        $cliente = $idEditando > 0 ? $this->repositorio->consultar($idEditando) : null;
        $errores = ($idEditando > 0 && $cliente === null)
            ? ['No se encontró el cliente a editar.']
            : [];
        $datos = $cliente === null ? [] : [
            'id' => $cliente->id,
            'nombre' => $cliente->nombre,
            'correo' => $cliente->correo,
            'telefono' => $cliente->telefono ?? '',
            'activo' => $cliente->activo,
        ];

        $this->mostrarVista($errores, $datos);
    }

    private function procesarPost(): void
    {
        $accion = (string) ($_POST['accion'] ?? '');
        $id = (int) ($_POST['id'] ?? 0);

        if ($accion === 'eliminar') {
            if ($this->repositorio->eliminar($id)) {
                $this->redirigir();
            }

            $this->mostrarVista(['No se encontró el cliente a eliminar.']);
            return;
        }

        if ($accion !== 'crear' && $accion !== 'actualizar') {
            $this->mostrarVista(['La acción solicitada no es válida.']);
            return;
        }

        $telefono = trim((string) ($_POST['telefono'] ?? ''));
        $datos = [
            'id' => $accion === 'actualizar' ? $id : 0,
            'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'correo' => trim((string) ($_POST['correo'] ?? '')),
            'telefono' => $telefono === '' ? null : $telefono,
            'activo' => isset($_POST['activo']),
        ];

        $errores = $this->validador->validar($datos['nombre'], $datos['correo']);
        if ($errores !== []) {
            $this->mostrarVista($errores, $datos);
            return;
        }

        $cliente = new Cliente(
            $datos['id'],
            $datos['nombre'],
            $datos['correo'],
            $datos['activo'],
            $datos['telefono'],
        );

        if ($accion === 'actualizar') {
            if ($this->repositorio->actualizar($cliente)) {
                $this->redirigir();
            }

            $this->mostrarVista(['No se encontró el cliente a actualizar.'], $datos);
            return;
        }

        $this->repositorio->registrar($cliente);
        $this->redirigir();
    }

    /** @param list<string> $errores @param array<string, mixed> $datos */
    private function mostrarVista(array $errores = [], array $datos = []): void
    {
        $clientes = $this->repositorio->listar();
        $accion = ((int) ($datos['id'] ?? 0)) > 0 ? 'actualizar' : 'crear';

        require __DIR__ . '/../views/clientes.php';
    }

    private function redirigir(): void
    {
        header('Location: index.php');
        exit;
    }
}
