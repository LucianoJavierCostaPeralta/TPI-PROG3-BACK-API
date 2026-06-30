<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistroEmpresaService
{
    /**
     * @return array{empresa: Empresa, user: User, token: string}
     */
    public function registrar(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $empresa = Empresa::create([
                'razon_social' => $data['razon_social'],
                'cuit' => $data['cuit'],
                'email_contacto' => $data['email'],
                'telefono' => $data['telefono'],
                'tamano_flota' => $data['tamano_flota'],
                'terminos_aceptados_en' => now(),
            ]);

            $user = User::create([
                'empresa_id' => $empresa->id,
                'rol_id' => User::ROL_ADMIN,
                'nombre_completo' => $data['nombre_completo'] ?? 'Administrador',
                'email' => $data['email'],
                'password' => $data['password'],
                'telefono' => $data['telefono'],
                'activo' => true,
            ]);

            $user->load('rol:id,nombre_rol');

            return [
                'empresa' => $empresa,
                'user' => $user,
                'token' => $user->createToken('api-token')->plainTextToken,
            ];
        });
    }
}
