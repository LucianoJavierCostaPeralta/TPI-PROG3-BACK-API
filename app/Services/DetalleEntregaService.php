<?php

namespace App\Services;

use App\Models\DetalleEntrega;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DetalleEntregaService
{
    protected DetalleEntrega $detalle;

    public function __construct(DetalleEntrega $detalle)
    {
        $this->detalle = $detalle;
    }

    /**
     * Create a new detalle entrega with validation.
     *
     * @param array $data
     * @return DetalleEntrega
     * @throws ValidationException
     */
    public function create(array $data): DetalleEntrega
    {
        $this->validateDetalleData($data);

        return $this->detalle->create($data);
    }

    /**
     * Get all detalles entrega.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->detalle->all();
    }

    /**
     * Get a specific detalle entrega by ID.
     *
     * @param string $id
     * @return DetalleEntrega
     * @throws ModelNotFoundException
     */
    public function getById(string $id): DetalleEntrega
    {
        return $this->detalle->findOrFail($id);
    }

    /**
     * Update an existing detalle entrega.
     *
     * @param array $data
     * @param string $id
     * @return DetalleEntrega
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function update(array $data, string $id): DetalleEntrega
    {
        $detalle = $this->detalle->findOrFail($id);
        $this->validateDetalleData($data);
        $detalle->update($data);
        return $detalle;
    }

    /**
     * Delete a detalle entrega.
     *
     * @param string $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(string $id): bool
    {
        $detalle = $this->detalle->findOrFail($id);
        return $detalle->delete();
    }

    /**
     * Validate detalle entrega data.
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validateDetalleData(array $data): void
    {
        $validator = Validator::make($data, [
            'entrega_id' => 'required|uuid|exists:entregas,id',
            'producto_id' => 'required|uuid|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
