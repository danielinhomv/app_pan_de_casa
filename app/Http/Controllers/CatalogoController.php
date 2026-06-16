<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CatalogoController extends Controller
{
    public function index()
    {
        // Obtener productos activos con los campos necesarios para el catálogo
        $productos = Producto::where('is_active', true)
            ->select(['id', 'nombre', 'precio_venta', 'descripcion', 'imagen', 'unidad_medida'])
            ->orderBy('nombre', 'asc')
            ->get();

        return Inertia::render('Catalogo/index', [
            'productos' => $productos
        ]);
    }

    public function venta(Request $request)
    {
        // Recibir datos del carrito (POST) o mostrar vista vacía (GET)
        $productos = $request->input('productos', []);
        $total = $request->input('total');

        // Si no se envió total, calcularlo
        if ($total === null && !empty($productos)) {
            $total = collect($productos)->sum(function ($item) {
                return ($item['precio'] ?? 0) * ($item['cantidad'] ?? 1);
            });
        }

        return Inertia::render('Catalogo/Venta/index', [
            'productos' => $productos,
            'cliente' => Auth::user(),
            'total' => (float) ($total ?? 0)
        ]);
    }

    /**
     * Confirmar pedido - Crea Pedido, PedidoDetalle, Venta, DetalleVenta
     */
    public function confirmar(Request $request)
    {
        $request->validate([
            'productos' => 'required|array|min:1',
            'tipo_pago' => 'required|string',
            'modalidad_pago' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $total = $request->input('total');

            // Asegurar que existe el registro de Cliente para este usuario
            $cliente = $user->cliente;
            if (!$cliente) {
                $cliente = Cliente::create([
                    'nombre' => $user->name,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'direccion' => $user->direccion,
                    'user_id' => $user->id,
                ]);
            }
            $clienteId = $cliente->id;

            // 1. Crear Pedido
            $pedido = Pedido::create([
                'fecha' => now(),
                'total' => $total,
                'estado_produccion' => 'pending',
                'cliente_id' => $clienteId,
                'ubicacion_id' => $user->ubicacion ? $user->ubicacion->id : null,
            ]);

            // 2. Crear Detalles del Pedido
            foreach ($request->productos as $prod) {
                PedidoDetalle::create([
                    'cantidad' => $prod['cantidad'],
                    'precio_unitario' => $prod['precio'],
                    'subtotal' => $prod['precio'] * $prod['cantidad'],
                    'pedido_id' => $pedido->id,
                    'producto_id' => $prod['id'],
                ]);
            }

            // 3. Registrar Venta
            $venta = Venta::create([
                'fecha' => now(),
                'total' => $total,
                'tipo_pago' => $request->tipo_pago,
                'modo_pago' => $request->modalidad_pago,
                'pedido_id' => $pedido->id,
                'cliente_id' => $clienteId,
            ]);

            // 4. Registrar Detalles de Venta
            foreach ($request->productos as $prod) {
                DetalleVenta::create([
                    'cantidad' => $prod['cantidad'],
                    'precio_unitario' => $prod['precio'],
                    'subtotal' => $prod['precio'] * $prod['cantidad'],
                    'venta_id' => $venta->id,
                    'producto_id' => $prod['id'],
                ]);
            }

            DB::commit();

            // Si es pago por QR, retornar Inertia con venta_id para mostrar el QR Modal
            if ($request->tipo_pago === 'qr') {
                return Inertia::render('Catalogo/Venta/QRPago', [
                    'venta_id' => $venta->id,
                    'total' => (float) $total,
                    'productos' => $request->productos,
                    'cliente' => $user,
                ]);
            }

            BitacoraService::accionCrud(
                modulo: 'Catálogo',
                accion: 'Confirmar pedido y venta',
                registroId: $pedido->id,
                exitoso: true,
                detalle: 'Pedido #' . $pedido->id . ' creado exitosamente. Tipo de pago: ' . $request->tipo_pago . ' (Total: ' . $total . ')'
            );

            return redirect()->route('cliente.pedidos.index')->with('success', 'Pedido realizado con éxito');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al procesar el pedido: ' . $e->getMessage()]);
        }
    }
}
