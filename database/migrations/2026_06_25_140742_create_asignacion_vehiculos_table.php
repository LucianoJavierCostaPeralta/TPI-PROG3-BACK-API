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
        Schema::create('asignaciones_vehiculos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('usuario_id'); // El chofer
            $table->uuid('vehiculo_id'); // El vehículo
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable(); // Nulo si sigue usando esa camioneta
            $table->timestamps();

            // Claves foráneas
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vehiculo_id')->references('id')->on('vehiculos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_vehiculos');
    }
};
