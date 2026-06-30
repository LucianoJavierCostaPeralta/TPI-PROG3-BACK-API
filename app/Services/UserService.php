<?php

namespace App\Services;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para User - Autor: Ulises
class UserService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Crear un nuevo usuario con validación completa.
     * Validamos que el email sea único y que el rol_id exista antes de crear el usuario.
     * Validamos que el password sea encriptado antes de persistir.
     *
     * @param array $input
     * @return User
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        // Validamos que el email sea único en el sistema
        $validator = Validator::make($input, [
            'empresa_id' => 'nullable|uuid|exists:empresas,id',
            'rol_id' => 'required|integer|exists:roles,id',
            'nombre_completo' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Verificamos que el rol exista antes de crear el usuario
        $rol = Rol::find($input['rol_id']);
        if (!$rol) {
            throw ValidationException::withMessages([
                'rol_id' => ['El rol especificado no existe en el sistema.']
            ]);
        }

        // Validamos que el password sea encriptado antes de persistir
        if (isset($input['password'])) {
            $input['password'] = bcrypt($input['password']);
        }

        // Nota: el campo activo por defecto es true si no se proporciona
        if (!isset($input['activo'])) {
            $input['activo'] = true;
        }

        return $this->user->create($input);
    }

    /**
     * Obtener todos los usuarios del sistema.
     * Incluye las relaciones con empresa y rol para mostrar información completa.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        // Cargamos eager loading para optimizar consultas
        return $this->user->with(['empresa', 'rol'])->get();
    }
}
