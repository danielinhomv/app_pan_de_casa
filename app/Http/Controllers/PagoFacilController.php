<?php

namespace App\Http\Controllers;

use App\Models\CuentaCobro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Venta;
use App\Models\Pagos;
use App\Models\Pedido;
use App\Services\BitacoraService;
use Inertia\Inertia;
use GuzzleHttp\Client;

class PagoFacilController extends Controller
{
    private const PAYMENT_STATUS_PENDING   = 0;
    private const PAYMENT_STATUS_COMPLETED = 2;
    private const PAYMENT_STATUS_REJECTED  = 3;

    //verificar si esta en desarrollo o local
    private function getClient(): Client
    {
        return new Client([
            'verify' => app()->isProduction(), // false en local, true en prod
        ]);
    }

    /**
     * Generar QR para pago
     * ★ IMPORTANTE: inicio de transacción de dinero — siempre auditable
     */
    public function generarQR(Request $request)
    {
        try {
            Log::info('Inicio generarQR', ['request' => $request->all()]);

            $request->validate([
                'venta_id' => 'required|exists:ventas,id',
                'pago_id'  => 'required|exists:pagos,id',  // ← cuota específica
                'metodo_pago' => 'required|in:qr,tigo_money',
            ]);

            // Verificar que el pago pertenece a la venta y está pendiente
            $pago = Pagos::where('id', $request->pago_id)
                ->where('venta_id', $request->venta_id)
                ->where('estado', 'pendiente')
                ->firstOrFail();

            $venta = Venta::with(['pedido.cliente', 'pedido.detalles.producto'])
                ->findOrFail($request->venta_id);

            $tokenResponse = $this->obtenerToken();

            if (!isset($tokenResponse['values']['accessToken'])) {
                BitacoraService::accionCrud(
                    modulo: 'PagoFácil',
                    accion: 'Generar QR',
                    registroId: $venta->id,
                    exitoso: false,
                    detalle: 'No se pudo obtener token',
                );
                return response()->json(['success' => false, 'message' => 'No se pudo obtener token'], 500);
            }

            $accessToken = $tokenResponse['values']['accessToken'];
            $datosCuota  = $pago->datos_pago ?? [];
            $numeroCuota = $datosCuota['numero_cuota'] ?? 1;
            $totalCuotas = $datosCuota['total_cuotas'] ?? 1;

            // El nroPago identifica la cuota específica
            $nroPago = "venta-" . $venta->id . "-cuota-" . $numeroCuota . "-" . time();

            $body = [
                'paymentMethod' => config('pagofacil.payment_method'),
                'clientName'    => $venta->pedido->cliente->nombre ?? 'Cliente',
                'documentType'  => 1,
                'documentId'    => (string)($request->ci_nit ?? "0"),
                'phoneNumber'   => (string)($request->telefono ?? "0"),
                'email'         => $venta->pedido->cliente->email ?? '',
                'paymentNumber' => $nroPago,
                'amount'        => (float) $pago->monto,   // ← monto de la cuota, no el total
                'currency'      => 2,
                'clientCode'    => (string)$venta->pedido->cliente->id,
                'callbackUrl'   => config('pagofacil.callback_url'),
                'orderDetail'   => $this->formatearDetallesPedido($venta),
            ];

            $client   = $this->getClient();
            $url      = config('pagofacil.base_url') . '/generate-qr';
            $response = $client->post($url, [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json'    => $body,
                'timeout' => config('pagofacil.timeout', 30),
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($result['values'])) {
                BitacoraService::accionCrud(
                    modulo: 'PagoFácil',
                    accion: 'Generar QR',
                    registroId: $venta->id,
                    exitoso: false,
                    detalle: 'Respuesta inválida de pasarela',
                );
                return response()->json(['success' => false, 'message' => 'Respuesta inválida del servicio'], 500);
            }

            $values        = $result['values'];
            $qrBase64      = $values['qrBase64']      ?? null;
            $transactionId = $values['transactionId'] ?? null;

            if (!$qrBase64 || !$transactionId) {
                BitacoraService::accionCrud(
                    modulo: 'PagoFácil',
                    accion: 'Generar QR',
                    registroId: $venta->id,
                    exitoso: false,
                    detalle: 'QR o transactionId ausente',
                );
                return response()->json(['success' => false, 'message' => 'Error al obtener datos del QR'], 500);
            }

            // Actualizar el pago existente con la referencia de PagoFácil
            $pago->update([
                'referencia_externa' => $nroPago,
                'transaction_id'     => $transactionId,
                'datos_pago'         => array_merge($datosCuota, [
                    'qr_generado_at' => now(),
                    'pf_response'    => $result,
                ]),
            ]);

            BitacoraService::accionCrud(
                modulo: 'PagoFácil',
                accion: 'Generar QR',
                registroId: $pago->id,
                exitoso: true,
                detalle: 'Venta #' . $venta->id
                    . ' | Cuota ' . $numeroCuota . '/' . $totalCuotas
                    . ' | Monto: ' . $pago->monto . ' BOB'
                    . ' | Tx: ' . $transactionId,
            );

            return response()->json([
                'success'        => true,
                'qr_image'       => 'data:image/png;base64,' . $qrBase64,
                'transaction_id' => $transactionId,
                'nro_pago'       => $nroPago,
                'pago_id'        => $pago->id,
                'numero_cuota'   => $numeroCuota,
                'total_cuotas'   => $totalCuotas,
                'monto_cuota'    => $pago->monto,
            ]);
        } catch (\Throwable $th) {
            BitacoraService::accionCrud(
                modulo: 'PagoFácil',
                accion: 'Generar QR',
                exitoso: false,
                detalle: 'Excepción: ' . $th->getMessage(),
            );
            Log::error('Error en generarQR', ['error' => $th->getMessage(), 'line' => $th->getLine()]);
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Consultar estado del pago
     * No se registra en bitácora: es un polling automático del frontend,
     * no una acción del usuario. Solo se registra el resultado final en callback.
     */
    public function consultarEstado(Request $request)
    {
        set_time_limit(120);

        try {
            $transactionId = $request->input('transaction_id');

            if (!$transactionId) {
                return response()->json(['success' => false, 'message' => 'Transaction ID es requerido'], 400);
            }

            Log::info('Consultando estado de transacción', ['transaction_id' => $transactionId]);

            try {
                $tokenResponse = $this->obtenerToken();
            } catch (\Exception $e) {
                Log::error('Fallo al obtener token en consultarEstado', ['error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Error de conexión con pasarela'], 500);
            }

            if (!isset($tokenResponse['values']['accessToken'])) {
                return response()->json(['success' => false, 'message' => 'No se pudo autenticar con PagoFácil'], 500);
            }

            $accessToken = $tokenResponse['values']['accessToken'];
            $client   = $this->getClient();

            $response = $client->post(config('pagofacil.base_url') . '/query-transaction', [
                'headers'         => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json'            => ['pagofacilTransactionId' => (int)$transactionId],
                'http_errors'     => false,
                'timeout'         => 90,
                'connect_timeout' => 10,
            ]);

            $responseContent = $response->getBody()->getContents();
            $result          = json_decode($responseContent, true);

            Log::info('Respuesta cruda consultarEstado', [
                'status_code' => $response->getStatusCode(),
                'content'     => $result,
            ]);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['success' => false, 'message' => 'Respuesta inválida del proveedor'], 500);
            }

            if (isset($result['error']) && $result['error'] != 0) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Error en la transacción',
                ], 400);
            }

            if (!isset($result['values'])) {
                return response()->json(['success' => false, 'message' => 'Datos no encontrados'], 404);
            }

            $values = $result['values'];

            return response()->json([
                'success' => true,
                'data'    => [
                    'pagofacilTransactionId'   => $values['pagofacilTransactionId']   ?? null,
                    'companyTransactionId'      => $values['companyTransactionId']      ?? null,
                    'paymentStatus'             => $values['paymentStatus']             ?? null,
                    'paymentDate'               => $values['paymentDate']               ?? null,
                    'paymentTime'               => $values['paymentTime']               ?? null,
                    'paymentStatusDescription'  => $values['paymentStatusDescription']  ?? '',
                ],
                'message' => $result['message'] ?? 'Consulta realizada',
            ]);
        } catch (\Exception $e) {
            Log::error('Excepción crítica en consultarEstado', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);
            return response()->json(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Callback para notificaciones de Pago Fácil
     * ★ MUY IMPORTANTE: confirmación real de pago — siempre auditable
     * No hay usuario autenticado aquí (viene de PagoFácil), se usa user_id = null
     */
    public function callback(Request $request)
    {
        try {
            Log::info('Callback recibido de Pago Fácil', ['data' => $request->all()]);

            $pedidoId   = $request->input('PedidoID');
            $fecha      = $request->input('Fecha');
            $hora       = $request->input('Hora');
            $metodoPago = $request->input('MetodoPago');
            $estado     = $request->input('Estado');

            if (!$pedidoId) {
                Log::error('Callback sin PedidoID', ['data' => $request->all()]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => "PedidoID es requerido",
                    'values' => false,
                ]);
            }

            // 1. Intentar buscar el pago directamente por su referencia externa
            $pago = Pagos::where('referencia_externa', $pedidoId)->first();

            // 2. Si no se encuentra, aplicar el fallback por Regex
            if (!$pago) {
                if (preg_match('/^venta-(\d+)(?:-cuota-\d+)?-\d+$/', $pedidoId, $matches)) {
                    $ventaId = $matches[1];

                    $pagoAlternativo = Pagos::where('venta_id', $ventaId)
                        ->where('estado', 'pendiente')
                        ->orderBy('id', 'asc')
                        ->first();

                    if ($pagoAlternativo) {
                        $pago = $pagoAlternativo;
                        // Lo vinculamos inmediatamente con la referencia que nos envió PagoFácil
                        $pago->update(['referencia_externa' => $pedidoId]);
                    }
                }

                if (!$pago) {
                    BitacoraService::registrar('accion_crud', [
                        'modulo'  => 'PagoFácil',
                        'accion'  => 'Callback recibido',
                        'exitoso' => false,
                        'detalle' => 'Pago no encontrado | PedidoID: ' . $pedidoId,
                    ]);

                    return response()->json([
                        'error' => 1,
                        'status' => 0,
                        'message' => "Pago no encontrado en el sistema",
                        'values' => false,
                    ]);
                }
            }

            $estadoInterno  = $this->mapearEstadoPago($estado);
            $estadoAnterior = $pago->estado;
            $datosPagoActual = is_array($pago->datos_pago) ? $pago->datos_pago : [];

            // 🚀 CRÍTICO: Envolver toda la actualización en una transacción DB
            \DB::beginTransaction();

            $pago->update([
                'estado'     => $estadoInterno,
                'fecha_pago' => now(),
                'datos_pago' => array_merge($datosPagoActual, [
                    'callback_data'         => $request->all(),
                    'fecha_callback'        => now(),
                    'metodo_pago_pagofacil' => $metodoPago,
                    'fecha_pago_pagofacil'  => $fecha,
                    'hora_pago_pagofacil'   => $hora,
                ]),
            ]);

            if ($estadoInterno === 'completado') {
                // Descontamos el saldo en la cuenta de cobro
                $this->descontarCuentaCobro($pago);

                // Verificamos si ya es hora de dar por completado el Pedido entero
                $this->actualizarEstadoPedidoSiCorresponde($pago);
            }

            \DB::commit();

            BitacoraService::registrar('accion_crud', [
                'modulo'      => 'PagoFácil',
                'accion'      => 'Callback — pago ' . $estadoInterno,
                'registro_id' => $pago->id,
                'exitoso'     => $estadoInterno === 'completado',
                'detalle'     => 'PedidoID: ' . $pedidoId
                    . ' | Estado: ' . $estadoAnterior . ' → ' . $estadoInterno
                    . ' | Monto: ' . $pago->monto . ' BOB',
            ]);

            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => "Pago procesado correctamente",
                'values' => true,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error en callback de PagoFácil', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => "No se pudo procesar el pago debido a un error interno",
                'values' => false,
            ]);
        }
    }

    // ── Métodos privados (sin cambios) ────────────────────────────────────────

    private function isPaidStatus($status)
    {
        if ($status === null || $status === '') return false;

        $statusInt = (int)$status;
        $statusStr = strtolower((string)$status);

        if (in_array($statusInt, [1, 2, 5])) return true;
        if (in_array($statusStr, ['paid', 'completado', 'procesado', 'approved', 'pagado'])) return true;

        return false;
    }

    private function mapearEstadoPago($estado)
    {
        $estadoLower = strtolower((string)$estado);

        if (
            $estadoLower === 'completado' || $estadoLower === 'pagado' ||
            $estado === '1' || $estado === 1 ||
            $estado === self::PAYMENT_STATUS_COMPLETED ||
            str_contains($estadoLower, 'procesado')
        ) {
            return 'completado';
        }

        if (
            $estadoLower === 'rechazado' || $estadoLower === 'cancelado' ||
            $estado === '3' || $estado === 3 ||
            $estado === self::PAYMENT_STATUS_REJECTED
        ) {
            return 'rechazado';
        }

        return 'pendiente';
    }

    private function obtenerToken()
    {
        try {

            $client   = $this->getClient();

            $response = $client->post(config('pagofacil.base_url') . '/login', [
                'headers' => [
                    'Accept'         => 'application/json',
                    'tcTokenService' => config('pagofacil.token_service'),
                    'tcTokenSecret'  => config('pagofacil.token_secret'),
                ],
                'timeout' => config('pagofacil.timeout', 30),
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Error al obtener token de Pago Fácil', ['error' => $e->getMessage()]);
            throw new \Exception("Error al obtener el token: " . $e->getMessage());
        }
    }

    private function formatearDetallesPedido($venta)
    {
        $detalles = [];
        foreach ($venta->pedido->detalles as $detalle) {
            $detalles[] = [
                'serial'   => $detalle->id,
                'product'  => $detalle->producto->nombre,
                'quantity' => $detalle->cantidad,
                'price'    => $detalle->precio_unitario,
                'discount' => 0,
                'total'    => $detalle->cantidad * $detalle->precio_unitario,
            ];
        }
        return $detalles;
    }

    // ★ Solo completa el pedido cuando todas las cuotas estén pagadas
    private function actualizarEstadoPedidoSiCorresponde(Pagos $pago): void
    {
        try {
            // Verificar si quedan pagos pendientes para esta venta
            $pagosPendientes = Pagos::where('venta_id', $pago->venta_id)
                ->where('id', '!=', $pago->id) // 👈 Forzar exclusión del actual
                ->where('estado', 'pendiente')
                ->count();

            if ($pagosPendientes > 0) {
                Log::info('Pedido no completado aún — quedan cuotas pendientes', [
                    'venta_id'         => $pago->venta_id,
                    'pendientes'       => $pagosPendientes,
                ]);
                return;
            }

            // También verificar por CuentaCobro si existe
            $cuentaCobro = CuentaCobro::where('venta_id', $pago->venta_id)->first();
            if ($cuentaCobro && $cuentaCobro->saldo_pendiente > 0) {
                Log::info('Pedido no completado — saldo_pendiente aún mayor a 0', [
                    'venta_id'        => $pago->venta_id,
                    'saldo_pendiente' => $cuentaCobro->saldo_pendiente,
                ]);
                return;
            }

            $venta = Venta::with('pedido')->find($pago->venta_id);

            if (!$venta || !$venta->pedido) {
                Log::warning('No se encontró venta o pedido', [
                    'pago_id'  => $pago->id,
                    'venta_id' => $pago->venta_id,
                ]);
                return;
            }

            $venta->pedido->update(['estado' => 'COMPLETADO']);

            Log::info('Pedido marcado como COMPLETADO — todas las cuotas pagadas', [
                'pedido_id' => $venta->pedido->id,
                'venta_id'  => $venta->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado del pedido', [
                'pago_id' => $pago->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
    private function descontarCuentaCobro(Pagos $pago): void
    {
        try {
            $cuentaCobro = CuentaCobro::where('venta_id', $pago->venta_id)->first();

            if (!$cuentaCobro) {
                Log::info('Sin CuentaCobro — pago al contado', ['venta_id' => $pago->venta_id]);
                return;
            }

            $saldoAnterior = $cuentaCobro->saldo_pendiente; // ← guardar ANTES de update
            $nuevoSaldo    = max(0, $saldoAnterior - $pago->monto);

            $cuentaCobro->update(['saldo_pendiente' => $nuevoSaldo]);

            Log::info('CuentaCobro actualizada', [
                'venta_id'       => $pago->venta_id,
                'descontado'     => $pago->monto,
                'saldo_anterior' => $saldoAnterior,
                'saldo_nuevo'    => $nuevoSaldo,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al descontar CuentaCobro', [
                'pago_id' => $pago->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
