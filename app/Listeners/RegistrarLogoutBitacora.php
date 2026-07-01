<?php

namespace App\Listeners;

use App\Models\Bitacora;
use Illuminate\Auth\Events\Logout;

class RegistrarLogoutBitacora
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        Bitacora::registrarLogout($event->user);
    }
}
