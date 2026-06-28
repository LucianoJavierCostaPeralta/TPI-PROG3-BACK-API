<?php

namespace App\Services;

use App\Models\Empresa;
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
     * Validate empresa data.
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validateEmpresaData(array $data): void
    {
        $validator = Validator::make($data, [
            'razon_social' => 'required|string|max:255',
            'cuit' => 'required|string|max:20|unique:empresas,cuit',
            'email_contacto' => 'required|email|max:255',
            'telefono' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
