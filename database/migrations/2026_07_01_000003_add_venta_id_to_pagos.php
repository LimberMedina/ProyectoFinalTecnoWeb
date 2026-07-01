<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add nullable venta_id with FK to ventas
        Schema::table('pagos', function (Blueprint $table) {
            $table->unsignedInteger('venta_id')->nullable()->after('cuota_id');
        });

        try {
            DB::statement('ALTER TABLE pagos ADD CONSTRAINT pagos_venta_id_foreign FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE SET NULL');
        } catch (\Throwable $e) {
            // ignore if the constraint cannot be added automatically
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE pagos DROP FOREIGN KEY IF EXISTS pagos_venta_id_foreign');
        } catch (\Throwable $e) {
            try {
                DB::statement('ALTER TABLE pagos DROP FOREIGN KEY pagos_venta_id_foreign');
            } catch (\Throwable $e) {
                // ignore
            }
        }

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('venta_id');
        });
    }
};
