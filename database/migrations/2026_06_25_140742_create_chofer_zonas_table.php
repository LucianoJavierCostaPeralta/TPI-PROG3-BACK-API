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
        Schema::create('chofer_zonas', function (Blueprint $table) {
            $table->uuid('usuario_id');
            $table->uuid('zona_id');
            $table->timestamps();

            // PK combinada de ambos FK, tal como sugiere tu esquema [5]
            $table->primary(['usuario_id', 'zona_id']);

            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('zona_id')->references('id')->on('zonas_cobertura')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chofer_zonas');
    }
};
