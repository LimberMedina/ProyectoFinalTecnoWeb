<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carrito_detalle', function (Blueprint $table) {
            if (!Schema::hasColumn('carrito_detalle', 'producto_variante_id')) {
                $table->foreignId('producto_variante_id')
                    ->nullable()
                    ->after('producto_id')
                    ->constrained('producto_variante')
                    ->cascadeOnDelete();
            }
        });

        DB::statement(
            "UPDATE carrito_detalle cd
             SET producto_variante_id = (
                 SELECT pv.id
                 FROM producto_variante pv
                 WHERE pv.id_producto = cd.producto_id
                 ORDER BY pv.id
                 LIMIT 1
             )
             WHERE cd.producto_variante_id IS NULL"
        );
    }

    public function down(): void
    {
        Schema::table('carrito_detalle', function (Blueprint $table) {
            if (Schema::hasColumn('carrito_detalle', 'producto_variante_id')) {
                $table->dropConstrainedForeignId('producto_variante_id');
            }
        });
    }
};