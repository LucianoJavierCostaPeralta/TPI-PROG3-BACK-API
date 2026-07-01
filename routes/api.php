<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\V1\Admin\ChoferController;
use App\Http\Controllers\Api\V1\Admin\EntregaController as AdminEntregaController;
use App\Http\Controllers\Api\V1\Admin\ResumenController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\RegistroController;
use App\Http\Controllers\Api\V1\Chofer\EntregaController as ChoferEntregaController;
use App\Http\Controllers\Api\V1\Empresas\EmpresaController;
use App\Http\Controllers\Api\V1\Empresas\UserController;
use App\Http\Controllers\Api\V1\Logistica\EstadoEntregaController;
use Illuminate\Support\Facades\Route;

// ==========================================
// RUTAS PÚBLICAS
// ==========================================
Route::get('/health', HealthController::class);

Route::post('v1/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login');
Route::post('v1/registro', RegistroController::class)
    ->middleware('throttle:5,1')
    ->name('registro');
Route::post('v1/recuperar-password', [AuthController::class, 'recoverPassword'])
    ->middleware('throttle:5,1')
    ->name('password.recover');

// ==========================================
// RUTAS PROTEGIDAS (Sanctum)
// ==========================================
Route::prefix('v1')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::patch('profile/password', [AuthController::class, 'updatePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('estados-entrega', [EstadoEntregaController::class, 'index']);

        Route::middleware('role:chofer')
            ->prefix('chofer')
            ->group(function () {
                Route::get('entregas', [ChoferEntregaController::class, 'index']);
                Route::get('entregas/{entrega}', [ChoferEntregaController::class, 'show']);
                Route::patch('entregas/{entrega}/accept', [ChoferEntregaController::class, 'accept']);
                Route::patch('entregas/{entrega}/state', [ChoferEntregaController::class, 'state']);
            });

        Route::middleware('role:admin')
            ->group(function () {
                Route::apiResource('empresas', EmpresaController::class)
                    ->except(['store']);
                Route::apiResource('users', UserController::class);

                Route::prefix('admin')->group(function () {
                    Route::get('resumen', [ResumenController::class, 'index']);
                    Route::apiResource('choferes', ChoferController::class)
                        ->parameters(['choferes' => 'chofer']);
                    Route::patch('choferes/{chofer}/password', [ChoferController::class, 'resetPassword']);

                    Route::get('entregas', [AdminEntregaController::class, 'index']);
                    Route::post('entregas', [AdminEntregaController::class, 'store']);
                    Route::get('entregas/{entrega}', [AdminEntregaController::class, 'show']);
                    Route::patch('entregas/{entrega}/assign', [AdminEntregaController::class, 'assign']);
                });
            });
    });
