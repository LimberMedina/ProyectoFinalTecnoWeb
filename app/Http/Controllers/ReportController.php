<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService)
    {
    }

    public function index()
    {
        return Inertia::render('Reportes/Index');
    }

    public function stats(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'bar_metric' => 'nullable|string',
            'donut_metric' => 'nullable|string',
            'line_metric' => 'nullable|string',
            'chart' => 'nullable|string|in:bar,donut,line',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

        if ($fechaInicio->diffInDays($fechaFin) > 365) {
            return response()->json([
                'message' => 'El rango no puede ser mayor a 1 año',
            ], 422);
        }

        $indicadores = $this->reportService->indicadoresGenerales($fechaInicio, $fechaFin);

        $barMetric = $request->input('bar_metric', 'productos_vendidos');
        $donutMetric = $request->input('donut_metric', 'categorias');
        $lineMetric = $request->input('line_metric', 'evolucion');
        $chartType = $request->input('chart');

        $response = [
            'indicadores' => $indicadores,
            'selected' => [
                'bar_metric' => $barMetric,
                'donut_metric' => $donutMetric,
                'line_metric' => $lineMetric,
            ],
        ];

        if ($chartType) {
            $response['chart'] = match ($chartType) {
                'bar' => $this->buildBarChartData($barMetric, $fechaInicio, $fechaFin),
                'donut' => $this->buildDonutChartData($donutMetric, $fechaInicio, $fechaFin),
                'line' => $this->buildLineChartData($lineMetric, $fechaInicio, $fechaFin),
            };
        } else {
            $response['charts'] = [
                'bar' => $this->buildBarChartData($barMetric, $fechaInicio, $fechaFin),
                'donut' => $this->buildDonutChartData($donutMetric, $fechaInicio, $fechaFin),
                'line' => $this->buildLineChartData($lineMetric, $fechaInicio, $fechaFin),
            ];
        }

        return response()->json($response);
    }

    protected function buildBarChartData($metric, $fechaInicio, $fechaFin)
    {
        $ventasPorMetodo = $this->reportService->ventasPorMetodo($fechaInicio, $fechaFin);

        return match ($metric) {
            'ventas_metodo' => [
                'labels' => $ventasPorMetodo->pluck('metodo_pago')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Monto total',
                        'data' => $ventasPorMetodo->pluck('monto_total')->toArray(),
                        'backgroundColor' => '#16a34a',
                    ],
                ],
            ],
            'ventas_canal' => [
                'labels' => $this->reportService->ventasPorCanal($fechaInicio, $fechaFin)->pluck('origen')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Ingresos por canal',
                        'data' => $this->reportService->ventasPorCanal($fechaInicio, $fechaFin)->pluck('monto_total')->toArray(),
                        'backgroundColor' => '#0ea5e9',
                    ],
                ],
            ],
            default => [
                'labels' => $this->reportService->productosMasVendidosPorFecha($fechaInicio, $fechaFin, 10)->pluck('nombre')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Unidades vendidas',
                        'data' => $this->reportService->productosMasVendidosPorFecha($fechaInicio, $fechaFin, 10)->pluck('total_vendido')->toArray(),
                        'backgroundColor' => '#f59e0b',
                    ],
                ],
            ],
        };
    }

    protected function buildDonutChartData($metric, $fechaInicio, $fechaFin)
    {
        if ($metric === 'pagos_contado_credito') {
            $rows = $this->reportService->pagosContadoCredito($fechaInicio, $fechaFin);

            return [
                'labels' => $rows->pluck('tipo_pago')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Ingresos',
                        'data' => $rows->pluck('monto_total')->map(fn ($value) => (float) $value)->values()->toArray(),
                        'backgroundColor' => ['#22c55e', '#f97316'],
                    ],
                ],
            ];
        }

        if ($metric === 'estado_pedidos') {
            $rows = $this->reportService->estadoPedidos($fechaInicio, $fechaFin);

            return [
                'labels' => $rows->pluck('estado')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Pedidos',
                        'data' => $rows->pluck('cantidad')->map(fn ($value) => (int) $value)->values()->toArray(),
                        'backgroundColor' => ['#0ea5e9', '#6366f1', '#f59e0b', '#ef4444'],
                    ],
                ],
            ];
        }

        $rows = $this->reportService->ventasPorCategoria($fechaInicio, $fechaFin, 6);

        return [
            'labels' => $rows->pluck('categoria')->values()->toArray(),
            'datasets' => [
                [
                    'label' => 'Participación',
                    'data' => $rows->pluck('total_cantidad')->map(fn ($value) => (float) $value)->values()->toArray(),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6'],
                ],
            ],
        ];
    }

    protected function buildLineChartData($metric, $fechaInicio, $fechaFin)
    {
        return match ($metric) {
            'ventas_hora' => [
                'labels' => range(0, 23),
                'datasets' => collect($this->reportService->ventasPorHora($fechaInicio, $fechaFin))
                    ->groupBy('origen')
                    ->map(function ($group, $origen) use ($fechaInicio, $fechaFin) {
                        $values = array_fill(0, 24, 0);
                        foreach ($group as $item) {
                            $values[(int) $item->hora] = (float) $item->monto_total;
                        }
                        return [
                            'label' => ucfirst($origen),
                            'data' => $values,
                            'borderColor' => $origen === 'online' ? '#2563eb' : '#16a34a',
                            'backgroundColor' => $origen === 'online' ? 'rgba(37,99,235,0.12)' : 'rgba(22,163,74,0.12)',
                            'fill' => true,
                        ];
                    })->values()->toArray(),
            ],
            'ticket_promedio' => [
                'labels' => $this->reportService->ticketPromedioMensual($fechaInicio, $fechaFin)->pluck('periodo')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Ticket promedio',
                        'data' => $this->reportService->ticketPromedioMensual($fechaInicio, $fechaFin)->pluck('promedio_total')->toArray(),
                        'borderColor' => '#f43f5e',
                        'backgroundColor' => 'rgba(244,63,94,0.18)',
                        'fill' => true,
                    ],
                ],
            ],
            default => [
                'labels' => $this->reportService->ventasPorDiaPorRango($fechaInicio, $fechaFin)->pluck('fecha')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Ventas diarias',
                        'data' => $this->reportService->ventasPorDiaPorRango($fechaInicio, $fechaFin)->pluck('monto')->toArray(),
                        'borderColor' => '#14b8a6',
                        'backgroundColor' => 'rgba(20,184,166,0.16)',
                        'fill' => true,
                    ],
                ],
            ],
        };
    }

    public function show($tipo, Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

        // Validar que el rango no sea mayor a 1 año
        if ($fechaInicio->diffInDays($fechaFin) > 365) {
            return back()->withErrors(['fecha_fin' => 'El rango no puede ser mayor a 1 año']);
        }

        $datos = [];
        $titulo = '';

        switch ($tipo) {
            case 'ventas-fecha':
                $datos = $this->reportService->ventasPorFecha($fechaInicio, $fechaFin);
                $titulo = 'Ventas por Fecha';
                break;

            case 'ventas-metodo':
                $datos = $this->reportService->ventasPorMetodo($fechaInicio, $fechaFin);
                $titulo = 'Ventas por Método de Pago';
                break;

            case 'creditos-estado':
                $datos = $this->reportService->creditosPorEstado($fechaInicio, $fechaFin);
                $titulo = 'Créditos por Estado';
                break;

            case 'productos-vendidos':
                $limite = $request->input('limite', 20);
                $datos = $this->reportService->productosMasVendidosPorFecha($fechaInicio, $fechaFin, $limite);
                $titulo = 'Productos Más Vendidos';
                break;

            case 'clientes-top':
                $limite = $request->input('limite', 10);
                $datos = $this->reportService->clientesTop($fechaInicio, $fechaFin, $limite);
                $titulo = 'Clientes Top';
                break;

            case 'inventario-critico':
                $stockMinimo = $request->input('stock_minimo', 10);
                $datos = $this->reportService->inventarioCritico($stockMinimo);
                $titulo = 'Inventario Crítico';
                break;

            default:
                abort(404, 'Tipo de reporte no válido');
        }

        // If the request expects JSON and is not an Inertia visit, return JSON payload
        if (! $request->header('X-Inertia') && ($request->wantsJson() || $request->ajax() || $request->input('as') === 'json')) {
            return response()->json([
                'tipo' => $tipo,
                'titulo' => $titulo,
                'datos' => $datos,
                'parametros' => [
                    'fecha_inicio' => $request->fecha_inicio,
                    'fecha_fin' => $request->fecha_fin,
                    'limite' => $request->input('limite'),
                    'stock_minimo' => $request->input('stock_minimo')
                ]
            ]);
        }

        return Inertia::render('Reportes/Show', [
            'tipo' => $tipo,
            'titulo' => $titulo,
            'datos' => $datos,
            'parametros' => [
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'limite' => $request->input('limite'),
                'stock_minimo' => $request->input('stock_minimo')
            ]
        ]);
    }

    public function pdf($tipo, Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();

        if ($fechaInicio->diffInDays($fechaFin) > 365) {
            return back()->withErrors(['fecha_fin' => 'El rango no puede ser mayor a 1 año']);
        }

        $datos = [];
        $titulo = '';
        $vista = '';

        switch ($tipo) {
            case 'ventas-fecha':
                $datos = $this->reportService->ventasPorFecha($fechaInicio, $fechaFin);
                $titulo = 'Ventas por Fecha';
                $vista = 'reports.ventas-fecha';
                break;

            case 'ventas-metodo':
                $datos = $this->reportService->ventasPorMetodo($fechaInicio, $fechaFin);
                $titulo = 'Ventas por Método de Pago';
                $vista = 'reports.ventas-metodo';
                break;

            case 'creditos-estado':
                $datos = $this->reportService->creditosPorEstado($fechaInicio, $fechaFin);
                $titulo = 'Créditos por Estado';
                $vista = 'reports.creditos-estado';
                break;

            case 'productos-vendidos':
                $limite = $request->input('limite', 20);
                $datos = $this->reportService->productosMasVendidosPorFecha($fechaInicio, $fechaFin, $limite);
                $titulo = 'Productos Más Vendidos';
                $vista = 'reports.productos-vendidos';
                break;

            case 'clientes-top':
                $limite = $request->input('limite', 10);
                $datos = $this->reportService->clientesTop($fechaInicio, $fechaFin, $limite);
                $titulo = 'Clientes Top';
                $vista = 'reports.clientes-top';
                break;

            case 'inventario-critico':
                $stockMinimo = $request->input('stock_minimo', 10);
                $datos = $this->reportService->inventarioCritico($stockMinimo);
                $titulo = 'Inventario Crítico';
                $vista = 'reports.inventario-critico';
                break;

            default:
                abort(404, 'Tipo de reporte no válido');
        }

        $pdf = Pdf::loadView($vista, [
            'titulo' => $titulo,
            'datos' => $datos,
            'fechaInicio' => $fechaInicio->format('d/m/Y'),
            'fechaFin' => $fechaFin->format('d/m/Y')
        ])->setPaper('a4', 'portrait');

        $nombreArchivo = str_replace(' ', '-', strtolower($titulo)) . '-' . now()->format('YmdHis') . '.pdf';
        
        return $pdf->download($nombreArchivo);
    }
}
