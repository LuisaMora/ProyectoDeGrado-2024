<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id(); // Columna autoincrementable para la clave primaria
            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->unsignedBigInteger('mesa_id')->nullable();
            $table->integer('monto_total')->nullable();
            $table->timestamps();

            // Claves foráneas
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('mesa_id')->references('id')->on('mesas')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};
