<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification'; // 👈 Importamos el manejador de Toasts
import AppLayout from '@/Layouts/AppLayout.vue';
import QRModal from '@/Pages/PagoFacil/QRModal.vue';

const props = defineProps({
    venta_id:      { type: Number, required: true },
    pago_id:       { type: Number, required: true },
    total:         { type: Number, required: true },
    monto_cuota:   { type: Number, required: true },
    modalidad:     { type: String, default: 'contado' },
    numero_cuotas: { type: Number, default: 1 },
    productos:     { type: Array,  default: () => [] },
    cliente:       { type: Object, default: () => ({}) },
});

const toast = useToast(); // 👈 Inicializamos el plugin de toasts
const showQRModal   = ref(true);
const paymentStatus = ref(null);

const esCuotas = computed(() => props.modalidad === 'cuotas');

const handleQRSuccess = () => {
    showQRModal.value   = false;
    paymentStatus.value = 'completado';

    if (typeof localStorage !== 'undefined') {
        localStorage.removeItem('carrito_backup');
    }

    // 🔔 REEMPLAZO DE ALERTS POR TOASTS ESTILIZADOS
    if (esCuotas.value) {
        toast.success(
            `¡Cuota 1/${props.numero_cuotas} pagada correctamente!\n` +
            `Monto pagado: Bs ${props.monto_cuota.toFixed(2)}\n` +
            `Saldo restante: Bs ${(props.total - props.monto_cuota).toFixed(2)}`,
            { timeout: 5000 } // Más tiempo para que se logre leer los saldos
        );
    } else {
        toast.success('¡Pago completado! Tu pedido ha sido procesado correctamente.', { timeout: 4000 });
    }

    // Redirección limpia al historial de pedidos
    setTimeout(() => router.visit(route('cliente.pedidos.index')), 1500);
};

const handleQRClose    = () => { showQRModal.value = false; };
const volverAlCatalogo = () => router.visit(route('catalogo.index'));
</script>

<template>
    <AppLayout title="Pago con QR">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 flex items-center gap-2">
                    <i class="fas fa-qrcode text-purple-600"></i> Pago con Código QR
                </h2>
            </div>
        </template>

        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <div class="lg:col-span-2 space-y-6">

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-purple-100">
                                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <i class="fas fa-shopping-bag text-purple-600"></i> Resumen del Pedido
                                </h3>
                            </div>

                            <div class="divide-y divide-gray-100">
                                <div
                                    v-for="item in productos"
                                    :key="item.id"
                                    class="flex items-center gap-4 p-6 hover:bg-gray-50 transition-colors"
                                >
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800">{{ item.nombre }}</h4>
                                        <p class="text-sm text-gray-500">Cantidad: {{ item.cantidad }}</p>
                                        <p class="text-sm text-gray-500">Precio: Bs {{ Number(item.precio).toFixed(2) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-purple-600">
                                            Bs {{ (item.precio * item.cantidad).toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-gray-50 border-t border-gray-100 space-y-2">
                                <div class="flex justify-between text-gray-600">
                                    <span>Total del pedido</span>
                                    <span class="font-semibold">Bs {{ total.toFixed(2) }}</span>
                                </div>

                                <template v-if="esCuotas">
                                    <div class="flex justify-between text-gray-500 text-sm">
                                        <span>Modalidad</span>
                                        <span>{{ numero_cuotas }} cuotas</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-xl border-t pt-2">
                                        <span>Pagar ahora (cuota 1/{{ numero_cuotas }})</span>
                                        <span class="text-purple-600">Bs {{ monto_cuota.toFixed(2) }}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 text-right">
                                        Saldo restante tras este pago:
                                        Bs {{ (total - monto_cuota).toFixed(2) }}
                                    </p>
                                </template>

                                <template v-else>
                                    <div class="flex justify-between font-bold text-xl border-t pt-2">
                                        <span>Total a Pagar</span>
                                        <span class="text-purple-600">Bs {{ total.toFixed(2) }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-user text-purple-600"></i> Información del Cliente
                            </h3>
                            <div class="space-y-2 text-gray-700">
                                <p><strong>Nombre:</strong> {{ cliente.name }}</p>
                                <p><strong>Email:</strong> {{ cliente.email }}</p>
                                <p><strong>Teléfono:</strong> {{ cliente.telefono || 'No registrado' }}</p>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                            <h3 class="text-lg font-bold text-blue-900 mb-3 flex items-center gap-2">
                                <i class="fas fa-info-circle text-blue-600"></i> Instrucciones
                            </h3>
                            <ol class="list-decimal list-inside space-y-2 text-blue-800">
                                <li>El código QR se mostrará en el panel derecho</li>
                                <li>Abre tu billetera digital (Tigo Money, etc.)</li>
                                <li>Escanea el código QR</li>
                                <li v-if="esCuotas">
                                    Confirma el monto de <strong>Bs {{ monto_cuota.toFixed(2) }}</strong>
                                    (cuota 1 de {{ numero_cuotas }})
                                </li>
                                <li v-else>
                                    Confirma el monto de <strong>Bs {{ total.toFixed(2) }}</strong>
                                </li>
                                <li>Completa el pago</li>
                                <li>Tu pedido se procesará automáticamente</li>
                            </ol>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
                            <div class="text-center mb-4">
                                <p class="text-sm text-gray-600 font-semibold uppercase tracking-wide">
                                    {{ esCuotas ? 'Monto cuota 1/' + numero_cuotas : 'Monto a Pagar' }}
                                </p>
                                <p class="text-4xl font-extrabold text-purple-600 mt-2">
                                    Bs {{ monto_cuota.toFixed(2) }}
                                </p>
                                <p v-if="esCuotas" class="text-sm text-gray-400 mt-1">
                                    Total del pedido: Bs {{ total.toFixed(2) }}
                                </p>
                            </div>

                            <div
                                class="rounded-lg p-3 mb-4 text-center text-sm font-medium"
                                :class="esCuotas
                                    ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                    : 'bg-purple-50 text-purple-700 border border-purple-100'"
                            >
                                <i :class="esCuotas ? 'fas fa-calendar-alt' : 'fas fa-qrcode'" class="mr-2"></i>
                                {{ esCuotas ? 'Pago en ' + numero_cuotas + ' cuotas' : 'Pago al contado' }}
                            </div>
                        </div>

                        <QRModal
                            :show="showQRModal"
                            :venta-id="venta_id"
                            :pago-id="pago_id"
                            :total="monto_cuota"
                            @close="handleQRClose"
                            @success="handleQRSuccess"
                        />

                        <button
                            @click="volverAlCatalogo"
                            class="w-full bg-gray-200 text-gray-800 py-3 rounded-xl font-medium hover:bg-gray-300 transition-all"
                        >
                            <i class="fas fa-arrow-left mr-2"></i> Volver al Catálogo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>