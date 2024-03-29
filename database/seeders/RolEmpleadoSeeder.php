<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolEmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rol_empleados')->insert([
            'nombre' => 'Mesero',
        ]);
        DB::table('rol_empleados')->insert([
            'nombre' => 'Cajero',
        ]);
        DB::table('rol_empleados')->insert([
            'nombre' => 'Cocinero',
        ]);
    }
}
