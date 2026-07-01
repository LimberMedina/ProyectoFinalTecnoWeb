<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoriaId = $this->route('categoria')->id;

        return [
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categorias', 'nombre')->ignore($categoriaId),
            ],
            'descripcion' => 'nullable|string|max:500',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto válido.',
            'nombre.unique' => 'Esta categoría ya existe.',
            'nombre.max' => 'El nombre no puede exceder :max caracteres.',
            'descripcion.string' => 'La descripción debe ser texto válido.',
            'descripcion.max' => 'La descripción no puede exceder :max caracteres.',
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.mimes' => 'La imagen debe ser JPG, JPEG, PNG o WEBP.',
            'imagen.max' => 'La imagen no puede superar los 2 MB.',
        ];
    }

    /**
     * Nombres personalizados de atributos en español
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
            'imagen' => 'imagen',
        ];
    }
}
