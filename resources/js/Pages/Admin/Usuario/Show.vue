<script setup>
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    usuario: { type: Object, required: true },
});

const roles = ['propietario', 'encargadoalmacen', 'produccion'];

const editando = ref(false);

const form = useForm({
    name:                  props.usuario.name,
    email:                 props.usuario.email,
    rol:                   props.usuario.rol,
    password:              '',
    password_confirmation: '',
});

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

const submit = () => {
    form.put(route('usuarios.update', props.usuario.id), {
        onSuccess: () => { editando.value = false; },
    });
};

const cancelar = () => {
    form.reset();
    form.clearErrors();
    editando.value = false;
};
</script>

<template>
    <AppLayout title="Detalle de Usuario">
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('usuarios.index')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-user mr-2 text-amber-600"></i>
                    {{ usuario.name }}
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-5">

                <!-- Info actual -->
                <div v-if="!editando" class="bg-white rounded-xl shadow p-6 space-y-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-700">Información del usuario</h3>
                        <button
                            @click="editando = true"
                            class="text-sm text-amber-600 hover:text-amber-700 font-medium flex items-center gap-1"
                        >
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>

                    <dl class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">Nombre</dt>
                            <dd class="text-sm font-medium text-gray-800">{{ usuario.name }}</dd>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">Correo</dt>
                            <dd class="text-sm text-gray-700">{{ usuario.email }}</dd>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-50">
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">Rol</dt>
                            <dd>
                                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold', badgeRol(usuario.rol)]">
                                    <i :class="iconoRol(usuario.rol)"></i>
                                    {{ etiquetaRol(usuario.rol) }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <dt class="text-xs text-gray-400 uppercase tracking-wide">Registrado</dt>
                            <dd class="text-sm text-gray-500">{{ usuario.created_at }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Formulario edición -->
                <div v-else class="bg-white rounded-xl shadow p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-edit text-amber-500"></i> Editar usuario
                    </h3>

                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            :class="['w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent',
                                      form.errors.name ? 'border-red-400 bg-red-50' : 'border-gray-300']"
                        />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo *</label>
                        <input
                            v-model="form.email"
                            type="email"
                            :class="['w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent',
                                      form.errors.email ? 'border-red-400 bg-red-50' : 'border-gray-300']"
                        />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rol *</label>
                        <div class="grid grid-cols-1 gap-2">
                            <label
                                v-for="rol in roles"
                                :key="rol"
                                :class="['flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all',
                                          form.rol === rol
                                            ? 'border-amber-400 bg-amber-50 ring-1 ring-amber-400'
                                            : 'border-gray-200 hover:border-amber-200']"
                            >
                                <input v-model="form.rol" type="radio" :value="rol" class="text-amber-500" />
                                <i :class="[iconoRol(rol), 'text-sm']"></i>
                                <span class="text-sm font-medium text-gray-700">{{ etiquetaRol(rol) }}</span>
                            </label>
                        </div>
                        <p v-if="form.errors.rol" class="text-red-500 text-xs mt-1">{{ form.errors.rol }}</p>
                    </div>

                    <!-- Nueva contraseña (opcional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nueva contraseña <span class="text-gray-400 font-normal">(dejar vacío para no cambiar)</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="Mínimo 8 caracteres"
                            :class="['w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent',
                                      form.errors.password ? 'border-red-400 bg-red-50' : 'border-gray-300']"
                        />
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>

                    <div v-if="form.password">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña *</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Repite la contraseña"
                            :class="['w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent',
                                      form.errors.password_confirmation ? 'border-red-400 bg-red-50' : 'border-gray-300']"
                        />
                        <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3 pt-2">
                        <button
                            @click="cancelar"
                            :disabled="form.processing"
                            class="flex-1 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 font-medium hover:bg-gray-50 transition disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="submit"
                            :disabled="form.processing"
                            class="flex-1 py-2.5 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 transition disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <i v-if="form.processing" class="fas fa-spinner animate-spin"></i>
                            {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>
                </div>

                <!-- Volver -->
                <div class="flex justify-end">
                    <Link
                        :href="route('usuarios.index')"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors flex items-center gap-1"
                    >
                        <i class="fas fa-arrow-left"></i> Volver a usuarios
                    </Link>
                </div>

            </div>
        </div>
    </AppLayout>
</template>