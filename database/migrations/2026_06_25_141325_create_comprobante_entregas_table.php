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
        Schema::create('comprobantes_entrega', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entrega_id')->unique(); // Unique porque es un comprobante por entrega
            $table->string('url_foto'); // Enlace al Cloud Storage
            $table->decimal('latitud_captura', 10, 8)->nullable();
            $table->decimal('longitud_captura', 11, 8)->nullable();
            $table->timestamps(); // Esto ya incluye tu required created_at

            $table->foreign('entrega_id')->references('id')->on('entregas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_entregas');
    }
};
