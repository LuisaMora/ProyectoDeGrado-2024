<?php

namespace Database\Seeders;

use App\Models\Empleado;
use App\Models\Pedido;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PedidoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empleadoIdsRestaurante1 = Empleado::where('id_propietario', 1)->where('id_rol', 1)->pluck('id')->toArray();
        print_r($empleadoIdsRestaurante1);
        $empleadoIdsRestaurante2 = Empleado::where('id_propietario', 2)->where('id_rol', 1)->pluck('id')->toArray();
        $empleadoIdsRestaurante3 = Empleado::where('id_propietario', 3)->where('id_rol', 1)->pluck('id')->toArray();
        $fechaHaceSieteDias = date('Y-m-d', strtotime('-7 days'));
        $cantidadFechas = 10;
        $arregloFechas = [];
        for ($i = 0; $i < $cantidadFechas; $i++) {
            $arregloFechas[] = date('Y-m-d', strtotime('-' . $i . ' days'));
        }
        $empleadoIdAleatorio = $empleadoIdsRestaurante1[array_rand($empleadoIdsRestaurante1)];
        Pedido::factory(10)->asignarDatos($empleadoIdAleatorio, $fechaHaceSieteDias, 0)->create();
        Pedido::factory(1)->asignarDatos(array_rand($empleadoIdsRestaurante1), $fechaHaceSieteDias, 0, 1)->create();
        $empleadoIdAleatorio = $empleadoIdsRestaurante1[array_rand($empleadoIdsRestaurante1)];
        Pedido::factory(1)->asignarDatos($empleadoIdAleatorio, $fechaHaceSieteDias, 0, 2)->create();
        $empleadoIdAleatorio = $empleadoIdsRestaurante1[array_rand($empleadoIdsRestaurante1)];
        Pedido::factory(1)->asignarDatos($empleadoIdAleatorio, $fechaHaceSieteDias, 0, 2)->create();
        foreach ($arregloFechas as $fecha) {
            $empleadoIdAleatorio = $empleadoIdsRestaurante1[array_rand($empleadoIdsRestaurante1)];
            Pedido::factory(10)->asignarDatos($empleadoIdAleatorio, $fecha, 0)->create();
        }
    }
}
