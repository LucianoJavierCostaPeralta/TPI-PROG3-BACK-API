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
        Schema::create('entregas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('empresa_id');
            $table->uuid('chofer_id')->nullable(); // Apunta a usuarios. Nullable por si aún no se asignó.
            $table->uuid('cliente_id'); // Apunta a clientes_destinatarios
            $table->integer('estado_id')->default(1); // Apunta a estados_entrega (ej. 1 = Pendiente)
            
            $table->string('direccion_destino');
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->integer('orden_ruta')->nullable();
            $table->string('referencia')->nullable();
            $table->timestamp('fecha_asignacion')->nullable();

            $table->timestamps();

            // Declaración explícita de claves foráneas
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('chofer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cliente_id')->references('id')->on('clientes_destinatarios')->onDelete('cascade');
            $table->foreign('estado_id')->references('id')->on('estados_entrega');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
