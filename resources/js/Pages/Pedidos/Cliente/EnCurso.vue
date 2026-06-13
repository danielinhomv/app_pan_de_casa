<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ActividadPedido from './Components/ActividadPedido.vue';

const props = defineProps({
  pedido: Object,
  detalles: Array
});

const marcarRecibido = () => router.put(route('cliente.pedidos.recibido', props.pedido.id));
</script>

<template>
  <AppLayout title="Pedido en curso">
    <template #header>
      <h2 class="font-semibold text-xl">Pedido #{{ pedido.id }}</h2>
    </template>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow p-4 lg:col-span-2">
          <h3 class="font-bold mb-3">Detalle</h3>
          <ul class="space-y-2">
            <li v-for="d in detalles" :key="d.id" class="flex justify-between">
              <span>{{ d.producto?.nombre || 'Producto' }} x {{ d.cantidad }}</span>
              <span class="font-semibold">Bs {{ Number(d.subtotal).toFixed(2) }}</span>
            </li>
          </ul>
          <div class="mt-4 border-t pt-4">
            <p class="text-sm text-gray-600">Estado: {{ pedido.estado_produccion }}</p>
            <p class="text-sm text-gray-600 font-bold mt-2">Total: Bs {{ Number(pedido.total).toFixed(2) }}</p>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow p-4">
          <ActividadPedido :estado="pedido.estado_produccion" />
          <button class="mt-4 w-full px-4 py-2 bg-green-600 text-white rounded" @click="marcarRecibido">
            Marcar como recibido
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
