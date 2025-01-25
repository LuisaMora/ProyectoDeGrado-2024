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
        Schema::create('platillos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_menu')->nullable();
            $table->string('nombre', 250)->nullable();
            $table->integer('precio')->nullable();
            $table->string('imagen', 300)->nullable();
            $table->string('descripcion', 500)->nullable();
            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->boolean('disponible')->default(true);
            $table->boolean('plato_disponible_menu')->default(true);
            $table->timestamps();

            $table->foreign('id_menu')->references('id')->on('menus')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('id_categoria')->references('id')->on('categorias')->onDelete('restrict')->onUpdate('restrict');
            $table->foreignId('id_restaurante')->constrained('restaurantes')->onDelete('restrict')->onUpdate('restrict');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platillos');
    }
};
