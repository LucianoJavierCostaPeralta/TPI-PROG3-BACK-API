<?php

namespace App\Services;

use App\Models\SolicitudAsesoramiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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
        if (! isset($datos['leido'])) {
            $datos['leido'] = false;
        }

        return $this->solicitud->create($datos);
    }

    public function createForUser(User $user, string $mensaje): SolicitudAsesoramiento
    {
        $user->loadMissing('empresa');
        $empresa = $user->empresa;

        return $this->solicitud->create([
            'usuario_id' => $user->id,
            'datos_usuario' => [
                'id' => $user->id,
                'nombre_completo' => $user->nombre_completo,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'empresa' => $empresa ? [
                    'id' => $empresa->id,
                    'razon_social' => $empresa->razon_social,
                    'cuit' => $empresa->cuit,
                ] : null,
            ],
            'mensaje' => $mensaje,
            'nombre_empresa' => $empresa?->razon_social,
            'cuit' => $empresa?->cuit,
            'correo_corporativo' => $user->email,
            'telefono' => $user->telefono,
            'cantidad_vehiculos' => $empresa?->tamano_flota,
            'leido' => false,
        ]);
    }

    /**
     * Obtener todas las solicitudes de asesoramiento.
     */
    public function getAll(): Collection
    {
        return $this->solicitud
            ->newQuery()
            ->with('usuario')
            ->latest()
            ->get();
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
        if (! isset($datos['leido'])) {
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
