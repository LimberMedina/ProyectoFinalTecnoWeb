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
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('accion'); // 'login', 'logout', 'create', 'update', 'delete', etc.
            $table->string('entidad')->nullable(); // 'Producto', 'Usuario', 'Venta', etc.
            $table->unsignedBigInteger('entidad_id')->nullable(); // ID del registro afectado
            $table->string('entidad_nombre')->nullable(); // Nombre/descripción de lo afectado
            $table->json('cambios')->nullable(); // Cambios realizados (antes/después)
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('descripcion')->nullable(); // Descripción adicional
            $table->timestamps();
            
            // Índices para búsqueda rápida
            $table->index('user_id');
            $table->index('accion');
            $table->index('entidad');
            $table->index('created_at');
            
            // Foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
