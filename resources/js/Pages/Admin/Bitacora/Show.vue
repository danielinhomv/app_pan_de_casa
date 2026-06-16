<template>
    <AppLayout title="Inspección de Registro de Auditoría">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalle del Log #{{ log.id }}
                </h2>
                <button @click="regresar"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center transition-colors text-sm font-medium border border-gray-300">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al Listado
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div :class="[
                    'p-6 rounded-2xl shadow-sm border flex items-center justify-between',
                    log.exitoso ? 'bg-green-50 border-green-200 text-green-900' : 'bg-red-50 border-red-200 text-red-900'
                ]">
                    <div class="flex items-center space-x-3">
                        <i
                            :class="[log.exitoso ? 'fas fa-shield-alt text-2xl text-green-600' : 'fas fa-exclamation-triangle text-2xl text-red-600']"></i>
                        <div>
                            <h3 class="font-bold text-lg">Operación {{ log.exitoso ? 'Completada Exitosamente' :
                                'Fallida / Alerta' }}</h3>
                            <p class="text-xs opacity-80">Registrado de forma inmutable por el módulo de seguridad.</p>
                        </div>
                    </div>
                    <span
                        :class="['px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider', log.exitoso ? 'bg-green-200 text-green-900' : 'bg-red-200 text-red-900']">
                        {{ log.exitoso ? 'OK' : 'FAIL' }}
                    </span>
                </div>

                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">

                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Datos del Evento</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs text-gray-500 font-medium">Fecha y Hora Precisa</label>
                                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ formatFecha(log.ocurrido_en) }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 font-medium">Módulo del Sistema</label>
                                <p class="text-sm font-semibold text-purple-700 mt-0.5">{{ log.modulo || 'General /Raíz'}}</p>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 font-medium">Identificador de Evento</label>
                                <p class="text-sm font-mono font-medium text-gray-800 mt-0.5">{{ log.tipo_evento }}</p>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 font-medium">Acción Descriptiva</label>
                                <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ log.accion || 'Sin registrar'
                                }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-b border-gray-100">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Actor Responsable</h4>
                        <div class="flex items-center space-x-4">
                            <div
                                class="h-12 w-12 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center font-bold text-lg shadow-inner">
                                {{ getIniciales }}
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                                <div>
                                    <label class="block text-xs text-gray-400">Usuario Activo</label>
                                    <p class="text-sm font-semibold text-gray-900">{{ log.user ? log.user.name :
                                        (log.nombre_usuario || 'Sistema / Tarea Programada') }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400">Correo Electrónico Utilizado</label>
                                    <p class="text-sm text-gray-700">{{ log.user ? log.user.email : (log.email_intento
                                        || 'N/A')
                                    }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Metadatos de Red y
                            Servidor
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs text-gray-500 font-medium">Dirección IP de Origen</label>
                                <p
                                    class="text-sm font-mono font-bold text-gray-800 mt-0.5 bg-white px-2 py-1 border rounded shadow-sm inline-block">
                                    {{ log.ip }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 font-medium">Método HTTP</label>
                                <p class="mt-0.5">
                                    <span
                                        :class="['px-2 py-0.5 text-xs font-bold rounded-md uppercase text-white', getMetodoColor(log.metodo_http)]">
                                        {{ log.metodo_http || 'GET' }}
                                    </span>
                                </p>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs text-gray-500 font-medium">Ruta del End-point (URL)</label>
                                <p
                                    class="text-xs font-mono text-gray-700 bg-white border p-2 rounded mt-1 overflow-x-auto select-all">
                                    {{ log.url || '/' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Detalle Expandido /
                            Mensaje
                        </h4>
                        <div
                            class="bg-gray-900 text-gray-100 font-mono text-xs p-4 rounded-xl shadow-inner max-h-60 overflow-y-auto whitespace-pre-wrap leading-relaxed select-text">
                            {{ log.detalle || 'No se registraron parámetros o payload extra para este evento.' }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    log: Object
});

const regresar = () => {
    router.get(route('bitacoras.index'));
};

const formatFecha = (fechaStr) => {
    if (!fechaStr) return '';
    return new Date(fechaStr).toLocaleString('es-BO', {
        year: 'numeric', month: 'long', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    });
};

const getIniciales = computed(() => {
    if (props.log.user && props.log.user.name) {
        return props.log.user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    }
    if (props.log.nombre_usuario) {
        return props.log.nombre_usuario.substring(0, 2).toUpperCase();
    }
    return 'SYS';
});

const getMetodoColor = (metodo) => {
    const m = (metodo || 'GET').toUpperCase();
    if (m === 'POST') return 'bg-blue-600';
    if (m === 'PUT' || m === 'PATCH') return 'bg-amber-600';
    if (m === 'DELETE') return 'bg-red-600';
    return 'bg-green-600'; // Default para GET
};
</script>