<?php

namespace Database\Factories;

use App\Models\Mesa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mesa>
 */
class MesaFactory extends Factory
{
    protected static $numeroMesa = 0;

    public function registrar_a_restaurante($restaurante_id)
    {
        // Reiniciar la numeración de mesas para cada restaurante
        static::$numeroMesa = 0;

        return $this->state([
            'id_restaurante' => $restaurante_id,
        ]);
    }

    public function definition(): array
    {
        $nombreMesa = 'Mesa ' . static::$numeroMesa++;

        return [
            'nombre' => $nombreMesa,
        ];
    }
}
