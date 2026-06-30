<?php

use App\Http\Controllers\Api\V1\Admin\ChoferController;
use App\Http\Controllers\Api\V1\Admin\EntregaController as AdminEntregaController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Chofer\EntregaController as ChoferEntregaController;
use App\Http\Controllers\Api\V1\Empresas\EmpresaController;
use App\Http\Controllers\Api\V1\Logistica\ClienteDestinatarioController;
use App\Http\Controllers\Api\V1\Logistica\ProductoController;
use App\Http\Controllers\Api\V1\Logistica\EntregaController;
use App\Http\Controllers\Api\V1\Logistica\DetalleEntregaController;
use App\Http\Controllers\Api\V1\Logistica\EstadoEntregaController;
use App\Http\Controllers\Api\V1\Logistica\ComprobanteEntregaController;
use App\Http\Controllers\Api\V1\Flota\TipoVehiculoController;
use App\Http\Controllers\Api\V1\Flota\VehiculoController;
use App\Http\Controllers\Api\V1\Flota\AsignacionVehiculoController;
use App\Http\Controllers\Api\V1\Flota\ZonaCoberturaController;
use App\Http\Controllers\Api\V1\Flota\ChoferZonaController;
use App\Http\Controllers\Api\V1\Flota\JornadaTrabajoController;
use App\Http\Controllers\Api\V1\Logistica\MotivoRechazoController;
use App\Http\Controllers\Api\V1\Empresas\RoleController;
use App\Http\Controllers\Api\V1\Empresas\UserController;
use App\Http\Controllers\Api\V1\Soporte\NotificacionController;
use App\Http\Controllers\Api\V1\Soporte\AuditoriaLogController;
use App\Http\Controllers\Api\V1\Soporte\SolicitudAsesoramientoController;
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

        // Logística y Operaciones
        Route::apiResource('zonas-cobertura', ZonaCoberturaController::class);
        Route::apiResource('chofer-zonas', ChoferZonaController::class);
        Route::apiResource('jornadas-trabajo', JornadaTrabajoController::class);
        Route::apiResource('motivos-rechazo', MotivoRechazoController::class)->only(['index', 'store']);

        // Soporte y Sistema
        Route::apiResource('roles', RoleController::class)->only(['index', 'store']);
        Route::apiResource('users', UserController::class);
        Route::apiResource('notificaciones', NotificacionController::class);
        Route::apiResource('auditoria-logs', AuditoriaLogController::class);
        Route::apiResource('solicitudes-asesoramiento', SolicitudAsesoramientoController::class);
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
