<?php

declare(strict_types=1);

/**
 * Entidad Cliente: tipos simples dentro de un objeto (teoría, sección 05).
 */
class Cliente
{
    public function __construct(
        public int $id,
        public string $nombre,
        public string $correo,
        public bool $activo = true,
        public ?string $telefono = null,
    ) {}
}
