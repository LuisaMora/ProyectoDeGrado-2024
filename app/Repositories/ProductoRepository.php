<?php

namespace App\Repositories;

use App\Models\Platillo;
use App\Models\Restaurante;

class ProductoRepository
{
    public function getProductosByRestaurante($idRestaurante, $soloDisponibles = false)
    {
        $menuId = Restaurante::where('id', $idRestaurante)->value('id_menu');

        $query = Platillo::with('categoria')->where('id_menu', $menuId);

        if ($soloDisponibles) {
            $query->where('disponible', true)->where('plato_disponible_menu', true);
        } else {
            $query->where('disponible', true);
        }

        return $query->orderBy('nombre')->get();
    }

    public function getProductoById($id)
    {
        return Platillo::with('categoria')->where('disponible', true)->find($id);
    }

    public function getProductosMenu($idMenu)
    {
        return  Platillo::with('categoria')
        ->where('id_menu', $idMenu)
        ->where('disponible', true)
        ->where('plato_disponible_menu', true)
        ->orderBy('nombre')
        ->get();
    }

    public function createProducto($data)
    {
        return Platillo::create($data);
    }

    public function updateProducto($id, $data)
    {
        $platillo = Platillo::find($id);
        if ($platillo) {
            $platillo->update($data);
            return $platillo;
        }
        return null;
    }

    public function deleteProducto($id)
    {
        $platillo = Platillo::find($id);
        if ($platillo) {
            $platillo->delete();
            return $platillo;
        }
        return null;
    }
}
