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
        Schema::create('motivos_rechazo', function (Blueprint $table) {
            $table->integer('id')->primary(); // INT porque es catálogo fijo
            $table->string('descripcion'); // Ej: Domicilio cerrado, Cliente ausente
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motivo_rechazos');
    }
};
