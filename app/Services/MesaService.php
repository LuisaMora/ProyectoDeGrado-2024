<?php

namespace App\Services;

use App\Repositories\MesaRepository;

class MesaService
{
    public function __construct(private MesaRepository $mesaRepository){
    }

    public function getAllMesas(){

    }
    public function getMesasByIdRest($idRestaurante){
        $tipo_usuario = auth()->user()->tipo_usuario;

        if($tipo_usuario == 'Propietario' || $tipo_usuario == 'Empleado'){

            return $this->mesaRepository->getMesasByIdRest($idRestaurante);
        } else {
            throw new \Exception("Este usuario no puede ver las mesas", 403);
            
        }
    }

    public function createMesa(array $data){

    }

    public function updateMesa($id, array $data){

    }

    public function deleteMesa($id){

    }

}