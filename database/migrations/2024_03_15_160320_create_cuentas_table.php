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
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mesa');
            $table->enum('estado', ['Abierta', 'Cancelada', 'PagoPendiente', 'Pagada'])->default('Abierta');
            $table->string('nombre_razon_social', 100)->default('Anónimo');
            $table->integer('nit')->default(0);
            $table->decimal('monto_total', 8, 2)->default(0.00);
            $table->timestamps();
            $table->foreign('id_mesa')->references('id')->on('mesas')->onDelete('restrict')->onUpdate('restrict');
            // $table->foreign('id_estado_cuenta')->references('id')->on('estado_cuentas')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};
