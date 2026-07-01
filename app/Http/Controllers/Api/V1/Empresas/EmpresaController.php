<?php

namespace App\Http\Controllers\Api\V1\Empresas;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Empresa retrieved successfully',
            'data' => [$request->user()->empresa],
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Empresa retrieved successfully',
            'data' => $this->empresa($request, $id),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $empresa = $this->empresa($request, $id);
        $data = $request->validate([
            'razon_social' => ['sometimes', 'required', 'string', 'max:255'],
            'cuit' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('empresas', 'cuit')->ignore($empresa->id)],
            'email_contacto' => ['sometimes', 'required', 'email', 'max:255'],
            'telefono' => ['sometimes', 'required', 'string', 'max:50'],
            'tamano_flota' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        $empresa->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Empresa updated successfully',
            'data' => $empresa,
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->empresa($request, $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Empresa deleted successfully',
        ]);
    }

    private function empresa(Request $request, string $id): Empresa
    {
        return Empresa::query()
            ->whereKey($id)
            ->whereKey($request->user()->empresa_id)
            ->firstOrFail();
    }
}
