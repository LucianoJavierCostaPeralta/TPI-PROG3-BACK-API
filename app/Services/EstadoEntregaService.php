<?php

namespace App\Services;

use App\Models\EstadoEntrega;

class EstadoEntregaService
{
    protected EstadoEntrega $estado;

    public function __construct(EstadoEntrega $estado)
    {
        $this->estado = $estado;
    }

    /**
     * Get all estados entrega (read-only catalog).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->estado->all();
    }
}
