<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class BitacoraController extends Controller
{
    /**
     * Vista Principal - Panel de Auditoría con Filtros Avanzados
     * Se renombró a 'listar' para evitar conflictos de compatibilidad con Controller::index()
     */
    public function listar(Request $request)
    {
        // 1. Recolección de filtros desde la URL
        $buscar      = $request->input('buscar');       // Término general (usuario, detalle, ip)
        $tipoEvento  = $request->input('tipo_evento');  // login_exitoso, accion_crud, etc.
        $modulo      = $request->input('modulo');       // Pedidos, Productos, etc.
        $exitoso     = $request->input('exitoso');      // '1' (true) o '0' (false)
        $fechaDesde  = $request->input('fecha_desde');  // YYYY-MM-DD
        $fechaHasta  = $request->input('fecha_hasta');  // YYYY-MM-DD

        // 2. Construcción de la consulta con Query Builder dinámico
        $query = Bitacora::with('user:id,name,email')
            ->orderBy('ocurrido_en', 'desc');

        // Filtro por término general
        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre_usuario', 'like', "%{$buscar}%")
                  ->orWhere('email_intento', 'like', "%{$buscar}%")
                  ->orWhere('detalle', 'like', "%{$buscar}%")
                  ->orWhere('ip', 'like', "%{$buscar}%")
                  ->orWhere('accion', 'like', "%{$buscar}%");
            });
        }

        // Filtros específicos
        if ($tipoEvento) {
            $query->where('tipo_evento', $tipoEvento);
        }

        if ($modulo) {
            $query->where('modulo', $modulo);
        }

        if ($exitoso !== null && $exitoso !== '') {
            $query->where('exitoso', filter_var($exitoso, FILTER_VALIDATE_BOOLEAN));
        }

        // Filtro por rangos de fecha
        if ($fechaDesde) {
            $query->whereDate('ocurrido_en', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate('ocurrido_en', '<=', $fechaHasta);
        }

        // 3. Métricas rápidas para KPIs en el Dashboard de Bitácoras
        $kpis = [
            'total_hoy'         => Bitacora::deHoy()->count(),
            'logins_fallidos'   => Bitacora::loginsFallidos()->deHoy()->count(),
            'accesos_denegados' => Bitacora::deHoy()->where('tipo_evento', Bitacora::TIPO_ACCESO_DENEGADO)->count(),
            'acciones_crud_mes' => Bitacora::delMes()->where('tipo_evento', Bitacora::TIPO_ACCION_CRUD)->count(),
        ];

        // 4. Listados únicos para llenar los selects del Front-end de forma dinámica
        $modulosDisponibles    = Bitacora::whereNotNull('modulo')->distinct()->pluck('modulo');
        $tiposEventoDisponibles = [
            Bitacora::TIPO_LOGIN_EXITOSO   => 'Login Exitoso',
            Bitacora::TIPO_LOGIN_FALLIDO   => 'Login Fallido',
            Bitacora::TIPO_LOGOUT          => 'Logout',
            Bitacora::TIPO_ACCESO_MODULO   => 'Acceso a Módulo',
            Bitacora::TIPO_ACCION_CRUD     => 'Acción CRUD',
            Bitacora::TIPO_ACCESO_DENEGADO => 'Acceso Denegado',
            Bitacora::TIPO_EXPORTACION     => 'Exportación de Datos',
        ];

        // 5. Paginación preservando los filtros en los enlaces generados por Inertia
        $registros = $query->paginate(30)->withQueryString();

        return Inertia::render('Admin/Bitacora/Index', [
            'registros'          => $registros,
            'kpis'               => $kpis,
            'modulosDisponibles' => $modulosDisponibles,
            'tiposEvento'        => $tiposEventoDisponibles,
            'filtros'            => $request->only(['buscar', 'tipo_evento', 'modulo', 'exitoso', 'fecha_desde', 'fecha_hasta'])
        ]);
    }

    /**
     * Ver detalle extendido de una línea de bitácora específica.
     */
    public function show($id)
    {
        $log = Bitacora::with('user')->findOrFail($id);

        return Inertia::render('Admin/Bitacora/Show', [
            'log' => $log
        ]);
    }

    /**
     * Exportación en formato CSV sin librerías externas.
     */
    public function exportarCsv(Request $request)
    {
        $fecha = Carbon::now()->format('Y_m_d_His');
        $filename = "auditoria_bitacora_{$fecha}.csv";

        $query = Bitacora::orderBy('ocurrido_en', 'desc');
        
        if ($request->fecha_desde) $query->whereDate('ocurrido_en', '>=', $request->fecha_desde);
        if ($request->fecha_hasta) $query->whereDate('ocurrido_en', '<=', $request->fecha_hasta);
        if ($request->tipo_evento) $query->where('tipo_evento', $request->tipo_evento);

        $registros = $query->get();

        if (class_exists('BitacoraService')) {
            BitacoraService::accionCrud(
                modulo: 'Auditoría',
                accion: 'Exportar Bitácora',
                registroId: 0,
                exitoso: true,
                detalle: 'Se descargó un reporte en CSV con ' . $registros->count() . ' registros.'
            );
        }

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($registros) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Fecha', 'Usuario/Intento', 'Tipo Evento', 'Módulo', 'Acción', 'URL', 'Método HTTP', 'IP', 'Resultado', 'Detalle']);

            foreach ($registros as $row) {
                fputcsv($file, [
                    $row->ocurrido_en->format('Y-m-d H:i:s'),
                    $row->nombre_usuario ?? $row->email_intento ?? 'Sistema',
                    $row->tipo_evento,
                    $row->modulo ?? 'N/A',
                    $row->accion ?? 'N/A',
                    $row->url,
                    $row->metodo_http,
                    $row->ip,
                    $row->exitoso ? 'Exitoso' : 'Fallido',
                    $row->detalle
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}