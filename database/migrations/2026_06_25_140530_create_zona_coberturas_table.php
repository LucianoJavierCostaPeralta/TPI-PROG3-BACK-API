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
        Schema::create('zonas_cobertura', function (Blueprint $table) {
            $table->uuid('id')->primary(); // [4]
            $table->uuid('empresa_id'); // [4]
            $table->string('nombre_zona'); // Ej: Norte, Centro [4]
            $table->string('codigo_postal'); // [4]
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zonas_cobertura');
    }
};
