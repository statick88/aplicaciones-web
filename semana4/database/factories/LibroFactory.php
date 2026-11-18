<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Editorial;
use App\Models\Libro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Libro>
 */
class LibroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->unique()->sentence(3),
            'anio' => fake()->numberBetween(1950, 2026),
            'isbn' => fake()->unique()->numerify('978##########'),
            'editorial_id' => Editorial::factory(),
            'categoria_id' => Categoria::factory(),
        ];
    }
}
