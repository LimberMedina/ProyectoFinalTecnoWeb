<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Obtener todas las notificaciones del usuario autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = Notification::forUser($user->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Marcar una notificación como leída
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Verificar que pertenezca al usuario autenticado
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return back();
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        
        Notification::forUser($user->id)
            ->unread()
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);

        return back();
    }

    /**
     * Obtener notificaciones no leídas (API)
     */
    public function getUnread(Request $request)
    {
        $user = $request->user();
        
        $notifications = Notification::forUser($user->id)
            ->unread()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }
}
