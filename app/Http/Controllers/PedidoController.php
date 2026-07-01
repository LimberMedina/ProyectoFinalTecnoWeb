<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Carrito;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\MetodoPago;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\PagoFacilService;
use App\Services\PromocionService;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PedidoController extends Controller
{
    protected PromocionService $promocionService;
    protected VentaService $ventaService;

    public function __construct(
        PromocionService $promocionService,
        VentaService $ventaService,
        protected PagoFacilService $pagoFacilService
    ) {
        $this->promocionService = $promocionService;
        $this->ventaService = $ventaService;
    }

    /**
     * Listar pedidos con filtro por origen y estado.
     */
    public function index(Request $request)
    {
        $origen = $request->input('origen', 'tienda');
        $estado = $request->input('estado', 'pendiente');

        $query = Venta::with(['user', 'vendedor', 'metodoPago', 'detalles.variante.producto'])
            ->orderByDesc('created_at');

        if (in_array($origen, ['tienda', 'online'], true)) {
            $query->where('origen', $origen);
        }

        if (in_array($estado, ['pendiente', 'pagado', 'enviado', 'anulado'], true)) {
            $query->where('estado', $estado);
        }

        if ((int) $request->user()->role_id === 3) {
            $query->where('user_id', $request->user()->id);
        }

        $pedidos = $query->paginate(20)->withQueryString();

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
            'filtro_origen' => $origen,
            'filtro_estado' => $estado,
        ]);
    }

    /**
     * Mostrar página de checkout.
     */
    public function checkout(Request $request)
    {
        $carrito = Carrito::with(['detalles.variante.producto'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $carrito || $carrito->detalles->isEmpty()) {
            return redirect()->route('carritos.index')
                ->with('error', 'El carrito está vacío.');
        }

        $detallesConDescuento = $carrito->detalles
            ->map(function ($detalle) {
                $variante = $detalle->variante;
                $producto = $variante?->producto;

                if (! $variante || ! $producto) {
                    return null;
                }

                $precioOriginal = (float) $variante->precio_venta;
                $descuentoPersistido = (float) ($detalle->descuento ?? 0);
                $precioUnitario = $descuentoPersistido > 0
                    ? round(max(0, $precioOriginal - $descuentoPersistido), 2)
                    : round($precioOriginal, 2);
                $montoDescuento = $descuentoPersistido > 0
                    ? $descuentoPersistido
                    : round(($precioOriginal * $this->promocionService->calcularDescuentoProducto($producto, 'minorista')) / 100, 2);
                $subtotal = round($precioUnitario * (int) $detalle->cantidad, 2);

                return [
                    'producto' => $producto,
                    'variante' => [
                        'id' => $variante->id,
                        'talla' => $variante->talla,
                        'color' => $variante->color,
                        'sku' => $variante->sku,
                        'stock_actual' => $variante->stock_actual,
                    ],
                    'cantidad' => (int) $detalle->cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $montoDescuento,
                    'subtotal' => $subtotal,
                ];
            })
            ->filter()
            ->values();

        $total = round($detallesConDescuento->sum('subtotal'), 2);
        $metodosPago = MetodoPago::orderBy('nombre')->get();
        $metodoPagoContado = MetodoPago::where('nombre', 'Efectivo')->first();

        if (! $metodoPagoContado) {
            return redirect()->route('carritos.index')
                ->with('error', 'No hay un método de pago en efectivo configurado.');
        }

        return Inertia::render('Pedidos/Checkout', [
            'detalles' => $detallesConDescuento,
            'total' => $total,
            'metodosPago' => $metodosPago,
            'metodoPagoContado' => $metodoPagoContado,
            'direccionEntrega' => $request->user()->direccion ?? '',
        ]);
    }

    /**
     * Procesar la venta.
     */
    public function store(StoreVentaRequest $request)
    {
        $carrito = Carrito::with(['detalles.variante.producto'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $carrito || $carrito->detalles->isEmpty()) {
            return redirect()->route('carritos.index')
                ->with('error', 'El carrito está vacío.');
        }

        $detalles = $this->prepararDetallesVenta($carrito);

        if (empty($detalles)) {
            return redirect()->route('carritos.index')
                ->with('error', 'El carrito no contiene productos válidos.');
        }

        $erroresStock = $this->validarStockCarrito($carrito);

        if (! empty($erroresStock)) {
            return back()->withErrors(['stock' => implode(', ', $erroresStock)]);
        }

        $metodoPago = MetodoPago::findOrFail($request->metodo_pago_id);
        $esPagoQr = strtoupper((string) $metodoPago->nombre) === 'QR';

        try {
            DB::beginTransaction();

            $totales = $this->ventaService->calcularTotales($detalles);

            $venta = Venta::create([
                'user_id' => $request->user()->id,
                'vendedor_id' => $request->user()->id,
                'metodo_pago_id' => $request->metodo_pago_id,
                'numero_venta' => $this->ventaService->generarNumeroVenta(),
                'subtotal' => $totales['subtotal'],
                'descuento' => $totales['descuento'],
                'total' => $totales['total'],
                'estado' => $esPagoQr ? 'pendiente' : 'pagado',
                // Marcar como pedido/venta generado desde la web (checkout)
                'origen' => 'online',
                'direccion_entrega' => $request->direccion_entrega,
            ]);

            foreach ($carrito->detalles as $detalle) {
                $this->crearDetalleVentaDesdeCarrito($venta, $detalle);
            }

            if ($esPagoQr) {
                $glosa = "Venta #{$venta->numero_venta}";
                $qrData = $this->pagoFacilService->generarQRVentaSimulado(
                    $venta->id,
                    $venta->total,
                    $glosa
                );

                $venta->update([
                    'pago_facil_transaction_id' => $qrData['transaction_id'],
                    'pago_facil_payment_number' => $qrData['payment_number'] ?? null,
                    'pago_facil_qr_image' => $qrData['qr_image'],
                    'pago_facil_status' => $qrData['status'] ?? 'pending',
                    'pago_facil_raw_response' => json_encode($qrData),
                ]);
            }

            if ($request->tipo_venta === 'credito') {
                $this->crearCredito($venta, $request);
            }

            $carrito->detalles()->delete();
            $carrito->delete();

            DB::commit();

            return redirect()->route('pedidos.confirmacion', $venta->id)
                ->with('success', 'Venta procesada exitosamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error al procesar la venta: ' . $e->getMessage());
        }
    }

    private function prepararDetallesVenta(Carrito $carrito): array
    {
        return $carrito->detalles
            ->map(function ($detalle) {
                $variante = $detalle->variante;
                $producto = $variante?->producto;

                if (! $variante || ! $producto) {
                    return null;
                }

                $precioOriginal = (float) $variante->precio_venta;
                $descuentoPersistido = (float) ($detalle->descuento ?? 0);
                $precioUnitario = $descuentoPersistido > 0
                    ? round(max(0, $precioOriginal - $descuentoPersistido), 2)
                    : round($precioOriginal, 2);
                $montoDescuento = $descuentoPersistido > 0
                    ? $descuentoPersistido
                    : round(($precioOriginal * $this->promocionService->calcularDescuentoProducto($producto, 'minorista')) / 100, 2);
                $subtotal = round($precioUnitario * (int) $detalle->cantidad, 2);

                return [
                    'id_producto_variante' => $variante->id,
                    'cantidad' => (int) $detalle->cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $montoDescuento,
                    'subtotal' => $subtotal,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    private function validarStockCarrito(Carrito $carrito): array
    {
        $erroresStock = [];

        foreach ($carrito->detalles as $detalle) {
            $variante = $detalle->variante;

            if (! $variante || ! $variante->producto) {
                $erroresStock[] = 'Una de las variantes del carrito no existe.';
                continue;
            }

            if ((int) $variante->stock_actual < (int) $detalle->cantidad) {
                $erroresStock[] = "Stock insuficiente para {$variante->producto->nombre} ({$variante->talla} / {$variante->color}).";
            }
        }

        return $erroresStock;
    }

    private function crearDetalleVentaDesdeCarrito(Venta $venta, $detalle): void
    {
        $variante = $detalle->variante;
        $producto = $variante?->producto;

        if (! $variante || ! $producto) {
            throw new \Exception('Una de las variantes del carrito no existe.');
        }

        $precioOriginal = (float) $variante->precio_venta;
        $descuentoPersistido = (float) ($detalle->descuento ?? 0);
        $precioUnitario = $descuentoPersistido > 0
            ? round(max(0, $precioOriginal - $descuentoPersistido), 2)
            : round($precioOriginal, 2);
        $montoDescuento = $descuentoPersistido > 0
            ? $descuentoPersistido
            : round(($precioOriginal * $this->promocionService->calcularDescuentoProducto($producto, 'minorista')) / 100, 2);
        $subtotal = round($precioUnitario * (int) $detalle->cantidad, 2);

        $detalleVenta = VentaDetalle::create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'id_producto_variante' => $variante->id,
            'cantidad' => (int) $detalle->cantidad,
            'precio_unitario' => $precioUnitario,
            'descuento' => $montoDescuento,
            'subtotal' => $subtotal,
        ]);
    }

    private function crearCredito(Venta $venta, StoreVentaRequest $request): void
    {
        $mesesPlazo = max((int) $request->meses_plazo, 1);
        $tasaInteres = (float) $request->tasa_interes;

        $interes = round(($venta->total * $tasaInteres) / 100, 2);
        $montoTotalConInteres = round($venta->total + $interes, 2);
        $montoCuota = round($montoTotalConInteres / $mesesPlazo, 2);

        $fechaOtorgamiento = now();
        $fechaPrimerPago = Carbon::parse($request->fecha_primer_pago);
        $fechaVencimientoFinal = $fechaPrimerPago->copy()->addMonths($mesesPlazo - 1);

        $credito = Credito::create([
            'venta_id' => $venta->id,
            'monto_credito' => $venta->total,
            'interes' => $interes,
            'cuotas_total' => $mesesPlazo,
            'dias_mora' => 0,
            'monto_pagado' => 0,
            'monto_pendiente' => $montoTotalConInteres,
            'fecha_otorgamiento' => $fechaOtorgamiento,
            'fecha_vencimiento' => $fechaVencimientoFinal,
            'estado' => 'pendiente',
        ]);

        $fechaPago = $fechaPrimerPago->copy();
        $interesPorCuota = round($interes / $mesesPlazo, 2);

        for ($i = 1; $i <= $mesesPlazo; $i++) {
            Cuota::create([
                'credito_id' => $credito->id,
                'numero_cuota' => $i,
                'monto' => $montoCuota,
                'interes_cuota' => $interesPorCuota,
                'dias_mora' => 0,
                'monto_pagado' => 0,
                'monto_pendiente' => $montoCuota,
                'fecha_vencimiento' => $fechaPago->copy(),
                'estado' => 'pendiente',
            ]);

            $fechaPago->addMonth();
        }
    }

    public function confirmacion(Venta $venta)
    {
        $this->authorize('view', $venta);

        if (
            strtolower((string) $venta->estado) === 'pagado' ||
            strtolower((string) $venta->pago_facil_status) === 'completed'
        ) {
            return redirect()->route('mis-pedidos.show', $venta->id);
        }

        $venta->load(['detalles.variante.producto', 'metodoPago', 'credito.cuotas']);

        return Inertia::render('Pedidos/Confirmacion', [
            'venta' => $venta,
        ]);
    }
}