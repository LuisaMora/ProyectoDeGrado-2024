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
        Schema::create('administradores', function (Blueprint $table) {
                $table->id(); 
                $table->unsignedBigInteger('id_usuario')->nullable(); 
                $table->char('token', 200)->nullable();
                $table->timestamps();
    
                // Claves foráneas
                $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administradores');
    }
};
