<script setup>
import { onMounted, ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  pedido: Object,
  detalles: Array,
  route: Object
});

let map, storeMarker, customerMarker, routeLayer;

async function ensureLeaflet() {
  if (typeof window === 'undefined') return;
  if (window.L) { L = window.L; return; }

  // inject CSS once
  if (!document.getElementById('leaflet-css')) {
    const link = document.createElement('link');
    link.id = 'leaflet-css';
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);
  }

  // inject script and await load
  if (!document.getElementById('leaflet-script')) {
    await new Promise((resolve, reject) => {
      const s = document.createElement('script');
      s.id = 'leaflet-script';
      s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
      s.async = true;
      s.onload = () => resolve(true);
      s.onerror = (e) => reject(e);
      document.body.appendChild(s);
    });
  } else {
    // if script already present, wait until global is available
    await new Promise((resolve) => {
      const timer = setInterval(() => {
        if (window.L) {
          clearInterval(timer);
          resolve(true);
        }
      }, 50);
    });
  }

  L = window.L;
}

async function initMap() {
  await ensureLeaflet();

  const origin = props.route.origin;
  const dest = props.route.destination;
  const centerLat = (origin.lat + dest.lat) / 2;
  const centerLng = (origin.lng + dest.lng) / 2;

  map = L.map('delivery-map').setView([centerLat, centerLng], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:
    '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>' }).addTo(map);

  storeMarker = L.marker([origin.lat, origin.lng], { title: 'Tienda' }).addTo(map);
  customerMarker = L.marker([dest.lat, dest.lng], { title: 'Cliente' }).addTo(map);

  const latlngs = [[origin.lat, origin.lng],[dest.lat, dest.lng]];
  routeLayer = L.polyline(latlngs, { color: 'blue' }).addTo(map);
  map.fitBounds(routeLayer.getBounds(), { padding: [20,20] });
}

// Acciones de estado
const updateEstado = (nuevoEstado) => {
    router.put(route('pedidos.update', props.pedido.id), { estado_produccion: nuevoEstado });
};

const estadoActual = computed(() => props.pedido.estado_produccion);

onMounted(async () => await initMap());
</script>

<template>
  <AppLayout title="Seguimiento de Pedido">
    <template #header>
      <h2 class="font-semibold text-xl">Pedido #{{ pedido.id }}</h2>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Mapa -->
          <div class="bg-white rounded-xl shadow p-4 lg:col-span-2">
            <div id="delivery-map" class="w-full h-[400px] rounded"></div>
          </div>
          <!-- Detalles -->
          <div class="bg-white rounded-xl shadow p-4">
            <h3 class="font-bold mb-3">Detalle del Pedido</h3>
            <ul class="space-y-2">
              <li v-for="d in detalles" :key="d.id" class="flex justify-between">
                <span>{{ d.producto?.nombre || 'Producto' }} x {{ d.cantidad }}</span>
                <span class="font-semibold">Bs {{ Number(d.subtotal).toFixed(2) }}</span>
              </li>
            </ul>
            <div class="mt-4 border-t pt-4">
              <p class="text-sm text-gray-600">Cliente: {{ pedido.cliente?.nombre || pedido.cliente?.name || 'N/A' }}</p>
              <p class="text-sm text-gray-600">Estado: <span class="font-bold uppercase">{{ estadoActual }}</span></p>
              <p class="text-sm text-gray-600 font-bold mt-2">Total: Bs {{ Number(pedido.total).toFixed(2) }}</p>
            </div>

            <!-- Flujo de Botones -->
            <div class="mt-6">
                <button v-if="estadoActual === 'pending'" 
                        class="w-full px-4 py-3 bg-amber-600 text-white rounded-lg font-bold hover:bg-amber-700 transition" 
                        @click="updateEstado('assigned')">
                    <i class="fas fa-user-check mr-2"></i> Asignar Delivery
                </button>

                <button v-else-if="estadoActual === 'assigned'" 
                        class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition" 
                        @click="updateEstado('on_route')">
                    <i class="fas fa-motorcycle mr-2"></i> Marcar En Camino
                </button>

                <div v-else-if="estadoActual === 'on_route'" class="text-center p-3 bg-blue-50 text-blue-800 rounded-lg border border-blue-200">
                    <i class="fas fa-clock mr-2"></i> Esperando confirmación del cliente...
                </div>

                <button v-else-if="estadoActual === 'delivered'" 
                        class="w-full px-4 py-3 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition" 
                        @click="updateEstado('completed')">
                    <i class="fas fa-check-double mr-2"></i> Confirmar Entrega
                </button>

                <div v-else-if="estadoActual === 'completed'" class="text-center p-3 bg-green-50 text-green-800 rounded-lg border border-green-200 font-bold">
                    <i class="fas fa-flag-checkered mr-2"></i> Pedido Completado
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
