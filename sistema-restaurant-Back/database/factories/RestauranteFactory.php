<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Restaurante>
 */
class RestauranteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->name(),
            'nit' => $this->faker->numberBetween(10000000000, 99999999999),
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->numberBetween(40000000, 99999999),
            'correo' => $this->faker->email(),
            'licencia_funcionamiento' =>fake()->imageUrl(),
        ];
    }
}
