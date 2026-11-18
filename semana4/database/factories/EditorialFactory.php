<?php

namespace Database\Factories;

use App\Models\Editorial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Editorial>
 */
class EditorialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->company(),
            'pais' => fake()->unique()->country(),
            'pagina_web' => fake()->unique()->url(),
            'fundacion' => fake()->numberBetween(1900, 2020),
            'activa' => true,
        ];
    }
}
