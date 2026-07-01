<?php

namespace Database\Seeders;

use App\Models\AsignacionVehiculo;
use App\Models\ClienteDestinatario;
use App\Models\DetalleEntrega;
use App\Models\Empresa;
use App\Models\Entrega;
use App\Models\Producto;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\ZonaCobertura;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::first();
        $chofer = User::where('email', 'chofer@logistica.com')->first();
        $zona = ZonaCobertura::where('nombre_zona', 'Centro')->first();

        // 1. Asignar Zona al Chofer (Tabla pivot)
        DB::table('chofer_zonas')->updateOrInsert(
            ['usuario_id' => $chofer->id, 'zona_id' => $zona->id]
        );

        // 2. Crear un Vehículo
        $vehiculo = Vehiculo::firstOrCreate(
            ['patente' => 'AB123CD'],
            [
                'id' => Str::uuid(),
                'empresa_id' => $empresa->id,
                'tipo_id' => 2, // Furgoneta
                'marca_modelo' => 'Renault Kangoo',
                'estado_operativo' => true,
            ]
        );

        // 3. Asignar Vehículo al Chofer
        AsignacionVehiculo::firstOrCreate(
            ['usuario_id' => $chofer->id, 'vehiculo_id' => $vehiculo->id],
            [
                'id' => Str::uuid(),
                'fecha_inicio' => now(),
            ]
        );

        // 4. Crear Cliente Destinatario
        $cliente = ClienteDestinatario::firstOrCreate(
            ['telefono' => '1199887766'],
            [
                'id' => Str::uuid(),
                'empresa_id' => $empresa->id,
                'nombre_completo' => 'María González',
                'direccion_frecuente' => 'Av. Siempre Viva 742',
                'latitud_frecuente' => -34.6037,
                'longitud_frecuente' => -58.3816,
            ]
        );

        // 5. Crear Producto
        $producto = Producto::firstOrCreate(
            ['nombre_producto' => 'Caja de Documentos'],
            [
                'id' => Str::uuid(),
                'empresa_id' => $empresa->id,
                'peso_kg' => 2.5,
                'stock_disponible' => 100,
            ]
        );

        // 6. Crear la Entrega (Maestro)
        $entrega = Entrega::create([
            'id' => Str::uuid(),
            'empresa_id' => $empresa->id,
            'chofer_id' => $chofer->id,
            'cliente_id' => $cliente->id,
            'estado_id' => 1, // pending
            'direccion_destino' => 'Av. Siempre Viva 742',
            'orden_ruta' => 1,
            'referencia' => 'Tocar timbre 2B',
            'fecha_asignacion' => now(),
        ]);

        // 7. Crear el Detalle (Intermedia)
        DetalleEntrega::create([
            'id' => Str::uuid(),
            'entrega_id' => $entrega->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
        ]);
    }
}
