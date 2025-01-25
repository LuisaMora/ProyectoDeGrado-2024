<?php

namespace Database\Factories;

use App\Models\Notificacion; // Asegúrate de usar el nombre correcto de tu modelo
use App\Models\Pedido; // Asegúrate de usar el modelo correcto para pedidos
use App\Models\User; // Asegúrate de usar el modelo correcto para usuarios
use App\Models\Restaurante; // Asegúrate de usar el modelo correcto para restaurantes
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificacionFactory extends Factory
{
    protected $model = Notificacion::class;

    public function definition()
    {
        return [
            'id_pedido' => Pedido::factory(), // Crea un pedido nuevo o usa uno existente
            'id_creador' => User::factory(), // Crea un usuario nuevo o usa uno existente
            'id_restaurante' => Restaurante::factory(), // Crea un restaurante nuevo o usa uno existente
            'tipo' => $this->faker->randomElement(['pedido', 'platillo']),
            'titulo' => $this->faker->sentence(3), // Título aleatorio
            'mensaje' => $this->faker->sentence(10), // Mensaje aleatorio
            'read_at' => $this->faker->optional()->dateTime(), // Puede ser nulo o una fecha
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
