<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entregas', function (Blueprint $table) {
            $table->string('cliente_dni', 8)->nullable()->after('cliente');
        });
    }

    public function down(): void
    {
        Schema::table('entregas', function (Blueprint $table) {
            $table->dropColumn('cliente_dni');
        });
    }
};
