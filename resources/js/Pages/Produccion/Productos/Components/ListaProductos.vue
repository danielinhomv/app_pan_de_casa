<!-- ListaProductos.vue -->
<template>
    <div>
        <div class="p-6 border-b border-gray-200 flex flex-col md:flex-row gap-4 items-center">
            <div class="flex-1 relative">
                <input
                    v-model="searchQuery"
                    placeholder="Buscar productos..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>

            <div class="flex items-center gap-2">
                <select v-model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="true">Activos</option>
                    <option value="false">Inactivos</option>
                </select>

                <!-- ── CAMBIO: filtro por stock ── -->
                <select v-model="stockFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Todo el stock</option>
                    <option value="con_stock">Con stock</option>
                    <option value="stock_bajo">Stock bajo (≤5)</option>
                    <option value="sin_stock">Sin stock</option>
                </select>
                <!-- ── FIN CAMBIO ── -->
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagen</th>
                        <th
                            v-for="col in columns"
                            :key="col.field"
                            @click="onSort(col.field)"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                        >
                            <div class="flex items-center gap-1">
                                {{ col.label }}
                                <i class="fas fa-sort text-gray-400"></i>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="p in sorted"
                        :key="p.id"
                        class="hover:bg-gray-50 transition-colors"
                        :class="p.stock_disponible <= 0 ? 'bg-red-50 hover:bg-red-100' : ''"
                    >
                        <!-- Imagen -->
                        <td class="px-6 py-4">
                            <div class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 border relative">
                                <img
                                    :src="getImageUrl(p.imagen)"
                                    :alt="p.nombre"
                                    class="w-full h-full object-cover"
                                    @error="handleImageError"
                                />
                            </div>
                        </td>

                        <!-- ID -->
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">#{{ p.id }}</td>

                        <!-- Nombre -->
                        <td class="px-6 py-4 text-sm text-gray-900">{{ p.nombre }}</td>

                        <!-- Unidad -->
                        <td class="px-6 py-4 text-sm text-gray-900">{{ p.unidad_medida }}</td>

                        <!-- Precio -->
                        <td class="px-6 py-4 text-sm">
                            <p class="text-lg font-extrabold text-gray-900">
                                Bs{{ Number(p.precio_venta).toFixed(2) }}
                            </p>
                        </td>

                        <!-- ── CAMBIO: columna de stock ── -->
                        <td class="px-6 py-4 text-sm">
                            <div class="flex flex-col gap-1">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full w-fit"
                                    :class="p.stock_disponible > 5
                                        ? 'bg-emerald-100 text-emerald-800'
                                        : p.stock_disponible > 0
                                            ? 'bg-amber-100 text-amber-800'
                                            : 'bg-red-100 text-red-800'"
                                >
                                    <i
                                        class="fas"
                                        :class="p.stock_disponible > 5
                                            ? 'fa-check-circle'
                                            : p.stock_disponible > 0
                                                ? 'fa-exclamation-triangle'
                                                : 'fa-times-circle'"
                                    ></i>
                                    {{ p.stock_disponible > 0 ? `${p.stock_disponible} uds.` : 'Sin stock' }}
                                </span>

                                <!-- barra visual de stock -->
                                <div class="w-24 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :class="p.stock_disponible > 5
                                            ? 'bg-emerald-500'
                                            : p.stock_disponible > 0
                                                ? 'bg-amber-500'
                                                : 'bg-red-400'"
                                        :style="{ width: Math.min(100, (p.stock_disponible / 20) * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </td>
                        <!-- ── FIN CAMBIO ── -->

                        <!-- Estado -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="['inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                    p.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']"
                            >
                                {{ p.is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>

                        <!-- Acciones -->
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center justify-center space-x-3">
                                <button @click="$emit('edit', p)" class="text-blue-600 hover:text-blue-900">Editar</button>
                                <button @click="$emit('toggle', p)" :class="p.is_active ? 'text-orange-600' : 'text-green-600'">
                                    {{ p.is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                                <button @click="$emit('delete', p)" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="sorted.length === 0">
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2 text-gray-300 block"></i>
                            <p>No se encontraron productos</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    products:             { type: Array,  default: () => [] },
    initialSortField:     { type: String, default: 'id' },
    initialSortDirection: { type: String, default: 'desc' },
});

const searchQuery   = ref('');
const statusFilter  = ref('');
const stockFilter   = ref(''); // ── CAMBIO: nuevo filtro de stock ──
const sortField     = ref(props.initialSortField);
const sortDirection = ref(props.initialSortDirection);

const handleImageError = (e) => {
    if (!e.target.src.includes('placeholder')) {
        e.target.src = 'https://placehold.co/100x100/EEE/31343C?text=Producto';
    }
};

const getImageUrl = (img) => {
    if (!img || typeof img !== 'string') return 'https://placehold.co/100x100/EEE/31343C?text=Producto';
    if (img.startsWith('http') || img.startsWith('/')) return img;
    return `/${img}`;
};

const normalizar = (str) =>
    (str || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

const filtered = computed(() => {
    const q = normalizar(searchQuery.value);

    return props.products.filter(p => {
        const matchesSearch = !q ||
            normalizar(p.nombre).includes(q) ||
            normalizar(p.descripcion).includes(q);

        const matchesStatus = statusFilter.value === ''
            ? true
            : p.is_active === (statusFilter.value === 'true');

        // ── CAMBIO: aplicar filtro de stock ──
        const stock = p.stock_disponible ?? 0;
        const matchesStock =
            stockFilter.value === ''          ? true :
            stockFilter.value === 'con_stock' ? stock > 0 :
            stockFilter.value === 'stock_bajo'? stock > 0 && stock <= 5 :
            stockFilter.value === 'sin_stock' ? stock <= 0 : true;
        // ── FIN CAMBIO ──

        return matchesSearch && matchesStatus && matchesStock;
    });
});

const sorted = computed(() =>
    [...filtered.value].sort((a, b) => {
        let A = a[sortField.value];
        let B = b[sortField.value];
        if (typeof A === 'string') { A = A.toLowerCase(); B = (B || '').toLowerCase(); }
        if (A < B) return sortDirection.value === 'asc' ? -1 : 1;
        if (A > B) return sortDirection.value === 'asc' ? 1 : -1;
        return 0;
    })
);

const onSort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value     = field;
        sortDirection.value = 'asc';
    }
};

// ── CAMBIO: columna de stock agregada ──
const columns = [
    { field: 'id',               label: 'ID' },
    { field: 'nombre',           label: 'Producto' },
    { field: 'unidad_medida',    label: 'Unidad' },
    { field: 'precio_venta',     label: 'Precio' },
    { field: 'stock_disponible', label: 'Stock' },
    { field: 'is_active',        label: 'Estado' },
];
// ── FIN CAMBIO ──
</script>