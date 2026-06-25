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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->uuid('id')->primary(); // [3]
            $table->uuid('empresa_id'); // [3]
            $table->integer('tipo_id'); // FK a tipos_vehiculos [3]
            $table->string('patente')->unique(); // [3]
            $table->string('marca_modelo'); // [3]
            $table->boolean('estado_operativo')->default(true); // [3]
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('tipo_id')->references('id')->on('tipos_vehiculo');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
