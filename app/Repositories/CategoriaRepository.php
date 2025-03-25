<?php

namespace App\Repositories;

use App\Models\Categoria;
use App\Models\Platillo;

class CategoriaRepository
{
    public function getCategoriasByMenuId($id_menu)
    {
        return Categoria::where('id_menu', $id_menu)->where('estado', true)->get();
    }

    public function getCategoriaById($id)
    {
        return Categoria::where('estado', true)->find($id);
    }

    public function create(array $data)
    {
        return Categoria::create($data);
    }

    public function updateCategoria($id, array $data)
    {
        $categoria = Categoria::find($id);
        if ($categoria) {
            $categoria->update($data);
        }
        return $categoria;
    }

    public function softDeleteCategoria($id)
    {
        $categoria = Categoria::find($id);
        if ($categoria) {
            $categoria->estado = false;
            $categoria->save();
            Platillo::where('id_categoria', $id)
            ->update(['id_categoria' => 1]);
        }
        return $categoria;
    }
}
