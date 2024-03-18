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
                $table->unsignedBigInteger('usuario_id')->nullable(); 
                $table->char('token', 200)->nullable();
                $table->timestamps();
    
                // Claves foráneas
                $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('restrict')->onUpdate('restrict');
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
