<?php

namespace App\Http\Middleware;

use App\Services\BitacoraService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrarEnBitacora
{
    /** Prefijos que no se registran nunca */
    private const RUTAS_IGNORADAS = [
        'page-visits',
        'api/dashboard',
        'up',
        '_ignition',
        'sanctum',
    ];

    private const MAPA_MODULOS = [
        'pedidos'      => 'Pedidos',
        'mis-pedidos'  => 'Mis Pedidos',
        'catalogo'     => 'Catálogo',
        'menu'         => 'Menú',
        'inventario'   => 'Inventario',
        'produccion'   => 'Producción',
        'Contabilidad' => 'Contabilidad',
        'pagofacil'    => 'PagoFácil',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo usuarios autenticados
        if (!$request->user()) {
            return $response;
        }

        if ($this->esRutaIgnorada($request)) {
            return $response;
        }

        // ── Único caso que el middleware maneja: acceso denegado (403) ───────
        // Los GETs y CRUDs se registran desde cada controller directamente
        // para evitar duplicados.
        if ($response->getStatusCode() === 403) {
            $modulo = $this->detectarModulo($request);
            BitacoraService::accesoDenegado($modulo);
        }

        return $response;
    }

    private function detectarModulo(Request $request): ?string
    {
        $segmentos = explode('/', $request->path());
        return self::MAPA_MODULOS[$segmentos[0] ?? ''] ?? null;
    }

    private function esRutaIgnorada(Request $request): bool
    {
        $path = $request->path();
        foreach (self::RUTAS_IGNORADAS as $ignorada) {
            if (str_starts_with($path, $ignorada)) {
                return true;
            }
        }
        return false;
    }
}