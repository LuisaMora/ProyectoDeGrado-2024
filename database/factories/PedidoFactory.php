<?php

namespace Database\Factories;

use App\Models\Cuenta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pedido>
 */
class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function asignarDatos($id_empleado, $fecha_hora_pedido, $monto, $id_cuenta = 0){
        if($id_cuenta == 0)
        return $this->state( [
            'id_empleado' => $id_empleado,
            'fecha_hora_pedido' => $fecha_hora_pedido,
            'monto' => $monto,
            'id_cuenta' => Cuenta::factory(),
            'created_at' => $fecha_hora_pedido, 
            'updated_at' => $fecha_hora_pedido,
        ]);
        else
        return $this->state( [
            'id_cuenta' => $id_cuenta,
            'id_empleado' => $id_empleado,
            'fecha_hora_pedido' => $fecha_hora_pedido,
            'monto' => $monto,
        ]);
    }

    public function definition(): array
    {
        return [
            
            'tipo' => $this->faker->randomElement(['local', 'llevar']),
            'id_estado' => 4,
            // 'id_empleado' => 1,
            // 'fecha_hora_pedido' => $this->faker->dateTime(),
            // 'monto' => $this->faker->randomFloat(2, 1, 100),
        ];
    }
}
