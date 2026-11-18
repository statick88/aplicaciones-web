<?php

namespace Database\Factories;

use App\Models\Lector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lector>
 */
class LectorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->name(),
            'cedula' => fake()->unique()->numerify('09########'),
            'correo' => fake()->unique()->safeEmail(),
            'telefono' => fake()->numerify('09########'),
            'fecha_registro' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
        ];
    }
}
