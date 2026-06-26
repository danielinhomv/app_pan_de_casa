<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductoController extends Controller
{
  public function index()
{
    $productos = Producto::select([
            'id', 'nombre', 'unidad_medida',
            'precio_venta', 'descripcion', 'imagen', 'is_active'
        ])
        ->withSum(
            ['movimientosProducto as entradas' => fn($q) => $q->where('tipo_movimiento', 'entrada')],
            'cantidad'
        )
        ->withSum(
            ['movimientosProducto as salidas' => fn($q) => $q->where('tipo_movimiento', 'salida')],
            'cantidad'
        )
        ->get()
        ->map(function ($producto) {
            $producto->stock_disponible = max(0, ($producto->entradas ?? 0) - ($producto->salidas ?? 0));
            unset($producto->entradas, $producto->salidas);
            return $producto;
        });

    BitacoraService::accesoModulo('Productos', 'Listado');

    return Inertia::render('Produccion/Productos/Index', [
        'productos' => $productos,
    ]);
}

    public function show($id)
    {
        $producto = Producto::findOrFail($id);

        return Inertia::render('Produccion/Productos/Show', [
            'producto' => $producto,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:255',
            'precio_venta' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'is_active' => 'boolean',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $imagenPath = null;

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Guardar en public/images/products
            $file->move(public_path('images/products'), $filename);
            $imagenPath = '/images/products/' . $filename;
        }

        $producto = Producto::create([
            'nombre' => $request->nombre,
            'unidad_medida' => $request->unidad_medida,
            'precio_venta' => $request->precio_venta,
            'descripcion' => $request->descripcion,
            'imagen' => $imagenPath,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        BitacoraService::accionCrud(
            modulo: 'Productos',
            accion: 'Crear registro',
            registroId: $producto->id,
            exitoso: true,
            detalle: 'Producto creado: ' . $producto->nombre . ' (Precio: ' . $producto->precio_venta . ')'
        );

        return redirect()->route('productos.index')
            ->with('success', 'Producto ' . $producto->nombre . ' creado exitosamente');
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:255',
            'precio_venta' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
            'is_active' => 'boolean',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->only(['nombre', 'unidad_medida', 'precio_venta', 'descripcion', 'is_active']);

        // Manejar eliminación de imagen
        if ($request->input('remove_image') === '1') {
            // Eliminar archivo anterior si existe
            if ($producto->imagen && file_exists(public_path($producto->imagen))) {
                @unlink(public_path($producto->imagen));
            }
            $data['imagen'] = null;
        }

        // Manejar nueva imagen
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen && file_exists(public_path($producto->imagen))) {
                @unlink(public_path($producto->imagen));
            }

            $file = $request->file('imagen');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Guardar en public/images/products
            $file->move(public_path('images/products'), $filename);
            $data['imagen'] = '/images/products/' . $filename;
        }

        $producto->update($data);
        BitacoraService::accionCrud(
            modulo: 'Productos',
            accion: 'Actualizar registro',
            registroId: $producto->id,
            exitoso: true,
            detalle: 'Producto actualizado: ' . $producto->nombre
        );

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente');
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        // Eliminar imagen si existe
        if ($producto->imagen && file_exists(public_path($producto->imagen))) {
            @unlink(public_path($producto->imagen));
        }

        $producto->delete();

        BitacoraService::accionCrud(
            modulo: 'Productos',
            accion: 'Eliminar registro',
            registroId: $producto->id,
            exitoso: true,
            detalle: 'Producto eliminado: ' . $producto->nombre
        );

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado correctamente');
    }
}
