<?php

namespace Database\Seeders;

use App\Models\Empleado;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Empleado::factory(10)->asignarPropietario(1)->create();
        Empleado::factory(10)->asignarPropietario(2)->create();
        Empleado::factory(10)->asignarPropietario(3)->create();
    }
}
