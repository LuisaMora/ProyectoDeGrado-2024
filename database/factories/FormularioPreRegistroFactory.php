<?php

namespace Database\Factories;

use App\Models\FormularioPreRegistro;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormularioPreRegistroFactory extends Factory
{
    protected $model = FormularioPreRegistro::class;

    public function definition()
    {
        return [
            'nombre_restaurante' => $this->faker->company,
            'nit' => $this->faker->unique()->randomNumber(8),
            'celular_restaurante' => $this->faker->phoneNumber,
            'correo_restaurante' => $this->faker->unique()->safeEmail,
            'licencia_funcionamiento' => 'licencias_funcionamiento/' . Str::random(10) . '.pdf',
            'tipo_establecimiento' => $this->faker->randomElement(['Restaurante', 'Cafetería', 'Bar']),
            'latitud' => $this->faker->latitude(-90, 90),
            'longitud' => $this->faker->longitude(-180, 180),
            'nombre_propietario' => $this->faker->firstName,
            'apellido_paterno_propietario' => $this->faker->lastName,
            'apellido_materno_propietario' => $this->faker->lastName,
            'cedula_identidad_propietario' => $this->faker->unique()->randomNumber(7),
            'correo_propietario' => $this->faker->unique()->safeEmail,
            'fotografia_propietario' => 'fotografias_propietarios/' . Str::random(10) . '.jpg',
            'estado' => $this->faker->randomElement(['pendiente', 'aprobado', 'rechazado']),
            'pais' => $this->faker->country,
            'departamento' => $this->faker->state,
            'numero_mesas' => $this->faker->numberBetween(1, 20),
        ];
    }
}
