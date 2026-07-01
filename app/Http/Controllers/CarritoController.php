<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCarritoRequest;
use App\Http\Requests\UpdateCarritoRequest;
use App\Models\Carrito;
use App\Models\CarritoDetalle;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Services\PromocionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CarritoController extends Controller
{
    protected $promocionService;

    public function __construct(PromocionService $promocionService)
    {
        $this->promocionService = $promocionService;
    }

    /**
     * Mostrar el carrito del usuario
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Carrito::class);
        $carrito = Carrito::with([
            'detalles.variante.producto.categoria',
            'detalles.variante.producto.imagenes',
            'detalles.variante.producto.variantes',
                'detalles.variante',
            ])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$carrito) {
            return Inertia::render('Carrito/Index', [
                'carrito' => null,
                'detalles' => [],
                'total' => 0,
            ]);
        }

        // Aplicar descuentos automáticos
        $detallesConDescuento = $carrito->detalles->map(function ($detalle) {
            $variante = $detalle->variante;
            $producto = $variante?->producto;

            if (!$variante || !$producto) {
                return null;
            }

            $descuentoMontoPersistido = (float) ($detalle->descuento ?? 0);
            $precioUnitario = (float) $variante->precio_venta;
            $montoDescuento = $descuentoMontoPersistido > 0
                ? $descuentoMontoPersistido
                : (($precioUnitario * $this->promocionService->calcularDescuentoProducto($producto, 'minorista')) / 100);
            $precioFinal = round(max(0, $precioUnitario - $montoDescuento), 2);

            return [
                'id' => $detalle->id,
                'producto' => $producto,
                'variante' => [
                    'id' => $variante->id,
                    'talla' => $variante->talla,
                    'color' => $variante->color,
                    'sku' => $variante->sku,
                    'stock_actual' => $variante->stock_actual,
                ],
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
                'precio_unitario' => $precioFinal,
                'precio_original' => round($precioUnitario, 2),
                'descuento_porcentaje' => $descuentoMontoPersistido > 0 ? round(($descuentoMontoPersistido / $precioUnitario) * 100, 2) : 0,
                'descuento_monto' => round($montoDescuento, 2),
                'precio_con_descuento' => $precioFinal,
                'subtotal' => round($precioFinal * (int) $detalle->cantidad, 2),
            ];
        })->filter()->values();

        $total = $detallesConDescuento->sum('subtotal');

        return Inertia::render('Carrito/Index', [
            'carrito' => $carrito,
            'detalles' => $detallesConDescuento,
            'total' => round($total, 2),
        ]);
    }

    /**
     * Agregar producto al carrito
     */
    public function store(AddToCarritoRequest $request)
    {
        $this->authorize('create', Carrito::class);

        $promocionId = $request->input('promocion_id');
        $descuentoPorcentajeRequest = $request->input('descuento_porcentaje');

        // Obtener o crear carrito
        $carrito = Carrito::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $items = collect($request->input('items', []));

        if ($items->isEmpty()) {
            if ($request->filled('producto_variante_id') && $request->filled('cantidad')) {
                $items = collect([[
                    'producto_variante_id' => (int) $request->input('producto_variante_id'),
                    'cantidad' => (int) $request->input('cantidad'),
                ]]);
            }
        }

        if ($items->isEmpty()) {
            return back()->with('error', 'Debes seleccionar al menos una variante.');
        }

        foreach ($items as $item) {
            $variante = ProductoVariante::with('producto')->find($item['producto_variante_id']);

            if (!$variante) {
                return back()->with('error', 'Una de las variantes seleccionadas no existe.');
            }

            if (strtoupper((string) $variante->estado) !== 'ACTIVO') {
                return back()->with('error', 'Una de las variantes seleccionadas está inactiva.');
            }

            $cantidadSolicitada = (int) $item['cantidad'];
            if ($cantidadSolicitada < 1) {
                continue;
            }

            $detalleExistente = CarritoDetalle::where('carrito_id', $carrito->id)
                ->where('producto_variante_id', $variante->id)
                ->first();

            $nuevaCantidad = $detalleExistente
                ? $detalleExistente->cantidad + $cantidadSolicitada
                : $cantidadSolicitada;

            if ((int) $variante->stock_actual < $nuevaCantidad) {
                return back()->with('error', "Stock insuficiente para {$variante->producto->nombre} ({$variante->talla} / {$variante->color}).");
            }

            $descuentoPorcentaje = $descuentoPorcentajeRequest !== null
                ? (float) $descuentoPorcentajeRequest
                : $this->promocionService->calcularDescuentoProducto(
                    $variante->producto,
                    'minorista',
                    $promocionId ? (int) $promocionId : null
                );
            $precioUnitario = (float) $variante->precio_venta;
            $montoDescuento = ($precioUnitario * $descuentoPorcentaje) / 100;
            $precioFinal = round(max(0, $precioUnitario - $montoDescuento), 2);

            if ($detalleExistente) {
                $detalleExistente->update([
                    'cantidad' => $nuevaCantidad,
                    'precio_unitario' => $precioFinal,
                    'descuento' => round($montoDescuento, 2),
                ]);
                continue;
            }

            CarritoDetalle::create([
                'carrito_id' => $carrito->id,
                'producto_variante_id' => $variante->id,
                'cantidad' => $cantidadSolicitada,
                'precio_unitario' => $precioFinal,
                'descuento' => round($montoDescuento, 2),
            ]);
        }

        return back()->with('success', 'Productos agregados al carrito con éxito.');
    }

    /**
     * Actualizar cantidad de un item del carrito
     */
    public function update(UpdateCarritoRequest $request, CarritoDetalle $carritoDetalle)
    {
        // Verificar que el detalle pertenece al carrito del usuario
        if ($carritoDetalle->carrito->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }


        $productoVarianteId = (int) $request->input('producto_variante_id');
        $variante = ProductoVariante::with('producto')->find($productoVarianteId);

        if (!$variante) {
            return back()->with('error', 'La variante seleccionada no existe.');
        }

        if ($variante->id_producto !== $carritoDetalle->variante?->id_producto) {
            return back()->with('error', 'La variante seleccionada no corresponde al producto.');
        }

        if ((int) $variante->stock_actual < (int) $request->cantidad) {
            return back()->with('error', 'Stock insuficiente');
        }

        // Recalcular descuento
        $descuentoPorcentaje = $request->input('descuento_porcentaje') !== null
            ? (float) $request->input('descuento_porcentaje')
            : $this->promocionService->calcularDescuentoProducto($variante->producto, 'minorista');
        $precioUnitario = (float) $variante->precio_venta;
        $montoDescuento = ($precioUnitario * $descuentoPorcentaje) / 100;
        $precioFinal = round(max(0, $precioUnitario - $montoDescuento), 2);

        $detalleDuplicado = CarritoDetalle::where('carrito_id', $carritoDetalle->carrito_id)
            ->where('producto_variante_id', $variante->id)
            ->where('id', '!=', $carritoDetalle->id)
            ->first();

        if ($detalleDuplicado) {
            $cantidadCombinada = $detalleDuplicado->cantidad + (int) $request->cantidad;
            if ((int) $variante->stock_actual < $cantidadCombinada) {
                return back()->with('error', 'Stock insuficiente para combinar variantes.');
            }

            $detalleDuplicado->update([
                'cantidad' => $cantidadCombinada,
                'precio_unitario' => $precioFinal,
                'descuento' => round($montoDescuento, 2),
            ]);

            $carritoDetalle->delete();

            return back()->with('success', 'Variante y cantidad actualizadas');
        }

        $carritoDetalle->update([
            'cantidad' => $request->cantidad,
            'producto_variante_id' => $variante->id,
            'precio_unitario' => $precioFinal,
            'descuento' => round($montoDescuento, 2),
        ]);

        return back()->with('success', 'Cantidad actualizada');
    }

    /**
     * Eliminar un item del carrito o vaciar completamente
     */
    public function destroy(Request $request, $carritoDetalle)
    {
        // Caso especial: vaciar todo el carrito
        if ($carritoDetalle === 'vaciar-todo') {
            $this->authorize('create', Carrito::class);
            
            $carrito = Carrito::where('user_id', $request->user()->id)->first();

            if ($carrito) {
                $carrito->detalles()->delete();
                $carrito->delete();
            }

            return redirect()->route('carritos.index')->with('success', 'Carrito vaciado con éxito.');
        }

        // Caso normal: eliminar un item específico
        $detalle = CarritoDetalle::findOrFail($carritoDetalle);
        
        // Verificar que el detalle pertenece al carrito del usuario
        if ($detalle->carrito->user_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $detalle->delete();

        // Si el carrito quedó vacío, eliminarlo
        $carrito = Carrito::with('detalles')
            ->where('user_id', $request->user()->id)
            ->first();

        if ($carrito && $carrito->detalles->isEmpty()) {
            $carrito->delete();
        }

        return back()->with('success', 'Producto eliminado del carrito');
    }
}
