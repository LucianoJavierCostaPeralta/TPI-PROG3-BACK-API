<?php

namespace App\Services;

use App\Models\ComprobanteEntrega;
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
     * Validate comprobante entrega data.
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validateComprobanteData(array $data): void
    {
        $validator = Validator::make($data, [
            'entrega_id' => 'required|uuid|exists:entregas,id|unique:comprobantes_entrega,entrega_id',
            'url_foto' => 'required|string|max:500',
            'latitud_captura' => 'nullable|numeric|between:-90,90',
            'longitud_captura' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
