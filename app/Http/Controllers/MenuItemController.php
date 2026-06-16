<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Role;

class MenuItemController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('role:propietario|encargadoalmacen', except: ['index']),
        ];
    }

    public function index()
    {
        $menuItems = MenuItem::with('children')->orderBy('order')->get();
        BitacoraService::accesoModulo('Configuración de Menú', 'Listado');
        // Renderizamos la vista InterfazMenu.vue
        return Inertia::render('InterfazMenu', [
            'menuItems' => $menuItems,
        ]);
    }

    public function create()
    {
        $parents = MenuItem::whereNull('parent_id')->orderBy('order')->get();
        $roles = Role::all()->pluck('name')->toArray();

        return Inertia::render('MenuItems/Create', [
            'parents' => $parents,
            'availableRoles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order' => 'required|integer',
            'is_active' => 'required|boolean',
            'roles' => 'nullable|array',
        ]);

        if (isset($data['roles']) && !empty($data['roles'])) {
            $data['roles'] = array_values($data['roles']);
        } else {
            $data['roles'] = null;
        }

        $menu = MenuItem::create($data);

        BitacoraService::accionCrud(
            modulo: 'Configuración de Menú',
            accion: 'Crear registro',
            registroId: $menu->id,
            exitoso: true,
            detalle: 'Elemento de menú creado: ' . $data['title'] . ' (Ruta: ' . ($data['route'] ?? 'N/A') . ')'
        );
        return redirect()->route('menu.index')->with('success', 'Elemento de menú creado.');
    }

    public function edit(MenuItem $menu)
    {
        $parents = MenuItem::whereNull('parent_id')->where('id', '!=', $menu->id)->orderBy('order')->get();
        $roles = Role::all()->pluck('name')->toArray();

        return Inertia::render('MenuItems/Edit', [
            'menu' => $menu,
            'parents' => $parents,
            'availableRoles' => $roles,
        ]);
    }

    public function update(Request $request, MenuItem $menu)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order' => 'required|integer',
            'is_active' => 'required|boolean',
            'roles' => 'nullable|array',
        ]);

        if (isset($data['roles']) && !empty($data['roles'])) {
            $data['roles'] = array_values($data['roles']);
        } else {
            $data['roles'] = null;
        }

        $menu->update($data);

        BitacoraService::accionCrud(
            modulo: 'Configuración de Menú',
            accion: 'Actualizar registro',
            registroId: $menu->id,
            exitoso: true,
            detalle: 'Elemento de menú actualizado: ' . $menu->title
        );

        return redirect()->route('menu.index')->with('success', 'Elemento de menú actualizado.');
    }

    public function destroy(MenuItem $menu)
    {
        $menu->delete();
        BitacoraService::accionCrud(
            modulo: 'Configuración de Menú',
            accion: 'Eliminar registro',
            registroId: $menu->id,
            exitoso: true,
            detalle: 'Elemento de menú eliminado: ' . $menu->title
        );
        return redirect()->route('menu.index')->with('success', 'Elemento de menú eliminado.');
    }
}
