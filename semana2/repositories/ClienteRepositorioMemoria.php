<?php

declare(strict_types=1);

/**
 * Adaptador en memoria del contrato CRUD: la forma de la teoría (sección 05),
 * usada por los tests unitarios y como referencia de comportamiento.
 */
class ClienteRepositorioMemoria implements RepositorioClientes
{
    /** @var array<int, Cliente> */
    private array $clientes = [];

    public function registrar(Cliente $cliente): void
    {
        $this->clientes[$cliente->id] = $cliente;
    }

    public function consultar(int $id): ?Cliente
    {
        return $this->clientes[$id] ?? null;
    }

    /** @return list<Cliente> */
    public function listar(): array
    {
        return array_values($this->clientes);
    }

    public function actualizar(Cliente $cliente): bool
    {
        if (!isset($this->clientes[$cliente->id])) {
            return false;
        }

        $this->clientes[$cliente->id] = $cliente;
        return true;
    }

    public function eliminar(int $id): bool
    {
        if (!isset($this->clientes[$id])) {
            return false;
        }

        unset($this->clientes[$id]);
        return true;
    }
}
