<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metodo_pago_id' => 'required|exists:metodos_pago,id',
            'tipo_venta' => 'required|in:contado',
            'direccion_entrega' => 'required|string|min:10|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'metodo_pago_id.required' => 'El método de pago es obligatorio.',
            'metodo_pago_id.exists' => 'El método de pago seleccionado no existe.',
            'tipo_venta.required' => 'El tipo de venta es obligatorio.',
            'tipo_venta.in' => 'En la vista del cliente solo se permite pago al contado.',
            'direccion_entrega.required' => 'La dirección de entrega es obligatoria.',
            'direccion_entrega.min' => 'La dirección de entrega debe tener al menos 10 caracteres.',
            'direccion_entrega.max' => 'La dirección de entrega no puede superar 500 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'metodo_pago_id' => 'método de pago',
            'tipo_venta' => 'tipo de venta',
            'direccion_entrega' => 'dirección de entrega',
        ];
    }
}
