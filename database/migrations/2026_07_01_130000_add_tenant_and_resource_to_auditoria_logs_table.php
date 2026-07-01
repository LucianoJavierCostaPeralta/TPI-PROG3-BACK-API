<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditoria_logs', function (Blueprint $table) {
            $table->foreignUuid('empresa_id')->after('id')->constrained('empresas')->cascadeOnDelete();
            $table->uuid('recurso_id')->nullable()->after('tabla_afectada');
            $table->index('fecha_evento');
            $table->index('accion');
            $table->index(['tabla_afectada', 'recurso_id']);
        });
    }

    public function down(): void
    {
        Schema::table('auditoria_logs', function (Blueprint $table) {
            $table->dropIndex(['tabla_afectada', 'recurso_id']);
            $table->dropIndex(['accion']);
            $table->dropIndex(['fecha_evento']);
            $table->dropForeign(['empresa_id']);
            $table->dropColumn(['empresa_id', 'recurso_id']);
        });
    }
};
