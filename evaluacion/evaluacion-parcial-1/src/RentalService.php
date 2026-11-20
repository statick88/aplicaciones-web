<?php

declare(strict_types=1);

final class RentalError extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 422)
    {
        parent::__construct($message);
    }
}

final class RentalService
{
    private const RESOURCES = [
        'vehiculos' => ['Vehiculos', 'vehiculo_id'],
        'clientes' => ['Clientes', 'cliente_id'],
        'alquileres' => ['Alquileres', 'alquiler_id'],
    ];

    public function __construct(private PDO $db) {}

    public function list(string $resource): array
    {
        if (!isset(self::RESOURCES[$resource])) {
            throw new RentalError('Recurso no encontrado.', 404);
        }
        if ($resource === 'alquileres') {
            return $this->db->query('SELECT a.alquiler_id, a.vehiculo_id, a.cliente_id,
                a.fecha_inicio, a.fecha_devolucion, v.marca, v.modelo,
                c.nombre, c.apellido FROM Alquileres a
                JOIN Vehiculos v ON v.vehiculo_id = a.vehiculo_id
                JOIN Clientes c ON c.cliente_id = a.cliente_id
                ORDER BY a.alquiler_id DESC')->fetchAll();
        }
        [$table, $key] = self::RESOURCES[$resource];
        return $this->db->query("SELECT * FROM $table ORDER BY $key DESC")->fetchAll();
    }

    private function query(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    private function text(array $input, string $key, int $max): string
    {
        $value = $input[$key] ?? '';
        if (!is_string($value) || trim($value) === '' || !preg_match('//u', $value)
            || preg_match_all('/./us', trim($value)) > $max) {
            throw new RentalError("El campo $key es obligatorio y admite hasta $max caracteres.");
        }
        return trim($value);
    }

    private function id(array $input, string $key): int
    {
        $value = filter_var($input[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($value === false) {
            throw new RentalError("El campo $key debe ser un identificador positivo.");
        }
        return $value;
    }

    private function date(array $input, string $key): string
    {
        $value = $input[$key] ?? '';
        $date = is_string($value) ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
        if (!$date || $date->format('Y-m-d') !== $value || $value < '1000-01-01') {
            throw new RentalError("El campo $key debe ser una fecha válida.");
        }
        return $value;
    }

    private function lock(string $table, string $key, int $id): array
    {
        $row = $this->query("SELECT * FROM $table WHERE $key = ? FOR UPDATE", [$id])->fetch();
        if (!$row) {
            throw new RentalError('El registro no existe.', 404);
        }
        return $row;
    }

    public function execute(string $action, array $input): array
    {
        $allowed = ['guardar_vehiculo', 'guardar_cliente', 'eliminar_vehiculo',
            'eliminar_cliente', 'crear_alquiler', 'devolver_alquiler'];
        if (!in_array($action, $allowed, true)) {
            throw new RentalError('Acción no válida.', 400);
        }
        $this->db->beginTransaction();
        try {
            $result = match ($action) {
                'guardar_vehiculo' => $this->saveVehicle($input),
                'guardar_cliente' => $this->saveClient($input),
                'eliminar_vehiculo' => $this->delete('Vehiculos', 'vehiculo_id', $input),
                'eliminar_cliente' => $this->delete('Clientes', 'cliente_id', $input),
                'crear_alquiler' => $this->rent($input),
                'devolver_alquiler' => $this->returnVehicle($input),
            };
            $this->db->commit();
            return $result;
        } catch (Throwable $error) {
            $this->db->rollBack();
            if ($error instanceof PDOException && $error->getCode() === '23000') {
                throw new RentalError('No se guardaron cambios: licencia duplicada o registro relacionado con alquileres.', 409);
            }
            throw $error;
        }
    }

    private function saveVehicle(array $input): array
    {
        $brand = $this->text($input, 'marca', 80);
        $model = $this->text($input, 'modelo', 80);
        $year = filter_var($input['año'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1886, 'max_range' => 2100]]);
        $available = $input['disponible'] ?? '';
        if ($year === false || !in_array($available, ['0', '1', 0, 1], true)) {
            throw new RentalError('El año debe estar entre 1886 y 2100 y la disponibilidad debe ser 0 o 1.');
        }
        $id = isset($input['vehiculo_id']) && $input['vehiculo_id'] !== ''
            ? $this->id($input, 'vehiculo_id') : null;
        if ($id) {
            $this->lock('Vehiculos', 'vehiculo_id', $id);
            $active = $this->query('SELECT alquiler_id FROM Alquileres WHERE vehiculo_activo = ?', [$id])->fetch();
            if ($active && (int) $available === 1) {
                throw new RentalError('El vehículo tiene un alquiler activo y no puede marcarse disponible.', 409);
            }
            $this->query('UPDATE Vehiculos SET marca=?, modelo=?, `año`=?, disponible=? WHERE vehiculo_id=?',
                [$brand, $model, $year, $available, $id]);
        } else {
            $this->query('INSERT INTO Vehiculos (marca, modelo, `año`, disponible) VALUES (?, ?, ?, ?)',
                [$brand, $model, $year, $available]);
        }
        return ['id' => $id ?: (int) $this->db->lastInsertId(), 'status' => $id ? 200 : 201];
    }

    private function saveClient(array $input): array
    {
        $values = [$this->text($input, 'nombre', 80), $this->text($input, 'apellido', 80),
            $this->text($input, 'licencia', 40), $this->text($input, 'telefono', 25)];
        if (!preg_match('/^\+?[0-9 ()-]{7,25}$/D', $values[3])
            || strlen(preg_replace('/\D/', '', $values[3])) < 7) {
            throw new RentalError('El teléfono debe contener entre 7 y 25 caracteres y al menos 7 dígitos.');
        }
        $id = isset($input['cliente_id']) && $input['cliente_id'] !== ''
            ? $this->id($input, 'cliente_id') : null;
        if ($id) {
            $this->lock('Clientes', 'cliente_id', $id);
            $this->query('UPDATE Clientes SET nombre=?, apellido=?, licencia=?, telefono=? WHERE cliente_id=?',
                [...$values, $id]);
        } else {
            $this->query('INSERT INTO Clientes (nombre, apellido, licencia, telefono) VALUES (?, ?, ?, ?)', $values);
        }
        return ['id' => $id ?: (int) $this->db->lastInsertId(), 'status' => $id ? 200 : 201];
    }

    private function delete(string $table, string $key, array $input): array
    {
        $id = $this->id($input, $key);
        $this->lock($table, $key, $id);
        $this->query("DELETE FROM $table WHERE $key=?", [$id]);
        return ['id' => $id, 'status' => 200];
    }

    private function rent(array $input): array
    {
        $vehicleId = $this->id($input, 'vehiculo_id');
        $clientId = $this->id($input, 'cliente_id');
        $start = $this->date($input, 'fecha_inicio');
        $vehicle = $this->lock('Vehiculos', 'vehiculo_id', $vehicleId);
        $this->lock('Clientes', 'cliente_id', $clientId);
        if (!(bool) $vehicle['disponible']) {
            throw new RentalError('El vehículo no está disponible.', 409);
        }
        $this->query('INSERT INTO Alquileres (vehiculo_id, cliente_id, fecha_inicio) VALUES (?, ?, ?)',
            [$vehicleId, $clientId, $start]);
        $id = (int) $this->db->lastInsertId();
        $this->query('UPDATE Vehiculos SET disponible=0 WHERE vehiculo_id=?', [$vehicleId]);
        return ['id' => $id, 'status' => 201];
    }

    private function returnVehicle(array $input): array
    {
        $id = $this->id($input, 'alquiler_id');
        $returned = $this->date($input, 'fecha_devolucion');
        // Lock the vehicle first, consistently with rental and vehicle updates.
        $rental = $this->query('SELECT vehiculo_id FROM Alquileres WHERE alquiler_id=?', [$id])->fetch();
        if (!$rental) {
            throw new RentalError('El registro no existe.', 404);
        }
        $this->lock('Vehiculos', 'vehiculo_id', (int) $rental['vehiculo_id']);
        $rental = $this->lock('Alquileres', 'alquiler_id', $id);
        if ($rental['fecha_devolucion'] !== null) {
            throw new RentalError('El alquiler ya fue devuelto.', 409);
        }
        if ($returned < $rental['fecha_inicio']) {
            throw new RentalError('La devolución no puede ser anterior al inicio.');
        }
        $this->query('UPDATE Alquileres SET fecha_devolucion=? WHERE alquiler_id=?', [$returned, $id]);
        $this->query('UPDATE Vehiculos SET disponible=1 WHERE vehiculo_id=?', [$rental['vehiculo_id']]);
        return ['id' => $id, 'status' => 200];
    }
}
