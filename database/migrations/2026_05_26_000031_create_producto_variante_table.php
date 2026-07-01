<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_variante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto')->constrained('productos')->cascadeOnDelete();
            $table->string('sku', 80)->nullable()->unique();
            $table->string('talla', 30);
            $table->string('color', 50);
            $table->decimal('precio_compra', 12, 2);
            $table->decimal('precio_venta', 12, 2);
            $table->decimal('precio_venta_mayorista', 12, 2)->nullable();
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->integer('punto_reorden')->default(0);
            $table->decimal('costo_promedio', 12, 2)->default(0);
            $table->foreignId('id_tecnica_inventario')->nullable()->constrained('tecnica_inventario')->nullOnDelete();
            $table->foreignId('id_tecnica_costo')->nullable()->constrained('tecnica_costo')->nullOnDelete();
            $table->string('estado', 30)->default('ACTIVO');
            $table->timestamps();

            $table->index(['id_producto', 'talla', 'color']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_variante');
    }
};