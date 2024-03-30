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
        Schema::create('estado_cuentas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cuenta_id');
            $table->string('estado', 50);
            // "Abierta", "Cerrada", "Pendiente de Pago", "Pagada"
            $table->timestamp('fecha_hora')->default(now());
            $table->foreign('cuenta_id')->references('id')->on('cuentas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estado_cuentas');
    }
};
