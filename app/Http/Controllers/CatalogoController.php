<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\CuentaCobro;
use App\Models\Pagos;
use App\Models\MovimientoProducto; // ← AGREGADO para descuento de stock
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CatalogoController extends Controller
{
    public function index()
    {
        // ── CAMBIO: se agrega stock_disponible calculado desde MovimientoProducto ──
        // El stock real = SUM(entradas) - SUM(salidas) por producto
        $productos = Producto::where('is_active', true)
            ->select(['id', 'nombre', 'precio_venta', 'descripcion', 'imagen', 'unidad_medida'])
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function ($producto) {
                //obtener la ruta para local y produccion
                $producto->imagen = $producto->imagen ? asset($producto->imagen) : null;

                // Calcular stock disponible: entradas - salidas
                $entradas = MovimientoProducto::where('producto_id', $producto->id)
                    ->where('tipo_movimiento', 'entrada')
                    ->sum('cantidad');

                $salidas = MovimientoProducto::where('producto_id', $producto->id)
                    ->where('tipo_movimiento', 'salida')
                    ->sum('cantidad');

                $producto->stock_disponible = max(0, $entradas - $salidas);
                return $producto;
            });
        // ── FIN CAMBIO ──

        return Inertia::render('Catalogo/index', [
            'productos' => $productos
        ]);
    }

    public function venta(Request $request)
    {
        $productos = $request->input('productos', []);
        $total = $request->input('total');

        if ($total === null && !empty($productos)) {
            $total = collect($productos)->sum(function ($item) {
                return ($item['precio'] ?? 0) * ($item['cantidad'] ?? 1);
            });
        }

        return Inertia::render('Catalogo/Venta/index', [
            'productos' => $productos,
            'cliente'   => Auth::user(),
            'total'     => (float) ($total ?? 0)
        ]);
    }

    /**
     * Confirmar pedido - Crea Pedido, PedidoDetalle, Venta, DetalleVenta
     * y descuenta stock de productos vendidos.
     */ 
 public function confirmar(Request $request)
{
    $request->validate([
        'productos'      => 'required|array|min:1',
        'modalidad_pago' => 'required|string|in:contado,cuotas',
        'numero_cuotas'  => 'required_if:modalidad_pago,cuotas|integer|min:2|max:24',
        'total'          => 'required|numeric|min:0.01',
        'cliente_id'     => 'nullable|exists:users,id',
    ]);

    try {
        DB::beginTransaction();

        $user  = Auth::user();
        $total = (float) $request->input('total');

        // ── Validar stock disponible ANTES de crear cualquier registro ──
        $erroresStock = [];

        foreach ($request->productos as $prod) {
            $productoId = $prod['id'];
            $cantidadSolicitada = (int) $prod['cantidad'];

            $entradas = MovimientoProducto::where('producto_id', $productoId)
                ->where('tipo_movimiento', 'entrada')
                ->sum('cantidad');

            $salidas = MovimientoProducto::where('producto_id', $productoId)
                ->where('tipo_movimiento', 'salida')
                ->sum('cantidad');

            $stockDisponible = max(0, $entradas - $salidas);

            if ($stockDisponible < $cantidadSolicitada) {
                $producto = Producto::find($productoId);
                $nombre   = $producto?->nombre ?? "Producto #$productoId";
                $erroresStock[] = "\"$nombre\": solicitás $cantidadSolicitada unidades pero solo hay $stockDisponible disponibles.";
            }
        }

        if (!empty($erroresStock)) {
            DB::rollBack();
            return back()->withErrors([
                'stock' => 'Stock insuficiente para completar el pedido:',
                'stock_detalle' => $erroresStock,
            ]);
        }
        // ── FIN validación de stock ──

        $cliente = $user->cliente;
        if (!$cliente) {
            $cliente = Cliente::create([
                'nombre'    => $user->name,
                'email'     => $user->email,
                'telefono'  => $user->telefono ?? null,
                'direccion' => $user->direccion ?? null,
                'user_id'   => $user->id,
            ]);
        }
        $clienteId = $cliente->id;

        // 1. Pedido
        $pedido = Pedido::create([
            'fecha'             => now(),
            'total'             => $total,
            'estado_produccion' => 'pending',
            'cliente_id'        => $clienteId,
            'ubicacion_id'      => optional($user->ubicacion)->id,
        ]);

        // 2. Detalles del Pedido
        foreach ($request->productos as $prod) {
            PedidoDetalle::create([
                'cantidad'        => $prod['cantidad'],
                'precio_unitario' => $prod['precio'],
                'subtotal'        => $prod['precio'] * $prod['cantidad'],
                'pedido_id'       => $pedido->id,
                'producto_id'     => $prod['id'],
            ]);
        }

        // 3. Venta — el pago siempre es vía QR (PagoFácil)
        $venta = Venta::create([
            'fecha'      => now(),
            'total'      => $total,
            'tipo_pago'  => 'qr',
            'modo_pago'  => $request->modalidad_pago,
            'pedido_id'  => $pedido->id,
            'cliente_id' => $clienteId,
        ]);

        // 4. Detalles de Venta
        foreach ($request->productos as $prod) {
            DetalleVenta::create([
                'cantidad'        => $prod['cantidad'],
                'precio_unitario' => $prod['precio'],
                'subtotal'        => $prod['precio'] * $prod['cantidad'],
                'venta_id'        => $venta->id,
                'producto_id'     => $prod['id'],
            ]);
        }

        // 5. Descuento de stock
        foreach ($request->productos as $prod) {
            MovimientoProducto::create([
                'producto_id'         => $prod['id'],
                'tipo_movimiento'     => 'salida',
                'cantidad'            => $prod['cantidad'],
                'fecha'               => now(),
                'orden_produccion_id' => null,
            ]);
        }

        // 6. Lógica de pago QR: contado vs cuotas (ya no depende de tipo_pago)
        $primerPagoId = null;

        if ($request->modalidad_pago === 'cuotas') {

            $numeroCuotas = (int) $request->numero_cuotas;
            $montoCuota   = round($total / $numeroCuotas, 2);
            $montoUltima  = round($total - ($montoCuota * ($numeroCuotas - 1)), 2);

            CuentaCobro::create([
                'saldo_pendiente'   => $total,
                'fecha_vencimiento' => now()->addMonths($numeroCuotas),
                'venta_id'          => $venta->id,
                'cliente_id'        => $clienteId,
            ]);

            for ($i = 1; $i <= $numeroCuotas; $i++) {
                $monto = ($i === $numeroCuotas) ? $montoUltima : $montoCuota;

                $pago = Pagos::create([
                    'venta_id'    => $venta->id,
                    'monto'       => $monto,
                    'fecha'       => now(),
                    'metodo_pago' => 'PAGO_FACIL',
                    'estado'      => 'pendiente',
                    'datos_pago'  => [
                        'numero_cuota' => $i,
                        'total_cuotas' => $numeroCuotas,
                        'modalidad'    => 'cuotas',
                    ],
                ]);

                if ($i === 1) {
                    $primerPagoId = $pago->id;
                }
            }
        } else {
            $pago = Pagos::create([
                'venta_id'    => $venta->id,
                'monto'       => $total,
                'fecha'       => now(),
                'metodo_pago' => 'PAGO_FACIL',
                'estado'      => 'pendiente',
                'datos_pago'  => ['modalidad' => 'contado'],
            ]);

            $primerPagoId = $pago->id;
        }

        DB::commit();

        BitacoraService::accionCrud(
            modulo: 'Catálogo',
            accion: 'Confirmar pedido',
            registroId: $pedido->id,
            exitoso: true,
            detalle: 'Pedido #' . $pedido->id
                . ' | Modalidad: ' . $request->modalidad_pago
                . ' | Total: ' . $total . ' BOB',
        );

        return Inertia::render('Catalogo/Venta/QRPago', [
            'venta_id'      => $venta->id,
            'pago_id'       => $primerPagoId,
            'total'         => $total,
            'modalidad'     => $request->modalidad_pago,
            'numero_cuotas' => $request->numero_cuotas ?? 1,
            'monto_cuota'   => $request->modalidad_pago === 'cuotas'
                ? round($total / $request->numero_cuotas, 2)
                : $total,
            'productos'     => $request->productos,
            'cliente'       => $user,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error en confirmar()', ['error' => $e->getMessage()]);
        return back()->withErrors(['error' => 'Error al procesar el pedido: ' . $e->getMessage()]);
    }
}
}
