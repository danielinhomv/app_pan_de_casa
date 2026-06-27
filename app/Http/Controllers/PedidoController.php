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
     * 🔒 INTACTO
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
     * 🔒 INTACTO
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
     * 🔒 INTACTO
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
     * 🔒 INTACTO
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
     * 🛠️ CONTROL DE CONTADO AÑADIDO
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

        $pedidos = Pedido::with(['detalles.producto', 'venta.pagos', 'venta.cuentaCobro'])
            ->where('cliente_id', $clienteId)
            ->orderBy('created_at', 'desc')
            ->get();

        $pedidosProcesados = $pedidos->map(function ($pedido) {
            $venta = $pedido->venta;

            if ($venta) {
                // Sumamos los montos de los pagos completados
                $montoPagado = $venta->pagos->where('estado', 'completado')->sum('monto');
                
                // Determinar el Modo de Pago (Contado vs Crédito/Cuotas)
                $esContado = strtolower($venta->modo_pago) === 'contado';

                if ($esContado) {
                    // Si es al contado, no hay saldo pendiente real (o se pagó completo o falta impactar el QR único)
                    $saldoPendiente = $montoPagado > 0 ? 0 : (float) $venta->total;
                    $estadoPago = $montoPagado > 0 ? 'pagado' : 'pendiente';
                } else {
                    // Si es al crédito/cuotas, nos basamos estrictamente en la cuenta de cobro
                    $saldoPendiente = $venta->cuentaCobro 
                        ? (float) $venta->cuentaCobro->saldo_pendiente 
                        : max(0, (float) $venta->total - $montoPagado);

                    if ($saldoPendiente <= 0 && $venta->pagos->count() > 0) {
                        $estadoPago = 'pagado';
                    } elseif ($montoPagado > 0 && $saldoPendiente > 0) {
                        $estadoPago = 'parcial';
                    } else {
                        $estadoPago = 'pendiente';
                    }
                }
                
                $tipoPago = $venta->tipo_pago;
                $modoPago = $venta->modo_pago;
            } else {
                $montoPagado = 0;
                $saldoPendiente = (float) $pedido->total;
                $estadoPago = 'pendiente';
                $tipoPago = 'No definido';
                $modoPago = 'No definido';
            }

            $pedido->monto_pagado    = $montoPagado;
            $pedido->saldo_pendiente = $saldoPendiente;
            $pedido->estado_pago     = $estadoPago;
            $pedido->tipo_pago       = $tipoPago;
            $pedido->modo_pago       = $modoPago;

            return $pedido;
        });

        $enCurso    = $pedidosProcesados->filter(fn($p) => !in_array($p->estado_produccion, ['delivered', 'completed']));
        $realizados = $pedidosProcesados->filter(fn($p) =>  in_array($p->estado_produccion, ['delivered', 'completed']));

        return Inertia::render('Pedidos/Cliente/index', [
            'enCurso'    => $enCurso->values(),
            'realizados' => $realizados->values(),
        ]);
    }

    /**
     * Vista del cliente - detalle de su pedido
     * 🛠️ CONTROL DE CONTADO AÑADIDO
     */
    public function clienteShow($id)
    {
        $user   = auth()->user();
        $pedido = Pedido::with(['cliente', 'detalles.producto', 'venta.pagos', 'venta.cuentaCobro'])->findOrFail($id);

        if ($pedido->cliente_id !== ($user->cliente->id ?? null)) {
            BitacoraService::accesoDenegado('Mis Pedidos');
            abort(403);
        }

        $venta = $pedido->venta;
        if ($venta) {
            $montoPagado = $venta->pagos->where('estado', 'completado')->sum('monto');
            $esContado   = strtolower($venta->modo_pago) === 'contado';

            if ($esContado) {
                $saldoPendiente = $montoPagado > 0 ? 0 : (float) $venta->total;
                $estadoPago     = $montoPagado > 0 ? 'pagado' : 'pendiente';
            } else {
                $saldoPendiente = $venta->cuentaCobro ? (float) $venta->cuentaCobro->saldo_pendiente : max(0, (float) $venta->total - $montoPagado);
                $estadoPago     = $saldoPendiente <= 0 ? 'pagado' : ($montoPagado > 0 ? 'parcial' : 'pendiente');
            }
            
            $historialPagos = $venta->pagos;
        } else {
            $montoPagado    = 0;
            $saldoPendiente = (float) $pedido->total;
            $historialPagos = [];
            $estadoPago     = 'pendiente';
        }

        $pedido->monto_pagado    = $montoPagado;
        $pedido->saldo_pendiente = $saldoPendiente;
        $pedido->estado_pago     = $estadoPago;

        return Inertia::render('Pedidos/Cliente/EnCurso', [
            'pedido'   => $pedido,
            'detalles' => $pedido->detalles,
            'pagos'    => $historialPagos,
        ]);
    }

    /**
     * Cliente marca pedido como recibido
     * 🔒 INTACTO
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