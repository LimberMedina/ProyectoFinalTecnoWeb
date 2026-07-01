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
        Schema::table('cuotas', function (Blueprint $table) {
            if (! Schema::hasColumn('cuotas', 'mora')) {
                $table->decimal('mora', 10, 2)->default(0)->after('interes_cuota');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuotas', function (Blueprint $table) {
            if (Schema::hasColumn('cuotas', 'mora')) {
                $table->dropColumn('mora');
            }
        });
    }
};
