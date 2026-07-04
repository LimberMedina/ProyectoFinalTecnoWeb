<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCarritoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto_variante_id' => 'required_without:items|nullable|exists:producto_variante,id',
            'cantidad' => 'required_without:items|nullable|integer|min:1',
            'items' => 'required_without:producto_variante_id|array|min:1',
            'items.*.producto_variante_id' => 'required_with:items|exists:producto_variante,id',
            'items.*.cantidad' => 'required_with:items|integer|min:1',
            'items.*.tipo_venta' => 'nullable|in:minorista,mayorista',
            'tipo_venta' => 'nullable|in:minorista,mayorista',
        ];
    }

    public function messages(): array
    {
        return [
            'producto_variante_id.exists' => 'La variante seleccionada no existe.',
            'producto_variante_id.required' => 'La variante seleccionada es obligatoria.',
            'producto_variante_id.required_without' => 'Debes seleccionar una variante o enviar items.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.required_without' => 'Debes indicar la cantidad o enviar items.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'items.array' => 'El formato de variantes enviado no es válido.',
            'items.min' => 'Debes seleccionar al menos una variante.',
            'items.required_without' => 'Debes seleccionar al menos una variante.',
            'items.*.producto_variante_id.required_with' => 'Cada item debe incluir una variante.',
            'items.*.producto_variante_id.exists' => 'Una de las variantes seleccionadas no existe.',
            'items.*.cantidad.required_with' => 'Cada variante debe incluir cantidad.',
            'items.*.cantidad.integer' => 'La cantidad por variante debe ser un entero.',
            'items.*.cantidad.min' => 'La cantidad por variante debe ser al menos 1.',
            'items.*.tipo_venta.in' => 'El tipo de venta debe ser minorista o mayorista.',
            'tipo_venta.in' => 'El tipo de venta debe ser minorista o mayorista.',
        ];
    }

    public function attributes(): array
    {
        return [
            'producto_variante_id' => 'variante',
            'cantidad' => 'cantidad',
            'items' => 'variantes',
        ];
    }
}
