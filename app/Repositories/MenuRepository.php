<?php

namespace App\Repositories;

use App\Models\Menu;

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
        return Menu::where('id_restaurante',$id_restaurante)->first();
    }

    public function create()
    {
        $data = [
            'tema' => 'light-theme',
            'disponible' => true,
        ];
        return $this->model->create($data);
    }

    public function update($id, $data)
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