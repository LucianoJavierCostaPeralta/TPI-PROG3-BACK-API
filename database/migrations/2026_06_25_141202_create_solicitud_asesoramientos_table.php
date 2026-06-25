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
        Schema::create('solicitudes_asesoramiento', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre_empresa');
            $table->string('cuit');
            $table->string('correo_corporativo');
            $table->string('telefono');
            $table->string('cantidad_vehiculos');
            $table->boolean('leido')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_asesoramientos');
    }
};
