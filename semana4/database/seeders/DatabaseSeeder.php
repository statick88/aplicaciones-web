<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * El candidato aún no tiene modelos de dominio (llegan en el commit de
     * esquema), así que el seeder por defecto no crea filas: su papel es
     * hacer que `php artisan db:seed --force` del arranque de compose termine
     * con éxito en lugar de abortar la cadena `&&`.
     */
    public function run(): void
    {
        //
    }
}
