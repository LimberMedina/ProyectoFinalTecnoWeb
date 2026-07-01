<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE productos ALTER COLUMN precio_compra DROP NOT NULL');
        DB::statement('ALTER TABLE productos ALTER COLUMN precio_venta DROP NOT NULL');
        DB::statement('ALTER TABLE productos ALTER COLUMN precio_venta_mayorista DROP NOT NULL');
        DB::statement('ALTER TABLE productos ALTER COLUMN stock_actual DROP NOT NULL');
        DB::statement('ALTER TABLE productos ALTER COLUMN stock_minimo DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE productos ALTER COLUMN precio_compra SET NOT NULL');
        DB::statement('ALTER TABLE productos ALTER COLUMN precio_venta SET NOT NULL');
        DB::statement('ALTER TABLE productos ALTER COLUMN precio_venta_mayorista SET NOT NULL');
        DB::statement('ALTER TABLE productos ALTER COLUMN stock_actual SET NOT NULL');
        DB::statement('ALTER TABLE productos ALTER COLUMN stock_minimo SET NOT NULL');
    }
};