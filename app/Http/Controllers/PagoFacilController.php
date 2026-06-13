<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Venta;
use App\Models\Pagos;
use App\Models\Pedido;
use Inertia\Inertia;
use GuzzleHttp\Client;

class PagoFacilController extends Controller
{
    /**
     * Estados de pago según PagoFácil
     */
    private const PAYMENT_STATUS_PENDING = 0;
    private const PAYMENT_STATUS_COMPLETED = 2;
    private const PAYMENT_STATUS_REJECTED = 3;

    /**
     * Generar QR para pago
     */
    public function generarQR(Request $request)
    {
        try {
            Log::info('Inicio del método generarQR', ['request' => $request->all()]);

            $request->validate([
                'venta_id' => 'required|exists:ventas,id',
                'metodo_pago' => 'required|in:qr,tigo_money'
            ]);

            $venta = Venta::with(['pedido.cliente', 'pedido.detalles.producto'])->findOrFail($request->venta_id);
            Log::info('Venta encontrada', ['venta_id' => $venta->id]);

            // Obtener token de autenticación
            $tokenResponse = $this->obtenerToken();
            Log::info('Token obtenido', ['has_token' => isset($tokenResponse['values']['accessToken'])]);

            if (!isset($tokenResponse['values']['accessToken'])) {
                Log::error('No se pudo obtener un token válido', ['response' => $tokenResponse]);
                return response()->json(['success' => false, 'message' => 'No se pudo obtener un token válido'], 500);
            }

            $accessToken = $tokenResponse['values']['accessToken'];

            // Preparar datos del pedido
            $pedidoDetalle = $this->formatearDetallesPedido($venta);
            $nroPago = "venta-" . $venta->id . "-" . time();

            $body = [
                "paymentMethod" => 4, // 4 = QR
                "clientName" => $venta->pedido->cliente->nombre ?? 'Cliente',
                "documentType" => 1,
                "documentId" => (string)($request->ci_nit ?? "0"),
                "phoneNumber" => (string)($request->telefono ?? "0"),
                "email" => $venta->pedido->cliente->email ?? '',
                "paymentNumber" => $nroPago,
                "amount" => (float)$venta->total,
                "currency" => 2, // BOB
                "clientCode" => (string)$venta->pedido->cliente->id,
                "callbackUrl" => config('pagofacil.callback_url'),
                "orderDetail" => $pedidoDetalle,
            ];

            Log::info('Cuerpo de la solicitud generado', ['body' => $body]);

            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken
            ];

            $client = new Client();
            $url = config('pagofacil.base_url') . '/generate-qr';
            Log::info('Enviando solicitud a PagoFácil', ['url' => $url]);

            $response = $client->post($url, [
                'headers' => $headers,
                'json' => $body,
                'timeout' => config('pagofacil.timeout', 30)
            ]);

            $responseContent = $response->getBody()->getContents();
            Log::info('Contenido crudo de la respuesta', ['response_length' => strlen($responseContent)]);

            $result = json_decode($responseContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error al decodificar JSON', [
                    'error_message' => json_last_error_msg(),
                    'response_content' => substr($responseContent, 0, 500)
                ]);
                return response()->json(['success' => false, 'message' => 'Error al procesar la respuesta del servicio'], 500);
            }

            if (!isset($result['values'])) {
                Log::error('El campo values no está presente en la respuesta', ['result' => $result]);
                return response()->json(['success' => false, 'message' => 'Respuesta inesperada del servicio'], 500);
            }

            $values = $result['values'];
            $qrBase64 = $values['qrBase64'] ?? null;
            $transactionId = $values['transactionId'] ?? null;

            if (!$qrBase64 || !$transactionId) {
                Log::error('No se encontraron qrBase64 o transactionId en la respuesta', [
                    'values_keys' => array_keys((array)$values),
                    'qrBase64_encontrado' => !is_null($qrBase64),
                    'transactionId_encontrado' => !is_null($transactionId),
                ]);
                return response()->json(['success' => false, 'message' => 'Error al obtener los datos del QR'], 500);
            }

            $qrImageBase64 = "data:image/png;base64," . $qrBase64;

            // Crear registro de pago pendiente
            $pago = Pagos::create([
                'venta_id' => $venta->id,
                'monto' => $venta->total,
                'fecha' => now(),
                'metodo_pago' => 'PAGO_FACIL',
                'estado' => 'pendiente',
                'referencia_externa' => $nroPago,
                'transaction_id' => $transactionId,
                'datos_pago' => $result
            ]);

            Log::info('QR generado correctamente', [
                'pago_id' => $pago->id,
                'transaction_id' => $transactionId,
                'venta_id' => $venta->id
            ]);

            return response()->json([
                'success' => true,
                'qr_image' => $qrImageBase64,
                'transaction_id' => $transactionId,
                'nro_pago' => $nroPago,
                'pago_id' => $pago->id
            ]);
        } catch (\Throwable $th) {
            Log::error('Error en generarQR', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Consultar estado del pago
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

            // 1. Obtener token con manejo de errores
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
            $client = new Client();

            // 2. Realizar la petición con http_errors => false para evitar excepciones fatales
            $response = $client->post(config('pagofacil.base_url') . '/query-transaction', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'json' => [
                    'pagofacilTransactionId' => (int)$transactionId
                ],
                'http_errors' => false,
                'timeout' => 90,
                'connect_timeout' => 10
            ]);

            $responseContent = $response->getBody()->getContents();
            $result = json_decode($responseContent, true);

            Log::info('Respuesta cruda consultarEstado', [
                'status_code' => $response->getStatusCode(),
                'content' => $result
            ]);

            // 3. Validar si la respuesta es válida (JSON mal formado o null)
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON inválido en consultarEstado', ['error' => json_last_error_msg()]);
                return response()->json(['success' => false, 'message' => 'Respuesta inválida del proveedor'], 500);
            }

            // 4. Validar errores lógicos de la API
            if (isset($result['error']) && $result['error'] != 0) {
                Log::warning('Error en respuesta de PagoFácil', ['error' => $result['error'], 'message' => $result['message'] ?? '']);
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Error en la transacción'
                ], 400);
            }

            if (!isset($result['values'])) {
                Log::error('No hay values en respuesta', ['result' => $result]);
                return response()->json(['success' => false, 'message' => 'Datos no encontrados'], 404);
            }

            $values = $result['values'];

            Log::info('Datos de transacción recibidos', [
                'paymentStatus' => $values['paymentStatus'] ?? null,
                'paymentDate' => $values['paymentDate'] ?? null,
                'paymentTime' => $values['paymentTime'] ?? null
            ]);

            // 5. Retornar datos seguros (usando null coalescing operator ??)
            return response()->json([
                'success' => true,
                'data' => [
                    'pagofacilTransactionId' => $values['pagofacilTransactionId'] ?? null,
                    'companyTransactionId' => $values['companyTransactionId'] ?? null,
                    'paymentStatus' => $values['paymentStatus'] ?? null,
                    'paymentDate' => $values['paymentDate'] ?? null,
                    'paymentTime' => $values['paymentTime'] ?? null,
                    'paymentStatusDescription' => $values['paymentStatusDescription'] ?? ''
                ],
                'message' => $result['message'] ?? 'Consulta realizada'
            ]);
        } catch (\Exception $e) {
            Log::error('Excepción crítica en consultarEstado', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Callback para notificaciones de Pago Fácil
     */
    public function callback(Request $request)
    {
        try {
            Log::info('Callback recibido de Pago Fácil', ['data' => $request->all()]);

            $pedidoId = $request->input('PedidoID');
            $fecha = $request->input('Fecha');
            $hora = $request->input('Hora');
            $metodoPago = $request->input('MetodoPago');
            $estado = $request->input('Estado');

            if (!$pedidoId) {
                Log::error('Callback sin PedidoID', ['data' => $request->all()]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => "PedidoID es requerido",
                    'values' => false
                ]);
            }

            Log::info('Buscando pago con referencia externa', ['pedido_id' => $pedidoId]);

            $pago = Pagos::where('referencia_externa', $pedidoId)->first();

            if (!$pago) {
                // Intentar extraer el ID de venta del PedidoID (formato: venta-{id}-{timestamp})
                if (preg_match('/^venta-(\d+)-\d+$/', $pedidoId, $matches)) {
                    $ventaId = $matches[1];
                    Log::info('ID de venta extraído del PedidoID', ['venta_id' => $ventaId]);

                    $pagoAlternativo = Pagos::where('venta_id', $ventaId)
                        ->where('estado', 'pendiente')
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($pagoAlternativo) {
                        Log::warning('Pago encontrado con método alternativo', [
                            'pago_id' => $pagoAlternativo->id,
                            'referencia_original' => $pagoAlternativo->referencia_externa,
                            'referencia_callback' => $pedidoId
                        ]);
                        $pago = $pagoAlternativo;
                        $pago->update(['referencia_externa' => $pedidoId]);
                    }
                }

                if (!$pago) {
                    return response()->json([
                        'error' => 1,
                        'status' => 0,
                        'message' => "Pago no encontrado en el sistema",
                        'values' => false
                    ]);
                }
            }

            // Mapear estado del pago
            $estadoInterno = $this->mapearEstadoPago($estado);

            Log::info('Estado mapeado', [
                'estado_pagofacil' => $estado,
                'estado_interno' => $estadoInterno
            ]);

            // Actualizar el pago
            $pago->update([
                'estado' => $estadoInterno,
                'fecha_pago' => now(),
                'datos_pago' => [
                    'callback_data' => $request->all(),
                    'fecha_callback' => now(),
                    'metodo_pago_pagofacil' => $metodoPago,
                    'fecha_pago_pagofacil' => $fecha,
                    'hora_pago_pagofacil' => $hora
                ]
            ]);

            // Si el pago fue completado, actualizar el estado del pedido
            if ($estadoInterno === 'completado') {
                $this->actualizarEstadoPedido($pago);
            }

            Log::info('Pago actualizado exitosamente desde callback', [
                'pago_id' => $pago->id,
                'pedido_id' => $pedidoId,
                'estado_nuevo' => $estadoInterno,
            ]);

            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => "Pago procesado correctamente",
                'values' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Error en callback de PagoFácil', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => "No se pudo procesar el pago",
                'values' => false
            ]);
        }
    }

    /**
     * Verificar si el estado indica que el pago fue completado
     * PagoFácil devuelve estados numéricos:
     * 0 = Pendiente
     * 1 = Pagado/Completado
     * 2 = Pagado/Completado (alternativo)
     * 3 = Rechazado
     * 5 = Pagado/Completado (alternativo)
     */
    private function isPaidStatus($status)
    {
        if ($status === null || $status === '') {
            return false;
        }

        $statusInt = (int)$status;
        $statusStr = strtolower((string)$status);

        // Estados que indican pago completado según PagoFácil
        // Los estados 1, 2, 5 indican que el pago fue completado
        if (in_array($statusInt, [1, 2, 5])) {
            return true;
        }

        // También validar por texto
        if (in_array($statusStr, ['paid', 'completado', 'procesado', 'approved', 'pagado'])) {
            return true;
        }

        return false;
    }

    /**
     * Mapear estado de PagoFácil a estado interno
     */
    private function mapearEstadoPago($estado)
    {
        $estadoLower = strtolower((string)$estado);

        // Estados completados
        if (
            $estadoLower === 'completado' ||
            $estadoLower === 'pagado' ||
            $estado === '1' ||
            $estado === 1 ||
            $estado === self::PAYMENT_STATUS_COMPLETED ||
            str_contains($estadoLower, 'procesado')
        ) {
            return 'completado';
        }

        // Estados rechazados
        if (
            $estadoLower === 'rechazado' ||
            $estadoLower === 'cancelado' ||
            $estado === '3' ||
            $estado === 3 ||
            $estado === self::PAYMENT_STATUS_REJECTED
        ) {
            return 'rechazado';
        }

        return 'pendiente';
    }

    /**
     * Obtener token de autenticación de Pago Fácil
     */
    private function obtenerToken()
    {
        try {
            $client = new Client();

            $response = $client->post(config('pagofacil.base_url') . '/login', [
                'headers' => [
                    'Accept' => 'application/json',
                    'tcTokenService' => config('pagofacil.token_service'),
                    'tcTokenSecret' => config('pagofacil.token_secret')
                ],
                'timeout' => config('pagofacil.timeout', 30)
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (config('pagofacil.enable_logs')) {
                Log::info('Token obtenido de Pago Fácil');
            }

            return $result;
        } catch (\Exception $e) {
            if (config('pagofacil.enable_logs')) {
                Log::error('Error al obtener token de Pago Fácil', [
                    'error' => $e->getMessage(),
                ]);
            }
            throw new \Exception("Error al obtener el token: " . $e->getMessage());
        }
    }

    /**
     * Formatear detalles del pedido para Pago Fácil
     */
    private function formatearDetallesPedido($venta)
    {
        $detalles = [];

        foreach ($venta->pedido->detalles as $detalle) {
            $detalles[] = [
                'serial' => $detalle->id,
                'product' => $detalle->producto->nombre,
                'quantity' => $detalle->cantidad,
                'price' => $detalle->precio_unitario,
                'discount' => 0,
                'total' => $detalle->cantidad * $detalle->precio_unitario
            ];
        }

        return $detalles;
    }

    /**
     * Actualizar el estado del pedido cuando se completa un pago
     */
    private function actualizarEstadoPedido($pago)
    {
        try {
            $venta = Venta::with('pedido')->find($pago->venta_id);

            if (!$venta || !$venta->pedido) {
                Log::warning('No se encontró venta o pedido asociado al pago', [
                    'pago_id' => $pago->id,
                    'venta_id' => $pago->venta_id
                ]);
                return false;
            }

            // Actualizar el estado del pedido a COMPLETADO
            $venta->pedido->update(['estado' => 'COMPLETADO']);

            Log::info('Pedido actualizado como COMPLETADO', [
                'pedido_id' => $venta->pedido->id,
                'venta_id' => $venta->id,
                'pago_id' => $pago->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado del pedido', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
