<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_compra', function (Blueprint $table) {
            if (! Schema::hasColumn('detalle_compra', 'id_producto_variante')) {
                $table->unsignedBigInteger('id_producto_variante')->nullable();
            }

            if (! Schema::hasColumn('detalle_compra', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0);
            }
        });

        if (Schema::hasColumn('detalle_compra', 'id_producto') && Schema::hasColumn('detalle_compra', 'id_producto_variante')) {
            DB::statement(
                "UPDATE detalle_compra dc
                 SET id_producto_variante = (
                    SELECT pv.id
                    FROM producto_variante pv
                    WHERE pv.id_producto = dc.id_producto
                    ORDER BY pv.id
                    LIMIT 1
                 )
                 WHERE dc.id_producto_variante IS NULL"
            );
        }

        Schema::table('detalle_compra', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_compra', 'id_producto_variante')) {
                $table->foreign('id_producto_variante')
                    ->references('id')
                    ->on('producto_variante')
                    ->restrictOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('detalle_compra', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_compra', 'id_producto_variante')) {
                $table->dropForeign(['id_producto_variante']);
                $table->dropColumn('id_producto_variante');
            }

            if (Schema::hasColumn('detalle_compra', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
        });
    }
};