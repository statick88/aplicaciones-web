<?php

namespace Database\Factories;

use App\Models\Lector;
use App\Models\Libro;
use App\Models\Prestamo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prestamo>
 */
class PrestamoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prestamo = fake()->dateTimeBetween('-6 months', '-1 day');
        $devuelto = fake()->boolean(70);

        return [
            'fecha_prestamo' => $prestamo->format('Y-m-d'),
            'fecha_devolucion' => $devuelto
                ? fake()->dateTimeBetween($prestamo, 'now')->format('Y-m-d')
                : null,
            'estado' => $devuelto ? 'devuelto' : 'activo',
            'lector_id' => Lector::factory(),
            'libro_id' => Libro::factory(),
        ];
    }
}
