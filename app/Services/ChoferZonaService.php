<?php

namespace App\Services;

use App\Models\User;
use App\Models\ZonaCobertura;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para ChoferZona - Autor: Ulises
class ChoferZonaService
{
    /**
     * Crear una relación chofer-zona con validación de IDs.
     * Validamos existencia de IDs antes de crear la relación chofer-zona.
     *
     * @param array $parametros
     * @return bool
     * @throws ValidationException
     */
    public function create(array $parametros): bool
    {
        // Validamos que ambos IDs estén presentes
        $validator = Validator::make($parametros, [
            'usuario_id' => 'required|uuid',
            'zona_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Verificamos que el usuario exista antes de intentar la inserción
        $usuario = User::find($parametros['usuario_id']);
        if (!$usuario) {
            throw ValidationException::withMessages([
                'usuario_id' => ['El usuario especificado no existe en el sistema.']
            ]);
        }

        // Verificamos que la zona exista antes de intentar la inserción
        $zona = ZonaCobertura::find($parametros['zona_id']);
        if (!$zona) {
            throw ValidationException::withMessages([
                'zona_id' => ['La zona de cobertura especificada no existe en el sistema.']
            ]);
        }

        // Insertamos directamente en la tabla pivot usando DB::table
        // Esto evita errores de SQL y nos permite controlar la respuesta
        try {
            DB::table('chofer_zonas')->insert([
                'usuario_id' => $parametros['usuario_id'],
                'zona_id' => $parametros['zona_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return true;
        } catch (\Exception $e) {
            // Si la relación ya existe, retornamos un error claro
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                throw ValidationException::withMessages([
                    'relation' => ['Esta relación chofer-zona ya existe en el sistema.']
                ]);
            }
            throw $e;
        }
    }

    /**
     * Obtener todas las relaciones chofer-zona.
     * Incluye información del chofer y la zona para mostrar datos completos.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAll(): \Illuminate\Support\Collection
    {
        return DB::table('chofer_zonas')
            ->join('users', 'chofer_zonas.usuario_id', '=', 'users.id')
            ->join('zonas_cobertura', 'chofer_zonas.zona_id', '=', 'zonas_cobertura.id')
            ->select(
                'chofer_zonas.usuario_id',
                'chofer_zonas.zona_id',
                'users.name as chofer_nombre',
                'users.email as chofer_email',
                'zonas_cobertura.nombre_zona',
                'zonas_cobertura.codigo_postal'
            )
            ->get();
    }

    public function getById(string $usuarioId, string $zonaId): \stdClass
    {
        $relacion = DB::table('chofer_zonas')
            ->join('users', 'chofer_zonas.usuario_id', '=', 'users.id')
            ->join('zonas_cobertura', 'chofer_zonas.zona_id', '=', 'zonas_cobertura.id')
            ->select(
                'chofer_zonas.usuario_id',
                'chofer_zonas.zona_id',
                'users.name as chofer_nombre',
                'users.email as chofer_email',
                'zonas_cobertura.nombre_zona',
                'zonas_cobertura.codigo_postal'
            )
            ->where('chofer_zonas.usuario_id', $usuarioId)
            ->where('chofer_zonas.zona_id', $zonaId)
            ->first();

        if (!$relacion) {
            throw new ModelNotFoundException();
        }
        return $relacion;
    }

    public function delete(string $usuarioId, string $zonaId): bool
    {
        $deleted = DB::table('chofer_zonas')
            ->where('usuario_id', $usuarioId)
            ->where('zona_id', $zonaId)
            ->delete();

        if ($deleted === 0) {
            throw new ModelNotFoundException();
        }
        return true;
    }
}
