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
        Schema::create('propietarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('restrict')->onUpdate('restrict');
            $table->foreignId('id_administrador')->nullable()->constrained('administradores')->onDelete('restrict')->onUpdate('restrict');
            $table->foreignId('id_restaurante')->nullable()->constrained('restaurantes')->onDelete('restrict')->onUpdate('restrict');
            $table->integer('ci')->nullable();
            $table->date('fecha_registro')->nullable();
            $table->string('pais', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propietarios');
    }
};
