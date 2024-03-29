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
            $table->text('detalle')->nullable();
            $table->integer('cantidad')->nullable();
            $table->string('estado', 250)->nullable();
            $table->unsignedBigInteger('id_mesa')->nullable();
            $table->unsignedBigInteger('id_empleado')->nullable();
            $table->date('fecha')->nullable();
            $table->timestamps();

            $table->foreign('id_mesa')->references('id')->on('mesas')->onDelete('restrict')->onUpdate('restrict');
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
