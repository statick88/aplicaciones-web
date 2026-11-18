<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Editorial;
use App\Models\Lector;
use App\Models\Libro;
use App\Models\Prestamo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database idempotently.
     */
    public function run(): void
    {
        // Each seeder only creates rows when the table is empty, so
        // `db:seed` can run repeatedly without duplicating data.
        if (Editorial::query()->exists() === false) {
            Editorial::factory()
                ->count(4)
                ->create();
        }

        if (Categoria::query()->exists() === false) {
            Categoria::factory()
                ->count(4)
                ->create();
        }

        if (Lector::query()->exists() === false) {
            Lector::factory()
                ->count(5)
                ->create();
        }

        if (Libro::query()->exists() === false) {
            Libro::factory()
                ->count(8)
                ->state([
                    'editorial_id' => Editorial::query()->inRandomOrder()->value('id'),
                    'categoria_id' => Categoria::query()->inRandomOrder()->value('id'),
                ])
                ->create();
        }

        if (Prestamo::query()->exists() === false) {
            Prestamo::factory()
                ->count(6)
                ->state([
                    'lector_id' => Lector::query()->inRandomOrder()->value('id'),
                    'libro_id' => Libro::query()->inRandomOrder()->value('id'),
                ])
                ->create();
        }
    }
}
