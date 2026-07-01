<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\MetodoPago;
use App\Models\Carrito;
use App\Models\KardexInventario;
use App\Services\CreditService;
use App\Services\FifoInventoryService;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VentaController extends Controller
{
    public function __construct(
        protected CreditService $creditService,
        protected FifoInventoryService $fifoInventoryService,
        protected PagoFacilService $pagoFacilService
    )
    {
    }

    /**
     * Mostrar listado de ventas
     */
    public function index(Request $request)
    {
        $origen = $request->input('origen', 'tienda');
        
        $query = Venta::with([
            'user', 
            'vendedor', 
            'metodoPago', 
            'detalles.producto',
            'detalles.variante.producto',
            'credito.cuotas.pagos.metodoPago'
        ])
            ->where('estado', '!=', 'pendiente') // Excluir pedidos pendientes
            ->where('origen', $origen) // Filtrar por origen
            ->orderBy('created_at', 'desc');

        // Si es cliente, solo mostrar sus propias ventas
        if ($request->user()->role_id === 3) {
            $query->where('user_id', $request->user()->id);
        }

        $ventas = $query->paginate(10)->withQueryString();

        // Añadir cuotas_pendientes a cada venta si es de tipo crédito
        $ventas->getCollection()->transform(function ($venta) {
            if ($venta->tipo_pago === 'credito' && $venta->credito) {
                $venta->cuotas_pendientes = $venta->credito->cuotas_pendientes;
            } else {
                $venta->cuotas_pendientes = 0;
            }
            return $venta;
        });

        $rol = 'cliente';
        if ($request->user() && $request->user()->role) {
            $rol = $request->user()->role->nombre;
        }

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'filtro_origen' => $origen,
            'rol' => $rol,
        ]);
    }

    /**
     * Mostrar detalle de una venta
     */
    public function show($id)
    {
        $venta = Venta::with([
            'user',
            'vendedor',
            'metodoPago',
            'detalles.producto.categoria',
            'detalles.variante.producto.categoria',
            'detalles.salidasLote.lote.variante.producto',
            'credito.cuotas.pagos.metodoPago'
        ])->findOrFail($id);

        $rol = 'cliente';
        if (request()->user() && request()->user()->role) {
            $rol = request()->user()->role->nombre;
        }

        return Inertia::render('Ventas/Show', [
            'venta' => $venta,
            'qrData' => [
                'uuid' => $venta->numero_venta
            ],
            'rol' => $rol,
        ]);
    }

    public function storeVentaContado(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_variante_id' => 'nullable|exists:producto_variante,id',
            'items.*.producto_id' => 'nullable|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'cliente_id' => 'nullable|exists:users,id',
            'metodo_pago_id' => ['required_without:metodo_pago', 'nullable', 'exists:metodos_pago,id'],
            'metodo_pago' => ['required_without:metodo_pago_id', 'string'],
        ]);

        $metodoPago = $this->resolveMetodoPago($request);
        $esMetodoQr = str_contains(strtolower($metodoPago->nombre), 'qr');

        try {
            DB::beginTransaction();

            $total = 0;
            $ventaItems = [];

            // Calcular total y preparar items
            foreach ($request->items as $item) {
                $variante = $this->resolveVarianteForCheckout($item);

                if (! $variante) {
                    throw new \Exception('Una de las variantes del carrito no existe.');
                }

                $producto = $variante->producto;

                // Verificar stock
                if ($variante->stock_actual < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre} ({$variante->talla} / {$variante->color})");
                }

                $producto->loadMissing(['promociones' => function ($q) {
                    $q->where('fecha_inicio', '<=', now())
                      ->where('fecha_fin', '>=', now())
                      ->where('estado', true);
                }]);

                // Calcular precio con descuento
                $descuentoMaximo = $producto->promociones->max('descuento') ?? 0;
                $precioFinal = $variante->precio_venta - ($variante->precio_venta * $descuentoMaximo / 100);
                $subtotal = $precioFinal * $item['cantidad'];

                $ventaItems[] = [
                    'producto' => $producto,
                    'variante' => $variante,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $variante->precio_venta,
                    'descuento' => $descuentoMaximo,
                    'precio_final' => $precioFinal,
                    'subtotal' => $subtotal
                ];

                $total += $subtotal;
            }

            // Crear venta
            $numeroVenta = $this->generarNumeroVenta();
            
            $venta = Venta::create([
                'numero_venta' => $numeroVenta,
                'user_id' => $request->cliente_id ?? auth()->id(),
                'vendedor_id' => auth()->user()->esVendedor() ? auth()->id() : null,
                'metodo_pago_id' => $metodoPago->id,
                'tipo_pago' => 'contado',
                'total' => $total,
                'estado' => $esMetodoQr ? 'pendiente' : 'completada',
                'observaciones' => "Venta al contado - {$metodoPago->nombre}"
            ]);

            // Crear detalles y actualizar stock
            foreach ($ventaItems as $item) {
                $detalleVenta = VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['producto']->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $item['subtotal'],
                    'id_producto_variante' => $item['variante']->id,
                ]);

                $fifo = $this->fifoInventoryService->consumirVariante(
                    $item['variante'],
                    $item['cantidad'],
                    $detalleVenta->id
                );

                $detalleVenta->update([
                    'costo_unitario' => $fifo['costo_unitario'],
                    'costo_total' => $fifo['costo_total'],
                    'utilidad_bruta' => round($item['subtotal'] - $fifo['costo_total'], 2),
                ]);

                if (! $esMetodoQr) {
                    // Reducir stock y registrar kardex sólo para ventas de contado sin QR
                    $item['variante']->decrement('stock_actual', $item['cantidad']);

                    KardexInventario::create([
                        'producto_id' => $item['producto']->id,
                        'tipo' => 'salida',
                        'cantidad' => $item['cantidad'],
                        'referencia' => "Venta {$numeroVenta}",
                        'observaciones' => "Venta al contado - {$metodoPago->nombre}"
                    ]);
                }
            }

            // Limpiar carrito si es cliente autenticado
            if (auth()->check()) {
                $carrito = Carrito::where('user_id', auth()->id())->first();
                if ($carrito) {
                    $carrito->detalles()->delete();
                }
            }

            if ($esMetodoQr) {
                $glosa = "Venta Tienda #{$numeroVenta}";
                $qrData = $this->pagoFacilService->generarQRVentaSimulado(
                    $venta->id,
                    $total,
                    $glosa
                );

                $venta->update([
                    'pago_facil_transaction_id' => $qrData['transaction_id'],
                    'pago_facil_payment_number' => $qrData['payment_number'] ?? null,
                    'pago_facil_qr_image' => $qrData['qr_image'],
                    'pago_facil_status' => 'pending',
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Venta creada. Escanea el QR para completar el pago.',
                    'venta_id' => $venta->id,
                    'total' => $total,
                    'qr_image' => $qrData['qr_image'],
                    'transaction_id' => $qrData['transaction_id'],
                    'status' => $qrData['status'] ?? 'pending',
                    'expiration' => $qrData['expiration'] ?? null,
                    'descripcion' => $qrData['glosa'] ?? $glosa,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Venta realizada exitosamente',
                'venta_id' => $venta->id,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function storeVentaCredito(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_variante_id' => 'nullable|exists:producto_variante,id',
            'items.*.producto_id' => 'nullable|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'cliente_id' => 'required|exists:users,id',
            'metodo_pago_id' => ['required_without:metodo_pago', 'nullable', 'exists:metodos_pago,id'],
            'metodo_pago' => ['required_without:metodo_pago_id', 'string'],
            'cuotas' => 'required|integer|min:2|max:12',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'tasa_interes' => 'nullable|numeric|min:0'
        ]);

        $metodoPago = $this->resolveMetodoPago($request);

        try {
            DB::beginTransaction();

            $total = 0;
            $ventaItems = [];

            // Calcular total
            foreach ($request->items as $item) {
                $variante = $this->resolveVarianteForCheckout($item);

                if (! $variante) {
                    throw new \Exception('Una de las variantes del carrito no existe.');
                }

                $producto = $variante->producto;

                if ($variante->stock_actual < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre} ({$variante->talla} / {$variante->color})");
                }

                $producto->loadMissing(['promociones' => function ($q) {
                    $q->where('fecha_inicio', '<=', now())
                      ->where('fecha_fin', '>=', now())
                      ->where('estado', true);
                }]);

                $descuentoMaximo = $producto->promociones->max('descuento') ?? 0;
                $precioFinal = $variante->precio_venta - ($variante->precio_venta * $descuentoMaximo / 100);
                $subtotal = $precioFinal * $item['cantidad'];

                $ventaItems[] = [
                    'producto' => $producto,
                    'variante' => $variante,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $variante->precio_venta,
                    'descuento' => $descuentoMaximo,
                    'precio_final' => $precioFinal,
                    'subtotal' => $subtotal
                ];

                $total += $subtotal;
            }

            // Crear venta primero
            $numeroVenta = $this->generarNumeroVenta();
            
            $venta = Venta::create([
                'numero_venta' => $numeroVenta,
                'user_id' => $request->cliente_id,
                'vendedor_id' => auth()->id(),
                'metodo_pago_id' => $metodoPago->id,
                'tipo_pago' => 'credito',
                'total' => $total,
                'estado' => 'pendiente',
                'observaciones' => "Venta a crédito - {$metodoPago->nombre}"
            ]);

            // Crear detalles y reducir stock
            foreach ($ventaItems as $item) {
                $detalleVenta = VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['producto']->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $item['descuento'],
                    'subtotal' => $item['subtotal'],
                    'id_producto_variante' => $item['variante']->id,
                ]);

                $fifo = $this->fifoInventoryService->consumirVariante(
                    $item['variante'],
                    $item['cantidad'],
                    $detalleVenta->id
                );

                $detalleVenta->update([
                    'costo_unitario' => $fifo['costo_unitario'],
                    'costo_total' => $fifo['costo_total'],
                    'utilidad_bruta' => round($item['subtotal'] - $fifo['costo_total'], 2),
                ]);

                $item['variante']->decrement('stock_actual', $item['cantidad']);

                // Registrar en kardex
                KardexInventario::create([
                    'producto_id' => $item['producto']->id,
                    'tipo' => 'salida',
                    'cantidad' => $item['cantidad'],
                    'referencia' => "Venta {$numeroVenta}",
                    'observaciones' => "Venta a crédito - {$request->cuotas} cuotas"
                ]);
            }

            // Crear crédito usando CreditService
            $credito = $this->creditService->createCredit(
                $venta->id,
                [
                    'numero_cuotas' => $request->cuotas,
                    'fecha_inicio' => $request->fecha_inicio,
                    'tasa_interes' => $request->tasa_interes ?? 0,
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Venta a crédito creada exitosamente',
                'venta_id' => $venta->id,
                'credito_id' => $credito->id,
                'total' => $total,
                'cuotas' => $request->cuotas
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    private function generarNumeroVenta()
    {
        $fecha = now()->format('Ymd');
        $ultimaVenta = Venta::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $secuencia = $ultimaVenta ? intval(substr($ultimaVenta->numero_venta, -4)) + 1 : 1;
        
        return sprintf('VE-%s-%04d', $fecha, $secuencia);
    }

    private function resolveMetodoPago(Request $request): MetodoPago
    {
        if ($request->filled('metodo_pago_id')) {
            return MetodoPago::findOrFail($request->metodo_pago_id);
        }

        $valor = trim(mb_strtolower($request->input('metodo_pago', '')));
        $metodoPago = MetodoPago::query()
            ->get()
            ->first(function ($metodo) use ($valor) {
                return mb_strtolower(trim($metodo->nombre)) === $valor;
            });

        if (! $metodoPago) {
            throw new \Exception('Método de pago inválido.');
        }

        return $metodoPago;
    }

    private function resolveVarianteForCheckout(array $item): ?ProductoVariante
    {
        if (!empty($item['producto_variante_id'])) {
            return ProductoVariante::with(['producto.promociones' => function ($q) {
                $q->where('fecha_inicio', '<=', now())
                  ->where('fecha_fin', '>=', now())
                  ->where('estado', true);
            }])->find($item['producto_variante_id']);
        }

        if (!empty($item['producto_id'])) {
            return ProductoVariante::with(['producto.promociones' => function ($q) {
                $q->where('fecha_inicio', '<=', now())
                  ->where('fecha_fin', '>=', now())
                  ->where('estado', true);
            }])
                ->where('id_producto', $item['producto_id'])
                ->where('estado', 'ACTIVO')
                ->orderBy('id')
                ->first();
        }

        return null;
    }
}
