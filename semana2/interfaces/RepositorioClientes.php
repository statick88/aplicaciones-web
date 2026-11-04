<?php

declare(strict_types=1);

/**
 * Contrato CRUD de clientes compartido por todos los adaptadores de repositorio.
 */
interface RepositorioClientes
{
    /**
     * Da de alta un cliente. El adaptador puede asignarle el id definitivo.
     */
    public function registrar(Cliente $cliente): void;

    /**
     * Devuelve el cliente con ese id, o null si no existe.
     */
    public function consultar(int $id): ?Cliente;

    /**
     * Devuelve todos los clientes en orden de registro.
     *
     * @return list<Cliente>
     */
    public function listar(): array;

    /**
     * Sobrescribe los datos de un cliente existente. false si el id no existe.
     */
    public function actualizar(Cliente $cliente): bool;

    /**
     * Elimina un cliente. false si el id no existe.
     */
    public function eliminar(int $id): bool;
}
