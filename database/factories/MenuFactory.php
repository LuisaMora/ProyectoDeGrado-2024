<?php

namespace Database\Factories;

use App\Models\Restaurante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'portada' => '',
            'tema' => 'light-theme',
            'qr' => '',
        ];
    }

    public function registrar_a_restaurante($restaurante_id)
    {
        return$this->state([
            'id_restaurante' => $restaurante_id,
        ]);
    }
}
