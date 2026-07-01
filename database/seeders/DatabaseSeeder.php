<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\MotivoRechazo;
use App\Models\Rol;
use App\Models\TipoVehiculo;
use App\Models\User;
use App\Models\ZonaCobertura;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Crear una Empresa base (Vital para que no fallen las FK de usuarios y envíos)
        $empresa = Empresa::firstOrCreate(
            ['cuit' => '30-12345678-9'],
            [
                'id' => Str::uuid(),
                'razon_social' => 'Logística Central',
                'email_contacto' => 'contacto@logisticacentral.com',
                'telefono' => '1122334455',
            ]
        );

        // 2. Crear los Roles (Catálogo)
        Rol::updateOrCreate(['id' => 1], ['nombre_rol' => 'Admin', 'descripcion' => 'Administrador del sistema']);
        Rol::updateOrCreate(['id' => 2], ['nombre_rol' => 'Chofer', 'descripcion' => 'Conductor logistico']);

        // 3. Crear Usuarios de prueba (El Admin que exige el TP y un Chofer para pruebas)
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'id' => Str::uuid(),
                'nombre_completo' => 'Administrador',
                'password' => Hash::make('123456'),
                'rol_id' => 1,
                'empresa_id' => $empresa->id,
                'activo' => true,
            ]
        );

        $choferExiste = User::query()
            ->where('email', 'chofer@logistica.com')
            ->orWhere('dni', '12345678')
            ->exists();

        if (! $choferExiste) {
            User::create([
                'id' => Str::uuid(),
                'nombre_completo' => 'Juan Chofer',
                'email' => 'chofer@logistica.com',
                'dni' => '12345678',
                'fecha_nacimiento' => '1990-05-12',
                'password' => Hash::make('123456'),
                'rol_id' => 2,
                'empresa_id' => $empresa->id,
                'telefono' => '1155667788',
                'activo' => true,
            ]);
        }

        // 4. Catálogo: Motivos de Rechazo
        $motivos = ['Domicilio cerrado', 'Cliente ausente', 'Dirección incorrecta'];
        foreach ($motivos as $index => $motivo) {
            MotivoRechazo::updateOrCreate(
                ['id' => $index + 1],
                ['descripcion' => $motivo]
            );
        }

        // 5. Catálogo: Tipos de Vehículo
        TipoVehiculo::updateOrCreate(['id' => 1], ['nombre_tipo' => 'Moto', 'capacidad_kg' => 50]);
        TipoVehiculo::updateOrCreate(['id' => 2], ['nombre_tipo' => 'Furgoneta', 'capacidad_kg' => 500]);
        TipoVehiculo::updateOrCreate(['id' => 3], ['nombre_tipo' => 'Camión', 'capacidad_kg' => 5000]);

        // 6. Catálogo: Zonas de Cobertura
        $zonas = ['Norte', 'Sur', 'Centro'];
        foreach ($zonas as $zona) {
            ZonaCobertura::firstOrCreate(
                ['nombre_zona' => $zona, 'empresa_id' => $empresa->id],
                [
                    'id' => Str::uuid(),
                    'codigo_postal' => '1000',
                ]
            );
        }
    }
}
