<?php
declare(strict_types=1);

// Prepared-statement CRUD adapted from the instructor's Semana 5/cliente.php.
final class Cliente
{
    public function __construct(private mysqli $db) {}

    public function listar(): array
    {
        return $this->db->query('SELECT id, nombre, apellido, correo, telefono FROM clientes ORDER BY id DESC')
            ->fetch_all(MYSQLI_ASSOC);
    }

    public function buscar(int $id): ?array
    {
        $query = $this->db->prepare('SELECT id, nombre, apellido, correo, telefono FROM clientes WHERE id = ?');
        $query->bind_param('i', $id);
        $query->execute();
        return $query->get_result()->fetch_assoc();
    }

    public function correoExiste(string $correo, int $id): bool
    {
        $query = $this->db->prepare('SELECT id FROM clientes WHERE correo = ? AND id <> ?');
        $query->bind_param('si', $correo, $id);
        $query->execute();
        return $query->get_result()->fetch_assoc() !== null;
    }

    public function guardar(array $data, int $id): int
    {
        [$nombre, $apellido, $correo, $telefono] = array_values($data);
        if ($id === 0) {
            $query = $this->db->prepare('INSERT INTO clientes (nombre, apellido, correo, telefono) VALUES (?, ?, ?, ?)');
            $query->bind_param('ssss', $nombre, $apellido, $correo, $telefono);
        } else {
            $query = $this->db->prepare('UPDATE clientes SET nombre = ?, apellido = ?, correo = ?, telefono = ? WHERE id = ?');
            $query->bind_param('ssssi', $nombre, $apellido, $correo, $telefono, $id);
        }
        $query->execute();
        return $id ?: (int) $this->db->insert_id;
    }

    public function eliminar(int $id): void
    {
        $query = $this->db->prepare('DELETE FROM clientes WHERE id = ?');
        $query->bind_param('i', $id);
        $query->execute();
    }
}
