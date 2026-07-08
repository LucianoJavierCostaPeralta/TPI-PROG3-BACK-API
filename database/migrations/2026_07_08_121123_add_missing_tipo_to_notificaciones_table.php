<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('notificaciones', 'tipo')) {
                $table->string('tipo')->default('info');
            }

            if (! Schema::hasColumn('notificaciones', 'leida')) {
                $table->boolean('leida')->default(false);
            }
        });
    }

    public function down(): void
    {
        // This migration repairs drift in deployed schemas; rolling it back should not remove existing columns.
    }
};
