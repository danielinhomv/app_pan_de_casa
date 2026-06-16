<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\PedidoRastreo;
use App\Models\Ubicacion;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PedidoController extends Controller
{
    /**
     * Vista del propietario - lista todos los pedidos
     */
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'detalles.producto'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Pedidos/index', [
            'pedidos' => $pedidos
        ]);
    }

    /**
     * Vista del propietario - detalle de pedido con mapa
     * No se registra: ver detalle es ruido, lo importante es la acción posterior
     */
    public function show($id)
    {
        $pedido   = Pedido::with(['cliente', 'detalles.producto'])->findOrFail($id);
        $detalles = $pedido->detalles;

        $rastreo   = PedidoRastreo::where('pedido_id', $id)->latest()->first();
        $tiendaLat = -17.7833;
        $tiendaLng = -63.1821;

        $route = [
            'origin'      => ['lat' => $tiendaLat, 'lng' => $tiendaLng],
            'destination' => [
                'lat' => $rastreo ? $rastreo->latitud  : $tiendaLat,
                'lng' => $rastreo ? $rastreo->longitud : $tiendaLng,
            ],
        ];

        return Inertia::render('Pedidos/show', [
            'pedido'   => $pedido,
            'detalles' => $detalles,
            'route'    => $route,
        ]);
    }

    /**
     * Actualizar estado del pedido (propietario/delivery)
     * ★ IMPORTANTE: cambio de estado es auditable
     */
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $data = $request->validate([
            'estado_produccion' => 'nullable|in:pending,assigned,on_route,delivered,completed',
        ]);

        $estadoAnterior = $pedido->estado_produccion;

        if (isset($data['estado_produccion'])) {
            $pedido->estado_produccion = $data['estado_produccion'];
            $pedido->save();
        }

        BitacoraService::accionCrud(
            modulo: 'Pedidos',
            accion: 'Actualizar registro',
            registroId: $pedido->id,
            exitoso: true,
            detalle: 'Estado: ' . $estadoAnterior . ' → ' . ($data['estado_produccion'] ?? $estadoAnterior),
        );

        return redirect()->back()->with('success', 'Estado actualizado');
    }

    /**
     * Acción rápida para avanzar al siguiente estado
     * ★ IMPORTANTE: cambio de estado es auditable
     */
    public function quickComplete($id)
    {
        $pedido  = Pedido::findOrFail($id);
        $estados = ['pending', 'assigned', 'on_route', 'delivered', 'completed'];
        $idx     = array_search($pedido->estado_produccion, $estados);

        $estadoAnterior = $pedido->estado_produccion;
        $avanzó         = false;

        if ($idx !== false && $idx < count($estados) - 1) {
            $pedido->estado_produccion = $estados[$idx + 1];
            $pedido->save();
            $avanzó = true;
        }

        BitacoraService::accionCrud(
            modulo: 'Pedidos',
            accion: 'Avance rápido de estado',
            registroId: $pedido->id,
            exitoso: $avanzó,
            detalle: $estadoAnterior . ' → ' . $pedido->estado_produccion,
        );

        return redirect()->back()->with('success', 'Estado avanzado');
    }

    /**
     * Vista del cliente - sus pedidos
     * Se registra: permite saber qué clientes usan activamente el sistema
     */
    public function clienteIndex()
    {
        $user      = auth()->user();
        $clienteId = $user->cliente->id ?? null;

        BitacoraService::accesoModulo('Mis Pedidos', 'Listado');

        if (!$clienteId) {
            return Inertia::render('Pedidos/Cliente/index', [
                'enCurso'    => [],
                'realizados' => [],
            ]);
        }

        $pedidos = Pedido::with(['detalles.producto'])
            ->where('cliente_id', $clienteId)
            ->orderBy('created_at', 'desc')
            ->get();

        $enCurso    = $pedidos->filter(fn($p) => !in_array($p->estado_produccion, ['delivered', 'completed']));
        $realizados = $pedidos->filter(fn($p) =>  in_array($p->estado_produccion, ['delivered', 'completed']));

        return Inertia::render('Pedidos/Cliente/index', [
            'enCurso'    => $enCurso->values(),
            'realizados' => $realizados->values(),
        ]);
    }

    /**
     * Vista del cliente - detalle de su pedido
     * ★ IMPORTANTE: registrar acceso denegado si intenta ver pedido ajeno
     */
    public function clienteShow($id)
    {
        $user   = auth()->user();
        $pedido = Pedido::with(['cliente', 'detalles.producto'])->findOrFail($id);

        if ($pedido->cliente_id !== ($user->cliente->id ?? null)) {
            BitacoraService::accesoDenegado('Mis Pedidos');
            abort(403);
        }

        return Inertia::render('Pedidos/Cliente/EnCurso', [
            'pedido'   => $pedido,
            'detalles' => $pedido->detalles,
        ]);
    }

    /**
     * Cliente marca pedido como recibido
     * ★ IMPORTANTE: confirmación de entrega es auditable
     */
    public function clienteRecibido($id)
    {
        $user   = auth()->user();
        $pedido = Pedido::findOrFail($id);

        if ($pedido->cliente_id !== ($user->cliente->id ?? null)) {
            BitacoraService::accesoDenegado('Mis Pedidos');
            abort(403);
        }

        $pedido->estado_produccion = 'completed';
        $pedido->save();

        BitacoraService::accionCrud(
            modulo: 'Mis Pedidos',
            accion: 'Confirmar recepción',
            registroId: $pedido->id,
            exitoso: true,
            detalle: 'Cliente confirmó recepción del pedido',
        );

        return redirect()->route('cliente.pedidos.index')
            ->with('success', 'Pedido marcado como recibido');
    }
}
