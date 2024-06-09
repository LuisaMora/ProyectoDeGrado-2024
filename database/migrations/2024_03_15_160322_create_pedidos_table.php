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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cuenta')->nullable();
            $table->enum('tipo', ['local', 'llevar'])->default('local');
            $table->unsignedBigInteger('id_empleado')->nullable();
            $table->dateTime('fecha_hora_pedido')->nullable();
            $table->decimal('monto', 8, 2)->nullable();
            $table->timestamps();
            $table->foreign('id_cuenta')->references('id')->on('cuentas')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('id_empleado')->references('id')->on('empleados')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
