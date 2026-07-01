<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $estados = [
            ['id' => 1, 'nombre_estado' => 'pending', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nombre_estado' => 'assigned', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'nombre_estado' => 'accepted', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'nombre_estado' => 'on_the_way', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'nombre_estado' => 'delivered', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'nombre_estado' => 'finished', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'nombre_estado' => 'cancelled', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('estados_entrega')->upsert($estados, ['id'], ['nombre_estado', 'updated_at']);
    }

    public function down(): void
    {
        // El catálogo no se elimina: puede estar referenciado por entregas e historiales.
    }
};
