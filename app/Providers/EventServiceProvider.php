<?php

namespace App\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Listeners\RegistrarLoginExitoso;
use App\Listeners\RegistrarLoginFallido;
use App\Listeners\RegistrarLogout;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // ── Eventos de autenticación de Laravel ─────────────────────────────
        Login::class => [
            RegistrarLoginExitoso::class,
        ],

        Failed::class => [
            RegistrarLoginFallido::class,
        ],

        Logout::class => [
            RegistrarLogout::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}