<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_asesoramiento', function (Blueprint $table) {
            $table->foreignUuid('usuario_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->json('datos_usuario')->nullable()->after('usuario_id');
            $table->text('mensaje')->nullable()->after('datos_usuario');
            $table->string('nombre_empresa')->nullable()->change();
            $table->string('cuit')->nullable()->change();
            $table->string('correo_corporativo')->nullable()->change();
            $table->string('telefono')->nullable()->change();
            $table->string('cantidad_vehiculos')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_asesoramiento', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropColumn(['usuario_id', 'datos_usuario', 'mensaje']);
        });
    }
};
