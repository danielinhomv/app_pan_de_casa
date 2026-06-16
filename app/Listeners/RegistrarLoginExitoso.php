<?php

namespace App\Listeners;

use App\Services\BitacoraService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RegistrarLoginExitoso
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
        BitacoraService::loginExitoso($event->user);
    }
}
