<?php

namespace Database\Seeders;

use App\Models\Platillo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatilloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // tengo categorias del 1 al 6, tienen que pertenecer al menu 1, 2 o 3 unos 30 platillos cada menu
        Platillo::factory(30)->asignarCategoria(1)->asignarMenu(1)->create();
    }
}
