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
        Schema::create('detalles_entrega', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entrega_id');
            $table->uuid('producto_id');
            $table->integer('cantidad');

            $table->timestamps();

            // Claves foráneas de la tabla pivot
            $table->foreign('entrega_id')->references('id')->on('entregas')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_entregas');
    }
};
