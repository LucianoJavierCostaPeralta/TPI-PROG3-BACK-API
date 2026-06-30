<?php

namespace App\Services;

use App\Models\ZonaCobertura;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        return $this->zona->with('empresa')->get();
    }

    public function getById(string $id): ZonaCobertura
    {
        return $this->zona->with('empresa')->findOrFail($id);
    }

    public function update(array $input, string $id): ZonaCobertura
    {
        $zona = $this->zona->findOrFail($id);
        $validator = Validator::make($input, [
            'empresa_id' => 'required|uuid|exists:empresas,id',
            'nombre_zona' => 'required|string|max:255',
            'codigo_postal' => 'required|string|max:20',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $zona->update($input);
        return $zona;
    }

    public function delete(string $id): bool
    {
        $zona = $this->zona->findOrFail($id);
        return $zona->delete();
    }
}
