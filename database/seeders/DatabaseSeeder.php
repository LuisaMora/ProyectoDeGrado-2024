<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\Pedido;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdministradorSeeder::class,
            RolEmpleadoSeeder::class,
            PropietarioSeeder::class,
            EmpleadoSeeder::class,
            CategoriaSeeder::class,
            MesaSeeder::class,
            EstadoPedidoSeeder::class,
            EstadoCuentaSeeder::class,
            PlatilloSeeder::class,
            PedidoSeeder::class,
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
