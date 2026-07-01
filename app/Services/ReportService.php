<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\PageVisit;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Obtiene ventas del día actual
     */
    public function ventasDia()
    {
        return Venta::whereDate('created_at', today())
            ->where(function($q) {
                $q->where('estado', 'completada')
                  ->orWhere('tipo_pago', 'credito');
            })
            ->count();
    }

    /**
     * Obtiene ventas de la última semana
     */
    public function ventasSemana()
    {
        return Venta::whereBetween('created_at', [now()->subWeek(), now()])
            ->where(function($q) {
                $q->where('estado', 'completada')
                  ->orWhere('tipo_pago', 'credito');
            })
            ->count();
    }

    /**
     * Obtiene ventas del mes actual
     */
    public function ventasMes()
    {
        return Venta::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where(function($q) {
                $q->where('estado', 'completada')
                  ->orWhere('tipo_pago', 'credito');
            })
            ->count();
    }

    /**
     * Calcula ingresos totales por período
     */
    public function ingresosTotales($periodo = 'mes')
    {
        $ventaQuery = Venta::where('estado', 'completada');
        $pagoQuery = \App\Models\Pago::query();

        switch ($periodo) {
            case 'dia':
                $ventaQuery->whereDate('created_at', today());
                $pagoQuery->whereDate('fecha', today());
                break;
            case 'semana':
                $ventaQuery->whereBetween('created_at', [now()->subWeek(), now()]);
                $pagoQuery->whereBetween('fecha', [now()->subWeek(), now()]);
                break;
            case 'mes':
            default:
                $ventaQuery->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                $pagoQuery->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year);
                break;
        }

        $ventasSum = (float) $ventaQuery->sum('total');
        $pagosSum = (float) $pagoQuery->sum('monto');

        // Ingresos totales = ventas completadas (contado) + pagos (cuotas y otros)
        return $ventasSum + $pagosSum;
    }

    
    /**
     * Productos más vendidos
     */
    public function productosMasVendidos($limite = 10)
    {
        return DB::table('detalle_venta')
            ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->select(
                'productos.nombre',
                'productos.codigo',
                DB::raw('SUM(detalle_venta.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_venta.subtotal) as ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo')
            ->orderByDesc('total_vendido')
            ->limit($limite)
            ->get();
    }

    /**
     * Créditos pendientes
     */
    public function creditosPendientes()
    {
        return Credito::whereIn('estado', ['pendiente', 'vencido'])->count();
    }

    /**
     * Créditos pagados
     */
    public function creditosPagados()
    {
        return Credito::where('estado', 'pagado')->count();
    }

    /**
     * Cuotas vencidas
     */
    public function cuotasVencidas()
    {
        return Cuota::where('estado', 'vencida')->count();
    }

    /**
     * Monto total de créditos pendientes o por estado
     */
    public function montoCreditos($estado = null)
    {
        if (is_null($estado)) {
            return Credito::whereIn('estado', ['pendiente', 'vencido'])->sum('monto_pendiente');
        }

        return Credito::where('estado', $estado)->sum('monto_pendiente');
    }

    /**
     * Páginas más visitadas
     */
    public function visitasTop($limite = 10)
    {
        return PageVisit::orderByDesc('contador')
            ->limit($limite)
            ->get();
    }

    /**
     * Total de visitas registradas
     */
    public function totalVisitas()
    {
        return PageVisit::sum('contador');
    }

    /**
     * Visitas agrupadas por fecha (últimos 7 días)
     */
    public function visitasPorDia($dias = 7)
    {
        return PageVisit::select(
                DB::raw('DATE(updated_at) as fecha'),
                DB::raw('SUM(contador) as total')
            )
            ->whereBetween('updated_at', [now()->subDays($dias), now()])
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();
    }

    /**
     * Indicadores personales del cliente
     */
    public function indicadoresCliente($userId)
    {
        $totalCompras = Venta::where('user_id', $userId)
            ->where('estado', 'completada')
            ->count();

        $totalGastado = Venta::where('user_id', $userId)
            ->where('estado', 'completada')
            ->sum('total');

        $creditosActivos = Credito::whereHas('venta', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereIn('estado', ['pendiente', 'vencido'])->count();

        $deudaPendiente = Credito::whereHas('venta', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereIn('estado', ['pendiente', 'vencido'])->sum('monto_pendiente');

        $cuotasPendientes = Cuota::whereHas('credito.venta', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereIn('estado', ['pendiente', 'vencida'])->count();

        $cuotasVencidas = Cuota::whereHas('credito.venta', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('estado', 'vencida')->count();

        return [
            'total_compras' => $totalCompras,
            'total_gastado' => $totalGastado,
            'creditos_activos' => $creditosActivos,
            'deuda_pendiente' => $deudaPendiente,
            'cuotas_pendientes' => $cuotasPendientes,
            'cuotas_vencidas' => $cuotasVencidas,
        ];
    }

    /**
     * Ventas agrupadas por día (para gráficos)
     */
    public function ventasPorDia($dias = 7)
    {
        return Venta::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as monto')
            )
            ->where('estado', 'completada')
            ->whereBetween('created_at', [now()->subDays($dias), now()])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha', 'asc')
            ->get();
    }

    public function ventasPorDiaPorRango($fechaInicio, $fechaFin)
    {
        return Venta::select(
                DB::raw("to_char(created_at, 'YYYY-MM-DD') as fecha"),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as monto')
            )
            ->where('estado', 'completada')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw("to_char(created_at, 'YYYY-MM-DD')"))
            ->orderBy('fecha', 'asc')
            ->get();
    }

    /**
     * Indicadores generales de tienda para el panel de estadísticas.
     */
    public function indicadoresGenerales($fechaInicio = null, $fechaFin = null)
    {
        $fechaInicio = $fechaInicio ?: now()->subMonth()->startOfDay();
        $fechaFin = $fechaFin ?: now()->endOfDay();

        return [
            'ventas_totales' => Venta::where('estado', 'completada')
                ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->count(),
            'ingresos_totales' => Venta::where('estado', 'completada')
                ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->sum('total'),
            'ventas_hoy' => $this->ventasDia(),
            'ingresos_mes' => $this->ingresosTotales('mes'),
            'creditos_pendientes' => $this->creditosPendientes(),
            'stock_critico' => Producto::where('stock_actual', '<=', 10)
                ->where('estado', true)
                ->count(),
            'productos_activos' => Producto::where('estado', true)->count(),
        ];
    }

    public function ventasPorCanal($fechaInicio, $fechaFin)
    {
        return Venta::select(
                'origen',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as monto_total')
            )
            ->where('estado', '!=', 'pendiente')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('origen')
            ->get();
    }

    public function ventasPorHora($fechaInicio, $fechaFin)
    {
        return Venta::select(
                'origen',
                DB::raw("date_part('hour', created_at) as hora"),
                DB::raw('SUM(total) as monto_total')
            )
            ->where('estado', 'completada')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('origen', DB::raw("date_part('hour', created_at)"))
            ->orderBy('hora')
            ->get();
    }

    public function ticketPromedioMensual($fechaInicio, $fechaFin)
    {
        return Venta::select(
                DB::raw("to_char(created_at, 'YYYY-MM') as periodo"),
                DB::raw('AVG(total) as promedio_total'),
                DB::raw('COUNT(*) as cantidad_ventas')
            )
            ->where('estado', 'completada')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw("to_char(created_at, 'YYYY-MM')"))
            ->orderBy('periodo')
            ->get();
    }

    public function ventasPorCategoria($fechaInicio, $fechaFin, $limite = 6)
    {
        return DB::table('detalle_venta')
            ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'categorias.nombre as categoria',
                DB::raw('SUM(detalle_venta.cantidad) as total_cantidad'),
                DB::raw('SUM(detalle_venta.subtotal) as ingresos')
            )
            ->groupBy('categorias.nombre')
            ->orderByDesc('total_cantidad')
            ->limit($limite)
            ->get();
    }

    public function pagosContadoCredito($fechaInicio, $fechaFin)
    {
        $contado = (float) Venta::where('estado', 'completada')
            ->where('tipo_pago', 'contado')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->sum('total');

        $credito = (float) Pago::whereNotNull('cuota_id')
            ->where(function ($query) {
                $query->where('pago_facil_status', 'completed')
                    ->orWhereNull('pago_facil_status');
            })
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('monto');

        $rows = collect([
            ['tipo_pago' => 'Contado', 'monto_total' => $contado],
            ['tipo_pago' => 'Crédito', 'monto_total' => $credito],
        ]);

        return $rows->filter(fn ($row) => (float) $row['monto_total'] > 0.01);
    }

    public function estadoPedidos($fechaInicio, $fechaFin)
    {
        return Venta::select(
                'estado',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as monto_total')
            )
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('estado')
            ->get();
    }

    /**
     * CU8 - Ventas por rango de fechas
     */
    public function ventasPorFecha($fechaInicio, $fechaFin)
    {
        return Venta::with(['user', 'vendedor', 'metodoPago', 'detalles.producto'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * CU8 - Ventas agrupadas por método de pago
     */
    public function ventasPorMetodo($fechaInicio, $fechaFin)
    {
        return DB::table('metodos_pago')
            ->leftJoin('ventas', function ($join) use ($fechaInicio, $fechaFin) {
                $join->on('ventas.metodo_pago_id', '=', 'metodos_pago.id')
                    ->where('ventas.estado', 'completada')
                    ->whereBetween('ventas.created_at', [$fechaInicio, $fechaFin]);
            })
            ->select(
                'metodos_pago.nombre as metodo_pago',
                DB::raw('COUNT(ventas.id) as cantidad'),
                DB::raw('COALESCE(SUM(ventas.total), 0) as monto_total')
            )
            ->groupBy('metodos_pago.id', 'metodos_pago.nombre')
            ->orderByRaw('COALESCE(SUM(ventas.total), 0) DESC')
            ->get();
    }

    /**
     * CU8 - Créditos agrupados por estado
     */
    public function creditosPorEstado($fechaInicio, $fechaFin)
    {
        return Credito::with(['venta.user'])
            ->whereBetween('fecha_otorgamiento', [$fechaInicio, $fechaFin])
            ->select(
                'estado',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto_credito) as monto_total'),
                DB::raw('SUM(monto_pendiente) as monto_pendiente')
            )
            ->groupBy('estado')
            ->get();
    }

    /**
     * CU8 - Productos más vendidos en rango de fechas
     */
    public function productosMasVendidosPorFecha($fechaInicio, $fechaFin, $limite = 20)
    {
        return DB::table('detalle_venta')
            ->join('productos', 'detalle_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
            ->whereBetween('ventas.created_at', [$fechaInicio, $fechaFin])
            ->select(
                'productos.nombre',
                'productos.codigo',
                'productos.stock_actual',
                DB::raw('SUM(detalle_venta.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_venta.subtotal) as ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.codigo', 'productos.stock_actual')
            ->orderByDesc('total_vendido')
            ->limit($limite)
            ->get();
    }

    /**
     * CU8 - Clientes top por compras
     */
    public function clientesTop($fechaInicio, $fechaFin, $limite = 10)
    {
        return DB::table('ventas')
            ->join('users', 'ventas.user_id', '=', 'users.id')
            ->whereBetween('ventas.created_at', [$fechaInicio, $fechaFin])
            ->select(
                DB::raw("COALESCE(users.nombre, '') || ' ' || COALESCE(users.apellidos, '') as name"),
                'users.email',
                DB::raw('COUNT(ventas.id) as total_compras'),
                DB::raw('SUM(ventas.total) as monto_total')
            )
            ->groupBy('users.id', 'users.nombre', 'users.apellidos', 'users.email')
            ->orderByDesc('monto_total')
            ->limit($limite)
            ->get();
    }

    /**
     * CU8 - Inventario crítico (stock bajo)
     */
    public function inventarioCritico($stockMinimo = 10)
    {
        return ProductoVariante::with(['producto.categoria'])
            ->where('stock_actual', '<=', $stockMinimo)
            ->orderBy('stock_actual', 'asc')
            ->get();
    }
}
