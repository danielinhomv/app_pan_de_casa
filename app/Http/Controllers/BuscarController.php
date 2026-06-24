<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BuscarController extends Controller
{
    public function buscar(Request $request)
    {
        $q    = trim($request->input('q', ''));
        $user = Auth::user();

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $resultados = [];
        $like       = "%{$q}%";

        // ── Productos (todos los roles) ───────────────────────────────────────
        if ($user->hasAnyRole(['propietario', 'produccion', 'encargadoalmacen', 'cliente'])) {
            $productos = DB::table('productos')
                ->where('is_active', true)
                ->where(function ($q) use ($like) {
                    $q->where('nombre', 'ilike', $like)
                      ->orWhere('descripcion', 'ilike', $like);
                })
                ->select('id', 'nombre', 'precio_venta', 'unidad_medida')
                ->limit(5)
                ->get();

            foreach ($productos as $p) {
                $resultados[] = [
                    'tipo'      => 'Producto',
                    'icono'     => 'fas fa-box',
                    'color'     => 'blue',
                    'titulo'    => $p->nombre,
                    'subtitulo' => number_format($p->precio_venta, 2) . ' Bs · ' . $p->unidad_medida,
                    'url'       => route('productos.show', $p->id),
                ];
            }
        }

        // ── Pedidos (propietario, encargadoalmacen) ───────────────────────────
        // clientes no tiene 'nombre' → join con users para obtener name
        if ($user->hasAnyRole(['propietario', 'encargadoalmacen'])) {
            $pedidos = DB::table('pedidos')
                ->join('clientes', 'pedidos.cliente_id', '=', 'clientes.id')
                ->join('users', 'clientes.user_id', '=', 'users.id')
                ->where(function ($q) use ($like) {
                    $q->where('users.name', 'ilike', $like)
                      ->orWhere('clientes.razon_social', 'ilike', $like)
                      ->orWhere('clientes.nit', 'ilike', $like)
                      ->orWhere('pedidos.estado_produccion', 'ilike', $like);
                })
                ->select(
                    'pedidos.id',
                    'users.name as cliente',
                    'clientes.razon_social',
                    'pedidos.estado_produccion',
                    'pedidos.created_at'
                )
                ->orderByDesc('pedidos.created_at')
                ->limit(5)
                ->get();

            foreach ($pedidos as $p) {
                $nombre = $p->razon_social ?? $p->cliente;
                $resultados[] = [
                    'tipo'      => 'Pedido',
                    'icono'     => 'fas fa-clipboard-list',
                    'color'     => 'amber',
                    'titulo'    => 'Pedido #' . $p->id . ' — ' . $nombre,
                    'subtitulo' => 'Estado: ' . $p->estado_produccion,
                    'url'       => route('pedidos.show', $p->id),
                ];
            }
        }

        // ── Mis pedidos (cliente) ─────────────────────────────────────────────
        if ($user->hasRole('cliente') && $user->cliente) {
            $misPedidos = DB::table('pedidos')
                ->where('cliente_id', $user->cliente->id)
                ->where('estado_produccion', 'ilike', $like)
                ->select('id', 'estado_produccion', 'created_at')
                ->orderByDesc('created_at')
                ->limit(3)
                ->get();

            foreach ($misPedidos as $p) {
                $resultados[] = [
                    'tipo'      => 'Mi Pedido',
                    'icono'     => 'fas fa-truck',
                    'color'     => 'green',
                    'titulo'    => 'Pedido #' . $p->id,
                    'subtitulo' => 'Estado: ' . $p->estado_produccion,
                    'url'       => route('cliente.pedidos.show', $p->id),
                ];
            }
        }

        // ── Ingredientes (propietario, encargadoalmacen, produccion) ──────────
        if ($user->hasAnyRole(['propietario', 'encargadoalmacen', 'produccion'])) {
            $ingredientes = DB::table('ingredientes')
                ->where(function ($q) use ($like) {
                    $q->where('nombre', 'ilike', $like)
                      ->orWhere('descripcion', 'ilike', $like);
                })
                ->select('id', 'nombre', 'unidad_medida')
                ->limit(4)
                ->get();

            foreach ($ingredientes as $i) {
                $resultados[] = [
                    'tipo'      => 'Ingrediente',
                    'icono'     => 'fas fa-seedling',
                    'color'     => 'green',
                    'titulo'    => $i->nombre,
                    'subtitulo' => $i->unidad_medida,
                    'url'       => route('almacen.index'),
                ];
            }
        }

        // ── Recetas (propietario, produccion) ─────────────────────────────────
        // recetas es tabla pivote producto_id + ingrediente_id, sin nombre propio
        // → buscamos por nombre del producto asociado
        if ($user->hasAnyRole(['propietario', 'produccion'])) {
            $recetas = DB::table('recetas')
                ->join('productos', 'recetas.producto_id', '=', 'productos.id')
                ->join('ingredientes', 'recetas.ingrediente_id', '=', 'ingredientes.id')
                ->where(function ($q) use ($like) {
                    $q->where('productos.nombre', 'ilike', $like)
                      ->orWhere('ingredientes.nombre', 'ilike', $like);
                })
                ->select(
                    'recetas.id',
                    'productos.nombre as producto',
                    'ingredientes.nombre as ingrediente',
                    'recetas.cant_x_unidad'
                )
                ->limit(4)
                ->get();

            foreach ($recetas as $r) {
                $resultados[] = [
                    'tipo'      => 'Receta',
                    'icono'     => 'fas fa-book',
                    'color'     => 'purple',
                    'titulo'    => 'Receta: ' . $r->producto,
                    'subtitulo' => $r->ingrediente . ' · ' . $r->cant_x_unidad . ' unid.',
                    'url'       => route('recetas.index'),
                ];
            }
        }

        // ── Proveedores (propietario, encargadoalmacen) ───────────────────────
        // tabla real: proveedors (no proveedores)
        if ($user->hasAnyRole(['propietario', 'encargadoalmacen'])) {
            $proveedores = DB::table('proveedors')
                ->where(function ($q) use ($like) {
                    $q->where('empresa', 'ilike', $like)
                      ->orWhere('contacto', 'ilike', $like);
                })
                ->select('id', 'empresa', 'contacto', 'estado')
                ->limit(3)
                ->get();

            foreach ($proveedores as $p) {
                $resultados[] = [
                    'tipo'      => 'Proveedor',
                    'icono'     => 'fas fa-truck-loading',
                    'color'     => 'gray',
                    'titulo'    => $p->empresa,
                    'subtitulo' => ($p->contacto ?? 'Sin contacto') . ($p->estado ? '' : ' · Inactivo'),
                    'url'       => route('proveedores.index'),
                ];
            }
        }

        return response()->json(array_slice($resultados, 0, 12));
    }
}