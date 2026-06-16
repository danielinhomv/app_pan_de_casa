<?php

namespace App\Listeners;

use App\Services\BitacoraService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RegistrarLoginFallido
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $email = $event->credentials['email'] ?? 'desconocido';
        BitacoraService::loginFallido($email);
    }
}
