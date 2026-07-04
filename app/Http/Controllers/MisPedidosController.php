<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use App\Models\Venta;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class MisPedidosController extends Controller
{
    protected PagoFacilService $pagoFacilService;

    public function __construct(PagoFacilService $pagoFacilService)
    {
        $this->pagoFacilService = $pagoFacilService;
    }

    /**
     * Muestra los pedidos del cliente autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Filtrar por estado si se envía
        $estado = $request->input('estado', 'pendiente');

        $query = Venta::with(['vendedor', 'metodoPago', 'detalles.variante.producto'])
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

        $pedido = Venta::with(['vendedor', 'metodoPago', 'detalles.variante.producto', 'credito.cuotas'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $metodosPago = MetodoPago::all();

        return Inertia::render('MisPedidos/Show', [
            'pedido' => $pedido,
            'metodosPago' => $metodosPago,
        ]);
    }

    /**
     * Generar o actualizar el método de pago para un pedido pendiente
     */
    public function generarQR(Request $request, $id)
    {
        $request->validate([
            'metodo_pago_id' => 'required|exists:metodos_pago,id',
        ]);

        $user = auth()->user();

        $venta = Venta::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($venta->estado !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden generar pagos para pedidos pendientes.',
            ], 422);
        }

        $metodoPago = MetodoPago::findOrFail($request->metodo_pago_id);
        $venta->metodo_pago_id = $metodoPago->id;

        $responsePayload = [
            'success' => true,
            'metodo_pago' => $metodoPago->nombre,
        ];

        if (str_contains(strtolower($metodoPago->nombre), 'qr')) {
            $glosa = "Pedido #{$venta->numero_venta}";
            $qrData = $this->pagoFacilService->generarQRVentaSimulado(
                $venta->id,
                $venta->total,
                $glosa,
            );

            $venta->pago_facil_transaction_id = $qrData['transaction_id'];
            $venta->pago_facil_payment_number = $qrData['payment_number'] ?? null;
            $venta->pago_facil_qr_image = $qrData['qr_image'];
            $venta->pago_facil_status = 'pending';
            $responsePayload['qr_image'] = $qrData['qr_image'];
            $responsePayload['transaction_id'] = $qrData['transaction_id'];
            $responsePayload['status'] = $qrData['status'] ?? 'pending';
            $responsePayload['monto'] = $venta->total;
        } else {
            $venta->pago_facil_transaction_id = null;
            $venta->pago_facil_payment_number = null;
            $venta->pago_facil_qr_image = null;
            $venta->pago_facil_status = null;
            $responsePayload['message'] = 'Método de pago actualizado. Aún deberá completar el pago con el vendedor si no es QR.';
        }

        $venta->save();

        return response()->json($responsePayload);
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

    public function reportarNoRecibido($id)
    {
        $user = auth()->user();

        $venta = Venta::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($venta->estado !== 'enviado') {
            return response()->json([
                'success' => false,
                'message' => 'Solo puedes reportar como no recibido un pedido que está en envío.',
            ], 422);
        }

        $observaciones = trim($venta->observaciones ?? '');
        $nota = 'El cliente indicó que no recibió el pedido.';

        $venta->update([
            'estado' => 'anulado',
            'observaciones' => $observaciones
                ? $observaciones . "\n" . $nota
                : $nota,
        ]);

        Log::info('Cliente reportó pedido no recibido', [
            'venta_id' => $id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Se notificó al propietario que el pedido no fue recibido.',
        ]);
    }
}
