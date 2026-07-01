<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarritoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cantidad' => 'required|integer|min:1',
            'producto_variante_id' => 'required|exists:producto_variante,id',
        ];
    }

    public function messages(): array
    {
        return [
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'producto_variante_id.exists' => 'La variante seleccionada no existe.',
            'producto_variante_id.required' => 'La variante seleccionada es obligatoria.',
        ];
    }

    public function attributes(): array
    {
        return [
            'cantidad' => 'cantidad',
            'producto_variante_id' => 'variante',
        ];
    }
}
