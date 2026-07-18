<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';

const page = usePage();
const menuItems = computed(() => page.props.menuItems || []);

const MAX_VISIBLE = 7;

const userRoles = computed(() => {
    const roles = page.props.auth?.user?.roles || [];
    if (!Array.isArray(roles)) return [];
    return roles.map(r => (typeof r === 'object' && r !== null ? (r.name ?? r) : String(r)));
});

const hasAccess = (itemRoles) => {
    if (!page.props.auth?.user) return false;
    if (userRoles.value.includes('propietario')) return true;
    if (!itemRoles || (Array.isArray(itemRoles) && itemRoles.length === 0)) return true;
    const roles = Array.isArray(itemRoles) ? itemRoles : [];
    return roles.some(r => userRoles.value.includes(r));
};

const isActive = (routeName) => {
    if (!routeName) return false;
    try { return route().current(routeName); } catch { return false; }
};

const routeExists = (routeName) => {
    if (!routeName) return false;
    try { route(routeName); return true; } catch { return false; }
};

/**
 * Centraliza todo lo necesario para renderizar el link de un item de menú:
 * si es navegable, su URL, y si corresponde marcarlo como activo.
 * Evita repetir route(item.route) / isActive / routeExists sueltos en el template.
 */
const navegarA = (item) => {
    const navegable = routeExists(item.route);
    return {
        navegable,
        href: navegable ? route(item.route) : null,
        activo: navegable && isActive(item.route),
    };
};

const itemsAccesibles = computed(() =>
    menuItems.value.filter(item => item.is_active && hasAccess(item.roles))
);

const itemsVisibles = computed(() => itemsAccesibles.value.slice(0, MAX_VISIBLE));
const itemsOcultos = computed(() => itemsAccesibles.value.slice(MAX_VISIBLE));
const hayMas = computed(() => itemsOcultos.value.length > 0);

const mostrarMas = ref(false);
const dropdownRef = ref(null);

const toggleMas = (e) => {
    e.stopPropagation();
    mostrarMas.value = !mostrarMas.value;
};

const cerrarMas = () => { mostrarMas.value = false; };

const onClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        cerrarMas();
    }
};

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>

<template>
    <!-- Un solo flex — sin divs intermedios que creen separación -->
    <div class="hidden sm:flex sm:ms-10 items-center space-x-4">

        <template v-for="item in itemsVisibles" :key="item.id">

            <!-- Sin hijos -->
            <NavLink v-if="!item.children?.length && navegarA(item).navegable" :href="navegarA(item).href"
                :active="navegarA(item).activo">
                <i v-if="item.icon" :class="item.icon" class="mr-1"></i>
                {{ item.title }}
            </NavLink>

            <!-- Con hijos (dropdown hover) -->
            <div v-else-if="item.children?.length" class="relative group">
                <button
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-full text-sm font-semibold transition-all duration-200 text-gray-600 hover:text-amber-600 hover:bg-amber-50">
                    <i v-if="item.icon" :class="item.icon"></i>
                    {{ item.title }}
                    <svg class="ms-2 -me-0.5 size-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <div
                    class="absolute left-0 top-full mt-3 w-56 rounded-2xl border border-amber-100 bg-white/95 backdrop-blur shadow-xl shadow-amber-100/60 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-20">
                    <div class="py-3 px-2 space-y-1">
                        <template v-for="child in item.children" :key="child.id">
                            <NavLink v-if="child.is_active && hasAccess(child.roles) && navegarA(child).navegable"
                                :href="navegarA(child).href" :active="navegarA(child).activo"
                                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-600 rounded-lg transition-colors duration-150 hover:bg-amber-50 hover:text-amber-600">
                                <i v-if="child.icon" :class="child.icon"></i>
                                {{ child.title }}
                            </NavLink>
                        </template>
                    </div>
                </div>
            </div>

        </template>

        <!-- Botón "Más" — solo aparece si hay más de MAX_VISIBLE items -->
        <div v-if="hayMas" ref="dropdownRef" class="relative">
            <button @click="toggleMas" :class="[
                'inline-flex items-center gap-1.5 px-3 py-2 rounded-full text-sm font-semibold transition-all duration-200',
                mostrarMas ? 'text-amber-600 bg-amber-50' : 'text-gray-600 hover:text-amber-600 hover:bg-amber-50'
            ]">
                <i class="fas fa-grip-horizontal"></i>
                Más
                <svg class="size-3 transition-transform duration-200" :class="{ 'rotate-180': mostrarMas }"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>

            <transition enter-active-class="transition ease-out duration-150"
                enter-from-class="opacity-0 -translate-y-1 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 -translate-y-1 scale-95">
                <div v-show="mostrarMas"
                    class="absolute left-0 top-full mt-2 w-56 rounded-xl border border-gray-100 bg-white shadow-xl z-50">
                    <div class="py-2 px-1.5 space-y-0.5">
                        <template v-for="item in itemsOcultos" :key="item.id">
                            <NavLink v-if="!item.children?.length && navegarA(item).navegable"
                                :href="navegarA(item).href" :active="navegarA(item).activo"
                                class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-600 rounded-lg hover:bg-amber-50 hover:text-amber-600 transition-colors"
                                @click="cerrarMas">
                                <i v-if="item.icon" :class="item.icon" class="mr-1"></i>
                                {{ item.title }}
                            </NavLink>
                        </template>
                    </div>
                </div>
            </transition>
        </div>

    </div>
</template>