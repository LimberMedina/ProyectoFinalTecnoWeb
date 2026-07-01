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
        Schema::create('proveedor', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('nit', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('direccion')->nullable();
            $table->string('estado', 30)->nullable();
            $table->date('fecha_creacion')->nullable();
            $table->date('fecha_modificacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedor');
    }
};
