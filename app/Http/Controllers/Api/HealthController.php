<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/** @tags Sistema */
class HealthController extends Controller
{
    /** Verificar el estado de la API. */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'API REST funcionando',
        ]);
    }
}
