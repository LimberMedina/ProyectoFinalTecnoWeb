<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('carrito_detalle') && Schema::hasColumn('carrito_detalle', 'producto_id')) {
            DB::statement(
                "UPDATE carrito_detalle cd
                 SET producto_variante_id = COALESCE(
                     cd.producto_variante_id,
                     (
                         SELECT pv.id
                         FROM producto_variante pv
                         WHERE pv.id_producto = cd.producto_id
                         ORDER BY pv.id
                         LIMIT 1
                     )
                 )
                 WHERE cd.producto_variante_id IS NULL"
            );

            DB::statement('ALTER TABLE carrito_detalle ALTER COLUMN producto_variante_id SET NOT NULL');

            Schema::table('carrito_detalle', function (Blueprint $table) {
                $table->dropConstrainedForeignId('producto_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('carrito_detalle')) {
            return;
        }

        Schema::table('carrito_detalle', function (Blueprint $table) {
            if (! Schema::hasColumn('carrito_detalle', 'producto_id')) {
                $table->foreignId('producto_id')
                    ->nullable()
                    ->after('carrito_id')
                    ->constrained('productos')
                    ->cascadeOnDelete();
            }
        });

        DB::statement(
            "UPDATE carrito_detalle cd
             SET producto_id = pv.id_producto
             FROM producto_variante pv
             WHERE pv.id = cd.producto_variante_id
               AND cd.producto_id IS NULL"
        );

        DB::statement('ALTER TABLE carrito_detalle ALTER COLUMN producto_id SET NOT NULL');
    }
};