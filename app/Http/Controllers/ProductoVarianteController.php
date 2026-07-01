<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoVariante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoVarianteController extends Controller
{
    public function store(Request $request, Producto $producto)
    {
        $request->merge([
            'sku' => $request->input('sku') === '' ? null : $request->input('sku'),
            'precio_compra' => $request->input('precio_compra') === '' ? 0 : $request->input('precio_compra', 0),
            'stock_actual' => $request->input('stock_actual') === '' ? 0 : $request->input('stock_actual', 0),
            'stock_minimo' => $request->input('stock_minimo') === '' ? 0 : $request->input('stock_minimo', 0),
            'punto_reorden' => $request->input('punto_reorden') === '' ? 0 : $request->input('punto_reorden'),
            'precio_venta' => $request->input('precio_venta') === ''
                ? ($producto->precio_venta_base ?? $producto->precio_venta ?? 0)
                : $request->input('precio_venta'),
            'precio_venta_mayorista' => $request->input('precio_venta_mayorista') === ''
                ? ($producto->precio_venta_mayorista_base ?? $producto->precio_venta_mayorista ?? $producto->precio_venta_base ?? 0)
                : $request->input('precio_venta_mayorista'),
        ]);

        $data = $request->validate([
            'sku' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('producto_variante', 'sku'),
            ],
            'talla' => 'required|string|max:30',
            'color' => 'required|string|max:50',
            'precio_compra' => 'required|numeric|min:0|max:999999.99',
            'precio_venta' => 'required|numeric|min:0.01|max:999999.99',
            'precio_venta_mayorista' => 'nullable|numeric|min:0|max:999999.99',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'punto_reorden' => 'nullable|integer|min:0',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $producto->variantes()->create($data + [
            'id_tecnica_inventario' => null,
            'id_tecnica_costo' => null,
            'costo_promedio' => 0,
        ]);

        return back()->with('success', 'Variante creada exitosamente.');
    }

    public function update(Request $request, Producto $producto, ProductoVariante $variante)
    {
        abort_unless($variante->id_producto === $producto->id, 404);

        $request->merge([
            'precio_compra' => $request->input('precio_compra') === '' ? 0 : $request->input('precio_compra', 0),
            'stock_actual' => $request->input('stock_actual') === '' ? 0 : $request->input('stock_actual', 0),
            'stock_minimo' => $request->input('stock_minimo') === '' ? 0 : $request->input('stock_minimo', 0),
            'punto_reorden' => $request->input('punto_reorden') === '' ? 0 : $request->input('punto_reorden'),
            'precio_venta' => $request->input('precio_venta') === ''
                ? ($producto->precio_venta_base ?? $producto->precio_venta ?? 0)
                : $request->input('precio_venta'),
            'precio_venta_mayorista' => $request->input('precio_venta_mayorista') === ''
                ? ($producto->precio_venta_mayorista_base ?? $producto->precio_venta_mayorista ?? $producto->precio_venta_base ?? 0)
                : $request->input('precio_venta_mayorista'),
        ]);

        $data = $request->validate([
            'sku' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('producto_variante', 'sku')->ignore($variante->id, 'id'),
            ],
            'talla' => 'required|string|max:30',
            'color' => 'required|string|max:50',
            'precio_compra' => 'required|numeric|min:0|max:999999.99',
            'precio_venta' => 'required|numeric|min:0.01|max:999999.99',
            'precio_venta_mayorista' => 'nullable|numeric|min:0|max:999999.99',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'punto_reorden' => 'nullable|integer|min:0',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $variante->update($data);

        return back()->with('success', 'Variante actualizada exitosamente.');
    }

    public function destroy(Producto $producto, ProductoVariante $variante)
    {
        abort_unless($variante->id_producto === $producto->id, 404);

        $variante->delete();

        return back()->with('success', 'Variante eliminada exitosamente.');
    }
}
