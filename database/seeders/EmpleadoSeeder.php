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
        Empleado::factory(10)->asignarPropietario(1,1)->create();
        Empleado::factory(10)->asignarPropietario(2,2)->create();
        Empleado::factory(10)->asignarPropietario(3,3)->create();
        //Set empleado que id_propietario = 1 y id_tipo_empleado = 1 nikcname = 'mesero'
        $empleado = Empleado::where('id_propietario', 1)->where('id_rol', 1)->first();
        $usuario = $empleado->User;
        $usuario->nickname = 'mesero';
        $usuario->save();
        $empleado = Empleado::where('id_propietario', 1)->where('id_rol', 2)->first();
        $usuario = $empleado->User;
        $usuario->nickname = 'cajero';
        $usuario->save();
        $empleado = Empleado::where('id_propietario', 1)->where('id_rol', 3)->first();
        $usuario = $empleado->User;
        $usuario->nickname = 'cocinero';
        $usuario->save();

        $empleado = Empleado::where('id_propietario', 2)->where('id_rol', 1)->first();
        $usuario = $empleado->User;
        $usuario->nickname = 'mesero2';
        $usuario->save();
        $empleado = Empleado::where('id_propietario', 2)->where('id_rol', 2)->first();
        $usuario = $empleado->User;
        $usuario->nickname = 'cajero2';
        $usuario->save();
        $empleado = Empleado::where('id_propietario', 2)->where('id_rol', 3)->first();
        $usuario = $empleado->User;
        $usuario->nickname = 'cocinero2';
        $usuario->save();

        $empleado = Empleado::where('id_propietario', 3)->where('id_rol', 1)->first();
        $usuario = $empleado->User;
        $usuario->nickname = 'mesero3';
        $usuario->save();
        $empleado = Empleado::where('id_propietario', 3)->where('id_rol', 2)->first();
        $usuario = $empleado->User;
        $usuario->nickname = 'cajero3';
        $usuario->save();
        $empleado = Empleado::where('id_propietario', 3)->where('id_rol', 3)->first();
        $usuario = $empleado->User;
        $usuario->nickname = 'cocinero3';
        $usuario->save();
    }
}
