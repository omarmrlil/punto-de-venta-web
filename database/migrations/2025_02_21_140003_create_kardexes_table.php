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
        Schema::create('kardex', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('productos')->cascadeOnDelete();
            $table->enum('tipo_transaccion', ['Compra', 'Venta', 'Ajuste', 'Apertura']);
            $table->string('descripcion_transaccion');
            $table->integer('entrada') ->nullable();
            $table->integer('salida') ->nullable();
            $table->integer('saldo');
            $table->decimal('costo_unitario',);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kardex');
    }
};
