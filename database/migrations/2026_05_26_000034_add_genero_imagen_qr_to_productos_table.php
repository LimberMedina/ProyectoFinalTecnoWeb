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
        Schema::table('productos', function (Blueprint $table) {
            if (! Schema::hasColumn('productos', 'imagen')) {
                $table->text('imagen')->nullable()->after('codigo');
            }

            if (! Schema::hasColumn('productos', 'qr')) {
                $table->text('qr')->nullable()->after('imagen');
            }

            if (! Schema::hasColumn('productos', 'genero')) {
                $table->string('genero', 30)->nullable()->after('marca');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'genero')) {
                $table->dropColumn('genero');
            }

            if (Schema::hasColumn('productos', 'qr')) {
                $table->dropColumn('qr');
            }

            if (Schema::hasColumn('productos', 'imagen')) {
                $table->dropColumn('imagen');
            }
        });
    }
};
