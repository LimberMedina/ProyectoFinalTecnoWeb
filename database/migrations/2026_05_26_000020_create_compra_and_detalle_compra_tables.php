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
        if (! Schema::hasTable('compra')) {
            Schema::create('compra', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_propietario');
                $table->foreign('id_propietario')->references('id')->on('users')->onDelete('cascade');
                $table->unsignedBigInteger('id_proveedor');
                $table->foreign('id_proveedor')->references('id')->on('proveedor')->onDelete('restrict');
                $table->string('num_compra', 50)->nullable();
                $table->decimal('precio_total', 12, 2)->default(0);
                $table->date('fecha_compra');
            });
        }

        if (! Schema::hasTable('detalle_compra')) {
            Schema::create('detalle_compra', function (Blueprint $table) {
                $table->increments('id_detalle');
                $table->unsignedBigInteger('id_compra');
                $table->foreign('id_compra')->references('id')->on('compra')->onDelete('cascade');
                $table->unsignedInteger('id_producto');
                $table->foreign('id_producto')->references('id')->on('productos')->onDelete('restrict');
                $table->integer('cantidad');
                $table->decimal('precio', 12, 2)->nullable();
                $table->decimal('descuento', 12, 2)->nullable()->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('detalle_compra')) {
            Schema::drop('detalle_compra');
        }

        if (Schema::hasTable('compra')) {
            Schema::drop('compra');
        }
    }
};
