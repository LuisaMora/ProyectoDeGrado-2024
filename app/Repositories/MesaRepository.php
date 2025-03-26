<?php

namespace App\Repositories;

use App\Models\Mesa;

class MesaRepository
{
    /**
     * Get mesas by restaurant ID.
     *
     * @param int $idRestaurante
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMesas(int $idRestaurante)
    {
        return Mesa::where('id_restaurante', $idRestaurante)->get();
    }

    /**
     * Get mesas by ID, only ids
     *
     * @param int $idMesa
     * @return  \Illuminate\Database\Eloquent\Collection
     */

    public function getMesasIds(int $idRestaurante)
    {
        return Mesa::where('id_restaurante', $idRestaurante)->pluck('id');
    }

    public function getMesasByIdRest(int $idRestaurante){
        return Mesa::select('id','id_restaurante','nombre')->where('id_restaurante', $idRestaurante)->get();
    }

    public function crearMesa(array $data)
    {
        return Mesa::create($data);
    }

    public function obtenerNombreMesa($idMesa)
    {
        return Mesa::where('id', $idMesa)->first()->nombre;
    }
}