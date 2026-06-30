<?php

namespace App\Services;

use App\Models\Rol;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para Rol - Autor: Ulises
class RoleService
{
    protected Rol $rol;

    public function __construct(Rol $rol)
    {
        $this->rol = $rol;
    }

    /**
     * Crear un nuevo rol con validación.
     * Este es un catálogo fijo, por lo que debería usarse solo durante la configuración inicial.
     *
     * @param array $datos
     * @return Rol
     * @throws ValidationException
     */
    public function create(array $datos): Rol
    {
        // Validamos que el nombre del rol sea único en el sistema
        $validator = Validator::make($datos, [
            'nombre_rol' => 'required|string|max:100|unique:roles,nombre_rol',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->rol->create($datos);
    }

    /**
     * Obtener todos los roles del sistema (catálogo de lectura).
     * Este método es de solo lectura ya que los roles son catálogos fijos.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->rol->all();
    }
}
