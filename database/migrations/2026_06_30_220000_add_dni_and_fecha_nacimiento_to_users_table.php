<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->string("dni", 8)->nullable()->unique()->after("nombre_completo");
            $table->date("fecha_nacimiento")->nullable()->after("dni");
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->dropUnique(["dni"]);
            $table->dropColumn(["dni", "fecha_nacimiento"]);
        });
    }
};