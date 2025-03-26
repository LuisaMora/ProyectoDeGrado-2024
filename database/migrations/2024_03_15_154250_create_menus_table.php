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
        Schema::create('menus', function (Blueprint $table) {
            $table->id(); // Columna autoincrementable para la clave primaria
            $table->unsignedBigInteger('id_restaurante')->nullable();
            $table->string('portada', 100)->default('');
            $table->string('tema')->default('light-theme');
            $table->string('qr', 100)->default('');
            $table->boolean('disponible')->default(true);
            $table->timestamps();

            $table->foreign('id_restaurante')->references('id')->on('restaurantes')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
