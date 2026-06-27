<!-- /Pages/PagoFacil/ConfirmacionCuotaModal.vue -->
<script setup>
const props = defineProps({
    show:            { type: Boolean, required: true },
    monto:           { type: Number,  required: true },
    numeroCuota:     { type: Number,  required: true },
    totalCuotas:     { type: Number,  required: true },
    saldoRestante:   { type: Number,  required: true },
    processing:      { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
        <div
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            @click="!processing && emit('close')"
        ></div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full overflow-hidden transform transition-all z-10">

            <!-- Header -->
            <div class="p-6 bg-gradient-to-r from-purple-50 to-violet-50 border-b border-gray-100 text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3 text-purple-600">
                    <i class="fas fa-qrcode text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900">¿Pagar cuota {{ numeroCuota }}?</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Cuota {{ numeroCuota }} de {{ totalCuotas }} — Se generará un código QR para completar el pago
                </p>
            </div>

            <!-- Cuerpo -->
            <div class="p-6 space-y-4">

                <!-- Monto a pagar ahora -->
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-5 text-center">
                    <p class="text-xs text-purple-600 font-semibold uppercase tracking-wide mb-1">Monto a pagar ahora</p>
                    <p class="text-4xl font-extrabold text-purple-700">Bs {{ monto.toFixed(2) }}</p>
                </div>

                <!-- Detalle de cuotas -->
                <div class="bg-gray-50 rounded-xl p-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Método de pago</span>
                        <span class="font-semibold text-gray-800 flex items-center gap-1">
                            <i class="fas fa-qrcode text-purple-600"></i> Código QR
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cuota</span>
                        <span class="font-semibold text-gray-800">
                            {{ numeroCuota }} de {{ totalCuotas }}
                        </span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-3">
                        <span class="text-gray-500">Saldo restante tras el pago</span>
                        <span class="font-bold text-red-600">Bs {{ saldoRestante.toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Aviso -->
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex gap-3">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <p class="text-xs text-blue-800">
                        Al confirmar se generará un QR que deberás escanear con tu billetera digital (Tigo Money, etc.)
                        para completar el pago de esta cuota.
                    </p>
                </div>
            </div>

            <!-- Botones -->
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
                    class="flex-1 py-2.5 rounded-xl bg-purple-600 text-white font-medium hover:bg-purple-700 shadow-sm transition active:scale-95 disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <i v-if="processing" class="fas fa-spinner animate-spin"></i>
                    {{ processing ? 'Generando QR...' : 'Sí, pagar cuota' }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
</style>