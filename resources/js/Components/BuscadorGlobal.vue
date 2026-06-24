<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const query       = ref('');
const resultados  = ref([]);
const cargando    = ref(false);
const mostrar     = ref(false);
const inputRef    = ref(null);

let debounceTimer = null;

// ── Colores por tipo ──────────────────────────────────────────────────────────
const colorBadge = (color) => ({
    blue:   'bg-blue-100 text-blue-700',
    amber:  'bg-amber-100 text-amber-700',
    green:  'bg-green-100 text-green-700',
    purple: 'bg-purple-100 text-purple-700',
    gray:   'bg-gray-100 text-gray-600',
}[color] ?? 'bg-gray-100 text-gray-600');

const colorIcono = (color) => ({
    blue:   'text-blue-500',
    amber:  'text-amber-500',
    green:  'text-green-500',
    purple: 'text-purple-500',
    gray:   'text-gray-400',
}[color] ?? 'text-gray-400');

// ── Búsqueda con debounce ─────────────────────────────────────────────────────
watch(query, (val) => {
    clearTimeout(debounceTimer);
    if (val.trim().length < 2) {
        resultados.value = [];
        mostrar.value    = false;
        return;
    }
    cargando.value = true;
    debounceTimer  = setTimeout(() => buscar(val.trim()), 350);
});

const buscar = async (q) => {
    try {
        const res  = await fetch(`/buscar?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        resultados.value = data;
        mostrar.value    = true;
    } catch (e) {
        resultados.value = [];
    } finally {
        cargando.value = false;
    }
};

const irA = (url) => {
    mostrar.value  = false;
    query.value    = '';
    resultados.value = [];
    router.visit(url);
};

// ── Atajo de teclado Ctrl+K / Cmd+K ──────────────────────────────────────────
const onKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        inputRef.value?.focus();
    }
    if (e.key === 'Escape') {
        mostrar.value = false;
        query.value   = '';
    }
};

// ── Cerrar al click fuera ─────────────────────────────────────────────────────
const onClickOutside = (e) => {
    if (!e.target.closest('.buscador-global')) {
        mostrar.value = false;
    }
};

onMounted(() => {
    document.addEventListener('keydown', onKeydown);
    document.addEventListener('click', onClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown);
    document.removeEventListener('click', onClickOutside);
});
</script>

<template>
    <div class="buscador-global relative flex items-center">
        <!-- Campo de búsqueda -->
        <div class="relative">
            <input
                ref="inputRef"
                v-model="query"
                type="text"
                placeholder="Buscar... (Ctrl+K)"
                class="w-56 lg:w-80 pl-9 pr-4 py-1.5 text-sm bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all duration-200 focus:w-96"
                @focus="mostrar = resultados.length > 0"
            />
            <!-- Ícono búsqueda / spinner -->
            <div class="absolute left-2.5 top-2 text-gray-400">
                <i v-if="!cargando" class="fas fa-search text-xs"></i>
                <svg v-else class="animate-spin h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
            </div>
            <!-- Limpiar -->
            <button
                v-if="query"
                @click="query = ''; resultados = []; mostrar = false"
                class="absolute right-2 top-1.5 text-gray-300 hover:text-gray-500 transition-colors"
            >
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Dropdown de resultados -->
        <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mostrar && (resultados.length > 0 || cargando)"
                class="absolute top-full left-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 z-50 overflow-hidden"
            >
                <!-- Cargando -->
                <div v-if="cargando && resultados.length === 0" class="px-4 py-6 text-center text-sm text-gray-400">
                    <i class="fas fa-circle-notch fa-spin mr-2"></i> Buscando...
                </div>

                <!-- Resultados -->
                <ul v-else class="divide-y divide-gray-50 dark:divide-gray-700 max-h-80 overflow-y-auto">
                    <li
                        v-for="(r, i) in resultados"
                        :key="i"
                        @click="irA(r.url)"
                        class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors group"
                    >
                        <!-- Ícono -->
                        <div class="mt-0.5 flex-shrink-0">
                            <i :class="[r.icono, colorIcono(r.color), 'text-sm w-4 text-center']"></i>
                        </div>
                        <!-- Texto -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate group-hover:text-amber-600 transition-colors">
                                {{ r.titulo }}
                            </p>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ r.subtitulo }}</p>
                        </div>
                        <!-- Badge tipo -->
                        <span :class="['text-xs px-1.5 py-0.5 rounded font-medium flex-shrink-0', colorBadge(r.color)]">
                            {{ r.tipo }}
                        </span>
                    </li>
                </ul>

                <!-- Sin resultados -->
                <div v-if="!cargando && resultados.length === 0" class="px-4 py-6 text-center text-sm text-gray-400">
                    <i class="fas fa-search mr-2"></i> Sin resultados para "<strong>{{ query }}</strong>"
                </div>

                <!-- Footer -->
                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs text-gray-400 flex items-center gap-2">
                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded text-xs">↵</kbd>
                        para ir
                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded text-xs">Esc</kbd>
                        para cerrar
                    </p>
                </div>
            </div>
        </transition>
    </div>
</template>