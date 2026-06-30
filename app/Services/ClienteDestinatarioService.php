<?php

namespace App\Services;

use App\Models\ClienteDestinatario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClienteDestinatarioService
{
    protected ClienteDestinatario $cliente;

    public function __construct(ClienteDestinatario $cliente)
    {
        $this->cliente = $cliente;
    }

    /**
     * Create a new cliente destinatario with validation.
     *
     * @param array $data
     * @return ClienteDestinatario
     * @throws ValidationException
     */
    public function create(array $data): ClienteDestinatario
    {
        $this->validateClienteData($data);

        return $this->cliente->create($data);
    }

    /**
     * Get all clientes destinatarios.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->cliente->all();
    }

    /**
     * Get a specific cliente destinatario by ID.
     *
     * @param string $id
     * @return ClienteDestinatario
     * @throws ModelNotFoundException
     */
    public function getById(string $id): ClienteDestinatario
    {
        return $this->cliente->findOrFail($id);
    }

    /**
     * Update an existing cliente destinatario.
     *
     * @param array $data
     * @param string $id
     * @return ClienteDestinatario
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function update(array $data, string $id): ClienteDestinatario
    {
        $cliente = $this->cliente->findOrFail($id);
        $this->validateClienteData($data);
        $cliente->update($data);
        return $cliente;
    }

    /**
     * Delete a cliente destinatario.
     *
     * @param string $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(string $id): bool
    {
        $cliente = $this->cliente->findOrFail($id);
        return $cliente->delete();
    }

    /**
     * Validate cliente destinatario data.
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validateClienteData(array $data): void
    {
        $validator = Validator::make($data, [
            'empresa_id' => 'required|uuid|exists:empresas,id',
            'nombre_completo' => 'required|string|max:255',
            'telefono' => 'required|string|max:50',
            'direccion_frecuente' => 'required|string|max:500',
            'latitud_frecuente' => 'nullable|numeric|between:-90,90',
            'longitud_frecuente' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
