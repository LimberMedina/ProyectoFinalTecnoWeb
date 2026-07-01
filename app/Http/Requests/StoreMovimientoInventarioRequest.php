<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovimientoInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_producto_variante' => 'required|integer|exists:producto_variante,id',
            'id_tecnica_inventario' => 'required|integer|exists:tecnica_inventario,id',
            'id_tecnica_costo' => 'required|integer|exists:tecnica_costo,id',
            'tipo_movimiento' => 'required|in:ingreso,salida',
            'motivo' => 'nullable|string|max:1000',
            'cantidad' => 'required|integer|min:1',
            'costo_unitario' => 'required|numeric|min:0',
            'fecha' => 'required|date',
            'observacion' => 'nullable|string|max:1000',
        ];
    }
}
