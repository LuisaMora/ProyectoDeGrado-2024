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
        Schema::create('restaurantes', function (Blueprint $table) {
            $table->id(); // Columna autoincrementable para la clave primaria
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->string('nombre', 100);
            $table->integer('nit');
            $table->string('direccion', 100);
            $table->integer('telefono');
            $table->string('correo', 100);
            $table->string('licencia_funcionamiento', 100);
            $table->timestamps();

            // Clave foránea
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurantes');
    }
};
