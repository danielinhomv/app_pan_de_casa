<!-- Index.vue -->
<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ListaProductos from './Components/ListaProductos.vue';
import CreateProductoModal from './Create.vue';
import UpdateProductoModal from './Update.vue';

const props = defineProps({
    productos: { type: Array, default: () => [] }
});

const productosLocal = ref((props.productos || []).map(p => ({ ...p })));
watch(() => props.productos, (n) => productosLocal.value = (n || []).map(p => ({ ...p })), { immediate: true });

const showCreateModal  = ref(false);
const showEditModal    = ref(false);
const editingProduct   = ref(null);
const showSuccess      = ref(false);
const successMessage   = ref('');

const openCreateModal = () => showCreateModal.value = true;
const closeCreateModal = () => showCreateModal.value = false;

const onCreated = () => {
    showCreateModal.value = false;
    showSuccess.value     = true;
    successMessage.value  = 'Producto creado correctamente';
    setTimeout(() => showSuccess.value = false, 3000);
};

const openEditModal  = (p) => { editingProduct.value = p; showEditModal.value = true; };
const closeEditModal = () => { editingProduct.value = null; showEditModal.value = false; };

const onUpdated = () => {
    showEditModal.value  = false;
    showSuccess.value    = true;
    successMessage.value = 'Producto actualizado correctamente';
    setTimeout(() => showSuccess.value = false, 3000);
};

const onToggle = async (p) => {
    const newState = !p.is_active;
    const found = productosLocal.value.find(x => x.id === p.id);
    if (found) found.is_active = newState;
    try {
        await router.put(route('productos.update', p.id), {
            nombre: p.nombre, unidad_medida: p.unidad_medida,
            precio_venta: p.precio_venta, descripcion: p.descripcion,
            is_active: newState,
        });
        showSuccess.value    = true;
        successMessage.value = `Producto ${newState ? 'activado' : 'desactivado'}`;
        setTimeout(() => showSuccess.value = false, 3000);
    } catch (e) {
        if (found) found.is_active = !newState;
        console.error(e);
    }
};

const onDelete = async (p) => {
    if (!confirm(`Eliminar producto "${p.nombre}"? Esta acción es definitiva.`)) return;
    try {
        await router.delete(route('productos.destroy', p.id), {
            onSuccess: () => {
                showSuccess.value    = true;
                successMessage.value = 'Producto eliminado';
                setTimeout(() => showSuccess.value = false, 3000);
            }
        });
    } catch (e) { console.error(e); }
};
</script>

<template>
    <AppLayout title="Lista de Productos">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-900 dark:text-gray-100 leading-tight">
                    Lista de Productos
                </h2>
                <button
                    @click="openCreateModal"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
                >
                    <i class="fas fa-plus mr-2"></i> Nuevo Producto
                </button>
            </div>
        </template>

        <div class="py-12 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- ── CAMBIO: tarjetas de resumen de stock general ── -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Con stock</p>
                            <p class="text-2xl font-bold text-emerald-600">
                                {{ productosLocal.filter(p => p.stock_disponible > 0).length }}
                            </p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Stock bajo (≤5)</p>
                            <p class="text-2xl font-bold text-amber-600">
                                {{ productosLocal.filter(p => p.stock_disponible > 0 && p.stock_disponible <= 5).length }}
                            </p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Sin stock</p>
                            <p class="text-2xl font-bold text-red-600">
                                {{ productosLocal.filter(p => p.stock_disponible <= 0).length }}
                            </p>
                        </div>
                    </div>
                </div>
                <!-- ── FIN CAMBIO ── -->

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div v-if="showSuccess" class="mb-4 text-sm text-green-700 bg-green-100 px-4 py-2 rounded">
                        {{ successMessage }}
                    </div>

                    <ListaProductos
                        :products="productosLocal"
                        @edit="openEditModal"
                        @toggle="onToggle"
                        @delete="onDelete"
                    />
                </div>
            </div>
        </div>

        <CreateProductoModal v-if="showCreateModal" @close="closeCreateModal" @created="onCreated" />
        <UpdateProductoModal v-if="showEditModal && editingProduct" :product="editingProduct" @close="closeEditModal" @updated="onUpdated" />
    </AppLayout>
</template>