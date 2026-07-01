<?php

namespace App\Services;

use App\Models\Entrega;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EntregaService
{
    protected Entrega $entrega;

    public function __construct(Entrega $entrega)
    {
        $this->entrega = $entrega;
    }

    /**
     * Create a new entrega with validation.
     *
     * @throws ValidationException
     */
    public function create(array $data): Entrega
    {
        $this->validateEntregaData($data);

        return $this->entrega->create($data);
    }

    /**
     * Get all entregas.
     */
    public function getAll(): Collection
    {
        return $this->entrega->all();
    }

    /**
     * Get a specific entrega by ID.
     *
     * @throws ModelNotFoundException
     */
    public function getById(string $id): Entrega
    {
        return $this->entrega->findOrFail($id);
    }

    /**
     * Update an existing entrega.
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function update(array $data, string $id): Entrega
    {
        $entrega = $this->entrega->findOrFail($id);
        $this->validateEntregaData($data);
        $entrega->update($data);

        return $entrega;
    }

    /**
     * Delete an entrega.
     *
     * @throws ModelNotFoundException
     */
    public function delete(string $id): bool
    {
        $entrega = $this->entrega->findOrFail($id);

        return $entrega->delete();
    }

    /**
     * Validate entrega data.
     *
     * @throws ValidationException
     */
    protected function validateEntregaData(array $data): void
    {
        $validator = Validator::make($data, [
            'empresa_id' => 'required|uuid|exists:empresas,id',
            'chofer_id' => 'nullable|uuid|exists:users,id',
            'cliente_id' => 'required|uuid|exists:clientes_destinatarios,id',
            'estado_id' => 'required|integer|exists:estados_entrega,id',
            'direccion_destino' => 'required|string|max:500',
            'orden_ruta' => 'nullable|integer|min:1',
            'referencia' => 'nullable|string|max:255',
            'fecha_asignacion' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
