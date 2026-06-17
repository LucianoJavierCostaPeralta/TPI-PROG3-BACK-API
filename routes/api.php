<?php

use App\Http\Controllers\Api\Admin\ChoferController;
use App\Http\Controllers\Api\Admin\EnvioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Chofer\EnvioController as ChoferEnvioController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API REST funcionando',
    ]);
});

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:chofer')
        ->prefix('chofer')
        ->group(function () {
            Route::get('envios', [ChoferEnvioController::class, 'index']);
            Route::patch('envios/{envio}/accept', [ChoferEnvioController::class, 'accept']);
            Route::patch('envios/{envio}/state', [ChoferEnvioController::class, 'state']);
        });

    Route::middleware('role:admin')
        ->prefix('admin')
        ->group(function () {
            Route::apiResource('choferes', ChoferController::class)
                ->parameters(['choferes' => 'chofer']);
            Route::patch('choferes/{chofer}/password', [ChoferController::class, 'resetPassword']);

            Route::get('envios', [EnvioController::class, 'index']);
            Route::post('envios', [EnvioController::class, 'store']);
            Route::patch('envios/{envio}/assign', [EnvioController::class, 'assign']);
        });
});
