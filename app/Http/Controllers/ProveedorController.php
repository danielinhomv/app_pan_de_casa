<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function index()
    {
        // Recuperar solo las columnas definidas en la migración
        $proveedores = Proveedor::select(['id', 'empresa', 'contacto', 'estado'])->get();
        BitacoraService::accesoModulo('Proveedores', 'Listado');
        return Inertia::render('Inventario/Proveedores/index', [
            'proveedores' => $proveedores,
        ]);
    }

    public function store(Request $request)
    {
        // Validación estricta según migración: empresa (required), contacto (nullable), estado (boolean)
        $request->validate([
            'empresa'  => 'required|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'estado'   => 'boolean',
        ]);

        $proveedor = Proveedor::create([
            'empresa'  => $request->empresa,
            'contacto' => $request->contacto,
            'estado'   => $request->has('estado') ? (bool)$request->estado : true,
        ]);

        BitacoraService::accionCrud(
            modulo: 'Proveedores',
            accion: 'Crear registro',
            registroId: $proveedor->id,
            exitoso: true,
            detalle: 'Proveedor creado: ' . $proveedor->empresa . ' (Contacto: ' . ($proveedor->contacto ?? 'N/A') . ')'
        );

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor ' . $proveedor->empresa . ' creado exitosamente');
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $request->validate([
            'empresa'  => 'required|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'estado'   => 'boolean',
        ]);

        $proveedor->update($request->only(['empresa', 'contacto', 'estado']));

        BitacoraService::accionCrud(
            modulo: 'Proveedores',
            accion: 'Actualizar registro',
            registroId: $proveedor->id,
            exitoso: true,
            detalle: 'Proveedor actualizado: ' . $proveedor->empresa
        );

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente');
    }
}
