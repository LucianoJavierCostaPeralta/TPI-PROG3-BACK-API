<?php

namespace App\Services;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $validator = Validator::make($input, [
            'empresa_id' => 'nullable|uuid|exists:empresas,id',
            'rol_id' => 'required|integer|exists:roles,id',
            'nombre_completo' => 'required|string|max:255',
            'dni' => 'nullable|string|regex:/^\d{8}$/|unique:users,dni',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if ((int) $input['rol_id'] === User::ROL_CHOFER) {
            $missing = [];

            if (empty($input['dni'])) {
                $missing['dni'] = ['El DNI es obligatorio para choferes.'];
            }

            if (empty($input['fecha_nacimiento'])) {
                $missing['fecha_nacimiento'] = ['La fecha de nacimiento es obligatoria para choferes.'];
            }

            if ($missing !== []) {
                throw ValidationException::withMessages($missing);
            }
        }

        $rol = Rol::find($input['rol_id']);
        if (! $rol) {
            throw ValidationException::withMessages([
                'rol_id' => ['El rol especificado no existe en el sistema.']
            ]);
        }

        if (isset($input['password'])) {
            $input['password'] = bcrypt($input['password']);
        }

        if (! isset($input['activo'])) {
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
        return $this->user->with(['empresa', 'rol'])->get();
    }

    public function getById(string $id): User
    {
        return $this->user->with(['empresa', 'rol'])->findOrFail($id);
    }

    public function update(array $input, string $id): User
    {
        $user = $this->user->findOrFail($id);
        $validator = Validator::make($input, [
            'empresa_id' => 'nullable|uuid|exists:empresas,id',
            'rol_id' => 'required|integer|exists:roles,id',
            'nombre_completo' => 'required|string|max:255',
            'dni' => 'nullable|string|regex:/^\d{8}$/|unique:users,dni,' . $id,
            'fecha_nacimiento' => 'nullable|date|before:today',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        if (isset($input['password'])) {
            $input['password'] = bcrypt($input['password']);
        }
        $user->update($input);
        return $user;
    }

    public function delete(string $id): bool
    {
        $user = $this->user->findOrFail($id);
        return $user->delete();
    }
}
