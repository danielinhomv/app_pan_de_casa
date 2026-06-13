<script setup>
import { computed } from 'vue';

const props = defineProps({
    tipoPago: String,
    modalidadPago: String,
    numeroCuotas: Number,
    total: Number
});

const emit = defineEmits(['update:tipoPago', 'update:modalidadPago', 'update:numeroCuotas']);

const montoPorCuota = computed(() => {
    if (props.modalidadPago !== 'cuotas' || !props.total) return 0;
    return (props.total / props.numeroCuotas).toFixed(2);
});
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-credit-card text-emerald-600"></i> Método de Pago
        </h3>
        
        <!-- Modalidad -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Modalidad de Pago</label>
            <div class="grid grid-cols-2 gap-3">
                <button @click="emit('update:modalidadPago', 'contado')"
                        :class="modalidadPago === 'contado' 
                            ? 'bg-emerald-600 text-white shadow-md ring-2 ring-emerald-600 ring-offset-2' 
                            : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'"
                        class="py-3 px-4 rounded-xl font-medium transition-all duration-200 flex items-center justify-center gap-2">
                    <i class="fas fa-money-bill-1-wave"></i>
                    Al Contado
                </button>
                <button @click="emit('update:modalidadPago', 'cuotas')"
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
        <div v-if="modalidadPago === 'cuotas'" class="mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100 animate-fade-in">
            <label class="block text-sm font-medium text-blue-800 mb-2">Plazo de pago</label>
            <select :value="numeroCuotas" 
                    @change="emit('update:numeroCuotas', Number($event.target.value))"
                    class="w-full border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white py-2">
                <option :value="3">3 cuotas de ${{ (total / 3).toFixed(2) }}</option>
                <option :value="6">6 cuotas de ${{ (total / 6).toFixed(2) }}</option>
                <option :value="12">12 cuotas de ${{ (total / 12).toFixed(2) }}</option>
            </select>
            <p class="text-xs text-blue-600 mt-2">
                <i class="fas fa-info-circle mr-1"></i> El primer pago se realiza al recibir el producto.
            </p>
        </div>

        <!-- Tipo de Pago -->
        <div class="space-y-3">
            <label class="block text-sm font-medium text-gray-700 mb-2">Medio de Pago</label>
            
            <!-- Efectivo -->
            <label class="relative flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md"
                   :class="tipoPago === 'efectivo' ? 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500' : 'border-gray-200 hover:border-emerald-200'">
                <input type="radio" name="tipo_pago" :checked="tipoPago === 'efectivo'" @change="emit('update:tipoPago', 'efectivo')" class="text-emerald-600 focus:ring-emerald-500" />
                <div class="flex items-center gap-4 w-full">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-xl">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <span class="font-bold text-gray-800 block">Efectivo</span>
                        <p class="text-xs text-gray-500">Pago contra entrega</p>
                    </div>
                </div>
            </label>

            <!-- Tarjeta -->
            <label class="relative flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md"
                   :class="tipoPago === 'tarjeta' ? 'border-blue-500 bg-blue-50/50 ring-1 ring-blue-500' : 'border-gray-200 hover:border-blue-200'">
                <input type="radio" name="tipo_pago" :checked="tipoPago === 'tarjeta'" @change="emit('update:tipoPago', 'tarjeta')" class="text-blue-600 focus:ring-blue-500" />
                <div class="flex items-center gap-4 w-full">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div>
                        <span class="font-bold text-gray-800 block">Tarjeta</span>
                        <p class="text-xs text-gray-500">Débito o Crédito (POS)</p>
                    </div>
                </div>
            </label>

            <!-- QR -->
            <label class="relative flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md"
                   :class="tipoPago === 'qr' ? 'border-purple-500 bg-purple-50/50 ring-1 ring-purple-500' : 'border-gray-200 hover:border-purple-200'">
                <input type="radio" name="tipo_pago" :checked="tipoPago === 'qr'" @change="emit('update:tipoPago', 'qr')" class="text-purple-600 focus:ring-purple-500" />
                <div class="flex items-center gap-4 w-full">
                    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 text-xl">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div>
                        <span class="font-bold text-gray-800 block">Código QR</span>
                        <p class="text-xs text-gray-500">Transferencia simple</p>
                    </div>
                </div>
            </label>
        </div>
    </div>
</template>
<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
