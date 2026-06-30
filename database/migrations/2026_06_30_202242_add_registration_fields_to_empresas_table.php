<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('tamano_flota', 20)->nullable()->after('telefono');
            $table->timestamp('terminos_aceptados_en')->nullable()->after('tamano_flota');
            $table->unique('cuit');
            $table->unique('email_contacto');
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropUnique(['cuit']);
            $table->dropUnique(['email_contacto']);
            $table->dropColumn(['tamano_flota', 'terminos_aceptados_en']);
        });
    }
};
