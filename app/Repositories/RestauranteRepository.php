<?php

namespace App\Repositories;

use App\Models\Restaurante;

class RestauranteRepository
{
    public function getMenuIdByRestauranteId($id_restaurante)
    {
        return Restaurante::where('id', $id_restaurante)->value('id_menu');
    }

    public function findRestauranteById($id_restaurante)
    {
        return Restaurante::find($id_restaurante);
    }

    public function create(array $data)
    {
        return Restaurante::create($data);
    }
}
