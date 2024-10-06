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
        Schema::create('plato_pedido', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_platillo');
            $table->unsignedBigInteger('id_pedido');
            
            $table->text('detalle')->nullable();
            $table->integer('cantidad');
            $table->timestamps();

            $table->foreign('id_platillo')->references('id')->on('platillos')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('id_pedido')->references('id')->on('pedidos')->onDelete('restrict')->onUpdate('restrict');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plato_pedido');
    }
};
