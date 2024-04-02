<?php

namespace Database\Seeders;

use App\Models\Mesa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mesa::factory(10)->registrar_a_restaurante(1)->create();
        Mesa::factory(8)->registrar_a_restaurante(2)->create();
        Mesa::factory(5)->registrar_a_restaurante(3)->create();
    }
}
