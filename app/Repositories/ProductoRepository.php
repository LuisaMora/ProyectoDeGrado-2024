<?php

namespace App\Repositories;

use App\Models\Platillo;

class ProductoRepository
{
    public function getProductosByIdMenu( $idMenu, $soloDisponibles = false)
    {
        $query = Platillo::with('categoria')->where('id_menu', $idMenu);

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

    public function getProductosMenu($idMenu, $filtrarPorDisponibilidad)
{
    $query = Platillo::with('categoria')
        ->where('id_menu', $idMenu)
        ->where('disponible', true);

    if ($filtrarPorDisponibilidad) {
        $query->where('plato_disponible_menu', true);
    }

    return $query->orderBy('nombre')->get();
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
