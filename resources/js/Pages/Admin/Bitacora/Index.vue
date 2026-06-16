<template>
    <AppLayout title="Auditoría del Sistema - Bitácoras">
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Auditoría del Sistema (Bitácoras)
                </h2>
                <button @click="exportarDatos"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors shadow-sm text-sm font-medium">
                    <i class="fas fa-file-csv mr-2"></i>
                    Exportar CSV
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                            <i class="fas fa-history text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Eventos de Hoy</p>
                            <h4 class="text-2xl font-bold text-gray-800">{{ kpis.total_hoy }}</h4>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
                        <div class="p-3 bg-red-50 text-red-600 rounded-lg">
                            <i class="fas fa-user-times text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Logins Fallidos (Hoy)
                            </p>
                            <h4 class="text-2xl font-bold text-gray-800">{{ kpis.logins_fallidos }}</h4>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
                        <div class="p-3 bg-orange-50 text-orange-600 rounded-lg">
                            <i class="fas fa-ban text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Accesos Denegados</p>
                            <h4 class="text-2xl font-bold text-gray-800">{{ kpis.accesos_denegados }}</h4>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
                        <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
                            <i class="fas fa-database text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Acciones CRUD (Mes)
                            </p>
                            <h4 class="text-2xl font-bold text-gray-800">{{ kpis.acciones_crud_mes }}</h4>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                    <div class="p-6 border-b border-gray-200 bg-gray-50/50">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            <div class="lg:col-span-2 xl:col-span-1">
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Buscar
                                    término</label>
                                <div class="relative">
                                    <input type="text" v-model="formFiltros.buscar" @keyup.enter="aplicarFiltros"
                                        placeholder="Usuario, IP, detalle..."
                                        class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Módulo</label>
                                <select v-model="formFiltros.modulo" @change="aplicarFiltros"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    <option value="">Todos los módulos</option>
                                    <option v-for="mod in modulosDisponibles" :key="mod" :value="mod">{{ mod }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Tipo de
                                    Evento</label>
                                <select v-model="formFiltros.tipo_evento" @change="aplicarFiltros"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    <option value="">Todos los tipos</option>
                                    <option v-for="(label, key) in tiposEvento" :key="key" :value="key">{{ label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase mb-1">Resultado</label>
                                <select v-model="formFiltros.exitoso" @change="aplicarFiltros"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                                    <option value="">Todos</option>
                                    <option value="1">Exitoso</option>
                                    <option value="0">Fallido</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Desde
                                    fecha</label>
                                <input type="date" v-model="formFiltros.fecha_desde" @change="aplicarFiltros"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Hasta
                                    fecha</label>
                                <input type="date" v-model="formFiltros.fecha_hasta" @change="aplicarFiltros"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div class="md:col-span-2 lg:col-span-1 xl:col-span-2 flex items-end space-x-2">
                                <button @click="aplicarFiltros"
                                    class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg font-medium transition-colors">
                                    Filtrar
                                </button>
                                <button @click="limpiarFiltros"
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm px-4 py-2 rounded-lg font-medium transition-colors">
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha /
                                        Hora</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Usuario /
                                        Cuenta</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Evento /
                                        Módulo</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acción
                                        ejecutada</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Dirección
                                        IP</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado</th>
                                    <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Detalles
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="log in registros.data" :key="log.id"
                                    class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                                        {{ formatFecha(log.ocurrido_en) }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ log.user ? log.user.name : (log.nombre_usuario || 'Sistema') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ log.user ? log.user.email : (log.email_intento || '') }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs font-semibold text-gray-700">
                                            {{ tiposEvento[log.tipo_evento] || log.tipo_evento }}
                                        </div>
                                        <span
                                            class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-medium rounded-md bg-gray-100 text-gray-600">
                                            {{ log.modulo || 'N/A' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-700 max-w-[200px] truncate">
                                        {{ log.accion || 'N/A' }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-mono text-gray-600">
                                        <span class="bg-gray-50 px-1.5 py-0.5 border border-gray-200 rounded">
                                            {{ log.ip }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'inline-flex px-2.5 py-1 text-xs font-semibold rounded-full',
                                            log.exitoso ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        ]">
                                            <i
                                                :class="[log.exitoso ? 'fas fa-check-circle' : 'fas fa-times-circle', 'mr-1 mt-0.5']"></i>
                                            {{ log.exitoso ? 'Exitoso' : 'Fallido' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="verDetalle(log.id)"
                                            class="text-purple-600 hover:text-purple-900 flex items-center space-x-1">
                                            <i class="fas fa-eye text-xs"></i>
                                            <span>Inspeccionar</span>
                                        </button>
                                    </td>
                                </tr>

                                <tr v-if="registros.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-shield-alt text-4xl mb-3 text-gray-300"></i>
                                        <p class="text-sm font-medium">No se encontraron registros de auditoría en la
                                            búsqueda
                                            actual</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between bg-gray-50">
                        <div class="text-sm text-gray-600">
                            Mostrando registros del <span class="font-semibold">{{ registros.from || 0 }}</span> al
                            <span class="font-semibold">{{ registros.to || 0 }}</span> de un total de <span
                                class="font-semibold">{{ registros.total }}</span>
                        </div>
                        <div class="flex space-x-1">
                            <template v-for="(link, key) in registros.links" :key="key">
                                <button v-if="link.url" @click="navegarPagina(link.url)" :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-md transition-colors border',
                                    link.active
                                        ? 'bg-purple-600 text-white border-purple-600'
                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                ]" v-html="link.label" />
                                <span v-else
                                    class="px-3 py-1.5 text-xs text-gray-400 bg-gray-100 border border-gray-200 rounded-md cursor-not-allowed"
                                    v-html="link.label" />
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    registros: Object,
    kpis: Object,
    modulosDisponibles: Array,
    tiposEvento: Object,
    filtros: Object
});

// Inicializamos el formulario local mapeando las props inyectadas por el Request de Laravel
const formFiltros = ref({
    buscar: props.filtros.buscar || '',
    tipo_evento: props.filtros.tipo_evento || '',
    modulo: props.filtros.modulo || '',
    exitoso: props.filtros.exitoso !== undefined ? props.filtros.exitoso : '',
    fecha_desde: props.filtros.fecha_desde || '',
    fecha_hasta: props.filtros.fecha_hasta || ''
});

const aplicarFiltros = () => {
    router.get(route('bitacoras.index'), formFiltros.value, {
        preserveState: true,
        replace: true
    });
};

const limpiarFiltros = () => {
    formFiltros.value = { buscar: '', tipo_evento: '', modulo: '', exitoso: '', fecha_desde: '', fecha_hasta: '' };
    router.get(route('bitacoras.index'));
};

const navegarPagina = (url) => {
    router.get(url, {}, { preserveState: true });
};

const verDetalle = (id) => {
    router.get(route('bitacoras.show', id));
};

const exportarDatos = () => {
    // Arma la URL con los parámetros actuales para que el CSV filtre según los inputs activos
    const params = new URLSearchParams(formFiltros.value).toString();
    window.location.href = `${route('bitacoras.exportar')}?${params}`;
};

const formatFecha = (fechaStr) => {
    if (!fechaStr) return '';
    const date = new Date(fechaStr);
    return date.toLocaleString('es-BO', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    });
};
</script>