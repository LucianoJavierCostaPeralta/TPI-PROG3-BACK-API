<?php

namespace App\Services;

use App\Models\ComprobanteEntrega;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ComprobanteEntregaService
{
    protected ComprobanteEntrega $comprobante;

    public function __construct(ComprobanteEntrega $comprobante)
    {
        $this->comprobante = $comprobante;
    }

    /**
     * Create a new comprobante entrega with validation.
     *
     * @param array $data
     * @return ComprobanteEntrega
     * @throws ValidationException
     */
    public function create(array $data): ComprobanteEntrega
    {
        $this->validateComprobanteData($data);

        return $this->comprobante->create($data);
    }

    /**
     * Get all comprobantes entrega.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->comprobante->all();
    }

    /**
     * Get a specific comprobante entrega by ID.
     *
     * @param string $id
     * @return ComprobanteEntrega
     * @throws ModelNotFoundException
     */
    public function getById(string $id): ComprobanteEntrega
    {
        return $this->comprobante->findOrFail($id);
    }

    /**
     * Update an existing comprobante entrega.
     *
     * @param array $data
     * @param string $id
     * @return ComprobanteEntrega
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function update(array $data, string $id): ComprobanteEntrega
    {
        $comprobante = $this->comprobante->findOrFail($id);
        $this->validateComprobanteData($data, $id);
        $comprobante->update($data);
        return $comprobante;
    }

    /**
     * Delete a comprobante entrega.
     *
     * @param string $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(string $id): bool
    {
        $comprobante = $this->comprobante->findOrFail($id);
        return $comprobante->delete();
    }

    /**
     * Validate comprobante entrega data.
     *
     * @param array $data
     * @param string|null $excludeId
     * @return void
     * @throws ValidationException
     */
    protected function validateComprobanteData(array $data, ?string $excludeId = null): void
    {
        $validator = Validator::make($data, [
            'entrega_id' => 'required|uuid|exists:entregas,id|unique:comprobantes_entrega,entrega_id,' . ($excludeId ?? ''),
            'url_foto' => 'required|string|max:500',
            'latitud_captura' => 'nullable|numeric|between:-90,90',
            'longitud_captura' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
