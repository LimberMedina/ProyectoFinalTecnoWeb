<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lote_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto_variante')->constrained('producto_variante')->cascadeOnDelete();
            $table->unsignedBigInteger('id_detalle_compra')->nullable();
            $table->date('fecha_ingreso');
            $table->integer('cantidad_inicial');
            $table->integer('cantidad_disponible');
            $table->decimal('costo_unitario', 12, 2);
            $table->string('estado', 30)->default('DISPONIBLE');
            $table->timestamps();

            $table->foreign('id_detalle_compra')
                ->references('id_detalle')
                ->on('detalle_compra')
                ->nullOnDelete();

            $table->index(['id_producto_variante', 'fecha_ingreso']);
        });

        Schema::create('salida_lote', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_detalle_venta');
            $table->foreignId('id_lote')->constrained('lote_inventario')->cascadeOnDelete();
            $table->integer('cantidad');
            $table->decimal('costo_unitario_aplicado', 12, 2);
            $table->decimal('costo_total', 12, 2);
            $table->timestamps();

            $table->foreign('id_detalle_venta')
                ->references('id')
                ->on('detalle_venta')
                ->cascadeOnDelete();

            $table->index(['id_detalle_venta', 'id_lote']);
        });

        Schema::table('detalle_venta', function (Blueprint $table) {
            if (! Schema::hasColumn('detalle_venta', 'id_producto_variante')) {
                $table->unsignedBigInteger('id_producto_variante')->nullable()->after('venta_id');
            }

            if (! Schema::hasColumn('detalle_venta', 'costo_unitario')) {
                $table->decimal('costo_unitario', 12, 2)->nullable()->after('subtotal');
            }

            if (! Schema::hasColumn('detalle_venta', 'costo_total')) {
                $table->decimal('costo_total', 12, 2)->nullable()->after('costo_unitario');
            }

            if (! Schema::hasColumn('detalle_venta', 'utilidad_bruta')) {
                $table->decimal('utilidad_bruta', 12, 2)->nullable()->after('costo_total');
            }
        });

        if (Schema::hasTable('detalle_venta') && Schema::hasColumn('detalle_venta', 'producto_id') && Schema::hasColumn('detalle_venta', 'id_producto_variante')) {
            DB::statement(
                "UPDATE detalle_venta dv
                 SET id_producto_variante = (
                    SELECT pv.id
                    FROM producto_variante pv
                    WHERE pv.id_producto = dv.producto_id
                    ORDER BY pv.id ASC
                    LIMIT 1
                 )
                 WHERE dv.id_producto_variante IS NULL"
            );
        }

        Schema::table('detalle_venta', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_venta', 'id_producto_variante')) {
                $table->foreign('id_producto_variante')
                    ->references('id')
                    ->on('producto_variante')
                    ->cascadeOnDelete();
            }
        });

        Schema::table('movimiento_inventario', function (Blueprint $table) {
            if (! Schema::hasColumn('movimiento_inventario', 'id_producto_variante')) {
                $table->unsignedBigInteger('id_producto_variante')->nullable()->after('id');
            }

            if (! Schema::hasColumn('movimiento_inventario', 'stock_anterior')) {
                $table->integer('stock_anterior')->nullable()->after('costo_total');
            }
        });

        if (Schema::hasColumn('movimiento_inventario', 'id_producto')) {
            DB::statement(
                "UPDATE movimiento_inventario mi
                 SET id_producto_variante = (
                    SELECT pv.id
                    FROM producto_variante pv
                    WHERE pv.id_producto = mi.id_producto
                    ORDER BY pv.id ASC
                    LIMIT 1
                 )
                 WHERE mi.id_producto_variante IS NULL"
            );
        }

        Schema::table('movimiento_inventario', function (Blueprint $table) {
            if (Schema::hasColumn('movimiento_inventario', 'id_producto_variante')) {
                $table->foreign('id_producto_variante')
                    ->references('id')
                    ->on('producto_variante')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('movimiento_inventario', function (Blueprint $table) {
            if (Schema::hasColumn('movimiento_inventario', 'id_producto_variante')) {
                $table->dropForeign(['id_producto_variante']);
                $table->dropColumn('id_producto_variante');
            }

            if (Schema::hasColumn('movimiento_inventario', 'stock_anterior')) {
                $table->dropColumn('stock_anterior');
            }
        });

        Schema::table('detalle_venta', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_venta', 'id_producto_variante')) {
                $table->dropForeign(['id_producto_variante']);
                $table->dropColumn('id_producto_variante');
            }

            if (Schema::hasColumn('detalle_venta', 'costo_unitario')) {
                $table->dropColumn('costo_unitario');
            }

            if (Schema::hasColumn('detalle_venta', 'costo_total')) {
                $table->dropColumn('costo_total');
            }

            if (Schema::hasColumn('detalle_venta', 'utilidad_bruta')) {
                $table->dropColumn('utilidad_bruta');
            }
        });

        Schema::dropIfExists('salida_lote');
        Schema::dropIfExists('lote_inventario');
    }
};