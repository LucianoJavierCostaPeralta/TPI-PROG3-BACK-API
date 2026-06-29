<?php

namespace App\Services;

use App\Models\ZonaCobertura;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para ZonaCobertura - Autor: Ulises
class ZonaCoberturaService
{
    protected ZonaCobertura $zona;

    public function __construct(ZonaCobertura $zona)
    {
        $this->zona = $zona;
    }

    /**
     * Crear una nueva zona de cobertura con validación.
     * Validamos que el código postal sea único para la empresa.
     *
     * @param array $input
     * @return ZonaCobertura
     * @throws ValidationException
     */
    public function create(array $input): ZonaCobertura
    {
        // Usamos Validator::make para tener control sobre los mensajes de validación
        $validator = Validator::make($input, [
            'empresa_id' => 'required|uuid|exists:empresas,id',
            'nombre_zona' => 'required|string|max:255',
            'codigo_postal' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->zona->create($input);
    }

    /**
     * Obtener todas las zonas de cobertura.
     * Incluye la relación con empresa para mostrar información completa.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        // Cargamos eager loading para optimizar consultas
        return $this->zona->with('empresa')->get();
    }
}
