<?php

namespace App\Repositories;

use App\Models\Cuenta;
use Illuminate\Support\Facades\DB;

class ReporteRepository
{
    public function obtenerCuentasPagadas($mesasDelRestaurante, $fechaInicio, $fechaFin)
    {
        return Cuenta::select('id')
        ->where('estado', 'Pagada')
        ->whereBetween('created_at', [$fechaInicio, $fechaFin])
        ->whereIn('id_mesa', $mesasDelRestaurante)
        ->get();
    }

    public function obtenerMontoTotalPorDia($fechaInicio, $fechaFin, $cuentas)
    {
        return DB::table('pedidos')
        ->select(DB::raw('DATE(fecha_hora_pedido) as fecha'), DB::raw('SUM(monto) as monto'))
        ->whereBetween('fecha_hora_pedido', [$fechaInicio, $fechaFin])
        ->whereIn('id_cuenta', $cuentas)
        ->groupBy('fecha')
        ->orderBy('fecha', 'ASC')
        ->get();
    }

    public function obtenerCantidadClientesPorDia($fechaInicio, $fechaFin, $cuentas)
    {
        return DB::table('cuentas')
        ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('COUNT(id) as cantidad'))
        ->whereBetween('created_at', [$fechaInicio, $fechaFin])
        ->whereIn('id', $cuentas)
        ->groupBy('fecha')
        ->orderBy('fecha', 'ASC')
        ->get();
    }

    public function obtenerCantidadClientesPorMesa($cuentas)
    {
        return DB::table('cuentas')
        ->join('mesas', 'cuentas.id_mesa', '=', 'mesas.id')
        ->whereIn('cuentas.id', $cuentas) // Asegúrate de que $cuentas es un array
        ->select(
            'mesas.nombre AS mesa',
            DB::raw('COUNT(cuentas.id) AS cantidad_pedidos')
        )
        ->groupBy('mesas.nombre') // Agrupamos por el nombre de la mesa
        ->orderByDesc('cantidad_pedidos') // Forma más clara de ordenar descendente
        ->get();
    }

    public function agruparPedidosPorCuenta($fechaInicio, $fechaFin, $cuentas)
    {
        $dbDriver = DB::getDriverName();

        $pedidosQuery = DB::table('cuentas')
            ->join('pedidos', 'pedidos.id_cuenta', '=', 'cuentas.id')
            ->join('empleados', 'empleados.id', '=', 'pedidos.id_empleado')
            ->join('usuarios', 'usuarios.id', '=', 'empleados.id_usuario')
            ->join('plato_pedido', 'plato_pedido.id_pedido', '=', 'pedidos.id')
            ->join('estado_pedidos', 'estado_pedidos.id', '=', 'pedidos.id_estado')
            ->join('platillos', 'platillos.id', '=', 'plato_pedido.id_platillo')
            ->whereIn('cuentas.id', $cuentas)
            ->whereBetween('cuentas.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'cuentas.id as id_cuenta',
                'pedidos.id as id_pedido',
                'usuarios.nombre',
                'usuarios.apellido_paterno as apellido',
                'estado_pedidos.nombre as estado_pedido',
                'pedidos.monto',
                'cuentas.created_at as fecha_hora_cuenta'
            );

        if ($dbDriver === 'pgsql') {
            $pedidosQuery->selectRaw('json_agg(json_build_object(
            \'id_platillo\', platillos.id,
            \'nombre\', platillos.nombre,
            \'precio\', platillos.precio,
            \'cantidad\', plato_pedido.cantidad,
            \'detalle\', plato_pedido.detalle
        )) as platillos');
        } else if ($dbDriver === 'mysql') {
            $pedidosQuery->selectRaw("group_concat(
            CONCAT(platillos.id, ':', platillos.nombre, ':', platillos.precio, ':', plato_pedido.cantidad, ':', plato_pedido.detalle)
            SEPARATOR '|'
        ) as platillos");
        }
        else if($dbDriver === 'sqlite'){
            $pedidosQuery->selectRaw('json_group_array(json_object(
            \'id_platillo\', platillos.id,
            \'nombre\', platillos.nombre,
            \'precio\', platillos.precio,
            \'cantidad\', plato_pedido.cantidad,
            \'detalle\', plato_pedido.detalle
        )) as platillos');
        }

        $pedidos = $pedidosQuery->groupBy(
            'cuentas.id', 
            'pedidos.id', 
            'usuarios.nombre', 
            'usuarios.apellido_paterno', 
            'estado_pedidos.nombre', 
            'pedidos.monto', 
            'cuentas.created_at'
        )->get();

        return $pedidos->map(function ($pedido) use ($dbDriver) {
            $platillos = $dbDriver === 'pgsql'
                ? json_decode($pedido->platillos)
                : $this->descomponerPlatillos($pedido->platillos);

            return [
                'id_cuenta' => $pedido->id_cuenta,
                'id_pedido' => $pedido->id_pedido,
                'empleado' => [
                    'nombre' => $pedido->nombre,
                    'apellido' => $pedido->apellido,
                ],
                'monto' => $pedido->monto,
                'fecha_hora_cuenta' => $pedido->fecha_hora_cuenta,
                'estado_pedido' => $pedido->estado_pedido,
                'platillos' => $platillos
            ];
        })->groupBy('id_cuenta');
    }

    private function descomponerPlatillos($platillosString)
    {
        $platillos = [];
        if ($platillosString) {
            $platillosData = explode('|', $platillosString);
            foreach ($platillosData as $platillo) {
                list($id, $nombre, $precio, $cantidad, $detalle) = explode(':', $platillo);
                $platillos[] = [
                    'id_platillo' => (int)$id,
                    'nombre' => $nombre,
                    'precio' => (float)$precio,
                    'cantidad' => (int)$cantidad,
                    'detalle' => $detalle,
                ];
            }
        }
        return $platillos;
    }
}
