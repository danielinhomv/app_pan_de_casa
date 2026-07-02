<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show:  { type: Boolean, required: true },
    roles: { type: Array,   default: () => [] },
});

const emit = defineEmits(['close']);

const form = useForm({
    name:                  '',
    email:                 '',
    rol:                   '',
    password:              '',
    password_confirmation: '',
});

const etiquetaRol = (rol) => ({
    propietario:      'Propietario',
    encargadoalmacen: 'Encargado de Almacén',
    produccion:       'Producción',
}[rol] ?? rol);

const iconoRol = (rol) => ({
    propietario:      'fas fa-crown text-amber-500',
    encargadoalmacen: 'fas fa-warehouse text-blue-500',
    produccion:       'fas fa-industry text-purple-500',
}[rol] ?? 'fas fa-user text-gray-400');

const submit = () => {
    form.post(route('usuarios.store'), {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const cerrar = () => {
    if (!form.processing) {
        form.reset();
        form.clearErrors();
        emit('close');
    }
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            @click="cerrar"
        ></div>

        <!-- Modal -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full overflow-hidden transform transition-all z-10">

            <!-- Header -->
            <div class="p-6 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Nuevo Usuario</h3>
                            <p class="text-xs text-gray-500">Los clientes se registran por su cuenta</p>
                        </div>
                    </div>
                    <button @click="cerrar" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4">

                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nombre completo *
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Ej: Juan Pérez"
                        :class="['w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-colors',
                                  form.errors.name ? 'border-red-400 bg-red-50' : 'border-gray-300']"
                    />
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico *
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="usuario@ejemplo.com"
                        :class="['w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-colors',
                                  form.errors.email ? 'border-red-400 bg-red-50' : 'border-gray-300']"
                    />
                    <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                </div>

                <!-- Rol -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                    <div class="grid grid-cols-1 gap-2">
                        <label
                            v-for="rol in roles"
                            :key="rol"
                            :class="['flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all',
                                      form.rol === rol
                                        ? 'border-amber-400 bg-amber-50 ring-1 ring-amber-400'
                                        : 'border-gray-200 hover:border-amber-200 hover:bg-amber-50/50']"
                        >
                            <input
                                v-model="form.rol"
                                type="radio"
                                :value="rol"
                                class="text-amber-500 focus:ring-amber-400"
                            />
                            <i :class="iconoRol(rol)"></i>
                            <span class="text-sm font-medium text-gray-700">{{ etiquetaRol(rol) }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.rol" class="text-red-500 text-xs mt-1">{{ form.errors.rol }}</p>
                </div>

                <!-- Contraseña -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña *
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="Mínimo 8 caracteres"
                        :class="['w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-colors',
                                  form.errors.password ? 'border-red-400 bg-red-50' : 'border-gray-300']"
                    />
                    <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                </div>

                <!-- Confirmar contraseña -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar contraseña *
                    </label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="Repite la contraseña"
                        :class="['w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-colors',
                                  form.errors.password_confirmation ? 'border-red-400 bg-red-50' : 'border-gray-300']"
                    />
                    <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
                </div>

            </div>

            <!-- Footer -->
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-3">
                <button
                    :disabled="form.processing"
                    @click="cerrar"
                    class="flex-1 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 font-medium hover:bg-gray-50 transition active:scale-95 disabled:opacity-50"
                >
                    Cancelar
                </button>
                <button
                    :disabled="form.processing"
                    @click="submit"
                    class="flex-1 py-2.5 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 shadow-sm transition active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <i v-if="form.processing" class="fas fa-spinner animate-spin"></i>
                    {{ form.processing ? 'Creando...' : 'Crear usuario' }}
                </button>
            </div>
        </div>
    </div>
</template>