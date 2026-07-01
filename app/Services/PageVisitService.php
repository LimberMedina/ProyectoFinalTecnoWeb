<?php

namespace App\Services;

use App\Models\PageVisit;
use Illuminate\Support\Facades\DB;

class PageVisitService
{
    /**
     * Registra una visita a una ruta específica
     */
    public function registrarVisita($ruta)
    {
        $pageVisit = PageVisit::where('ruta', $ruta)->first();
        
        if ($pageVisit) {
            $pageVisit->increment('contador');
        } else {
            PageVisit::create([
                'ruta' => $ruta,
                'contador' => 1
            ]);
        }
    }

    /**
     * Determina si una ruta debe ser contabilizada
     */
    public function debeContabilizar($ruta)
    {
        if (request()->method() !== 'GET') {
            return false;
        }

        // Rutas excluidas específicas (formularios, AJAX y rutas internas)
        $rutasExcluidas = [
            'login',
            'register',
            'forgot-password',
            'reset-password',
            'verify-email',
            'user/profile',
            'user/profile-information',
            'user/password',
            'user/two-factor-authentication',
            'user/settings/preferences',
            'notificaciones/no-leidas',
            'logout',
            'api/menu',
            'api/search/all'
        ];

        // Excluir rutas que comienzan con ciertos prefijos
        $prefijosExcluidos = ['api/', 'sanctum/', 'broadcasting/'];

        foreach ($prefijosExcluidos as $prefijo) {
            if (str_starts_with($ruta, $prefijo)) {
                return false;
            }
        }

        foreach ($rutasExcluidas as $excluida) {
            if (str_contains($ruta, $excluida)) {
                return false;
            }
        }

        return true;
    }
}
