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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('usuarios')->onDelete('restrict')->onUpdate('restrict');
            $table->foreignId('id_rol')->constrained('rol_empleados')->onDelete('restrict')->onUpdate('restrict');
            $table->foreignId('id_propietario')->nullable()->constrained('propietarios')->onDelete('restrict')->onUpdate('restrict');
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_contratacion')->nullable();
            $table->integer('ci')->nullable();
            $table->string('direccion', 150)->nullable();
            $table->foreignId('id_restaurante')->constrained('restaurantes')->onDelete('restrict')->onUpdate('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
