<?php

namespace App\Services;

use App\Models\DetalleCompra;
use App\Models\LoteInventario;
use App\Models\ProductoVariante;
use App\Models\SalidaLote;
use Illuminate\Validation\ValidationException;

class FifoInventoryService
{
    public function crearLoteDesdeCompra(DetalleCompra $detalleCompra): LoteInventario
    {
        $costoUnitario = $this->resolverCostoUnitarioBase($detalleCompra->variante ?? null, $detalleCompra->precio);

        return LoteInventario::create([
            'id_producto_variante' => $detalleCompra->id_producto_variante,
            'id_detalle_compra' => $detalleCompra->id_detalle,
            'fecha_ingreso' => $detalleCompra->compra?->fecha_compra ?? now()->toDateString(),
            'cantidad_inicial' => (int) $detalleCompra->cantidad,
            'cantidad_disponible' => (int) $detalleCompra->cantidad,
            'costo_unitario' => $costoUnitario,
            'estado' => 'DISPONIBLE',
        ]);
    }

    public function consumirVariante(ProductoVariante $variante, int $cantidad, int $idDetalleVenta): array
    {
        $this->asegurarLotesParaStockExistente($variante);

        $lotes = LoteInventario::query()
            ->where('id_producto_variante', $variante->id)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_ingreso')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $disponible = (int) $lotes->sum('cantidad_disponible');

        if ($disponible < $cantidad) {
            throw ValidationException::withMessages([
                'items' => "Stock FIFO insuficiente para {$variante->producto->nombre} ({$variante->talla} / {$variante->color}). Disponible: {$disponible}",
            ]);
        }

        $restante = $cantidad;
        $costoTotal = 0.0;

        foreach ($lotes as $lote) {
            if ($restante <= 0) {
                break;
            }

            $cantidadTomada = min($restante, (int) $lote->cantidad_disponible);

            if ($cantidadTomada <= 0) {
                continue;
            }

            $costoUnitarioLote = (float) $lote->costo_unitario;

            if ($costoUnitarioLote <= 0) {
                $costoUnitarioLote = $this->resolverCostoUnitarioBase($variante, null);

                if ($costoUnitarioLote > 0) {
                    $lote->update(['costo_unitario' => $costoUnitarioLote]);
                }
            }

            if ($costoUnitarioLote <= 0) {
                throw ValidationException::withMessages([
                    'items' => "No se pudo determinar el costo FIFO para {$variante->producto->nombre} ({$variante->talla} / {$variante->color}). Verifica el costo de compra o el costo promedio del producto.",
                ]);
            }

            $costoLote = round($cantidadTomada * $costoUnitarioLote, 2);
            $restante -= $cantidadTomada;
            $costoTotal += $costoLote;

            $nuevoDisponible = (int) $lote->cantidad_disponible - $cantidadTomada;
            $lote->update([
                'cantidad_disponible' => $nuevoDisponible,
                'estado' => $nuevoDisponible > 0 ? 'DISPONIBLE' : 'CONSUMIDO',
            ]);

            SalidaLote::create([
                'id_detalle_venta' => $idDetalleVenta,
                'id_lote' => $lote->id,
                'cantidad' => $cantidadTomada,
                'costo_unitario_aplicado' => $costoUnitarioLote,
                'costo_total' => $costoLote,
            ]);
        }

        return [
            'costo_unitario' => round($costoTotal / $cantidad, 2),
            'costo_total' => round($costoTotal, 2),
        ];
    }

    protected function asegurarLotesParaStockExistente(ProductoVariante $variante): void
    {
        $disponibleFIFO = (int) LoteInventario::query()
            ->where('id_producto_variante', $variante->id)
            ->sum('cantidad_disponible');

        if ($disponibleFIFO > 0) {
            return;
        }

        $stockActual = (int) $variante->stock_actual;

        if ($stockActual <= 0) {
            return;
        }

        $costoUnitario = $this->resolverCostoUnitarioBase($variante, null);

        if ($costoUnitario <= 0) {
            throw ValidationException::withMessages([
                'items' => "No se pudo crear un lote FIFO para {$variante->producto->nombre} ({$variante->talla} / {$variante->color}) porque no hay costo configurado.",
            ]);
        }

        LoteInventario::create([
            'id_producto_variante' => $variante->id,
            'id_detalle_compra' => null,
            'fecha_ingreso' => now()->toDateString(),
            'cantidad_inicial' => $stockActual,
            'cantidad_disponible' => $stockActual,
            'costo_unitario' => $costoUnitario,
            'estado' => 'DISPONIBLE',
        ]);
    }

    protected function resolverCostoUnitarioBase(?ProductoVariante $variante, ?float $fallback = null): float
    {
        if (! $variante) {
            return max(0, (float) ($fallback ?? 0));
        }

        $variante->loadMissing('producto');

        $candidatos = [
            $fallback,
            $variante->costo_promedio,
            $variante->precio_compra,
            $variante->producto?->precio_venta_base,
            $variante->producto?->precio_venta_mayorista_base,
            $variante->precio_venta,
        ];

        foreach ($candidatos as $costo) {
            $valor = (float) ($costo ?? 0);
            if ($valor > 0) {
                return round($valor, 2);
            }
        }

        return 0.0;
    }
}