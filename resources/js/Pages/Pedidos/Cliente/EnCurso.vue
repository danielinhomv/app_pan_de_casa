<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ActividadPedido from './Components/ActividadPedido.vue';

const props = defineProps({
  pedido: Object,
  detalles: Array,
  pagos: { type: Array, default: () => [] } // 🛠️ AGREGADO: Recibe la lista de cuotas/pagos desde el backend
});

const marcarRecibido = () => router.put(route('cliente.pedidos.recibido', props.pedido.id));

// 🛠️ AGREGADO: Helper estético para las cuotas de PagoFácil
const getEstadoPagoClass = (estado) => {
  if (estado === 'completado' || estado === 'pagado') return 'bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full font-bold';
  return 'bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full font-medium';
};
</script>

<template>
  <AppLayout title="Pedido en curso">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl">Seguimiento de Pedido #{{ pedido.id }}</h2>
        <span class="text-sm bg-gray-100 px-3 py-1 rounded-lg text-gray-600 font-medium">
          Producción: <span class="uppercase font-bold text-amber-700">{{ pedido.estado_produccion }}</span>
        </span>
      </div>
    </template>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="grid grid-cols-1 gap-6 lg:col-span-2">
          
          <div class="bg-white rounded-xl shadow p-5">
            <h3 class="font-bold mb-3 text-gray-800 border-b pb-2"><i class="fa-solid fa-basket-shopping mr-2"></i>Artículos Comprados</h3>
            <ul class="space-y-3">
              <li v-for="d in detalles" :key="d.id" class="flex justify-between items-center text-sm">
                <span class="text-gray-700 font-medium">{{ d.producto?.nombre || 'Producto' }} <span class="text-gray-400 font-normal">x{{ d.cantidad }}</span></span>
                <span class="font-semibold text-gray-900">Bs {{ Number(d.subtotal).toFixed(2) }}</span>
              </li>
            </ul>
          </div>

          <div class="bg-white rounded-xl shadow p-5 border-l-4 border-blue-500">
            <h3 class="font-bold mb-3 text-gray-800"><i class="fa-solid fa-sack-dollar mr-2"></i>Estado de Cuenta y Saldos</h3>
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

            <div class="mt-5" v-if="props.pagos.length > 0">
              <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider mb-2">Historial de Cuotas / Transacciones</h4>
              <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                  <thead class="bg-gray-100 text-gray-600 font-medium">
                    <tr>
                      <th class="px-3 py-2">Nro. Cuota</th>
                      <th class="px-3 py-2">Monto</th>
                      <th class="px-3 py-2">Estado</th>
                      <th class="px-3 py-2">ID Transacción (Banco)</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 bg-white text-gray-700">
                    <tr v-for="(pago, idx) in props.pagos" :key="pago.id" class="hover:bg-gray-50">
                      <td class="px-3 py-2 font-bold">Cuota {{ idx + 1 }}</td>
                      <td class="px-3 py-2 font-semibold text-gray-900">Bs {{ Number(pago.monto).toFixed(2) }}</td>
                      <td class="px-3 py-2">
                        <span :class="getEstadoPagoClass(pago.estado)">{{ pago.estado }}</span>
                      </td>
                      <td class="px-3 py-2 font-mono text-gray-500">{{ pago.transaction_id || 'Pendiente de pago' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>

        <div class="bg-white rounded-xl shadow p-5 h-fit flex flex-col justify-between">
          <div>
            <ActividadPedido :estado="pedido.estado_produccion" />
          </div>
          
          <div class="mt-6 pt-4 border-t">
            <button 
              :disabled="pedido.estado_produccion === 'completed'"
              :class="['w-full px-4 py-2.5 text-white rounded-lg font-medium shadow transition', 
                        pedido.estado_produccion === 'completed' ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700']" 
              @click="marcarRecibido"
            >
              <i class="fa-solid fa-circle-check mr-1"></i>
              {{ pedido.estado_produccion === 'completed' ? 'Pedido ya recibido' : 'Marcar como recibido' }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>