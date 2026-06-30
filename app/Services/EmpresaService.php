<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EmpresaService
{
    protected Empresa $empresa;

    public function __construct(Empresa $empresa)
    {
        $this->empresa = $empresa;
    }

    /**
     * Create a new empresa with validation.
     *
     * @param array $data
     * @return Empresa
     * @throws ValidationException
     */
    public function create(array $data): Empresa
    {
        $this->validateEmpresaData($data);

        return $this->empresa->create($data);
    }

    /**
     * Get all empresas.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->empresa->all();
    }

    /**
     * Get a specific empresa by ID.
     *
     * @param string $id
     * @return Empresa
     * @throws ModelNotFoundException
     */
    public function getById(string $id): Empresa
    {
        return $this->empresa->findOrFail($id);
    }

    /**
     * Update an existing empresa.
     *
     * @param array $data
     * @param string $id
     * @return Empresa
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function update(array $data, string $id): Empresa
    {
        $empresa = $this->empresa->findOrFail($id);
        $this->validateEmpresaData($data, $id);
        $empresa->update($data);
        return $empresa;
    }

    /**
     * Delete an empresa.
     *
     * @param string $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(string $id): bool
    {
        $empresa = $this->empresa->findOrFail($id);
        return $empresa->delete();
    }

    /**
     * Validate empresa data.
     *
     * @param array $data
     * @param string|null $excludeId
     * @return void
     * @throws ValidationException
     */
    protected function validateEmpresaData(array $data, ?string $excludeId = null): void
    {
        $validator = Validator::make($data, [
            'razon_social' => 'required|string|max:255',
            'cuit' => 'required|string|max:20|unique:empresas,cuit,' . ($excludeId ?? ''),
            'email_contacto' => 'required|email|max:255',
            'telefono' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
