<?php

namespace Database\Factories;

use App\Models\Restaurante;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Propietario>
 */
class PropietarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_usuario' => Usuario::factory(),
            'id_administrador' => 1,
            'id_restaurante' => Restaurante::factory(),
            'ci' => $this->faker->numberBetween(10000000, 99999999),
            'fecha_registro' => $this->faker->date(),
            'pais' => 'Bolivia',
            'departamento' => $this->faker->randomElement(['La Paz', 'Cochabamba', 'Santa Cruz', 'Oruro', 'Potosi', 'Tarija', 'Chuquisaca', 'Beni', 'Pando']),
        ];
    }
}
