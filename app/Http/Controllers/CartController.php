<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoDetalle;
use App\Models\Producto;
use Illuminate\Http\Request;
use App\Models\ProductoVariante;

class CartController extends Controller
{
    public function get()
    {
        if (!auth()->check()) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        $carrito = Carrito::with([
            'detalles.variante.producto.promociones' => function ($q) {
                $q->where('fecha_inicio', '<=', now())
                  ->where('fecha_fin', '>=', now())
                  ->where('estado', true);
            },
            'detalles.variante.producto.categoria',
            'detalles.variante.producto.imagenes',
            'detalles.variante.producto.variantes',
        ])->firstOrCreate(['user_id' => auth()->id()]);

        // Recalcular precios con promociones actuales
        $carrito->detalles->each(function ($detalle) {
            $variante = $detalle->variante;

            if (! $variante || ! $variante->producto) {
                return;
            }

            $producto = $variante->producto;
            $descuentoMaximo = $producto->promociones->max('descuento') ?? 0;
            
            $detalle->precio_unitario = $variante->precio_venta;
            $detalle->descuento = $variante->precio_venta * ($descuentoMaximo / 100);
            $detalle->save();
        });

        return response()->json([
            'items' => $carrito->detalles->map(function ($detalle) {
                $variante = $detalle->variante;
                $producto = $variante?->producto;

                if (! $variante || ! $producto) {
                    return null;
                }

                $descuento = (float) ($detalle->descuento ?? 0);
                $precioUnitario = (float) ($detalle->precio_unitario ?? 0);
                $precioFinal = max(0, $precioUnitario - $descuento);

                return [
                    'id' => $detalle->id,
                    'producto_variante_id' => $variante->id,
                    'producto' => [
                        'id' => $producto->id,
                        'codigo' => $producto->codigo,
                        'nombre' => $producto->nombre,
                        'imagen' => $producto->imagen,
                        'categoria' => $producto->categoria,
                    ],
                    'variante' => [
                        'id' => $variante->id,
                        'sku' => $variante->sku,
                        'talla' => $variante->talla,
                        'color' => $variante->color,
                        'stock_actual' => $variante->stock_actual,
                        'precio_venta' => $variante->precio_venta,
                    ],
                    'nombre' => $producto->nombre,
                    'imagen' => $producto->imagen,
                    'variantes_disponibles' => $producto->variantes
                        ->where('estado', 'ACTIVO')
                        ->map(fn ($item) => [
                            'id' => $item->id,
                            'talla' => $item->talla,
                            'color' => $item->color,
                            'stock_actual' => $item->stock_actual,
                            'precio_venta' => $item->precio_venta,
                        ])
                        ->values(),
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'descuento' => $detalle->descuento,
                    'precio_final' => $precioFinal,
                    'subtotal' => $detalle->subtotal,
                ];
            })->filter()->values(),
            'total' => $carrito->total()
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'producto_variante_id' => 'required|exists:producto_variante,id',
            'cantidad' => 'required|integer|min:1'
        ]);

        $variante = ProductoVariante::with(['producto.promociones' => function ($q) {
            $q->where('fecha_inicio', '<=', now())
              ->where('fecha_fin', '>=', now())
              ->where('activa', true);
        }, 'producto.categoria', 'producto.imagenes', 'producto.variantes'])->findOrFail($request->producto_variante_id);

        if (! $variante) {
            return response()->json(['error' => 'Debes seleccionar una variante válida'], 422);
        }

        if ($variante->stock_actual < $request->cantidad) {
            return response()->json(['error' => 'Stock insuficiente'], 422);
        }

        if (!auth()->check()) {
            return response()->json(['message' => 'Usa localStorage en el frontend'], 200);
        }

        $carrito = Carrito::firstOrCreate(['user_id' => auth()->id()]);

        $producto = $variante->producto;
        $descuentoMaximo = $producto->promociones->max('descuento') ?? 0;
        $descuentoMonto = $variante->precio_venta * ($descuentoMaximo / 100);

        $detalle = CarritoDetalle::where('carrito_id', $carrito->id)
            ->where('producto_variante_id', $variante->id)
            ->first();

        if ($detalle) {
            $detalle->cantidad += $request->cantidad;
            $detalle->producto_variante_id = $variante->id;
            $detalle->precio_unitario = $variante->precio_venta;
            $detalle->descuento = $descuentoMonto;
            $detalle->save();
        } else {
            CarritoDetalle::create([
                'carrito_id' => $carrito->id,
                'producto_variante_id' => $variante->id,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $variante->precio_venta,
                'descuento' => $descuentoMonto
            ]);
        }

        return $this->get();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'producto_variante_id' => 'required|exists:producto_variante,id',
        ]);

        if (!auth()->check()) {
            return response()->json(['message' => 'Usa localStorage en el frontend'], 200);
        }

        $detalle = CarritoDetalle::whereHas('carrito', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        $variante = ProductoVariante::with('producto')->findOrFail($request->producto_variante_id);

        if (! $variante) {
            return response()->json(['error' => 'La variante seleccionada no existe.'], 422);
        }

        // Verificar stock
        if ($variante->stock_actual < $request->cantidad) {
            return response()->json(['error' => 'Stock insuficiente'], 422);
        }

        $detalle->cantidad = $request->cantidad;
        $detalle->producto_variante_id = $variante->id;
        $detalle->precio_unitario = $variante->precio_venta;
        $detalle->descuento = ($variante->precio_venta * (($variante->producto->promociones->max('descuento') ?? 0) / 100));
        $detalle->save();

        return $this->get();
    }

    public function remove($id)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Usa localStorage en el frontend'], 200);
        }

        $detalle = CarritoDetalle::whereHas('carrito', function ($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        $detalle->delete();

        return $this->get();
    }

    public function clear()
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Usa localStorage en el frontend'], 200);
        }

        $carrito = Carrito::where('user_id', auth()->id())->first();

        if ($carrito) {
            $carrito->detalles()->delete();
        }

        return response()->json(['items' => [], 'total' => 0]);
    }

    public function sync(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.producto_variante_id' => 'required|exists:producto_variante,id',
            'items.*.cantidad' => 'required|integer|min:1'
        ]);

        if (!auth()->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $carrito = Carrito::firstOrCreate(['user_id' => auth()->id()]);

        // Limpiar carrito actual
        $carrito->detalles()->delete();

        // Agregar items desde localStorage
        foreach ($request->items as $item) {
            $variante = ProductoVariante::with('producto.promociones')->find($item['producto_variante_id']);

            if ($variante && $variante->stock_actual >= $item['cantidad']) {
                $producto = $variante->producto;
                $descuentoMaximo = $producto->promociones->max('descuento') ?? 0;
                $descuentoMonto = $variante->precio_venta * ($descuentoMaximo / 100);

                CarritoDetalle::create([
                    'carrito_id' => $carrito->id,
                    'producto_variante_id' => $variante->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $variante->precio_venta,
                    'descuento' => $descuentoMonto
                ]);
            }
        }

        return $this->get();
    }
}
