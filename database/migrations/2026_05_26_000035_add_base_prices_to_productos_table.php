<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'precio_venta_base')) {
                $table->decimal('precio_venta_base', 12, 2)->nullable()->after('genero');
            }

            if (!Schema::hasColumn('productos', 'precio_venta_mayorista_base')) {
                $table->decimal('precio_venta_mayorista_base', 12, 2)->nullable()->after('precio_venta_base');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'precio_venta_mayorista_base')) {
                $table->dropColumn('precio_venta_mayorista_base');
            }

            if (Schema::hasColumn('productos', 'precio_venta_base')) {
                $table->dropColumn('precio_venta_base');
            }
        });
    }
};