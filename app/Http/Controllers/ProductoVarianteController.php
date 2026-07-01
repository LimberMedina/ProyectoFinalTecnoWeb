<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoVariante;
use Illuminate\Http\Request;

class ProductoVarianteController extends Controller
{
    public function store(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'sku' => 'nullable|string|max:80',
            'talla' => 'required|string|max:30',
            'color' => 'required|string|max:50',
            'precio_compra' => 'required|numeric|min:0.01|max:999999.99',
            'precio_venta' => 'required|numeric|min:0.01|max:999999.99',
            'precio_venta_mayorista' => 'nullable|numeric|min:0.01|max:999999.99',
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

        $data = $request->validate([
            'sku' => 'nullable|string|max:80',
            'talla' => 'required|string|max:30',
            'color' => 'required|string|max:50',
            'precio_compra' => 'required|numeric|min:0.01|max:999999.99',
            'precio_venta' => 'required|numeric|min:0.01|max:999999.99',
            'precio_venta_mayorista' => 'nullable|numeric|min:0.01|max:999999.99',
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
