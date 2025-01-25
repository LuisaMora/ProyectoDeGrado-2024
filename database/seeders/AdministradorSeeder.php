<?php

namespace Database\Seeders;

use App\Models\Administrador;
use Illuminate\Database\Seeder;

class AdministradorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //crear un administrador
        Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        Administrador::factory()->asignarDatosSesion('admin_2', 'admin2@gmail.com')->create();
    }
}
