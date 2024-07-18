<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'imagen' => $this->faker->imageUrl(),
            'id_menu'=> 1 
        ];
    }

    public function nombre($nombre)
    {
        return $this->state([
            'nombre' => $nombre,
        ]);
    }
}
