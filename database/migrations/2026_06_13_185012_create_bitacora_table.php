<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();

            /*
            |------------------------------------------------------------------
            | ¿Quién?
            | Nullable porque los login_fallido pueden no tener user válido
            |------------------------------------------------------------------
            */
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->string('email_intento', 180)->nullable();  // correo escrito en el form
            $table->string('nombre_usuario', 120)->nullable(); // snapshot del nombre al momento

            /*
            |------------------------------------------------------------------
            | ¿Qué tipo de evento?
            |------------------------------------------------------------------
            */
            $table->enum('tipo_evento', [
                'login_exitoso',   // autenticación correcta
                'login_fallido',   // credenciales incorrectas
                'logout',          // cierre de sesión
                'acceso_modulo',   // navegó a un módulo/sección
                'accion_crud',     // create / read / update / delete
                'acceso_denegado', // intentó acceder sin permiso (403)
                'exportacion',     // descargó reporte / Excel / PDF
            ]);

            /*
            |------------------------------------------------------------------
            | ¿Sobre qué recurso?
            |------------------------------------------------------------------
            */
            $table->string('modulo', 80)->nullable();          // 'Ventas', 'Pedidos', etc.
            $table->string('accion', 60)->nullable();          // 'index','store','update','destroy'
            $table->string('url', 300)->nullable();            // ruta HTTP accedida
            $table->string('metodo_http', 10)->nullable();     // GET POST PUT DELETE

            // ID del registro afectado (ej: id del pedido editado)
            $table->unsignedBigInteger('registro_id')->nullable();

            /*
            |------------------------------------------------------------------
            | Contexto técnico
            | (IP y user_agent ya están en 'sessions', pero la bitácora
            |  es inmutable y debe ser autosuficiente)
            |------------------------------------------------------------------
            */
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 300)->nullable();

            /*
            |------------------------------------------------------------------
            | Resultado
            |------------------------------------------------------------------
            */
            $table->boolean('exitoso')->default(true);
            $table->text('detalle')->nullable(); // mensaje de error o info extra

            /*
            |------------------------------------------------------------------
            | Tiempo — tabla inmutable, sin updated_at
            |------------------------------------------------------------------
            */
            $table->timestamp('ocurrido_en')->useCurrent();
            $table->timestamp('created_at')->useCurrent();

            /*
            |------------------------------------------------------------------
            | Índices para el dashboard de bitácora
            |------------------------------------------------------------------
            */
            $table->index('tipo_evento',  'idx_bita_tipo');
            $table->index('user_id',      'idx_bita_user');
            $table->index('modulo',       'idx_bita_modulo');
            $table->index('ocurrido_en',  'idx_bita_fecha');
            $table->index('exitoso',      'idx_bita_exitoso');
            $table->index(['tipo_evento', 'ocurrido_en'], 'idx_bita_tipo_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};