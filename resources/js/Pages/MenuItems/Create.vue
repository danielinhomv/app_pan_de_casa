<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    parents:        { type: Array, default: () => [] },
    availableRoles: { type: Array, default: () => [] },
});

const form = useForm({
    title:     '',
    route:     '',
    icon:      '',
    parent_id: null,
    order:     0,
    is_active: true,
    roles:     [],
});

const submit = () => {
    // Garantizar que roles siempre sea array — el controller hace array_values()
    if (!form.roles) form.roles = [];
    form.post(route('menu.store'));
};
</script>

<template>
    <AppLayout title="Crear Item de Menú">
        <template #header>
            <div class="flex items-center space-x-3">
                <Link :href="route('menu.index')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Crear Nuevo Item de Menú
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-6">

                            <!-- Título -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Título *</label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                />
                                <p v-if="form.errors.title" class="text-red-600 text-sm mt-1">{{ form.errors.title }}</p>
                            </div>

                            <!-- Ruta -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ruta (Laravel route name)</label>
                                <input
                                    v-model="form.route"
                                    type="text"
                                    placeholder="pedidos.index"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                />
                                <p v-if="form.errors.route" class="text-red-600 text-sm mt-1">{{ form.errors.route }}</p>
                            </div>

                            <!-- Icono -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Icono (Font Awesome class)</label>
                                <div class="mt-1 flex items-center gap-3">
                                    <input
                                        v-model="form.icon"
                                        type="text"
                                        placeholder="fas fa-home"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                    />
                                    <i v-if="form.icon" :class="form.icon" class="text-gray-500 text-lg w-6 text-center"></i>
                                </div>
                                <p v-if="form.errors.icon" class="text-red-600 text-sm mt-1">{{ form.errors.icon }}</p>
                            </div>

                            <!-- Menú Padre -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Menú Padre (opcional)</label>
                                <select
                                    v-model="form.parent_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                >
                                    <option :value="null">-- Sin padre (nivel raíz) --</option>
                                    <option v-for="parent in parents" :key="parent.id" :value="parent.id">
                                        {{ parent.title }}
                                    </option>
                                </select>
                                <p v-if="form.errors.parent_id" class="text-red-600 text-sm mt-1">{{ form.errors.parent_id }}</p>
                            </div>

                            <!-- Orden -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Orden *</label>
                                <input
                                    v-model.number="form.order"
                                    type="number"
                                    min="0"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                />
                                <p class="text-xs text-gray-400 mt-1">1 = primero en el menú</p>
                                <p v-if="form.errors.order" class="text-red-600 text-sm mt-1">{{ form.errors.order }}</p>
                            </div>

                            <!-- Roles -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Roles con acceso</label>
                                <div class="space-y-2 bg-gray-50 rounded-lg p-3">
                                    <div v-for="role in availableRoles" :key="role" class="flex items-center">
                                        <input
                                            :id="`role-${role}`"
                                            v-model="form.roles"
                                            :value="role"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-amber-600 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                        />
                                        <label :for="`role-${role}`" class="ml-2 text-sm text-gray-700 capitalize">{{ role }}</label>
                                    </div>
                                    <p class="text-xs text-gray-500 pt-1 border-t border-gray-200 mt-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Sin roles seleccionados = visible para todos los usuarios autenticados
                                    </p>
                                </div>
                                <p v-if="form.errors.roles" class="text-red-600 text-sm mt-1">{{ form.errors.roles }}</p>
                            </div>

                            <!-- Activo -->
                            <div class="flex items-center">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    id="is_active"
                                    class="rounded border-gray-300 text-amber-600 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                />
                                <label for="is_active" class="ml-2 text-sm text-gray-700">Activo (visible en el menú)</label>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                                <Link
                                    :href="route('menu.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors"
                                >
                                    Cancelar
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-700 disabled:opacity-50 transition-colors flex items-center"
                                >
                                    <i class="fas fa-save mr-2"></i>
                                    {{ form.processing ? 'Guardando...' : 'Crear Item' }}
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>