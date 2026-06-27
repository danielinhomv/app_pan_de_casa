<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import AppLayout from '@/Layouts/AppLayout.vue';
import ActividadPedido from './Components/ActividadPedido.vue';
import QRModal from '@/Pages/PagoFacil/QRModal.vue';
import ConfirmacionCuotaModal from '@/Pages/PagoFacil/ConfirmacionCuotaModal.vue';

const props = defineProps({
    pedido:   { type: Object, default: () => ({}) },
    detalles: { type: Array,  default: () => [] },
    pagos:    { type: Array,  default: () => [] },
});

const toast = useToast();

const marcarRecibido = () => router.put(route('cliente.pedidos.recibido', props.pedido.id));

// ── Estado de modales ──
const showConfirmModal = ref(false);
const showQRModal      = ref(false);
const pagoActivo       = ref(null);

// ── Primer pago pendiente ──
const proximaCuota = computed(() =>
    props.pagos.find(p => p.estado === 'pendiente') ?? null
);

const numeroCuotaActual = computed(() => {
    if (!proximaCuota.value) return null;
    const datos = typeof proximaCuota.value.datos_pago === 'string'
        ? JSON.parse(proximaCuota.value.datos_pago)
        : (proximaCuota.value.datos_pago ?? {});
    return datos.numero_cuota ?? null;
});

const totalCuotas = computed(() => {
    if (!proximaCuota.value) return null;
    const datos = typeof proximaCuota.value.datos_pago === 'string'
        ? JSON.parse(proximaCuota.value.datos_pago)
        : (proximaCuota.value.datos_pago ?? {});
    return datos.total_cuotas ?? null;
});

// ── Paso 1: click "Pagar ahora" → abre ConfirmacionModal ──
const iniciarPagoCuota = () => {
    if (!proximaCuota.value) return;
    pagoActivo.value       = proximaCuota.value;
    showConfirmModal.value = true;
};

// ── Paso 2: click "Sí, confirmar" en modal → abre QRModal ──
const confirmarPagoCuota = () => {
    showConfirmModal.value = false;
    showQRModal.value      = true;
};

// ── Cancelar en ConfirmacionModal ──
const cancelarConfirmacion = () => {
    showConfirmModal.value = false;
    pagoActivo.value       = null;
};

const handleQRSuccess = () => {
    showQRModal.value = false;

    const pagoRecienPagadoId = pagoActivo.value?.id;
    pagoActivo.value = null;

    const quedanPendientes = props.pagos.filter(p =>
        p.estado === 'pendiente' && p.id !== pagoRecienPagadoId
    ).length;

    if (quedanPendientes === 0) {
        // Última cuota — toast de pedido completado
        toast.success('¡Todas las cuotas pagadas! Tu pedido está completamente cancelado.', { timeout: 4000 });
        setTimeout(() => router.visit(route('cliente.pedidos.index')), 1500);
    } else {
        // Quedan cuotas — toast informativo con cuántas faltan
        toast.success(
            `¡Cuota pagada correctamente! Quedan ${quedanPendientes} cuota${quedanPendientes > 1 ? 's' : ''} pendiente${quedanPendientes > 1 ? 's' : ''}.`,
            { timeout: 4000 }
        );
        router.reload({ only: ['pedido', 'pagos'] });
    }
};

// ── Cerrar QRModal sin pagar ──
const handleQRClose = () => {
    showQRModal.value = false;
    pagoActivo.value  = null;
};

// ── Helpers de estilo ──
const getEstadoPagoClass = (estado) => {
    if (estado === 'completado' || estado === 'pagado')
        return 'bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full font-bold';
    if (estado === 'rechazado')
        return 'bg-red-100 text-red-800 text-xs px-2 py-0.5 rounded-full font-bold';
    return 'bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full font-medium';
};

const getEstadoPagoIcono = (estado) => {
    if (estado === 'completado' || estado === 'pagado') return 'fas fa-check-circle text-green-500';
    if (estado === 'rechazado') return 'fas fa-times-circle text-red-500';
    return 'fas fa-clock text-amber-500';
};
</script>

<template>
    <AppLayout title="Pedido en curso">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl">Seguimiento de Pedido #{{ pedido.id }}</h2>
                <span class="text-sm bg-gray-100 px-3 py-1 rounded-lg text-gray-600 font-medium">
                    Producción:
                    <span class="uppercase font-bold text-amber-700">{{ pedido.estado_produccion }}</span>
                </span>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Columna principal -->
                <div class="grid grid-cols-1 gap-6 lg:col-span-2">

                    <!-- Artículos -->
                    <div class="bg-white rounded-xl shadow p-5">
                        <h3 class="font-bold mb-3 text-gray-800 border-b pb-2">
                            <i class="fa-solid fa-basket-shopping mr-2"></i>Artículos Comprados
                        </h3>
                        <ul class="space-y-3">
                            <li
                                v-for="d in detalles"
                                :key="d.id"
                                class="flex justify-between items-center text-sm"
                            >
                                <span class="text-gray-700 font-medium">
                                    {{ d.producto?.nombre || 'Producto' }}
                                    <span class="text-gray-400 font-normal">x{{ d.cantidad }}</span>
                                </span>
                                <span class="font-semibold text-gray-900">Bs {{ Number(d.subtotal).toFixed(2) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Estado de cuenta -->
                    <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
                        <h3 class="font-bold mb-3 text-gray-800">
                            <i class="fa-solid fa-sack-dollar mr-2"></i>Estado de Cuenta y Saldos
                        </h3>

                        <div class="grid grid-cols-3 gap-4 text-center bg-gray-50 p-4 rounded-xl border">
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase">Total Compra</p>
                                <p class="text-lg font-extrabold text-gray-900">Bs {{ Number(pedido.total).toFixed(2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-blue-600 font-medium uppercase">Monto Pagado</p>
                                <p class="text-lg font-extrabold text-blue-700">Bs {{ Number(pedido.monto_pagado).toFixed(2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-red-600 font-medium uppercase">Saldo Pendiente</p>
                                <p class="text-lg font-extrabold text-red-600">Bs {{ Number(pedido.saldo_pendiente).toFixed(2) }}</p>
                            </div>
                        </div>

                        <!-- Botón pagar próxima cuota -->
                        <div
                            v-if="proximaCuota"
                            class="mt-4 bg-purple-50 border border-purple-200 rounded-xl p-4 flex items-center justify-between gap-4"
                        >
                            <div>
                                <p class="text-sm font-semibold text-purple-800">
                                    <i class="fas fa-qrcode mr-1"></i>
                                    Cuota {{ numeroCuotaActual }} de {{ totalCuotas }} pendiente
                                </p>
                                <p class="text-xs text-purple-600 mt-0.5">
                                    Monto: <strong>Bs {{ Number(proximaCuota.monto).toFixed(2) }}</strong>
                                </p>
                            </div>
                            <button
                                @click="iniciarPagoCuota"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2 flex-shrink-0"
                            >
                                <i class="fas fa-qrcode"></i>
                                Pagar ahora
                            </button>
                        </div>

                        <!-- Todas las cuotas pagadas -->
                        <div
                            v-else-if="pagos.length > 0"
                            class="mt-4 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3"
                        >
                            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                            <p class="text-sm font-semibold text-emerald-800">Todas las cuotas han sido pagadas.</p>
                        </div>

                        <!-- Historial de cuotas -->
                        <div class="mt-5" v-if="pagos.length > 0">
                            <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider mb-2">
                                Historial de Cuotas / Transacciones
                            </h4>
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                                    <thead class="bg-gray-100 text-gray-600 font-medium">
                                        <tr>
                                            <th class="px-3 py-2">Nro. Cuota</th>
                                            <th class="px-3 py-2">Monto</th>
                                            <th class="px-3 py-2">Estado</th>
                                            <th class="px-3 py-2">ID Transacción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white text-gray-700">
                                        <tr
                                            v-for="(pago, idx) in pagos"
                                            :key="pago.id"
                                            class="hover:bg-gray-50"
                                            :class="proximaCuota && pago.id === proximaCuota.id ? 'bg-purple-50' : ''"
                                        >
                                            <td class="px-3 py-2 font-bold">
                                                <i :class="getEstadoPagoIcono(pago.estado)" class="mr-1"></i>
                                                Cuota {{ idx + 1 }}
                                            </td>
                                            <td class="px-3 py-2 font-semibold text-gray-900">
                                                Bs {{ Number(pago.monto).toFixed(2) }}
                                            </td>
                                            <td class="px-3 py-2">
                                                <span :class="getEstadoPagoClass(pago.estado)">{{ pago.estado }}</span>
                                            </td>
                                            <td class="px-3 py-2 font-mono text-gray-500">
                                                {{ pago.transaction_id || '—' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna lateral -->
                <div class="bg-white rounded-xl shadow p-5 h-fit flex flex-col justify-between">
                    <div>
                        <ActividadPedido :estado="pedido.estado_produccion" />
                    </div>
                    <div class="mt-6 pt-4 border-t">
                        <button
                            :disabled="pedido.estado_produccion === 'completed'"
                            :class="['w-full px-4 py-2.5 text-white rounded-lg font-medium shadow transition',
                                pedido.estado_produccion === 'completed'
                                    ? 'bg-gray-400 cursor-not-allowed'
                                    : 'bg-green-600 hover:bg-green-700']"
                            @click="marcarRecibido"
                        >
                            <i class="fa-solid fa-circle-check mr-1"></i>
                            {{ pedido.estado_produccion === 'completed' ? 'Pedido ya recibido' : 'Marcar como recibido' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Paso 1: modal de confirmación ── -->
<ConfirmacionCuotaModal
    v-if="pagoActivo"
    :show="showConfirmModal"
    :monto="Number(pagoActivo.monto)"
    :numero-cuota="numeroCuotaActual ?? 1"
    :total-cuotas="totalCuotas ?? 1"
    :saldo-restante="Number(pedido.saldo_pendiente) - Number(pagoActivo.monto)"
    :processing="false"
    @close="cancelarConfirmacion"
    @confirm="confirmarPagoCuota"
/>
        <!-- ── Paso 2: modal QR ── -->
        <QRModal
            v-if="pagoActivo"
            :show="showQRModal"
            :venta-id="pedido.venta?.id ?? pedido.venta_id"
            :pago-id="pagoActivo.id"
            :total="Number(pagoActivo.monto)"
            @close="handleQRClose"
            @success="handleQRSuccess"
        />
    </AppLayout>
</template>