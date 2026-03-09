<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL usa CHECK constraints para los enum de Laravel.
        // Eliminamos el constraint antiguo y creamos uno nuevo con los valores completos.
        DB::statement('ALTER TABLE ventas DROP CONSTRAINT IF EXISTS ventas_estado_check');
        DB::statement("ALTER TABLE ventas ADD CONSTRAINT ventas_estado_check CHECK (estado IN ('pendiente', 'pagado', 'enviado', 'anulado', 'entregado', 'completada'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ventas DROP CONSTRAINT IF EXISTS ventas_estado_check');
        DB::statement("ALTER TABLE ventas ADD CONSTRAINT ventas_estado_check CHECK (estado IN ('pendiente', 'pagado', 'enviado', 'anulado'))");
    }
};
