<script setup>
const props = defineProps({
    modalidadPago: String,
    numeroCuotas: Number,
    total: Number
});

const emit = defineEmits(['update:modalidadPago', 'update:numeroCuotas']);
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-credit-card text-emerald-600"></i> Método de Pago
            </h3>
            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-purple-700 bg-purple-50 border border-purple-100 px-2.5 py-1 rounded-full">
                <i class="fas fa-qrcode"></i> Pago con QR PagoFácil
            </span>
        </div>

        <!-- Modalidad -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Modalidad de Pago</label>
            <div class="grid grid-cols-2 gap-3">
                <button @click="emit('update:modalidadPago', 'contado')" type="button"
                    :class="modalidadPago === 'contado'
                        ? 'bg-emerald-600 text-white shadow-md ring-2 ring-emerald-600 ring-offset-2'
                        : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'"
                    class="py-3 px-4 rounded-xl font-medium transition-all duration-200 flex items-center justify-center gap-2">
                    <i class="fas fa-money-bill-1-wave"></i>
                    Al Contado
                </button>
                <button @click="emit('update:modalidadPago', 'cuotas')" type="button"
                    :class="modalidadPago === 'cuotas'
                        ? 'bg-blue-600 text-white shadow-md ring-2 ring-blue-600 ring-offset-2'
                        : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'"
                    class="py-3 px-4 rounded-xl font-medium transition-all duration-200 flex items-center justify-center gap-2">
                    <i class="fas fa-calendar-days"></i>
                    En Cuotas
                </button>
            </div>
        </div>

        <!-- Selector de Cuotas -->
        <div v-if="modalidadPago === 'cuotas'"
            class="mb-2 p-4 bg-blue-50 rounded-xl border border-blue-100 animate-fade-in">
            <label class="block text-sm font-medium text-blue-800 mb-2">Plazo de pago</label>

            <div class="grid grid-cols-2 gap-3">
                <button v-for="cuota in [2, 4, 8, 12]" :key="cuota" type="button"
                    @click="emit('update:numeroCuotas', cuota)"
                    class="text-left border rounded-lg p-3 transition-all"
                    :class="numeroCuotas === cuota
                        ? 'border-blue-500 bg-white ring-2 ring-blue-500'
                        : 'border-blue-200 bg-white/60 hover:border-blue-400'">
                    <p class="text-sm font-semibold text-blue-900">{{ cuota }} cuotas</p>
                    <p class="text-lg font-bold text-blue-800">${{ (total / cuota).toFixed(2) }}</p>
                </button>
            </div>

            <p class="text-xs text-blue-600 mt-3">
                <i class="fas fa-info-circle mr-1"></i> El primer pago se realiza al recibir el producto.
            </p>
        </div>

        <!-- Nota de confirmación de pago -->
        <div class="flex items-start gap-3 p-4 rounded-xl border border-gray-100 bg-gray-50">
            <div class="w-10 h-10 shrink-0 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                <i class="fas fa-qrcode"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">Se generará un código QR</p>
                <p class="text-xs text-gray-500">
                    Al confirmar, recibirás un QR de PagoFácil para completar
                    {{ modalidadPago === 'cuotas' ? 'tu primer pago.' : 'el pago total.' }}
                </p>
            </div>
        </div>
    </div>
</template>
<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>