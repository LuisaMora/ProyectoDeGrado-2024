<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Otros',
            'Desayunos',
            'Comida',
            'Cena',
            'Bebidas',
            'Postres',
        ];

        foreach ($categorias as $categoria) {
            \App\Models\Categoria::factory()->nombre($categoria)->create();
        }
    }
}
