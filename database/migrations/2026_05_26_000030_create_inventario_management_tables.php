<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tecnica_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('tecnica_costo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('movimiento_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('id_tecnica_inventario')->constrained('tecnica_inventario')->restrictOnDelete();
            $table->foreignId('id_tecnica_costo')->constrained('tecnica_costo')->restrictOnDelete();
            $table->string('tipo_movimiento', 50);
            $table->text('motivo')->nullable();
            $table->integer('cantidad');
            $table->decimal('costo_unitario', 12, 2);
            $table->decimal('costo_total', 12, 2);
            $table->integer('stock_resultante');
            $table->date('fecha');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['id_producto', 'fecha']);
            $table->index('tipo_movimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventario');
        Schema::dropIfExists('tecnica_costo');
        Schema::dropIfExists('tecnica_inventario');
    }
};
