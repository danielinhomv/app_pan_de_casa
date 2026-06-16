<?php

namespace App\Services;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BitacoraService
{
    /**
     * Método principal — registra cualquier evento en la bitácora.
     */
    public static function registrar(
        string  $tipoEvento,
        array   $extra = [],
        ?Request $request = null
    ): void {
        try {
            $request = $request ?? request();
            $user    = Auth::user();

            Bitacora::create([
                'user_id'       => $extra['user_id']       ?? $user?->id,
                'email_intento' => $extra['email_intento'] ?? null,
                'nombre_usuario'=> $extra['nombre_usuario'] ?? $user?->name,
                'tipo_evento'   => $tipoEvento,
                'modulo'        => $extra['modulo']        ?? null,
                'accion'        => $extra['accion']        ?? null,
                'url'           => $request->fullUrl(),
                'metodo_http'   => $request->method(),
                'registro_id'   => $extra['registro_id']   ?? null,
                'ip'            => $request->ip(),
                'user_agent'    => substr($request->userAgent() ?? '', 0, 300),
                'exitoso'       => $extra['exitoso']       ?? true,
                'detalle'       => $extra['detalle']       ?? null,
                'ocurrido_en'   => now(),
            ]);
        } catch (\Throwable $e) {
            // La bitácora nunca debe romper el flujo principal
            logger()->error('BitacoraService error: ' . $e->getMessage());
        }
    }

    // ── Atajos semánticos ────────────────────────────────────────────────────

    public static function loginExitoso($user): void
    {
        self::registrar(Bitacora::TIPO_LOGIN_EXITOSO, [
            'user_id'        => $user->id,
            'nombre_usuario' => $user->name,
            'exitoso'        => true,
        ]);
    }

    public static function loginFallido(string $email): void
    {
        self::registrar(Bitacora::TIPO_LOGIN_FALLIDO, [
            'email_intento' => $email,
            'exitoso'       => false,
            'detalle'       => 'Credenciales incorrectas',
        ]);
    }

    public static function logout($user): void
    {
        self::registrar(Bitacora::TIPO_LOGOUT, [
            'user_id'        => $user->id,
            'nombre_usuario' => $user->name,
        ]);
    }

    public static function accesoModulo(string $modulo, string $accion = 'index'): void
    {
        self::registrar(Bitacora::TIPO_ACCESO_MODULO, [
            'modulo' => $modulo,
            'accion' => $accion,
        ]);
    }

    public static function accionCrud(
        string $modulo,
        string $accion,
        ?int   $registroId = null,
        bool   $exitoso = true,
        string $detalle = null
    ): void {
        self::registrar(Bitacora::TIPO_ACCION_CRUD, [
            'modulo'      => $modulo,
            'accion'      => $accion,       // 'store','update','destroy'
            'registro_id' => $registroId,
            'exitoso'     => $exitoso,
            'detalle'     => $detalle,
        ]);
    }

    public static function accesoDenegado(string $modulo = null): void
    {
        self::registrar(Bitacora::TIPO_ACCESO_DENEGADO, [
            'modulo'  => $modulo,
            'exitoso' => false,
            'detalle' => 'Sin permiso para acceder al recurso',
        ]);
    }

    public static function exportacion(string $modulo, string $detalle = null): void
    {
        self::registrar(Bitacora::TIPO_EXPORTACION, [
            'modulo'  => $modulo,
            'accion'  => 'exportar',
            'detalle' => $detalle,
        ]);
    }
}