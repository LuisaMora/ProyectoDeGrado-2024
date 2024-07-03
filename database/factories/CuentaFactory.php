<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cuenta>
 */
class CuentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // protected $fillable = [
    //     'id_mesa',
    //     'nombre_razon_social',
    //     'monto_total',
    //     'estado',
    // ];
    public function asignarDatosCuenta($monto_total, $created_at, $updated_at){
        if( $created_at && $updated_at)
        return $this->state( [
            'monto_total' => $monto_total,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ]);
        else
        return $this->state( [
            'monto_total' => $monto_total,
        ]);
    }
    
    public function definition(): array
    {
        return [
            'id_mesa' => 1,
            'nombre_razon_social' => $this->faker->name(),
            'estado' => $this->faker->randomElement(['Abierta', 'Cancelada', 'PagoPendiente', 'Pagada']),
        ];
    }
}
