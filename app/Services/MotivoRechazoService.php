<?php

namespace App\Services;

use App\Models\MotivoRechazo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para MotivoRechazo - Autor: Ulises
class MotivoRechazoService
{
    protected MotivoRechazo $motivo;

    public function __construct(MotivoRechazo $motivo)
    {
        $this->motivo = $motivo;
    }

    /**
     * Crear un nuevo motivo de rechazo con validación.
     * Este es un catálogo fijo, por lo que debería usarse solo durante la configuración inicial.
     *
     * @param array $payload
     * @return MotivoRechazo
     * @throws ValidationException
     */
    public function create(array $payload): MotivoRechazo
    {
        // Validamos que la descripción sea única en el catálogo
        $validator = Validator::make($payload, [
            'descripcion' => 'required|string|max:255|unique:motivos_rechazo,descripcion',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->motivo->create($payload);
    }

    /**
     * Obtener todos los motivos de rechazo (catálogo de lectura).
     * Este método es de solo lectura ya que los motivos son catálogos fijos.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->motivo->all();
    }
}
