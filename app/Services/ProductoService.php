<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * Get a specific producto by ID.
     *
     * @param string $id
     * @return Producto
     * @throws ModelNotFoundException
     */
    public function getById(string $id): Producto
    {
        return $this->producto->findOrFail($id);
    }

    /**
     * Update an existing producto.
     *
     * @param array $data
     * @param string $id
     * @return Producto
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function update(array $data, string $id): Producto
    {
        $producto = $this->producto->findOrFail($id);
        $this->validateProductoData($data);
        $producto->update($data);
        return $producto;
    }

    /**
     * Delete a producto.
     *
     * @param string $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(string $id): bool
    {
        $producto = $this->producto->findOrFail($id);
        return $producto->delete();
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
