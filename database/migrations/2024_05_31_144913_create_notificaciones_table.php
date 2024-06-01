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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario'); // ID del usuario al que pertenece la notificación
            // Tipo de notificación sola para Pedidos, Platillos 
            $table->enum('tipo', ['pedido', 'platillo']);
            $table->text('mensaje'); // Mensaje de la notificación
            $table->timestamp('read_at')->nullable(); // Fecha de lectura
            $table->timestamps();

            // Llave foránea para el ID del usuario
            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
