<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimientoInventarioRequest;
use App\Http\Requests\UpdateMovimientoInventarioRequest;
use App\Models\KardexInventario;
use App\Models\LoteInventario;
use App\Models\MovimientoInventario;
use App\Models\SalidaLote;
use App\Models\ProductoVariante;
use App\Models\TecnicaCosto;
use App\Models\TecnicaInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MovimientoInventarioController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(MovimientoInventario::class, 'inventario');
    }

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $tab = $request->input('tab', 'stock');

        $salidas = SalidaLote::with([
            'detalleVenta.venta:id,numero_venta,created_at,estado,origen,total',
            'lote.variante.producto:id,nombre,codigo',
        ])
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->whereHas('lote.variante.producto', function ($productQuery) use ($search) {
                        $productQuery->where('nombre', 'ilike', "%{$search}%")
                            ->orWhere('codigo', 'ilike', "%{$search}%");
                    })->orWhereHas('detalleVenta.venta', function ($ventaQuery) use ($search) {
                        $ventaQuery->where('numero_venta', 'ilike', "%{$search}%");
                    });
                });
            })
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'salidas_page')
            ->withQueryString();

        $stockVariantes = ProductoVariante::with('producto:id,nombre,codigo')
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('sku', 'ilike', "%{$search}%")
                        ->orWhereHas('producto', function ($productQuery) use ($search) {
                            $productQuery->where('nombre', 'ilike', "%{$search}%")
                                ->orWhere('codigo', 'ilike', "%{$search}%");
                        });
                });
            })
            ->orderBy('id_producto')
            ->orderBy('talla')
            ->orderBy('color')
            ->paginate(15, ['*'], 'stock_page')
            ->withQueryString();

        $lotes = LoteInventario::with(['variante.producto:id,nombre,codigo', 'detalleCompra.compra:id,num_compra,fecha_compra'])
            ->when($search, function ($query, $search) {
                $query->whereHas('variante.producto', function ($productQuery) use ($search) {
                    $productQuery->where('nombre', 'ilike', "%{$search}%")
                        ->orWhere('codigo', 'ilike', "%{$search}%");
                });
            })
            ->orderByDesc('fecha_ingreso')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'lotes_page')
            ->withQueryString();

        $movimientos = MovimientoInventario::with([
            'variante.producto:id,nombre,codigo',
            'tecnicaInventario:id,nombre',
            'tecnicaCosto:id,nombre',
        ])
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('tipo_movimiento', 'ilike', "%{$search}%")
                        ->orWhere('motivo', 'ilike', "%{$search}%")
                        ->orWhereHas('variante.producto', function ($productQuery) use ($search) {
                            $productQuery->where('nombre', 'ilike', "%{$search}%")
                                ->orWhere('codigo', 'ilike', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'movimientos_page')
            ->withQueryString();

        $alertas = ProductoVariante::with('producto:id,nombre,codigo')
            ->where(function ($query) {
                $query->whereColumn('stock_actual', '<=', 'stock_minimo')
                    ->orWhereColumn('stock_actual', '<=', 'punto_reorden');
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('sku', 'ilike', "%{$search}%")
                        ->orWhereHas('producto', function ($productQuery) use ($search) {
                            $productQuery->where('nombre', 'ilike', "%{$search}%")
                                ->orWhere('codigo', 'ilike', "%{$search}%");
                        });
                });
            })
            ->orderBy('stock_actual')
            ->paginate(15, ['*'], 'alertas_page')
            ->withQueryString();

        $tecnicas = TecnicaInventario::query()
            ->select('id', 'nombre', 'descripcion')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Inventarios/Index', [
            'tab' => $tab,
            'search' => $search,
            'stockVariantes' => $stockVariantes,
            'lotes' => $lotes,
            'movimientos' => $movimientos,
            'salidas' => $salidas,
            'alertas' => $alertas,
            'tecnicas' => $tecnicas,
            'summary' => [
                'variantes' => ProductoVariante::count(),
                'lotes' => LoteInventario::count(),
                'movimientos' => MovimientoInventario::count(),
                'salidas' => SalidaLote::count(),
                'alertas' => ProductoVariante::where(function ($query) {
                    $query->whereColumn('stock_actual', '<=', 'stock_minimo')
                        ->orWhereColumn('stock_actual', '<=', 'punto_reorden');
                })->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventarios/Create', [
            'variantes' => ProductoVariante::with('producto:id,nombre,codigo')
                ->select('id', 'id_producto', 'sku', 'talla', 'color', 'stock_actual', 'precio_compra', 'estado')
                ->orderBy('id_producto')
                ->orderBy('talla')
                ->orderBy('color')
                ->get(),
            'tecnicasInventario' => TecnicaInventario::query()->select('id', 'nombre')->orderBy('nombre')->get(),
            'tecnicasCosto' => TecnicaCosto::query()->select('id', 'nombre')->orderBy('nombre')->get(),
        ]);
    }

    public function store(StoreMovimientoInventarioRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $variante = ProductoVariante::with('producto')->lockForUpdate()->findOrFail((int) $data['id_producto_variante']);

            $nuevoStock = $this->calculateNewStock(
                (int) $variante->stock_actual,
                $data['tipo_movimiento'],
                (int) $data['cantidad']
            );

            $costoUnitario = (float) $data['costo_unitario'];
            $costoTotal = $costoUnitario * (int) $data['cantidad'];

            MovimientoInventario::create([
                'id_producto' => $variante->id_producto,
                'id_producto_variante' => (int) $data['id_producto_variante'],
                'id_tecnica_inventario' => (int) $data['id_tecnica_inventario'],
                'id_tecnica_costo' => (int) $data['id_tecnica_costo'],
                'tipo_movimiento' => $data['tipo_movimiento'],
                'motivo' => $data['motivo'] ?? null,
                'cantidad' => (int) $data['cantidad'],
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoTotal,
                'stock_anterior' => (int) $variante->stock_actual,
                'stock_resultante' => $nuevoStock,
                'fecha' => $data['fecha'],
                'observacion' => $data['observacion'] ?? null,
            ]);

            $variante->update([
                'stock_actual' => $nuevoStock,
                'precio_compra' => $data['tipo_movimiento'] === 'ingreso' ? $costoUnitario : $variante->precio_compra,
            ]);

            $this->registerKardex($variante->id_producto, $data['tipo_movimiento'], (int) $data['cantidad'], $data['motivo'] ?? null);
        });

        return redirect()->route('inventarios.index')
            ->with('success', 'Movimiento de inventario registrado exitosamente.');
    }

    public function show(MovimientoInventario $inventario)
    {
        $inventario->load([
            'variante.producto:id,codigo,nombre,stock_actual',
            'tecnicaInventario:id,nombre',
            'tecnicaCosto:id,nombre',
        ]);

        return Inertia::render('Inventarios/Show', [
            'movimiento' => $inventario,
        ]);
    }

    public function edit(MovimientoInventario $inventario)
    {
        return Inertia::render('Inventarios/Edit', [
            'movimiento' => $inventario,
            'variantes' => ProductoVariante::with('producto:id,nombre,codigo')
                ->select('id', 'id_producto', 'sku', 'talla', 'color', 'stock_actual', 'precio_compra', 'estado')
                ->orderBy('id_producto')
                ->orderBy('talla')
                ->orderBy('color')
                ->get(),
            'tecnicasInventario' => TecnicaInventario::query()->select('id', 'nombre')->orderBy('nombre')->get(),
            'tecnicasCosto' => TecnicaCosto::query()->select('id', 'nombre')->orderBy('nombre')->get(),
        ]);
    }

    public function update(UpdateMovimientoInventarioRequest $request, MovimientoInventario $inventario)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $inventario) {
            $movimiento = MovimientoInventario::lockForUpdate()->findOrFail($inventario->id);

            $oldVariante = ProductoVariante::lockForUpdate()->findOrFail((int) $movimiento->id_producto_variante);
            $stockRevertido = $this->revertStockByMovement((int) $oldVariante->stock_actual, $movimiento->tipo_movimiento, (int) $movimiento->cantidad);
            $oldVariante->update(['stock_actual' => $stockRevertido]);

            $newVariante = ProductoVariante::lockForUpdate()->findOrFail((int) $data['id_producto_variante']);
            $nuevoStock = $this->calculateNewStock((int) $newVariante->stock_actual, $data['tipo_movimiento'], (int) $data['cantidad']);

            $costoUnitario = (float) $data['costo_unitario'];
            $costoTotal = $costoUnitario * (int) $data['cantidad'];

            $movimiento->update([
                'id_producto' => $newVariante->id_producto,
                'id_producto_variante' => (int) $data['id_producto_variante'],
                'id_tecnica_inventario' => (int) $data['id_tecnica_inventario'],
                'id_tecnica_costo' => (int) $data['id_tecnica_costo'],
                'tipo_movimiento' => $data['tipo_movimiento'],
                'motivo' => $data['motivo'] ?? null,
                'cantidad' => (int) $data['cantidad'],
                'costo_unitario' => $costoUnitario,
                'costo_total' => $costoTotal,
                'stock_anterior' => (int) $newVariante->stock_actual,
                'stock_resultante' => $nuevoStock,
                'fecha' => $data['fecha'],
                'observacion' => $data['observacion'] ?? null,
            ]);

            $newVariante->update([
                'stock_actual' => $nuevoStock,
                'precio_compra' => $data['tipo_movimiento'] === 'ingreso' ? $costoUnitario : $newVariante->precio_compra,
            ]);

            $this->registerKardex($newVariante->id_producto, 'ajuste', (int) $data['cantidad'], 'Ajuste por edición de movimiento #' . $movimiento->id);
        });

        return redirect()->route('inventarios.index')
            ->with('success', 'Movimiento de inventario actualizado exitosamente.');
    }

    public function destroy(MovimientoInventario $inventario)
    {
        DB::transaction(function () use ($inventario) {
            $movimiento = MovimientoInventario::lockForUpdate()->findOrFail($inventario->id);
            $variante = ProductoVariante::lockForUpdate()->findOrFail((int) $movimiento->id_producto_variante);

            $stockRevertido = $this->revertStockByMovement((int) $variante->stock_actual, $movimiento->tipo_movimiento, (int) $movimiento->cantidad);
            $variante->update(['stock_actual' => $stockRevertido]);

            $movimiento->delete();

            $this->registerKardex($variante->id_producto, 'ajuste', (int) $movimiento->cantidad, 'Reversión por eliminación de movimiento');
        });

        return redirect()->route('inventarios.index')
            ->with('success', 'Movimiento de inventario eliminado exitosamente.');
    }

    private function calculateNewStock(int $stockActual, string $tipo, int $cantidad): int
    {
        if ($tipo === 'ingreso') {
            return $stockActual + $cantidad;
        }

        if ($tipo === 'salida') {
            if ($stockActual < $cantidad) {
                throw ValidationException::withMessages([
                    'cantidad' => 'No hay stock suficiente para registrar la salida. Disponible: ' . $stockActual,
                ]);
            }

            return $stockActual - $cantidad;
        }

        throw ValidationException::withMessages([
            'tipo_movimiento' => 'Tipo de movimiento no válido.',
        ]);
    }

    private function revertStockByMovement(int $stockActual, string $tipo, int $cantidad): int
    {
        if ($tipo === 'ingreso') {
            if ($stockActual < $cantidad) {
                throw ValidationException::withMessages([
                    'cantidad' => 'No se puede revertir el movimiento porque el stock actual es menor al ingreso original.',
                ]);
            }

            return $stockActual - $cantidad;
        }

        if ($tipo === 'salida') {
            return $stockActual + $cantidad;
        }

        throw ValidationException::withMessages([
            'tipo_movimiento' => 'Tipo de movimiento no válido.',
        ]);
    }

    private function registerKardex(int $productoId, string $tipoMovimiento, int $cantidad, ?string $motivo = null): void
    {
        $tipoKardex = match ($tipoMovimiento) {
            'ingreso' => 'entrada',
            'salida' => 'salida',
            default => 'ajuste',
        };

        KardexInventario::create([
            'producto_id' => $productoId,
            'tipo' => $tipoKardex,
            'cantidad' => $cantidad,
            'referencia' => 'Inventario manual',
            'observaciones' => $motivo,
        ]);
    }
}
