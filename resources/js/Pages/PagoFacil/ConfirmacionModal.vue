<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: { type: Boolean, required: true },
    total: { type: Number, required: true },
    modalidadPago: { type: String, required: true },
    numeroCuotas: { type: Number, default: 3 },
    processing: { type: Boolean, default: false }
});

const emit = defineEmits(['close', 'confirm']);

const montoCuota = computed(() => {
    if (props.modalidadPago === 'cuotas' && props.numeroCuotas > 0) {
        return props.total / props.numeroCuotas;
    }
    return props.total;
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="!processing && emit('close')"></div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full overflow-hidden transform transition-all z-10">
            <div class="p-6 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-gray-100 text-center">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3 text-amber-600">
                    <i class="fas fa-shopping-bag text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">¿Confirmar tu pedido?</h3>
                <p class="text-sm text-gray-500 mt-1">Por favor, revisa las condiciones de tu compra</p>
            </div>

            <div class="p-6 space-y-4">
                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Método de pago</span>
                        <span class="font-semibold text-gray-800 flex items-center gap-1">
                            <i class="fas fa-qrcode text-purple-600"></i>
                            QR PagoFácil
                        </span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Plan de pago</span>
                        <span class="font-semibold text-gray-800 capitalize">
                            {{ modalidadPago === 'cuotas' ? `En ${numeroCuotas} cuotas mensuales` : 'Al contado' }}
                        </span>
                    </div>

                    <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
                        <span class="text-gray-700 font-medium">Total de la venta</span>
                        <span class="text-lg font-bold text-gray-900">Bs {{ total.toFixed(2) }}</span>
                    </div>
                </div>

                <div v-if="modalidadPago === 'cuotas'" class="bg-purple-50 border border-purple-100 rounded-xl p-4 flex gap-3">
                    <i class="fas fa-calendar-alt text-purple-600 mt-0.5"></i>
                    <div class="text-xs text-purple-900">
                        <p class="font-bold">Información de financiamiento:</p>
                        <p class="mt-1">Pagarás una cuota inicial de <strong class="text-sm">Bs {{ montoCuota.toFixed(2) }}</strong> hoy para habilitar tu pedido, seguido de {{ numeroCuotas - 1 }} cuotas idénticas mensuales.</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-100 flex gap-3">
                <button 
                    :disabled="processing"
                    @click="emit('close')"
                    class="flex-1 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 font-medium hover:bg-gray-50 transition active:scale-95 disabled:opacity-50"
                >
                    Cancelar
                </button>
                <button 
                    :disabled="processing"
                    @click="emit('confirm')"
                    class="flex-1 py-2.5 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 shadow-sm transition active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <i v-if="processing" class="fas fa-spinner animate-spin"></i>
                    {{ processing ? 'Procesando...' : 'Sí, confirmar' }}
                </button>
            </div>
        </div>
    </div>
</template>