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
            $table->string('nombre', 100);
            $table->bigInteger('nit')->unique();
            $table->decimal('latitud', 9, 6);
            $table->decimal('longitud', 9, 6);
            $table->string('celular', 20);
            $table->string('correo', 100)->unique( );
            $table->string('licencia_funcionamiento', 100);
            $table->string('tipo_establecimiento', 100);
            $table->timestamps();
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
