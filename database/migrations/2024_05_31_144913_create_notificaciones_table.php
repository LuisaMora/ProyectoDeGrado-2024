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
            $table->unsignedBigInteger('id_pedido'); // ID del pedido al que pertenece la notificación
            $table->unsignedBigInteger('id_creador'); // ID del usuario que creo la notificacion
            $table->unsignedBigInteger('id_restaurante'); // ID del restaurante al que pertenece la notificación
            // Tipo de notificación sola para Pedidos, Platillos 
            $table->enum('tipo', ['pedido', 'platillo']);
            $table->string('titulo', 100); // Título de la notificación (opcional
            $table->string('mensaje',100); // Mensaje de la notificación
            $table->timestamp('read_at')->nullable(); // Fecha de lectura
            $table->timestamps();

            // Llave foránea para el ID del usuario
            $table->foreign('id_creador')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_pedido')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('id_restaurante')->references('id')->on('restaurantes')->onDelete('cascade');
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
