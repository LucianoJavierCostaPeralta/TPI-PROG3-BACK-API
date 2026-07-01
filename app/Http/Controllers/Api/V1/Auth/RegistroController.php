<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterEmpresaRequest;
use App\Services\RegistroEmpresaService;
use Illuminate\Http\JsonResponse;

/** @tags Autenticacion */
class RegistroController extends Controller
{
    public function __construct(private readonly RegistroEmpresaService $registroEmpresaService) {}

    /** Registrar una empresa con su administrador. */
    public function __invoke(RegisterEmpresaRequest $request): JsonResponse
    {
        $registro = $this->registroEmpresaService->registrar($request->validated());

        return response()->json([
            'message' => 'Empresa registrada correctamente.',
            'data' => [
                'empresa' => $registro['empresa'],
                'user' => $registro['user'],
            ],
            'token' => $registro['token'],
        ], 201);
    }
}
