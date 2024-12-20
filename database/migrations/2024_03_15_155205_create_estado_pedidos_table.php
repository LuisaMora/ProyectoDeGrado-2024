<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // debe existir 4 estados: 1.-En espera 2.-En preparacion 3.-Listo para servir 4.-Servido
    public function up(): void
    {
        Schema::create('estado_pedidos', function (Blueprint $table) {
            $table->id(); // Columna autoincrementable para la clave primaria
            $table->string('nombre', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_pedidos');
    }
};
