<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    // Tabla inmutable — sin updated_at
    const UPDATED_AT = null;

    protected $table = 'bitacora';

    protected $fillable = [
        'user_id',
        'email_intento',
        'nombre_usuario',
        'tipo_evento',
        'modulo',
        'accion',
        'url',
        'metodo_http',
        'registro_id',
        'ip',
        'user_agent',
        'exitoso',
        'detalle',
        'ocurrido_en',
    ];

    protected $casts = [
        'exitoso'     => 'boolean',
        'ocurrido_en' => 'datetime',
        'created_at'  => 'datetime',
    ];

    // ── Tipos de evento disponibles ──────────────────────────────────────────
    const TIPO_LOGIN_EXITOSO   = 'login_exitoso';
    const TIPO_LOGIN_FALLIDO   = 'login_fallido';
    const TIPO_LOGOUT          = 'logout';
    const TIPO_ACCESO_MODULO   = 'acceso_modulo';
    const TIPO_ACCION_CRUD     = 'accion_crud';
    const TIPO_ACCESO_DENEGADO = 'acceso_denegado';
    const TIPO_EXPORTACION     = 'exportacion';

    // ── Relación ─────────────────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes para el dashboard ──────────────────────────────────────────────
    public function scopeLoginsFallidos($query)
    {
        return $query->where('tipo_evento', self::TIPO_LOGIN_FALLIDO);
    }

    public function scopeLoginsExitosos($query)
    {
        return $query->where('tipo_evento', self::TIPO_LOGIN_EXITOSO);
    }

    public function scopeDeHoy($query)
    {
        return $query->whereDate('ocurrido_en', today());
    }

    public function scopeDelMes($query)
    {
        return $query->whereMonth('ocurrido_en', now()->month)
                     ->whereYear('ocurrido_en', now()->year);
    }

    public function scopePorModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }
}