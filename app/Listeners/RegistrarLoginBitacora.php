<?php

namespace App\Listeners;

use App\Models\Bitacora;
use Illuminate\Auth\Events\Login;

class RegistrarLoginBitacora
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        Bitacora::registrarLogin($event->user);
    }
}
