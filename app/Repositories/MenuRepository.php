<?php

namespace App\Repositories;

use App\Models\Menu;
use App\Models\Restaurante;

class MenuRepository
{
    protected $model;

    public function __construct(Menu $menu)
    {
        $this->model = $menu;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function getMenuByRestaurantId(string $id_restaurante)
    {
        return Restaurante::find($id_restaurante)->menu;
    }

    public function create()
    {
        $data = [
            'tema' => 'light-theme',
            'disponible' => true,
        ];
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $menu = $this->model->find($id);
        if ($menu) {
            $menu->update($data);
            return $menu;
        }
        return null;
    }

    public function delete($id)
    {
        $menu = $this->model->find($id);
        if ($menu) {
            return $menu->delete();
        }
        return false;
    }
}