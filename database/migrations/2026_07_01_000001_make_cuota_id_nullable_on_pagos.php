<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Try to alter using raw SQL for MySQL (safer without requiring doctrine/dbal)
        try {
            $driver = DB::getDriverName();
        } catch (\Exception $e) {
            $driver = null;
        }

        if ($driver === 'mysql') {
            try {
                // Drop FK if exists (name is the convention used by Laravel)
                DB::statement('ALTER TABLE pagos DROP FOREIGN KEY IF EXISTS pagos_cuota_id_foreign');
            } catch (\Throwable $e) {
                // Some MySQL versions don't support IF EXISTS; try without it
                try {
                    DB::statement('ALTER TABLE pagos DROP FOREIGN KEY pagos_cuota_id_foreign');
                } catch (\Throwable $e) {
                    // If dropping fails, continue — altering may still work depending on engine
                }
            }

            // Make column nullable
            DB::statement('ALTER TABLE pagos MODIFY cuota_id INT UNSIGNED NULL');

            // Recreate FK (if cuotas table exists)
            try {
                DB::statement('ALTER TABLE pagos ADD CONSTRAINT pagos_cuota_id_foreign FOREIGN KEY (cuota_id) REFERENCES cuotas(id) ON DELETE CASCADE');
            } catch (\Throwable $e) {
                // ignore
            }
        } else {
            // Fallback: use schema change (requires doctrine/dbal)
            Schema::table('pagos', function (Blueprint $table) {
                $table->unsignedInteger('cuota_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            $driver = DB::getDriverName();
        } catch (\Exception $e) {
            $driver = null;
        }

        if ($driver === 'mysql') {
            try {
                DB::statement('ALTER TABLE pagos DROP FOREIGN KEY IF EXISTS pagos_cuota_id_foreign');
            } catch (\Throwable $e) {
                try {
                    DB::statement('ALTER TABLE pagos DROP FOREIGN KEY pagos_cuota_id_foreign');
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            DB::statement('ALTER TABLE pagos MODIFY cuota_id INT UNSIGNED NOT NULL');

            try {
                DB::statement('ALTER TABLE pagos ADD CONSTRAINT pagos_cuota_id_foreign FOREIGN KEY (cuota_id) REFERENCES cuotas(id) ON DELETE CASCADE');
            } catch (\Throwable $e) {
                // ignore
            }
        } else {
            Schema::table('pagos', function (Blueprint $table) {
                $table->unsignedInteger('cuota_id')->nullable(false)->change();
            });
        }
    }
};
