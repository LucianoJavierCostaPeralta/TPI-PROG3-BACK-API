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
        Schema::create('tipos_vehiculo', function (Blueprint $table) {
            $table->integer('id')->primary(); // INT porque es catálogo fijo [3]
            $table->string('nombre_tipo'); // Ej: Moto, Furgoneta, Camión [3]
            $table->decimal('capacidad_kg', 8, 2); // [3]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_vehiculo');
    }
};
