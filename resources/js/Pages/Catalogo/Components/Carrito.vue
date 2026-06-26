<script setup>
import { computed } from 'vue';

const props = defineProps({
    items: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'checkout']);

const total = computed(() =>
    props.items.reduce((sum, item) => sum + (item.precio * item.cantidad), 0)
);

const eliminarItem = (index) => {
    props.items.splice(index, 1);
};

// ── CAMBIO: incrementar respeta el stock_disponible del item ──
const incrementar = (item) => {
    if (item.cantidad >= item.stock_disponible) {
        alert(`Solo hay ${item.stock_disponible} unidades disponibles de "${item.nombre}".`);
        return;
    }
    item.cantidad++;
};
// ── FIN CAMBIO ──

const decrementar = (item) => {
    if (item.cantidad > 1) item.cantidad--;
};

const handleImageError = (e) => {
    e.target.src = 'https://placehold.co/100x100/EEE/31343C?text=IMG';
};

// ── CAMBIO: detectar si algún item supera su stock (por si el stock cambió externamente) ──
const hayItemsConStockExcedido = computed(() =>
    props.items.some(item => item.cantidad > (item.stock_disponible ?? Infinity))
);

const totalItems = computed(() =>
    props.items.reduce((sum, item) => sum + item.cantidad, 0)
);
// ── FIN CAMBIO ──
</script>

<template>
    <!-- Overlay -->
    <div @click="emit('close')" class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm"></div>

    <!-- Sidebar -->
    <div class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col z-[200]">

        <!-- Header -->
        <div class="p-6 bg-gradient-to-r from-amber-600 to-orange-600 text-white flex justify-between items-center">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <i class="fas fa-shopping-cart"></i> Carrito de Compras
                <!-- ── CAMBIO: contador de items en el header ── -->
                <span v-if="totalItems > 0" class="bg-white/20 text-white text-xs px-2 py-0.5 rounded-full font-medium">
                    {{ totalItems }} {{ totalItems === 1 ? 'item' : 'items' }}
                </span>
                <!-- ── FIN CAMBIO ── -->
            </h3>
            <button
                @click="emit('close')"
                class="text-white/80 hover:text-white transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20"
            >
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- ── CAMBIO: aviso si hay items con stock excedido ── -->
        <div v-if="hayItemsConStockExcedido" class="bg-red-50 border-b border-red-200 px-4 py-2 flex items-center gap-2 text-red-700 text-sm">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Algunos productos superan el stock disponible. Ajustá las cantidades.</span>
        </div>
        <!-- ── FIN CAMBIO ── -->

        <!-- Lista de Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <div
                v-for="(item, idx) in items"
                :key="idx"
                class="flex gap-4 bg-gray-50 p-4 rounded-lg border shadow-sm transition-all"
                :class="item.cantidad > (item.stock_disponible ?? Infinity)
                    ? 'border-red-300 bg-red-50'
                    : 'border-gray-200'"
            >
                <!-- Imagen -->
                <div class="w-20 h-20 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
                    <img
                        :src="item.imagen"
                        class="w-full h-full object-cover"
                        alt="Producto"
                        @error="handleImageError"
                    />
                </div>

                <!-- Detalles -->
                <div class="flex-1 flex flex-col justify-between">
                    <div>
                        <h4 class="font-semibold text-gray-800 text-sm line-clamp-2">{{ item.nombre }}</h4>
                        <p class="text-amber-600 font-bold text-lg mt-1">Bs {{ Number(item.precio).toFixed(2) }}</p>

                        <!-- ── CAMBIO: indicador de stock disponible por item ── -->
                        <p
                            class="text-xs mt-0.5"
                            :class="item.stock_disponible > 5
                                ? 'text-emerald-600'
                                : item.stock_disponible > 0
                                    ? 'text-amber-600'
                                    : 'text-red-500'"
                        >
                            <i class="fas fa-box mr-1"></i>
                            {{ item.stock_disponible > 0
                                ? `${item.stock_disponible} disponibles`
                                : 'Sin stock' }}
                        </p>
                        <!-- ── FIN CAMBIO ── -->
                    </div>

                    <!-- Controles -->
                    <div class="flex justify-between items-center mt-2">

                        <!-- Cantidad -->
                        <div class="flex items-center bg-white rounded-lg border border-gray-200">
                            <!-- decrementar -->
                            <button
                                @click="decrementar(item)"
                                :disabled="item.cantidad <= 1"
                                class="w-8 h-8 flex items-center justify-center rounded-l-lg transition-colors"
                                :class="item.cantidad <= 1
                                    ? 'text-gray-300 cursor-not-allowed'
                                    : 'hover:bg-gray-100 text-gray-600'"
                            >
                                <i class="fas fa-minus text-xs"></i>
                            </button>

                            <span class="w-10 text-center text-sm font-bold text-gray-800">
                                {{ item.cantidad }}
                            </span>

                            <!-- ── CAMBIO: incrementar deshabilitado si se alcanzó el stock ── -->
                            <button
                                @click="incrementar(item)"
                                :disabled="item.cantidad >= (item.stock_disponible ?? Infinity)"
                                class="w-8 h-8 flex items-center justify-center rounded-r-lg transition-colors"
                                :class="item.cantidad >= (item.stock_disponible ?? Infinity)
                                    ? 'text-gray-300 cursor-not-allowed'
                                    : 'hover:bg-gray-100 text-gray-600'"
                                :title="item.cantidad >= (item.stock_disponible ?? Infinity)
                                    ? 'Stock máximo alcanzado'
                                    : 'Aumentar cantidad'"
                            >
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                            <!-- ── FIN CAMBIO ── -->
                        </div>

                        <!-- Subtotal -->
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Subtotal</p>
                            <p class="font-bold text-gray-800">Bs {{ (item.precio * item.cantidad).toFixed(2) }}</p>
                        </div>

                        <!-- Eliminar -->
                        <button
                            @click="eliminarItem(idx)"
                            class="text-red-400 hover:text-red-600 transition-colors ml-2"
                            title="Eliminar del carrito"
                        >
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>

                    <!-- ── CAMBIO: aviso inline si la cantidad supera el stock ── -->
                    <p
                        v-if="item.cantidad > (item.stock_disponible ?? Infinity)"
                        class="text-xs text-red-600 mt-1 flex items-center gap-1"
                    >
                        <i class="fas fa-exclamation-circle"></i>
                        Cantidad supera el stock disponible. Reducí a {{ item.stock_disponible }}.
                    </p>
                    <!-- ── FIN CAMBIO ── -->
                </div>
            </div>

            <!-- Carrito vacío -->
            <div v-if="items.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-400">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-shopping-cart text-3xl text-gray-300"></i>
                </div>
                <p class="text-lg font-medium text-gray-500">Tu carrito está vacío</p>
                <p class="text-sm">¡Agrega algunos productos deliciosos!</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-200 bg-gray-50">

            <!-- ── CAMBIO: resumen de items y total ── -->
            <div class="flex justify-between items-center mb-2 text-sm text-gray-500">
                <span>{{ totalItems }} {{ totalItems === 1 ? 'producto' : 'productos' }}</span>
                <span>{{ items.length }} {{ items.length === 1 ? 'tipo' : 'tipos' }}</span>
            </div>
            <!-- ── FIN CAMBIO ── -->

            <div class="flex justify-between items-center mb-4">
                <span class="text-lg font-semibold text-gray-700">Total:</span>
                <span class="text-2xl font-extrabold text-amber-600">Bs {{ total.toFixed(2) }}</span>
            </div>

            <!-- ── CAMBIO: botón bloqueado si hay stock excedido o carrito vacío ── -->
            <button
                @click="emit('checkout')"
                :disabled="items.length === 0 || hayItemsConStockExcedido"
                class="w-full py-3 rounded-lg font-bold text-lg transition-all shadow-lg flex items-center justify-center gap-2"
                :class="items.length === 0 || hayItemsConStockExcedido
                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none'
                    : 'bg-gradient-to-r from-amber-600 to-orange-600 text-white hover:from-amber-700 hover:to-orange-700 shadow-amber-200'"
                :title="hayItemsConStockExcedido ? 'Ajustá las cantidades antes de continuar' : ''"
            >
                <i
                    class="fas"
                    :class="hayItemsConStockExcedido ? 'fa-exclamation-triangle' : 'fa-arrow-right'"
                ></i>
                <span>
                    {{ hayItemsConStockExcedido
                        ? 'Ajustá las cantidades'
                        : 'Proceder al Pago' }}
                </span>
            </button>
            <!-- ── FIN CAMBIO ── -->
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>