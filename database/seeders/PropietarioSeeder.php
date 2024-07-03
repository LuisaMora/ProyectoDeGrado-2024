<?php

namespace Database\Seeders;

use App\Models\Propietario;
use Illuminate\Database\Seeder;

class PropietarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Propietario::factory(1)->asignarDatosSesion('propietario', 'propietario@gmail.com')->create();
        Propietario::factory(1)->asignarDatosSesion('propietarior2','propietarior2@gmail.com')->create();
        Propietario::factory(1)->asignarDatosSesion('propietarior3','propietarior3@gmail.com')->create();
    }
}
