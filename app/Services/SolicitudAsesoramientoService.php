<?php

namespace App\Services;

use App\Models\SolicitudAsesoramiento;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para SolicitudAsesoramiento - Autor: Ulises
class SolicitudAsesoramientoService
{
    protected SolicitudAsesoramiento $solicitud;

    public function __construct(SolicitudAsesoramiento $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    /**
     * Crear una nueva solicitud de asesoramiento con validación.
     * Nota: el campo leido por defecto es 0 (no leído).
     *
     * @param array $datos
     * @return SolicitudAsesoramiento
     * @throws ValidationException
     */
    public function create(array $datos): SolicitudAsesoramiento
    {
        // Validamos los campos de la solicitud de asesoramiento
        $validator = Validator::make($datos, [
            'nombre_empresa' => 'required|string|max:255',
            'cuit' => 'required|string|max:20',
            'correo_corporativo' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'cantidad_vehiculos' => 'required|string|max:50',
            'leido' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Nota: el campo leido por defecto es 0 si no se proporciona
        if (!isset($datos['leido'])) {
            $datos['leido'] = false;
        }

        return $this->solicitud->create($datos);
    }

    /**
     * Obtener todas las solicitudes de asesoramiento.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->solicitud->all();
    }

    public function getById(string $id): SolicitudAsesoramiento
    {
        return $this->solicitud->findOrFail($id);
    }

    public function update(array $datos, string $id): SolicitudAsesoramiento
    {
        $solicitud = $this->solicitud->findOrFail($id);
        $validator = Validator::make($datos, [
            'nombre_empresa' => 'required|string|max:255',
            'cuit' => 'required|string|max:20',
            'correo_corporativo' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'cantidad_vehiculos' => 'required|string|max:50',
            'leido' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        if (!isset($datos['leido'])) {
            $datos['leido'] = false;
        }
        $solicitud->update($datos);
        return $solicitud;
    }

    public function delete(string $id): bool
    {
        $solicitud = $this->solicitud->findOrFail($id);
        return $solicitud->delete();
    }
}
