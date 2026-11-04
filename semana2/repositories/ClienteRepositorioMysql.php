<?php

declare(strict_types=1);

/**
 * Adaptador MySQL del contrato CRUD sobre la tabla `clientes`.
 * Sustituye al adaptador en memoria en la aplicación web (ver compose.yaml).
 */
class ClienteRepositorioMysql implements RepositorioClientes
{
    public function __construct(private mysqli $conexion) {}

    public function registrar(Cliente $cliente): void
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO clientes (nombre, correo, activo, telefono) VALUES (?, ?, ?, ?)'
        );

        $nombre = $cliente->nombre;
        $correo = $cliente->correo;
        $activo = $cliente->activo ? 1 : 0;
        $telefono = $cliente->telefono;

        $stmt->bind_param('ssis', $nombre, $correo, $activo, $telefono);
        $stmt->execute();
        $cliente->id = (int) $this->conexion->insert_id;
        $stmt->close();
    }

    public function consultar(int $id): ?Cliente
    {
        $stmt = $this->conexion->prepare(
            'SELECT id, nombre, correo, activo, telefono FROM clientes WHERE id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila === null ? null : $this->aCliente($fila);
    }

    /** @return list<Cliente> */
    public function listar(): array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id, nombre, correo, activo, telefono FROM clientes ORDER BY id'
        );
        $stmt->execute();

        $clientes = [];
        $resultado = $stmt->get_result();
        while ($fila = $resultado->fetch_assoc()) {
            $clientes[] = $this->aCliente($fila);
        }
        $stmt->close();

        return $clientes;
    }

    public function actualizar(Cliente $cliente): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE clientes SET nombre = ?, correo = ?, activo = ?, telefono = ? WHERE id = ?'
        );

        $nombre = $cliente->nombre;
        $correo = $cliente->correo;
        $activo = $cliente->activo ? 1 : 0;
        $telefono = $cliente->telefono;
        $id = $cliente->id;

        $stmt->bind_param('ssisi', $nombre, $correo, $activo, $telefono, $id);
        $stmt->execute();
        $filasAfectadas = $stmt->affected_rows;
        $stmt->close();

        if ($filasAfectadas > 0) {
            return true;
        }

        // 0 filas puede ser "sin cambios" o "id inexistente": preguntar a la base.
        return $this->consultar($id) !== null;
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conexion->prepare('DELETE FROM clientes WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $filasAfectadas = $stmt->affected_rows;
        $stmt->close();

        return $filasAfectadas > 0;
    }

    /** @param array<string, mixed> $fila */
    private function aCliente(array $fila): Cliente
    {
        return new Cliente(
            (int) $fila['id'],
            (string) $fila['nombre'],
            (string) $fila['correo'],
            (bool) $fila['activo'],
            $fila['telefono'] === null ? null : (string) $fila['telefono'],
        );
    }
}
