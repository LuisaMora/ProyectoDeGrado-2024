<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Restaurante;
use App\Models\User;
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
    public function asignarDatosSesion($nickname, $correo){
        if($nickname && $correo)
        return $this->state( [
            'id_usuario' => User::factory()->asignarNicknameCorreo($nickname, $correo)->create([
                'tipo_usuario' => 'Propietario'
            ]),
        ]);
        else
        return $this->state( [
            'id_usuario' => User::factory()->create([
                'tipo_usuario' => 'Propietario'
            ]),
        ]);
    }
    public function definition(): array
    {
        $restaurante = Restaurante::factory()->create();
        Menu::factory()->registrar_a_restaurante($restaurante->id)->create();
        return [
            'id_administrador' => 1,
            'id_restaurante' => $restaurante->id,
            'ci' => $this->faker->numberBetween(10000000, 99999999),
            'fecha_registro' => $this->faker->date(),
            'pais' => 'Bolivia',
            'departamento' => $this->faker->randomElement(['La Paz', 'Cochabamba', 'Santa Cruz', 'Oruro', 'Potosi', 'Tarija', 'Chuquisaca', 'Beni', 'Pando']),
        ];
    }
}
