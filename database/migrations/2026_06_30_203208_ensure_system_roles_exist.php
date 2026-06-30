<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(
            ['id' => 1],
            [
                'nombre_rol' => 'Admin',
                'descripcion' => 'Administrador de empresa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('roles')->updateOrInsert(
            ['id' => 2],
            [
                'nombre_rol' => 'Chofer',
                'descripcion' => 'Conductor logistico',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        // Los roles no se eliminan porque pueden estar referenciados por usuarios.
    }
};
