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
        Schema::create('historial_estados_entrega', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entrega_id');
            $table->integer('estado_anterior_id')->nullable();
            $table->integer('estado_nuevo_id');
            $table->integer('motivo_rechazo_id')->nullable();
            $table->uuid('usuario_id'); // El chofer que tocó el botón
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->timestamps();

            // Claves foráneas
            $table->foreign('entrega_id')->references('id')->on('entregas')->onDelete('cascade');
            $table->foreign('estado_anterior_id')->references('id')->on('estados_entrega');
            $table->foreign('estado_nuevo_id')->references('id')->on('estados_entrega');
            $table->foreign('motivo_rechazo_id')->references('id')->on('motivos_rechazo');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_estado_entregas');
    }
};
