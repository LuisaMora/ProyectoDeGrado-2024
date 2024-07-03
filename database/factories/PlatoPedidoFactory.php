<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\Platillo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlatoPedido>
 */
class PlatoPedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pedido' => Pedido::factory(),
            'id_platillo' => Platillo::factory(),
            'cantidad' => $this->faker->numberBetween(1, 10),
            'detalle' => $this->faker->sentence,
        ];
    }
}
