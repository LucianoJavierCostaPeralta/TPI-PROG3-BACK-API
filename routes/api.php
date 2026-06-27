<?php

use App\Http\Controllers\Api\Admin\ChoferController;
use App\Http\Controllers\Api\Admin\EntregaController as AdminEntregaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Chofer\EntregaController as ChoferEntregaController;
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
