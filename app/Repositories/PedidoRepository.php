<?php

namespace App\Repositories;

use App\Models\Pedido;
use Illuminate\Support\Collection;

class PedidoRepository
{
    public function obtenerPedidosPorMesero(int $idEmpleado, int $idRestaurante): Collection
    {
        return Pedido::with(['cuenta.mesa', 'platos', 'estado'])
            ->whereDate('fecha_hora_pedido', now())
            ->where('id_empleado', $idEmpleado)
            ->whereHas('cuenta.mesa', function ($query) use ($idRestaurante) {
                $query->where('id_restaurante', $idRestaurante);
            })
            ->whereHas('cuenta', function ($query) {
                $query->where('estado', '!=', 'Pagada');
            })
            ->get()
            ->groupBy('cuenta.mesa.id'); // Agrupar pedidos por mesa
    }
    
    public function obtenerPlatillosDePedido($idPedido, $idRestaurante)
    {
        return Pedido::with('platos')
            ->whereDate('fecha_hora_pedido', now())
            ->where('id', $idPedido)
            ->whereHas('cuenta.mesa', function ($query) use ($idRestaurante) {
                $query->where('id_restaurante', $idRestaurante);
            })
            ->first();
    }

    public function obtenerPedidosPorCocinero(int $idRestaurante): Collection
    {
        return Pedido::with(['cuenta.mesa', 'platos', 'estado'])
            ->whereDate('fecha_hora_pedido', now())
            ->whereHas('cuenta.mesa', function ($query) use ($idRestaurante) {
                $query->where('id_restaurante', $idRestaurante);
            })
            ->whereHas('cuenta', function ($query) {
                $query->where('estado', '!=', 'Pagada');
            })
            ->get();
    }

    public function crearPedido($datosPedido): Pedido
    {
        return Pedido::create($datosPedido);
    }

    public function update($id, array $data)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->update($data);
            return $pedido;
        }
        return null;
    }
}
