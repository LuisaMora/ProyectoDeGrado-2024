<?php

namespace Database\Seeders;

use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Platillo;
use App\Models\PedidoPlatillo;
use App\Models\PlatoPedido;
use Illuminate\Database\Seeder;

class PedidoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platillosRestaurante1 = Platillo::where('id_menu', 1)->pluck('id')->toArray();
        $empleadoIdsRestaurante1 = Empleado::where('id_propietario', 1)->where('id_rol', 1)->pluck('id')->toArray();
        $empleadoIdsRestaurante2 = Empleado::where('id_propietario', 2)->where('id_rol', 1)->pluck('id')->toArray();
        $empleadoIdsRestaurante3 = Empleado::where('id_propietario', 3)->where('id_rol', 1)->pluck('id')->toArray();

        $fechaHaceSieteDias = date('Y-m-d', strtotime('-7 days'));
        $cantidadFechas = 10;
        $arregloFechas = [];

        for ($i = 0; $i < $cantidadFechas; $i++) {
            $arregloFechas[] = date('Y-m-d', strtotime('-' . $i . ' days'));
        }

        $this->crearPedidosConPlatillos($empleadoIdsRestaurante1, $platillosRestaurante1, $fechaHaceSieteDias, 0, 10);
        $this->crearPedidosConPlatillos($empleadoIdsRestaurante1, $platillosRestaurante1, $fechaHaceSieteDias, 0, 1, 1);
        $this->crearPedidosConPlatillos($empleadoIdsRestaurante1, $platillosRestaurante1, $fechaHaceSieteDias, 0, 1, 2);
        $this->crearPedidosConPlatillos($empleadoIdsRestaurante1, $platillosRestaurante1, $fechaHaceSieteDias, 0, 1, 2);

        foreach ($arregloFechas as $fecha) {
            $this->crearPedidosConPlatillos($empleadoIdsRestaurante1, $platillosRestaurante1, $fecha, 0, 10);
        }
    }

    private function crearPedidosConPlatillos(array $empleadoIds, array $platilloIds, string $fecha, float $monto, int $cantidad, int $idCuenta = 0)
    {
        for ($i = 0; $i < $cantidad; $i++) {
            $empleadoIdAleatorio = $empleadoIds[array_rand($empleadoIds)];
            $pedido = Pedido::factory()->asignarDatos($empleadoIdAleatorio, $fecha, $monto, $idCuenta)->create();

            $numPlatillos = rand(1, 5);
            for ($j = 0; $j < $numPlatillos; $j++) {
                PlatoPedido::factory()->create([
                    'id_pedido' => $pedido->id,
                    'id_platillo' => $platilloIds[array_rand($platilloIds)],
                ]);
            }
        }
    }
}
