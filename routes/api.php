<?php

use App\Http\Controllers\Api\Admin\ChoferController;
use App\Http\Controllers\Api\Admin\EntregaController as AdminEntregaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Chofer\EntregaController as ChoferEntregaController;
use App\Http\Controllers\Api\V1\EmpresaController;
use App\Http\Controllers\Api\V1\ClienteDestinatarioController;
use App\Http\Controllers\Api\V1\ProductoController;
use App\Http\Controllers\Api\V1\EntregaController;
use App\Http\Controllers\Api\V1\DetalleEntregaController;
use App\Http\Controllers\Api\V1\EstadoEntregaController;
use App\Http\Controllers\Api\V1\ComprobanteEntregaController;
use App\Http\Controllers\Api\V1\TipoVehiculoController;
use App\Http\Controllers\Api\V1\VehiculoController;
use App\Http\Controllers\Api\V1\AsignacionVehiculoController;
use Illuminate\Support\Facades\Route;

// ==========================================
// RUTAS PÚBLICAS
// ==========================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API REST funcionando',
    ]);
});

Route::post('/login', [AuthController::class, 'login'])->name('login');

// ==========================================
// API V1
// ==========================================
Route::prefix('v1')
    ->namespace('App\Http\Controllers\Api\V1')
    ->group(function () {
        Route::apiResource('empresas', EmpresaController::class);
        Route::apiResource('clientes-destinatarios', ClienteDestinatarioController::class);
        Route::apiResource('productos', ProductoController::class);
        Route::apiResource('entregas', EntregaController::class);
        Route::apiResource('detalles-entrega', DetalleEntregaController::class);
        Route::apiResource('estados-entrega', EstadoEntregaController::class)->only(['index']);
        Route::apiResource('comprobantes-entrega', ComprobanteEntregaController::class);
        
        // Gestión de Flota
        Route::apiResource('tipos-vehiculo', TipoVehiculoController::class)->only(['index', 'store']);
        Route::apiResource('vehiculos', VehiculoController::class);
        Route::apiResource('asignaciones-vehiculos', AsignacionVehiculoController::class);
    });

// ==========================================
// RUTAS PROTEGIDAS (Sanctum)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // Perfil y Cierre de Sesión
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ----------------------------------------
    // RUTAS DEL CHOFER
    // ----------------------------------------
    Route::middleware('role:chofer')
        ->prefix('chofer')
        ->group(function () {
            Route::get('entregas', [ChoferEntregaController::class, 'index']);
            Route::get('entregas/{entrega}', [ChoferEntregaController::class, 'show']);
            Route::patch('entregas/{entrega}/accept', [ChoferEntregaController::class, 'accept']);
            Route::patch('entregas/{entrega}/state', [ChoferEntregaController::class, 'state']);
        });

    // ----------------------------------------
    // RUTAS DEL ADMINISTRADOR
    // ----------------------------------------
    Route::middleware('role:admin')
        ->prefix('admin')
        ->group(function () {
            // CRUD de Choferes
            Route::apiResource('choferes', ChoferController::class)
                ->parameters(['choferes' => 'chofer']);
            Route::patch('choferes/{chofer}/password', [ChoferController::class, 'resetPassword']);

            // Gestión de Entregas
            Route::get('entregas', [AdminEntregaController::class, 'index']);
            Route::post('entregas', [AdminEntregaController::class, 'store']);
            Route::get('entregas/{entrega}', [AdminEntregaController::class, 'show']);
            Route::patch('entregas/{entrega}/assign', [AdminEntregaController::class, 'assign']);
        });
});
