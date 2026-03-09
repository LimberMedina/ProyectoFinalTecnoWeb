<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class MisPedidosController extends Controller
{
    /**
     * Muestra los pedidos del cliente autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Filtrar por estado si se envía
        $estado = $request->input('estado', 'pendiente');

        $query = Venta::with(['vendedor', 'metodoPago', 'detalles.producto'])
            ->where('user_id', $user->id)
            // El cliente no debe ver pedidos anulados
            ->whereNotIn('estado', ['anulado']);

        if ($estado === 'pendiente') {
            $query->where('estado', 'pendiente');
        } elseif ($estado === 'pagado') {
            $query->whereIn('estado', ['pagado', 'completada']);
        } elseif ($estado === 'enviado') {
            $query->where('estado', 'enviado');
        } elseif ($estado === 'entregado') {
            $query->where('estado', 'entregado');
        }

        $pedidos = $query->orderByDesc('created_at')->paginate(10);

        return Inertia::render('MisPedidos/Index', [
            'pedidos' => $pedidos,
            'filtro' => $estado,
        ]);
    }

    /**
     * Muestra el detalle de un pedido específico
     */
    public function show($id)
    {
        $user = auth()->user();

        $pedido = Venta::with(['vendedor', 'metodoPago', 'detalles.producto', 'credito.cuotas'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return Inertia::render('MisPedidos/Show', [
            'pedido' => $pedido,
        ]);
    }

    /**
     * Confirmar recepción del pedido por parte del cliente
     */
    public function confirmarRecibido($id)
    {
        $user = auth()->user();

        try {
            $venta = Venta::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Validar que el pedido está en estado enviado
            if ($venta->estado !== 'enviado') {
                return redirect()->route('mis-pedidos.index', ['estado' => 'enviado'])
                    ->with('error', 'Solo puedes confirmar recepción de pedidos enviados');
            }

            $venta->update(['estado' => 'entregado']);

            Log::info('Cliente confirmó recepción de pedido', [
                'venta_id' => $id,
                'user_id' => $user->id
            ]);

            return redirect()->route('mis-pedidos.index', ['estado' => 'enviado'])
                ->with('success', '¡Recepción confirmada! Gracias por tu compra.');

        } catch (\Exception $e) {
            Log::error('Error al confirmar recepción de pedido', [
                'venta_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('mis-pedidos.index')
                ->with('error', 'Error al confirmar la recepción del pedido');
        }
    }
}
