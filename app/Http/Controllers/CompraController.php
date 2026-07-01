<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\UpdateCompraRequest;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\KardexInventario;
use App\Models\LoteInventario;
use App\Models\MovimientoInventario;
use App\Models\ProductoVariante;
use App\Models\Proveedor;
use App\Models\TecnicaCosto;
use App\Models\TecnicaInventario;
use App\Services\FifoInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CompraController extends Controller
{
    public function __construct(protected FifoInventoryService $fifoInventoryService)
    {
        $this->authorizeResource(Compra::class, 'compra');
    }

    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $compras = Compra::with(['proveedor:id,nombre'])
            ->withCount('detalles')
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('num_compra', 'ilike', "%{$search}%")
                        ->orWhereHas('proveedor', function ($q) use ($search) {
                            $q->where('nombre', 'ilike', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('fecha_compra')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Compras/Index', [
            'compras' => $compras,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('Compras/Create', [
            'proveedores' => Proveedor::query()
                ->select('id', 'nombre', 'nit', 'estado')
                ->orderBy('nombre')
                ->get(),
            'variantes' => ProductoVariante::with([
                    'producto:id,codigo,nombre,imagen',
                ])
                ->select(
                    'id',
                    'id_producto',
                    'sku',
                    'talla',
                    'color',
                    'precio_compra',
                    'precio_venta',
                    'precio_venta_mayorista',
                    'stock_actual',
                    'stock_minimo',
                    'punto_reorden',
                    'estado'
                )
                ->orderBy('id_producto')
                ->orderBy('talla')
                ->orderBy('color')
                ->get(),
        ]);
    }

    public function store(StoreCompraRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            $total = $this->calculateTotal($data['detalles']);

            $compra = Compra::create([
                'id_propietario' => $request->user()->id,
                'id_proveedor' => $data['id_proveedor'],
                'num_compra' => $data['num_compra'] ?? null,
                'precio_total' => $total,
                'fecha_compra' => $data['fecha_compra'],
            ]);

            $this->persistDetailsAndStock($compra, $data['detalles'], true);
        });

        return redirect()->route('compras.index')
            ->with('success', 'Compra registrada exitosamente.');
    }

    public function show(Compra $compra)
    {
        $compra->load([
            'proveedor:id,nombre,nit,telefono,email',
            'detalles.variante.producto:id,codigo,nombre,imagen',
        ]);

        return Inertia::render('Compras/Show', [
            'compra' => $compra,
        ]);
    }

    public function edit(Compra $compra)
    {
        $compra->load(['detalles.variante.producto:id,codigo,nombre,imagen']);

        return Inertia::render('Compras/Edit', [
            'compra' => $compra,
            'proveedores' => Proveedor::query()
                ->select('id', 'nombre', 'nit', 'estado')
                ->orderBy('nombre')
                ->get(),
            'variantes' => ProductoVariante::with([
                    'producto:id,codigo,nombre,imagen',
                ])
                ->select(
                    'id',
                    'id_producto',
                    'sku',
                    'talla',
                    'color',
                    'precio_compra',
                    'precio_venta',
                    'precio_venta_mayorista',
                    'stock_actual',
                    'stock_minimo',
                    'punto_reorden',
                    'estado'
                )
                ->orderBy('id_producto')
                ->orderBy('talla')
                ->orderBy('color')
                ->get(),
        ]);
    }

    public function update(UpdateCompraRequest $request, Compra $compra)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $compra) {
            // Revertir stock previo para rehacer el detalle completo
            $this->revertDetailsStock($compra);
            $compra->detalles()->delete();

            $total = $this->calculateTotal($data['detalles']);

            $compra->update([
                'id_proveedor' => $data['id_proveedor'],
                'num_compra' => $data['num_compra'] ?? null,
                'precio_total' => $total,
                'fecha_compra' => $data['fecha_compra'],
            ]);

            $this->persistDetailsAndStock($compra, $data['detalles'], true);
        });

        return redirect()->route('compras.index')
            ->with('success', 'Compra actualizada exitosamente.');
    }

    public function destroy(Compra $compra)
    {
        DB::transaction(function () use ($compra) {
            $this->revertDetailsStock($compra);
            $compra->detalles()->delete();
            $compra->delete();
        });

        return redirect()->route('compras.index')
            ->with('success', 'Compra eliminada exitosamente.');
    }

    private function calculateTotal(array $details): float
    {
        return collect($details)->sum(function ($detail) {
            $precio = (float) $detail['precio'];
            $descuento = (float) ($detail['descuento'] ?? 0);
            $cantidad = (int) $detail['cantidad'];
            $subtotal = (float) ($detail['subtotal'] ?? 0);

            return $subtotal > 0
                ? $subtotal
                : max(0, $precio - $descuento) * $cantidad;
        });
    }

    private function persistDetailsAndStock(Compra $compra, array $details, bool $registerKardex): void
    {
        foreach ($details as $detail) {
            $cantidad = (int) $detail['cantidad'];
            $precio = (float) $detail['precio'];
            $descuento = (float) ($detail['descuento'] ?? 0);
            $subtotal = max(0, ($precio - $descuento) * $cantidad);

            $variante = ProductoVariante::with('producto')->findOrFail((int) $detail['id_producto_variante']);

            $detalleCompra = DetalleCompra::create([
                'id_compra' => $compra->id,
                'id_producto' => $variante->id_producto,
                'id_producto_variante' => $variante->id,
                'cantidad' => $cantidad,
                'precio' => $precio,
                'descuento' => $descuento,
                'subtotal' => $subtotal,
            ]);

            $detalleCompra->setRelation('compra', $compra);

            $this->fifoInventoryService->crearLoteDesdeCompra($detalleCompra);

            $stockAnterior = (int) $variante->stock_actual;
            $variante->increment('stock_actual', $cantidad);
            $stockResultante = $stockAnterior + $cantidad;
            $variante->update([
                'precio_compra' => $precio,
                'costo_promedio' => $precio,
            ]);

            $this->registrarMovimientoInventario(
                $variante,
                'ingreso',
                $cantidad,
                $precio,
                $stockAnterior,
                $stockResultante,
                'Compra ' . ($compra->num_compra ?: ('#' . $compra->id)),
                'Ingreso por compra a proveedor'
            );

            if ($registerKardex) {
                KardexInventario::create([
                    'producto_id' => $variante->id_producto,
                    'tipo' => 'entrada',
                    'cantidad' => $cantidad,
                    'referencia' => 'Compra ' . ($compra->num_compra ?: ('#' . $compra->id)),
                    'observaciones' => 'Ingreso por compra a proveedor',
                ]);
            }
        }
    }

    private function revertDetailsStock(Compra $compra): void
    {
        $compra->loadMissing('detalles');

        foreach ($compra->detalles as $detail) {
            $variante = $detail->variante;

            if (! $variante) {
                continue;
            }

            $lotesConsumidos = LoteInventario::where('id_detalle_compra', $detail->id_detalle)
                ->whereHas('salidas')
                ->exists();

            if ($lotesConsumidos) {
                throw ValidationException::withMessages([
                    'detalles' => 'No se puede modificar una compra con lotes FIFO ya consumidos.',
                ]);
            }

            $stockAnterior = (int) $variante->stock_actual;
            $nuevoStock = max(0, (int) $variante->stock_actual - (int) $detail->cantidad);
            $variante->update(['stock_actual' => $nuevoStock]);

            $this->registrarMovimientoInventario(
                $variante,
                'ajuste',
                (int) $detail->cantidad,
                (float) $detail->precio,
                $stockAnterior,
                $nuevoStock,
                'Reversión compra #' . $compra->id,
                'Ajuste por edición/eliminación de compra'
            );

            LoteInventario::where('id_detalle_compra', $detail->id_detalle)->delete();

            KardexInventario::create([
                'producto_id' => $variante->id_producto,
                'tipo' => 'ajuste',
                'cantidad' => (int) $detail->cantidad,
                'referencia' => 'Reversión compra #' . $compra->id,
                'observaciones' => 'Ajuste por edición/eliminación de compra',
            ]);
        }
    }

    private function registrarMovimientoInventario(
        ProductoVariante $variante,
        string $tipoMovimiento,
        int $cantidad,
        float $costoUnitario,
        int $stockAnterior,
        int $stockResultante,
        string $motivo,
        string $observacion
    ): void {
        $tecnicaInventarioId = $variante->id_tecnica_inventario
            ?? TecnicaInventario::query()->orderBy('id')->value('id');
        if (! $tecnicaInventarioId) {
            $tecnicaInventarioId = TecnicaInventario::query()->firstOrCreate(
                ['nombre' => 'Automática'],
                ['descripcion' => 'Técnica creada automáticamente para registrar movimientos']
            )->id;
        }

        $tecnicaCostoId = $variante->id_tecnica_costo
            ?? TecnicaCosto::query()->orderBy('id')->value('id');
        if (! $tecnicaCostoId) {
            $tecnicaCostoId = TecnicaCosto::query()->firstOrCreate(
                ['nombre' => 'Costo automático'],
                ['descripcion' => 'Técnica creada automáticamente para registrar costos de movimientos']
            )->id;
        }

        MovimientoInventario::create([
            'id_producto' => $variante->id_producto,
            'id_producto_variante' => $variante->id,
            'id_tecnica_inventario' => $tecnicaInventarioId,
            'id_tecnica_costo' => $tecnicaCostoId,
            'tipo_movimiento' => $tipoMovimiento,
            'motivo' => $motivo,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'costo_total' => round($costoUnitario * $cantidad, 2),
            'stock_anterior' => $stockAnterior,
            'stock_resultante' => $stockResultante,
            'fecha' => now()->toDateString(),
            'observacion' => $observacion,
        ]);
    }
}
