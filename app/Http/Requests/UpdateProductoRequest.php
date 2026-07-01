<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateProductoRequest - Validación para editar productos
 * 
 * Similar a StoreProductoRequest pero:
 * - El código debe ser único excepto para el producto actual
 * - Todos los campos son opcionales (PATCH)
 */
class UpdateProductoRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado (se usa Policy)
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        $productoId = $this->route('producto')->id;

        return [
            'codigo' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('productos', 'codigo')->ignore($productoId),
            ],
            'nombre' => 'sometimes|required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'descripcion' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'qr' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'marca' => 'nullable|string|max:100',
            'genero' => 'nullable|string|max:30',
            'precio_venta_base' => 'nullable|numeric|min:0',
            'precio_venta_mayorista_base' => 'nullable|numeric|min:0',
            'estado' => 'boolean',
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'codigo.required' => 'El código del producto es obligatorio.',
            'codigo.unique' => 'Este código ya está registrado.',
            'codigo.max' => 'El código no puede exceder :max caracteres.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder :max caracteres.',
            'categoria_id.required' => 'La categoría del producto es obligatoria.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'imagen.image' => 'La imagen debe ser una imagen válida.',
            'imagen.mimes' => 'La imagen debe ser formato: jpeg, jpg, png o webp.',
            'imagen.max' => 'La imagen no puede pesar más de 2MB.',
            'qr.image' => 'El QR debe ser una imagen válida.',
            'qr.mimes' => 'El QR debe ser formato: jpeg, jpg, png o webp.',
            'qr.max' => 'El QR no puede pesar más de 2MB.',
            'marca.max' => 'La marca no puede exceder :max caracteres.',
            'genero.max' => 'El género no puede exceder :max caracteres.',
            'precio_venta_base.numeric' => 'El precio de venta base debe ser numérico.',
            'precio_venta_base.min' => 'El precio de venta base no puede ser menor que 0.',
            'precio_venta_mayorista_base.numeric' => 'El precio de venta mayorista base debe ser numérico.',
            'precio_venta_mayorista_base.min' => 'El precio de venta mayorista base no puede ser menor que 0.',
            'estado.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }

    /**
     * Nombres personalizados de atributos en español
     */
    public function attributes(): array
    {
        return [
            'codigo' => 'código',
            'nombre' => 'nombre',
            'categoria_id' => 'categoría',
            'descripcion' => 'descripción',
            'imagen' => 'imagen',
            'qr' => 'QR',
            'marca' => 'marca',
            'genero' => 'género',
            'precio_venta_base' => 'precio de venta base',
            'precio_venta_mayorista_base' => 'precio de venta mayorista base',
            'estado' => 'estado',
        ];
    }
}
