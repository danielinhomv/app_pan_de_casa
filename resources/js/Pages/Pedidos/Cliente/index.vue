<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ListaPedidos from '../Components/ListaPedidos.vue';

const props = defineProps({
  enCurso: { type: Array, default: () => [] },
  realizados: { type: Array, default: () => [] }
});

const search = ref('');
const norm = t => (t||'').toLowerCase();
const filtrar = list => list.filter(p => norm(p.cliente?.nombre || p.id.toString()).includes(norm(search.value)));

const ver = p => router.get(route('cliente.pedidos.show', p.id));

// 🛠️ AGREGADO: Utilidades estéticas para los badges financieros
const getBadgeEstadoPago = (estado) => {
  if (estado === 'pagado') return 'bg-green-100 text-green-800 border-green-200';
  if (estado === 'parcial') return 'bg-blue-100 text-blue-800 border-blue-200';
  return 'bg-red-100 text-red-800 border-red-200';
};

const formatEstado = (estado) => {
  if (estado === 'pagado') return 'Pagado';
  if (estado === 'parcial') return 'Pago Parcial';
  return 'Pendiente de Pago';
};
</script>

<template>
  <AppLayout title="Mis Pedidos">
    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
          <button @click="router.visit(route('catalogo.index'))" class="text-gray-600 hover:text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Ir al Catálogo
          </button>
          <h2 class="font-semibold text-xl">Mis Pedidos</h2>
        </div>
        <input v-model="search" placeholder="Buscar pedido..." class="border rounded px-3 py-2 text-sm" />
      </div>
    </template>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
      
      <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="font-bold mb-4 text-lg text-amber-700 border-b pb-2 flex justify-between items-center">
          <span>En curso</span>
          <span class="text-xs font-normal text-gray-500">Muestra el saldo pendiente de tus cuentas por cobrar</span>
        </h3>
        
        <div v-if="filtrar(props.enCurso).length === 0" class="text-gray-500 text-sm py-2">No tienes pedidos en curso.</div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="p in filtrar(props.enCurso)" :key="p.id" class="border rounded-xl p-4 hover:shadow-md transition bg-gray-50 flex flex-col justify-between">
            <div>
              <div class="flex justify-between items-start mb-2">
                <span class="font-bold text-gray-800 text-lg">Pedido #{{ p.id }}</span>
                <span :class="['px-2.5 py-1 text-xs font-semibold border rounded-full', getBadgeEstadoPago(p.estado_pago)]">
                  {{ formatEstado(p.estado_pago) }}
                </span>
              </div>
              <p class="text-xs text-gray-500 mb-2">Fecha: {{ new Date(p.created_at).toLocaleDateString() }}</p>
              
              <div class="space-y-1 text-sm border-t pt-2 mt-2">
                <div class="flex justify-between"><span class="text-gray-600">Método:</span> <span class="font-medium uppercase text-xs bg-gray-200 px-1.5 py-0.5 rounded">{{ p.tipo_pago }} - {{ p.modo_pago }}</span></div>
                <div class="flex justify-between"><span class="text-gray-600">Total Venta:</span> <span class="font-bold text-gray-900">Bs {{ Number(p.total).toFixed(2) }}</span></div>
                <div class="flex justify-between text-blue-700 font-medium"><span class="text-gray-600">Monto Pagado:</span> <span>Bs {{ Number(p.monto_pagado).toFixed(2) }}</span></div>
                <div class="flex justify-between text-red-600 font-bold border-t border-dashed pt-1 mt-1"><span class="text-gray-600 font-normal">Saldo Pendiente:</span> <span>Bs {{ Number(p.saldo_pendiente).toFixed(2) }}</span></div>
              </div>
            </div>
            <button @click="ver(p)" class="mt-4 w-full text-center bg-amber-600 text-white text-sm font-medium py-2 rounded-lg hover:bg-amber-700 transition">
              <i class="fa-solid fa-eye mr-1"></i> Ver Detalles y Seguimiento
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="font-bold mb-4 text-lg text-gray-700 border-b pb-2">Historial</h3>
        <ListaPedidos :items="filtrar(props.realizados)" @ver="ver" />
      </div>

    </div>
  </AppLayout>
</template>