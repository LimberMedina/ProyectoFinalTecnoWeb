<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_proveedor' => 'required|integer|exists:proveedor,id',
            'num_compra' => 'nullable|string|max:50',
            'fecha_compra' => 'required|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto_variante' => 'required|integer|exists:producto_variante,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio' => 'required|numeric|min:0',
            'detalles.*.descuento' => 'nullable|numeric|min:0',
            'detalles.*.subtotal' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'id_proveedor.required' => 'Debe seleccionar un proveedor.',
            'id_proveedor.exists' => 'El proveedor seleccionado no existe.',
            'fecha_compra.required' => 'La fecha de compra es obligatoria.',
            'detalles.required' => 'Debe agregar al menos un producto.',
            'detalles.min' => 'Debe agregar al menos un producto.',
            'detalles.*.id_producto_variante.required' => 'Debe seleccionar una variante en cada detalle.',
            'detalles.*.id_producto_variante.exists' => 'Una de las variantes seleccionadas no existe.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria en cada detalle.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'detalles.*.precio.required' => 'El precio es obligatorio en cada detalle.',
            'detalles.*.precio.min' => 'El precio no puede ser negativo.',
            'detalles.*.descuento.min' => 'El descuento no puede ser negativo.',
            'detalles.*.subtotal.min' => 'El subtotal no puede ser negativo.',
        ];
    }
}
