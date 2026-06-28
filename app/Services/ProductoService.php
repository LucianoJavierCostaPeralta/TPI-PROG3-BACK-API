<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductoService
{
    protected Producto $producto;

    public function __construct(Producto $producto)
    {
        $this->producto = $producto;
    }

    /**
     * Create a new producto with validation.
     *
     * @param array $data
     * @return Producto
     * @throws ValidationException
     */
    public function create(array $data): Producto
    {
        $this->validateProductoData($data);

        return $this->producto->create($data);
    }

    /**
     * Get all productos.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->producto->all();
    }

    /**
     * Validate producto data.
     *
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function validateProductoData(array $data): void
    {
        $validator = Validator::make($data, [
            'empresa_id' => 'required|uuid|exists:empresas,id',
            'nombre_producto' => 'required|string|max:255',
            'peso_kg' => 'required|numeric|min:0|max:999999.99',
            'stock_disponible' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
