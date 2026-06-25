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
        Schema::create('auditoria_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('usuario_id')->nullable(); // Quién hizo la acción
            $table->string('tabla_afectada'); // Ej: 'vehiculos', 'entregas'
            $table->string('accion'); // 'INSERT', 'UPDATE', 'DELETE'
            $table->json('detalle_json')->nullable(); // Qué datos modificó
            $table->timestamp('fecha_evento')->useCurrent();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('set null');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_logs');
    }
};
