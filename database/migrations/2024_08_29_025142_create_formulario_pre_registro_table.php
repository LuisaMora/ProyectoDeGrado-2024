<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('formulario_pre_registro', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_restaurante', 100);
            $table->bigInteger('nit');
            $table->string('celular_restaurante', 20);
            $table->string('correo_restaurante', 100);
            $table->string('licencia_funcionamiento', 100);
            $table->string('tipo_establecimiento', 100);
            $table->decimal('latitud', 9, 6);
            $table->decimal('longitud', 9, 6);
            $table->string('nombre_propietario', 50);
            $table->string('apellido_paterno_propietario', 100);
            $table->string('apellido_materno_propietario', 100)->nullable();
            $table->integer('cedula_identidad_propietario');
            $table->string('correo_propietario', 100);
            $table->string('fotografia_propietario', 150)->nullable();
            $table->timestamps(); // Automatically adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formulario_pre_registro');
    }
};
