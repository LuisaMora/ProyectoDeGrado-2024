<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Administrador>
 */
class AdministradorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     public function asignarDatosSesion($nickname, $correo){
        if($nickname && $correo)
        return $this->state( [
            'id_usuario' => User::factory()->asignarNicknameCorreo($nickname, $correo),
        ]);
        else
        return $this->state( [
            'id_usuario' => User::factory(),
        ]); 
     }

    public function definition(): array
    {
        return [
            'token' => $this->faker->regexify('[A-Za-z0-9]{200}'),
        ];
    }
}
