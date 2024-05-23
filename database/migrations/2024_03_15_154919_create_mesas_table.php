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
        Schema::create('mesas', function (Blueprint $table) {
            $table->id(); // Columna autoincrementable para la clave primaria
            $table->unsignedBigInteger('id_restaurante');
            $table->string('nombre', 15)->nullable();
            $table->timestamps();

            $table->foreign('id_restaurante')->references('id')->on('restaurantes')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
