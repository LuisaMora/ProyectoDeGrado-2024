<?php

namespace Database\Factories;

use App\Models\Mesa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mesa>
 */
class MesaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     public function registrar_a_restaurante($restaurante_id)
    {
        return$this->state([
            'id_restaurante' => $restaurante_id,
        ]);
    }

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word(),
        ];
    }
}