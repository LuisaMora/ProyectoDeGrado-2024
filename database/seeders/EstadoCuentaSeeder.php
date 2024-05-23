<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstadoCuentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            ['estado' => 'Abierta'],
            ['estado' => 'Pagada'],
            ['estado' => 'Cancelada'],
            ['estado' => 'PagoPendiente']
        ];

        foreach ($estados as $estado) {
            \App\Models\EstadoCuenta::create($estado);
        }
    }
}
