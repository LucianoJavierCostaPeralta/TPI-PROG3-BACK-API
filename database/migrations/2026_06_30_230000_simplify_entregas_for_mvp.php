<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entregas', function (Blueprint $table) {
            $table->string('cliente', 150)->nullable()->after('cliente_id');
            $table->string('producto', 150)->nullable()->after('cliente');
            $table->uuid('cliente_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('entregas', function (Blueprint $table) {
            $table->dropColumn(['cliente', 'producto']);
            $table->uuid('cliente_id')->nullable(false)->change();
        });
    }
};
