<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\EmpresaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmpresaController extends Controller
{
    protected EmpresaService $empresaService;

    public function __construct(EmpresaService $empresaService)
    {
        $this->empresaService = $empresaService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $empresas = $this->empresaService->getAll();

        return response()->json([
            'status' => 'success',
            'message' => 'Empresas retrieved successfully',
            'data' => $empresas
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $empresa = $this->empresaService->create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Empresa created successfully',
                'data' => $empresa
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $empresa = $this->empresaService->getById($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Empresa retrieved successfully',
                'data' => $empresa
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Empresa not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $empresa = $this->empresaService->update($request->all(), $id);

            return response()->json([
                'status' => 'success',
                'message' => 'Empresa updated successfully',
                'data' => $empresa
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Empresa not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->empresaService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Empresa deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Empresa not found'
            ], 404);
        }
    }
}
