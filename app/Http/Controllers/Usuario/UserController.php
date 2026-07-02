<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Solo propietario puede acceder — protegido en rutas.
     * Roles disponibles para crear: todos menos 'cliente'.
     */
    private const ROLES_GESTIONABLES = ['propietario', 'encargadoalmacen', 'produccion'];

    public function index()
    {
        $usuarios = User::with('roles')
            ->whereHas('roles', fn($q) => $q->whereIn('name', self::ROLES_GESTIONABLES))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'rol'        => $u->roles->first()?->name ?? 'sin rol',
                'created_at' => $u->created_at->format('d/m/Y'),
            ]);

        $roles = Role::whereIn('name', self::ROLES_GESTIONABLES)->pluck('name');

        BitacoraService::accesoModulo('Usuarios', 'Listado');

        return Inertia::render('Admin/Usuario/Index', [
            'usuarios' => $usuarios,
            'roles'    => $roles,
        ]);
    }

    public function show($id)
    {
        $usuario = User::with('roles')->findOrFail($id);

        // No mostrar clientes
        if ($usuario->hasRole('cliente')) {
            abort(403);
        }

        return Inertia::render('Admin/Usuario/Show', [
            'usuario' => [
                'id'         => $usuario->id,
                'name'       => $usuario->name,
                'email'      => $usuario->email,
                'rol'        => $usuario->roles->first()?->name ?? 'sin rol',
                'created_at' => $usuario->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'rol'      => 'required|in:' . implode(',', self::ROLES_GESTIONABLES),
        ], [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El correo es obligatorio.',
            'email.unique'       => 'Este correo ya está registrado.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'rol.required'       => 'Debes seleccionar un rol.',
            'rol.in'             => 'Rol no válido. No se puede asignar el rol cliente desde aquí.',
        ]);

        $usuario = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $usuario->assignRole($request->rol);

        BitacoraService::accionCrud(
            modulo:     'Usuarios',
            accion:     'Crear registro',
            registroId: $usuario->id,
            exitoso:    true,
            detalle:    'Usuario: ' . $usuario->name . ' | Rol: ' . $request->rol,
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario ' . $usuario->name . ' creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->hasRole('cliente')) {
            abort(403);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id,
            'rol'      => 'required|in:' . implode(',', self::ROLES_GESTIONABLES),
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'email.unique'       => 'Este correo ya está en uso.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'rol.in'             => 'Rol no válido.',
        ]);

        $rolAnterior = $usuario->roles->first()?->name;

        $usuario->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        // Reasignar rol
        $usuario->syncRoles([$request->rol]);

        BitacoraService::accionCrud(
            modulo:     'Usuarios',
            accion:     'Actualizar registro',
            registroId: $usuario->id,
            exitoso:    true,
            detalle:    'Usuario: ' . $usuario->name . ' | Rol: ' . $rolAnterior . ' → ' . $request->rol,
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);

        // No eliminar clientes ni al propietario autenticado
        if ($usuario->hasRole('cliente')) {
            abort(403);
        }

        if ($usuario->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $nombre = $usuario->name;
        $rol    = $usuario->roles->first()?->name;
        $usuario->delete();

        BitacoraService::accionCrud(
            modulo:     'Usuarios',
            accion:     'Eliminar registro',
            registroId: (int) $id,
            exitoso:    true,
            detalle:    'Usuario eliminado: ' . $nombre . ' | Rol: ' . $rol,
        );

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario ' . $nombre . ' eliminado.');
    }
}