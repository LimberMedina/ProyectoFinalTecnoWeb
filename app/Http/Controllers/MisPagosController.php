<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Cuota;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MisPagosController extends Controller
{
    /**
     * Muestra los pagos del cliente autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Obtener pagos de cuotas (crédito)
        $pagos = Pago::with(['cuota.credito.venta', 'metodoPago'])
            ->whereHas('cuota.credito.venta', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('fecha')
            ->paginate(15);

        // Obtener ventas pagadas con método "QR" (pagos Yape, billeteras digitales)
        $ventasPagadas = \App\Models\Venta::with(['metodoPago', 'detalles.producto'])
            ->where('user_id', $user->id)
            ->where('estado', 'pagado')
            ->whereHas('metodoPago', function($q) {
                $q->where('nombre', 'QR');
            })
            ->orderByDesc('updated_at')
            ->get();

        // Obtener cuotas pendientes
        $cuotasPendientes = Cuota::with(['credito.venta', 'pagos'])
            ->whereHas('credito.venta', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereIn('estado', ['pendiente', 'vencida'])
            ->orderBy('fecha_vencimiento')
            ->get();

        return Inertia::render('MisPagos/Index', [
            'pagos' => $pagos,
            'ventasPagadas' => $ventasPagadas,
            'cuotasPendientes' => $cuotasPendientes,
        ]);
    }
}
