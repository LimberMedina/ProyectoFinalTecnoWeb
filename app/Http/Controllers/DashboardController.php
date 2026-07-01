<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Promocion;
use App\Services\ReportService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index()
    {
        $user = auth()->user();

        // Dashboard para Cliente
        if ($user->esCliente()) {
            // Obtener productos activos con sus categorías y promociones
            $productos = \App\Models\Producto::with(['categoria', 'imagenes', 'promociones' => function($q) {
                $q->where('fecha_inicio', '<=', now())
                  ->where('fecha_fin', '>=', now());
            }])
            ->where('stock_actual', '>', 0)
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria.nombre');

            // Obtener promociones activas
            $promociones = \App\Models\Promocion::with(['productos' => function($q) {
                $q->where('estado', true)->where('stock_actual', '>', 0);
            }])
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->get();

            // Carrito actual del usuario
            $carrito = \App\Models\Carrito::where('user_id', $user->id)->first();
            $itemsCarrito = $carrito 
                ? \App\Models\CarritoDetalle::where('carrito_id', $carrito->id)
                    ->with('variante.producto')
                    ->get()
                : collect();

            $data = [
                'rol' => 'cliente',
                'productos' => $productos,
                'promociones' => $promociones,
                'carrito' => [
                    'items' => $itemsCarrito,
                    'total' => $itemsCarrito->sum(fn($item) => $item->subtotal),
                    'cantidad_items' => $itemsCarrito->sum('cantidad'),
                ],
                'indicadores' => $this->reportService->indicadoresCliente($user->id),
            ];

            return Inertia::render('Dashboard', $data);
        }

        // Dashboard para Propietario y Vendedor
        $productosActivos = Producto::where('estado', true)->count();
        $promocionesActivas = Promocion::where('estado', true)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->count();
        $stockCritico = Producto::where('estado', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->count();

        $data = [
            'rol' => $user->esPropietario() ? 'propietario' : 'vendedor',
            'kpis' => [
                'ventas_hoy' => $this->reportService->ventasDia(),
                'ventas_semana' => $this->reportService->ventasSemana(),
                'ventas_mes' => $this->reportService->ventasMes(),
                'ingresos_hoy' => $this->reportService->ingresosTotales('dia'),
                'ingresos_semana' => $this->reportService->ingresosTotales('semana'),
                'ingresos_mes' => $this->reportService->ingresosTotales('mes'),
                'productos_activos' => $productosActivos,
                'promociones_activas' => $promocionesActivas,
                'creditos_pendientes' => $this->reportService->creditosPendientes(),
                'cuotas_vencidas' => $this->reportService->cuotasVencidas(),
                'stock_critico' => $stockCritico,
            ],
            'movimientosRecientes' => MovimientoInventario::with(['variante.producto'])
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->limit(8)
                ->get(),
        ];

        return Inertia::render('Dashboard', $data);
    }
}
