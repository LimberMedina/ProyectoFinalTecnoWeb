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
        if (! Schema::hasTable('pedido')) {
            Schema::create('pedido', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('carrito_id');
                $table->string('numero_pedido', 50)->nullable();
                $table->timestamp('fecha_pedido')->nullable();
                $table->timestamp('fecha_pago')->nullable();
                $table->timestamp('fecha_envio')->nullable();
                $table->timestamp('fecha_entrega')->nullable();
                $table->timestamp('fecha_recibido_conforme')->nullable();
                $table->string('tipo_venta', 50)->nullable();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('descuento', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);
                $table->string('estado', 50)->nullable();
                $table->text('direccion_entrega')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('carrito_id')->references('id')->on('carritos')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('pedido_detalle')) {
            Schema::create('pedido_detalle', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pedido_id');
                $table->unsignedBigInteger('producto_variante_id');
                $table->integer('cantidad');
                $table->decimal('precio_unitario', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->decimal('descuento', 12, 2)->default(0);
                $table->timestamps();

                $table->foreign('pedido_id')->references('id')->on('pedido')->onDelete('cascade');
                $table->foreign('producto_variante_id')->references('id')->on('producto_variante')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('pago_contado')) {
            Schema::create('pago_contado', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('venta_id');
                $table->unsignedBigInteger('metodo_pago_id');
                $table->date('fecha')->nullable();
                $table->decimal('monto_pago', 12, 2)->default(0);
                $table->decimal('interes_mora_cobrado', 12, 2)->default(0);
                $table->decimal('recargo_extra', 12, 2)->default(0);
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
                $table->foreign('metodo_pago_id')->references('id')->on('metodos_pago')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('pago_cuota')) {
            Schema::create('pago_cuota', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('credito_id');
                $table->unsignedBigInteger('metodo_pago_id');
                $table->integer('numero_cuota')->nullable();
                $table->decimal('interes_cuota', 12, 2)->default(0);
                $table->date('fecha_vencimiento')->nullable();
                $table->date('fecha_pago')->nullable();
                $table->decimal('monto_pagado', 12, 2)->default(0);
                $table->integer('dias_mora')->nullable();
                $table->decimal('monto_pendiente', 12, 2)->default(0);
                $table->string('estado', 30)->nullable();
                $table->timestamps();

                $table->foreign('credito_id')->references('id')->on('creditos')->onDelete('cascade');
                $table->foreign('metodo_pago_id')->references('id')->on('metodos_pago')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pago_cuota')) {
            Schema::dropIfExists('pago_cuota');
        }

        if (Schema::hasTable('pago_contado')) {
            Schema::dropIfExists('pago_contado');
        }

        if (Schema::hasTable('pedido_detalle')) {
            Schema::dropIfExists('pedido_detalle');
        }

        if (Schema::hasTable('pedido')) {
            Schema::dropIfExists('pedido');
        }
    }
};
