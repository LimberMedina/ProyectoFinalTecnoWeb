<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class BitacoraController extends Controller
{
    /**
     * Mostrar el listado de eventos de la bitácora
     */
    public function index(Request $request)
    {
        $query = Bitacora::with('user')->reciente();

        // Filtrar por fecha
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
            $query->entre($fechaInicio, $fechaFin);
        }

        // Filtrar por acción
        if ($request->filled('accion') && $request->accion !== 'todas') {
            $query->porAccion($request->accion);
        }

        // Filtrar por entidad
        if ($request->filled('entidad') && $request->entidad !== 'todas') {
            $query->porEntidad($request->entidad);
        }

        // Filtrar por usuario
        if ($request->filled('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }

        // Búsqueda general
        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        $bitacora = $query->paginate(50)->withQueryString();

        // Obtener opciones para filtros
        $acciones = Bitacora::distinct()->pluck('accion')->sort();
        $entidades = Bitacora::distinct()->pluck('entidad')->sort();

        return Inertia::render('Bitacora/Index', [
            'bitacora' => $bitacora,
            'acciones' => $acciones,
            'entidades' => $entidades,
            'filtros' => [
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'accion' => $request->accion,
                'entidad' => $request->entidad,
                'usuario_id' => $request->usuario_id,
                'buscar' => $request->buscar,
            ],
        ]);
    }

    /**
     * Mostrar detalles de un evento específico
     */
    public function show($id)
    {
        $evento = Bitacora::with('user')->findOrFail($id);

        return Inertia::render('Bitacora/Show', [
            'evento' => $evento,
        ]);
    }

    /**
     * Exportar bitácora a CSV
     */
    public function exportar(Request $request)
    {
        $query = Bitacora::with('user')->reciente();

        // Aplicar mismos filtros que en index
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
            $query->entre($fechaInicio, $fechaFin);
        }

        if ($request->filled('accion') && $request->accion !== 'todas') {
            $query->porAccion($request->accion);
        }

        if ($request->filled('entidad') && $request->entidad !== 'todas') {
            $query->porEntidad($request->entidad);
        }

        if ($request->filled('usuario_id')) {
            $query->porUsuario($request->usuario_id);
        }

        $registros = $query->get();

        // Crear CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="bitacora_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['ID', 'Usuario', 'Acción', 'Entidad', 'Entidad ID', 'Descripción', 'IP', 'Fecha']);

        foreach ($registros as $registro) {
            fputcsv($handle, [
                $registro->id,
                $registro->user?->name ?? 'Sistema',
                $registro->accion,
                $registro->entidad,
                $registro->entidad_id,
                $registro->descripcion,
                $registro->ip_address,
                $registro->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($handle);

        return response()->stream(function () use ($handle) {}, 200, $headers);
    }

    /**
     * Obtener estadísticas de la bitácora
     */
    public function estadisticas()
    {
        $hoy = Carbon::today();
        $ultimoMes = Carbon::now()->subMonth();

        return response()->json([
            'total_eventos' => Bitacora::count(),
            'eventos_hoy' => Bitacora::where('created_at', '>=', $hoy)->count(),
            'eventos_mes' => Bitacora::where('created_at', '>=', $ultimoMes)->count(),
            'acciones_frecuentes' => Bitacora::selectRaw('accion, COUNT(*) as total')
                ->groupBy('accion')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
            'entidades_frecuentes' => Bitacora::selectRaw('entidad, COUNT(*) as total')
                ->where('entidad', '!=', null)
                ->groupBy('entidad')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
            'usuarios_activos' => Bitacora::where('created_at', '>=', $ultimoMes)
                ->distinct('user_id')
                ->count('user_id'),
        ]);
    }
}
