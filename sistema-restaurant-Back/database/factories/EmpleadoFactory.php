<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empleado>
 */
class EmpleadoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    //  private $id_propietario;

     public function asignarPropietario($id){
        // $this->id_propietario = $id;
        return $this->state( [
            'id_propietario' => $id,
        ]); 
     }  

    public function definition(): array
    {
        return [
            'id_usuario' => Usuario::factory(),
            'id_rol' => $this->faker->numberBetween(1, 3),
            'fecha_nacimiento' => $this->faker->dateTimeInInterval('-25 years', '-18 years')->format('Y-m-d'),
            'fecha_contratacion' => $this->faker->date(),
            'ci' => $this->faker->numberBetween(1000000, 9999999),
            'direccion' => $this->faker->address(),
        ];
    }
}
