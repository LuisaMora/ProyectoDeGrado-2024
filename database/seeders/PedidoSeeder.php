<?php

namespace Database\Seeders;

use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\Mesa;
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
        $mesasR1 = Mesa::where('id_restaurante', 1)->pluck('id')->toArray();
        $empleadoIdsRestaurante1 = Empleado::where('id_propietario', 1)->where('id_rol', 1)->pluck('id')->toArray();
        $empleadoIdsRestaurante2 = Empleado::where('id_propietario', 2)->where('id_rol', 1)->pluck('id')->toArray();
        $empleadoIdsRestaurante3 = Empleado::where('id_propietario', 3)->where('id_rol', 1)->pluck('id')->toArray();

        $fechaHaceSieteDias = date('Y-m-d', strtotime('-7 days'));
        $cantidadFechas = 10;
        $arregloFechas = [];

        for ($i = 0; $i < $cantidadFechas; $i++) {
            $arregloFechas[] = date('Y-m-d', strtotime('-' . $i . ' days'));
        }

        $this->crearPedidosConPlatillos($empleadoIdsRestaurante1, $platillosRestaurante1, $fechaHaceSieteDias, 0, 10, $mesasR1);
        $this->crearPedidosConPlatillos($empleadoIdsRestaurante1, $platillosRestaurante1, $fechaHaceSieteDias, 0, 1, $mesasR1, 1);
        $this->crearPedidosConPlatillos($empleadoIdsRestaurante1, $platillosRestaurante1, $fechaHaceSieteDias, 0, 1, $mesasR1, 2);
        $this->crearPedidosConPlatillos($empleadoIdsRestaurante1, $platillosRestaurante1, $fechaHaceSieteDias, 0, 1, $mesasR1, 2);

        foreach ($arregloFechas as $fecha) {
            $this->crearPedidosConPlatillos($empleadoIdsRestaurante1, $platillosRestaurante1, $fecha, 0, 10, $mesasR1);
        }

        $this->actualizarMontoDePedidoCuenta(1);
    }

    private function crearPedidosConPlatillos(array $empleadoIds, array $platilloIds, string $fecha, float $monto, int $cantidad, array $idMesas, int $idCuenta = 0)
    {
        
        for ($i = 0; $i < $cantidad; $i++) {
    
            $empleadoIdAleatorio = $empleadoIds[array_rand($empleadoIds)];
            $idCuentaActual = $idCuenta;
            if ($idCuenta === 0) {
                $idMesa = $idMesas[array_rand($idMesas)];
                $idCuentaActual = Cuenta::factory()->asignarDatosCuenta($idMesa,0,$fecha,$fecha)->create()->id;
            }

            $pedido = Pedido::factory()->asignarDatos($empleadoIdAleatorio, $fecha, $monto, $idCuentaActual)->create();


            $numPlatillos = rand(1, 5);

            for ($j = 0; $j < $numPlatillos; $j++) {
                PlatoPedido::factory()->create([
                    'id_pedido' => $pedido->id,
                    'id_platillo' => $platilloIds[array_rand($platilloIds)],
                ]);
            }
        }
    }

    private function actualizarMontoDePedidoCuenta(int $id_restaurante){
        $idMesas = Mesa::where('id_restaurante', $id_restaurante)->pluck('id')->toArray();
        $cuentas = Cuenta::whereIn('id_mesa', $idMesas)->get();
        $pedidos = Pedido::whereIn('id_cuenta', $cuentas->pluck('id'))->get();
        //platospedidos agrupados por id_pedido
        $platosPedidos = PlatoPedido::whereIn('id_pedido', $pedidos->pluck('id'))->get()->groupBy('id_pedido');
        foreach ($platosPedidos as $id_pedido => $platosPedido) {
            $monto = 0;
            foreach ($platosPedido as $platoPedido) {
                $platillo = Platillo::find($platoPedido->id_platillo);
                $monto += $platillo->precio * $platoPedido->cantidad;
            }
            $pedido = Pedido::find($id_pedido);
            $pedido->monto = $monto;
            $pedido->save();
            $cuenta = Cuenta::find($pedido->id_cuenta);
            $cuenta->monto_total += $monto;
            $cuenta->save();
        }

    }
}
