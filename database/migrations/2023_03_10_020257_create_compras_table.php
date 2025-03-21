<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->cascadeOnDelete();
            $table->foreignId('comprobante_id')->nullable()->constrained('comprobantes')->cascadeOnDelete();
            $table->foreignId('proveedore_id')->nullable()->constrained('proveedores')->cascadeOnDelete();
            $table->string('numero_comprobante')->nullable()->unique();
            $table->string('comprobante_path', 2048)->nullable();
            $table->enum('metodo_pago', ['Efectivo', 'Tarjeta', 'Transferencia']);
            $table->dateTime('fecha_hora');
            $table->decimal('impuesto',)->unsigned()->nullable();
            $table->decimal('subtotal',)->unsigned();
            $table->decimal('total',)->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compras');
    }
};
