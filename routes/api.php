<?php

use App\Http\Controllers\Api\Admin\ChoferController;
use App\Http\Controllers\Api\Admin\EnvioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Chofer\EnvioController as ChoferEnvioController;
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
            Route::get('envios', [ChoferEnvioController::class, 'index']);
            Route::get('envios/{envio}', [ChoferEnvioController::class, 'show']); // Agregado: Mejora recomendada
            Route::patch('envios/{envio}/accept', [ChoferEnvioController::class, 'accept']);
            Route::patch('envios/{envio}/state', [ChoferEnvioController::class, 'state']);
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

            // Gestión de Envíos
            Route::get('envios', [EnvioController::class, 'index']);
            Route::post('envios', [EnvioController::class, 'store']);
            Route::get('envios/{envio}', [EnvioController::class, 'show']); // Agregado: Mejora recomendada
            Route::patch('envios/{envio}/assign', [EnvioController::class, 'assign']);
        });
});