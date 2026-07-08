<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// Servicio encargado de la lógica de negocio para Notificacion - Autor: Ulises
class NotificacionService
{
    public function __construct(protected Notificacion $notificacion)
    {
    }

    /**
     * Crear una nueva notificación con validación.
     * Nota: el campo leida por defecto es 0 (no leída).
     *
     * @param array<string, mixed> $payload
     * @throws ValidationException
     */
    public function create(array $payload): Notificacion
    {
        $validator = Validator::make($payload, [
            'usuario_id' => 'required|uuid|exists:users,id',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'sometimes|in:info,success,warning,error',
            'leida' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $payload['tipo'] = $payload['tipo'] ?? 'info';
        $payload['leida'] = $payload['leida'] ?? false;

        return $this->notificacion->create($payload);
    }

    public function createForUser(User $user, string $titulo, string $mensaje, string $tipo = 'info', bool $leida = false): Notificacion
    {
        return $this->notificacion->create([
            'usuario_id' => $user->id,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
            'leida' => $leida,
        ]);
    }

    /**
     * Crear una notificación para múltiples usuarios.
     *
     * @param iterable<User> $users
     * @return SupportCollection<int, Notificacion>
     */
    public function createForUsers(iterable $users, string $titulo, string $mensaje, string $tipo = 'info'): SupportCollection
    {
        $created = collect();

        foreach ($users as $user) {
            if ($user instanceof User) {
                $created->push($this->createForUser($user, $titulo, $mensaje, $tipo));
            }
        }

        return $created;
    }

    public function createForCompanyAdmins(User $actor, string $titulo, string $mensaje, string $tipo = 'info'): SupportCollection
    {
        $admins = User::query()
            ->where('empresa_id', $actor->empresa_id)
            ->where('rol_id', User::ROL_ADMIN)
            ->get();

        return $this->createForUsers($admins, $titulo, $mensaje, $tipo);
    }

    /**
     * Obtener las notificaciones del usuario autenticado.
     */
    public function getForUser(User $user): Collection
    {
        return $this->notificacion->newQuery()
            ->where('usuario_id', $user->id)
            ->latest()
            ->get();
    }

    public function getByIdForUser(User $user, string $id): Notificacion
    {
        return $this->notificacion->newQuery()
            ->where('usuario_id', $user->id)
            ->findOrFail($id);
    }

    public function markAsRead(User $user, string $id): Notificacion
    {
        $notificacion = $this->notificacion->newQuery()
            ->where('usuario_id', $user->id)
            ->findOrFail($id);

        $notificacion->update(['leida' => true]);

        return $notificacion;
    }

    public function markAllAsRead(User $user): int
    {
        return $this->notificacion->newQuery()
            ->where('usuario_id', $user->id)
            ->where('leida', false)
            ->update(['leida' => true]);
    }

    /**
     * Obtener todas las notificaciones del sistema.
     * Incluye la relación con usuario para mostrar información completa.
     */
    public function getAll(): Collection
    {
        return $this->notificacion->with('usuario')->latest()->get();
    }

    public function getById(string $id): Notificacion
    {
        return $this->notificacion->with('usuario')->findOrFail($id);
    }

    public function update(array $payload, string $id): Notificacion
    {
        $notificacion = $this->notificacion->findOrFail($id);
        $validator = Validator::make($payload, [
            'usuario_id' => 'required|uuid|exists:users,id',
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'sometimes|in:info,success,warning,error',
            'leida' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $payload['tipo'] = $payload['tipo'] ?? $notificacion->tipo ?? 'info';
        $payload['leida'] = $payload['leida'] ?? false;
        $notificacion->update($payload);
        return $notificacion;
    }

    public function delete(string $id): bool
    {
        $notificacion = $this->notificacion->findOrFail($id);
        return $notificacion->delete();
    }
}
