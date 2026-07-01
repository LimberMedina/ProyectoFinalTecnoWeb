<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150',
            'nit' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'direccion' => 'nullable|string|max:500',
            'estado' => 'nullable|in:Activo,Inactivo',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto válido.',
            'nombre.max' => 'El nombre no puede exceder :max caracteres.',
            'nit.string' => 'El NIT debe ser texto válido.',
            'nit.max' => 'El NIT no puede exceder :max caracteres.',
            'telefono.string' => 'El teléfono debe ser texto válido.',
            'telefono.max' => 'El teléfono no puede exceder :max caracteres.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.max' => 'El correo no puede exceder :max caracteres.',
            'direccion.string' => 'La dirección debe ser texto válido.',
            'direccion.max' => 'La dirección no puede exceder :max caracteres.',
            'estado.in' => 'El estado debe ser Activo o Inactivo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'nit' => 'NIT',
            'telefono' => 'teléfono',
            'email' => 'correo electrónico',
            'direccion' => 'dirección',
            'estado' => 'estado',
        ];
    }
}