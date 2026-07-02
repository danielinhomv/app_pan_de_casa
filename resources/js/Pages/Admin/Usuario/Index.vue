<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CreateModal from './CreateModal.vue';

const props = defineProps({
    usuarios: { type: Array, default: () => [] },
    roles:    { type: Array, default: () => [] },
});

const showCreateModal = ref(false);
const showSuccessMessage = ref(false);
const successMessage = ref('');

const etiquetaRol = (rol) => ({
    propietario:      'Propietario',
    encargadoalmacen: 'Encargado de Almacén',
    produccion:       'Producción',
}[rol] ?? rol);

const badgeRol = (rol) => ({
    propietario:      'bg-amber-100 text-amber-700',
    encargadoalmacen: 'bg-blue-100 text-blue-700',
    produccion:       'bg-purple-100 text-purple-700',
}[rol] ?? 'bg-gray-100 text-gray-600');

const iconoRol = (rol) => ({
    propietario:      'fas fa-crown',
    encargadoalmacen: 'fas fa-warehouse',
    produccion:       'fas fa-industry',
}[rol] ?? 'fas fa-user');

const eliminar = (id, nombre) => {
    if (!confirm(`¿Eliminar al usuario "${nombre}"? Esta acción no se puede deshacer.`)) return;
    router.delete(route('usuarios.destroy', id), {
        onSuccess: () => showSuccess('Usuario eliminado correctamente.'),
    });
};

const showSuccess = (msg) => {
    successMessage.value = msg;
    showSuccessMessage.value = true;
    setTimeout(() => { showSuccessMessage.value = false; }, 4000);
};
</script>

<template>
    <AppLayout title="Gestión de Usuarios">
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        <i class="fas fa-users mr-2 text-amber-600"></i>
                        Gestión de Usuarios
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Solo personal interno — los clientes se registran aparte</p>
                </div>
                <button
                    @click="showCreateModal = true"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors text-sm font-medium"
                >
                    <i class="fas fa-user-plus"></i>
                    Nuevo Usuario
                </button>
            </div>
        </template>

        <!-- Toast éxito -->
        <div v-if="showSuccessMessage" class="fixed top-4 right-4 z-50">
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 animate-fade-in">
                <i class="fas fa-check-circle"></i>
                <span>{{ successMessage }}</span>
            </div>
        </div>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Stats rápidas -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
                        <div class="bg-amber-100 p-2.5 rounded-lg"><i class="fas fa-crown text-amber-600"></i></div>
                        <div>
                            <p class="text-xs text-gray-500">Propietarios</p>
                            <p class="text-xl font-bold text-gray-800">{{ usuarios.filter(u => u.rol === 'propietario').length }}</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
                        <div class="bg-blue-100 p-2.5 rounded-lg"><i class="fas fa-warehouse text-blue-600"></i></div>
                        <div>
                            <p class="text-xs text-gray-500">Almacén</p>
                            <p class="text-xl font-bold text-gray-800">{{ usuarios.filter(u => u.rol === 'encargadoalmacen').length }}</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
                        <div class="bg-purple-100 p-2.5 rounded-lg"><i class="fas fa-industry text-purple-600"></i></div>
                        <div>
                            <p class="text-xs text-gray-500">Producción</p>
                            <p class="text-xl font-bold text-gray-800">{{ usuarios.filter(u => u.rol === 'produccion').length }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado</th>
                                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="usuario in usuarios"
                                    :key="usuario.id"
                                    class="hover:bg-gray-50 transition-colors"
                                >
                                    <!-- Nombre -->
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i :class="iconoRol(usuario.rol)" class="text-amber-600 text-xs"></i>
                                            </div>
                                            <span class="font-medium text-gray-800">{{ usuario.name }}</span>
                                        </div>
                                    </td>

                                    <!-- Email -->
                                    <td class="px-5 py-3 text-gray-500">{{ usuario.email }}</td>

                                    <!-- Rol -->
                                    <td class="px-5 py-3">
                                        <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold', badgeRol(usuario.rol)]">
                                            <i :class="iconoRol(usuario.rol)" class="text-xs"></i>
                                            {{ etiquetaRol(usuario.rol) }}
                                        </span>
                                    </td>

                                    <!-- Fecha -->
                                    <td class="px-5 py-3 text-gray-400 text-xs">{{ usuario.created_at }}</td>

                                    <!-- Acciones -->
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-end gap-3">
                                            <Link
                                                :href="route('usuarios.show', usuario.id)"
                                                class="text-indigo-600 hover:text-indigo-800 transition-colors text-xs font-medium"
                                            >
                                                <i class="fas fa-eye mr-1"></i> Ver
                                            </Link>
                                            <button
                                                @click="eliminar(usuario.id, usuario.name)"
                                                class="text-red-500 hover:text-red-700 transition-colors text-xs font-medium"
                                            >
                                                <i class="fas fa-trash mr-1"></i> Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="usuarios.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        <i class="fas fa-users text-4xl mb-3 block text-gray-200"></i>
                                        <p class="font-medium">No hay usuarios registrados</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
                        {{ usuarios.length }} usuario{{ usuarios.length !== 1 ? 's' : '' }} en total
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal crear -->
        <CreateModal
            :show="showCreateModal"
            :roles="roles"
            @close="showCreateModal = false"
        />
    </AppLayout>
</template>

<style scoped>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { animation: fade-in 0.3s ease-out; }
</style>