<script setup>
import { ref, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import SearchBox from "@/Components/SearchBox.vue";
import PageVisitCounter from "@/Components/PageVisitCounter.vue";

defineProps({
    title: String,
});

import { usePage } from "@inertiajs/vue3";

const showingSidebar = ref(true);
const expandedMenus = ref({});
const page = usePage();

const ownerMenuOrder = {
    dashboard: 10,
    "usuarios.index": 20,
    "categorias.index": 30,
    "productos.index": 40,
    "proveedores.index": 50,
    "compras.index": 60,
    "inventarios.index": 70,
    "promociones.index": 80,
    "pedidos.index": 90,
    "ventas.index": 100,
    "metodos-pago.index": 105,
    "pagos.index": 110,
    "creditos.index": 120,
    "reportes.index": 9999,
};

const sortSidebarItems = (items, isOwner) => {
    if (!isOwner) {
        return items;
    }

    return [...items]
        .sort((firstItem, secondItem) => {
            const firstOrder = ownerMenuOrder[firstItem?.route] ?? 5000;
            const secondOrder = ownerMenuOrder[secondItem?.route] ?? 5000;

            return firstOrder - secondOrder;
        })
        .map((item) => ({
            ...item,
            children: Array.isArray(item?.children)
                ? [...item.children].sort((firstChild, secondChild) => {
                      const firstOrder =
                          ownerMenuOrder[firstChild?.route] ?? 5000;
                      const secondOrder =
                          ownerMenuOrder[secondChild?.route] ?? 5000;

                      return firstOrder - secondOrder;
                  })
                : item?.children,
        }));
};

// Menú dinámico desde props compartidos globalmente
const menuItems = computed(() => {
    const items = page.props.menuItems || [];
    const isOwner = page.props.auth?.user?.role?.id === 1;

    return sortSidebarItems(items, isOwner);
});

const toggleMenu = (itemId) => {
    expandedMenus.value[itemId] = !expandedMenus.value[itemId];
};

const iconLibrary = {
    FaChartLine: {
        viewBox: "0 0 24 24",
        paths: ["M3 3v18h18", "m7-5 4-4 3 3 4-6"],
    },
    FaBox: {
        viewBox: "0 0 24 24",
        paths: [
            "M3 7.5 12 3l9 4.5-9 4.5L3 7.5Z",
            "M3 7.5V16.5L12 21l9-4.5V7.5",
            "M12 12v9",
        ],
    },
    FaTags: {
        viewBox: "0 0 24 24",
        paths: ["m3 12 9-9h6l3 3v6l-9 9L3 12Z", "M16 8.5h.01"],
    },
    FaPercent: {
        viewBox: "0 0 24 24",
        paths: ["m19 5-14 14", "M7.5 7.5h.01", "M16.5 16.5h.01"],
    },
    FaCartPlus: {
        viewBox: "0 0 24 24",
        paths: [
            "M2 3h2l2.2 10.5a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L21 6H7",
            "M11 10h4",
            "M13 8v4",
            "M9 20h.01",
            "M18 20h.01",
        ],
    },
    FaClipboardList: {
        viewBox: "0 0 24 24",
        paths: ["M9 3h6l1 2h3v16H5V5h3l1-2Z", "M9 10h6", "M9 14h6"],
    },
    FaUsers: {
        viewBox: "0 0 24 24",
        paths: [
            "M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2",
            "M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z",
            "M22 21v-2a4 4 0 0 0-3-3.87",
            "M16 3.2a4 4 0 0 1 0 7.6",
        ],
    },
    FaCreditCard: {
        viewBox: "0 0 24 24",
        paths: [
            "M2 7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7Z",
            "M2 10h20",
            "M6 15h4",
        ],
    },
    FaCoins: {
        viewBox: "0 0 24 24",
        paths: [
            "M12 6c-4 0-7 1.3-7 3s3 3 7 3 7-1.3 7-3-3-3-7-3Z",
            "M5 9v6c0 1.7 3.1 3 7 3s7-1.3 7-3V9",
        ],
    },
    FaWallet: {
        viewBox: "0 0 24 24",
        paths: [
            "M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z",
            "M3 10h18",
            "M16 14h3",
        ],
    },
    FaFileChart: {
        viewBox: "0 0 24 24",
        paths: [
            "M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z",
            "M14 2v6h6",
            "M9 17v-4",
            "M12 17v-2",
            "M15 17v-6",
        ],
    },
    FaCar: {
        viewBox: "0 0 24 24",
        paths: [
            "M3 13h18l-1.5-5a2 2 0 0 0-1.9-1.5H6.4A2 2 0 0 0 4.5 8L3 13Z",
            "M4 13v4a1 1 0 0 0 1 1h1",
            "M20 13v4a1 1 0 0 1-1 1h-1",
            "M7 18h10",
            "M7 15h.01",
            "M17 15h.01",
        ],
    },
    FaPlus: {
        viewBox: "0 0 24 24",
        paths: ["M12 5v14", "M5 12h14"],
    },
    FaUser: {
        viewBox: "0 0 24 24",
        paths: ["M20 21a8 8 0 1 0-16 0", "M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"],
    },
    FaKey: {
        viewBox: "0 0 24 24",
        paths: [
            "m21 3-9.5 9.5",
            "M7 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z",
            "M14 7l3 3",
            "M17 4l3 3",
        ],
    },
    FaRightFromBracket: {
        viewBox: "0 0 24 24",
        paths: [
            "M10 17 15 12 10 7",
            "M15 12H3",
            "M13 21h6a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-6",
        ],
    },
    FaGrid: {
        viewBox: "0 0 24 24",
        paths: [
            "M4 4h6v6H4z",
            "M14 4h6v6h-6z",
            "M4 14h6v6H4z",
            "M14 14h6v6h-6z",
        ],
    },
};

const routeIconMap = {
    dashboard: "FaChartLine",
    "productos.index": "FaBox",
    "categorias.index": "FaTags",
    "promociones.index": "FaPercent",
    "ventas.index": "FaCar",
    "metodos-pago.index": "FaWallet",
    "pedidos.index": "FaClipboardList",
    "usuarios.index": "FaUsers",
    "creditos.index": "FaCreditCard",
    "pagos.index": "FaCoins",
    "reportes.index": "FaFileChart",
    "inventarios.index": "FaBox",
    "tecnicas-inventario.index": "FaClipboardList",
    "carritos.index": "FaCartPlus",
    "mis-pedidos.index": "FaClipboardList",
    "mis-creditos.index": "FaCreditCard",
    "mis-pagos.index": "FaCoins",
};

const resolveIconName = (item) => {
    if (item?.icon && iconLibrary[item.icon]) {
        return item.icon;
    }

    return routeIconMap[item?.route] || "FaGrid";
};

const iconDefinition = (item) =>
    iconLibrary[resolveIconName(item)] || iconLibrary.FaGrid;

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <div
        class="h-screen w-full overflow-hidden bg-gradient-to-br from-slate-50 via-white to-emerald-50/40 lg:pl-[19.5rem]"
    >
        <Head :title="title" />

        <Banner />

        <div
            v-if="showingSidebar"
            class="fixed inset-0 z-30 bg-slate-950/45 lg:hidden"
            @click="showingSidebar = false"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed left-3 top-3 bottom-3 w-72 bg-gradient-to-b from-cyan-50 via-white to-teal-50 shadow-[0_20px_60px_-28px_rgba(14,116,144,0.35)] flex flex-col overflow-hidden z-40 transition-transform duration-300 border border-cyan-200/70 rounded-2xl"
            :class="
                showingSidebar
                    ? 'translate-x-0'
                    : '-translate-x-full lg:translate-x-0'
            "
        >
            <!-- Logo Section -->
            <div
                class="p-6 border-b border-cyan-200/70 bg-gradient-to-r from-cyan-100/80 to-white"
            >
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3 hover:opacity-80 transition-opacity"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-teal-500 text-white font-bold text-lg shadow-lg shadow-cyan-500/30"
                    >
                        TE
                    </div>
                    <div>
                        <div
                            class="font-bold text-slate-800 text-sm tracking-tight"
                        >
                            Tienda Elena
                        </div>
                        <div class="text-xs text-cyan-700/90 font-medium">
                            Gestión
                        </div>
                    </div>
                </Link>
            </div>

            <!-- User Section -->
            <div class="px-6 py-4 border-b border-cyan-200/70 bg-cyan-50/60">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="truncate text-sm font-semibold text-slate-800"
                        >
                            {{ $page.props.auth.user.name }}
                        </p>
                        <p class="truncate text-xs text-slate-500">
                            {{ $page.props.auth.user.email }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav
                class="sidebar-nav flex-1 overflow-y-auto px-3 py-6 pr-2 space-y-2"
            >
                <template v-for="item in menuItems" :key="item.id">
                    <!-- Menu item with children -->
                    <div v-if="item.children && item.children.length">
                        <button
                            @click="toggleMenu(item.id)"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 hover:bg-cyan-100 hover:text-cyan-800 transition-all duration-200 group"
                        >
                            <div class="flex items-center gap-3">
                                <svg
                                    class="w-5 h-5 text-cyan-600 group-hover:text-cyan-700 transition-colors"
                                    :viewBox="iconDefinition(item).viewBox"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path
                                        v-for="(
                                            strokePath, index
                                        ) in iconDefinition(item).paths"
                                        :key="`${item.id}-path-${index}`"
                                        :d="strokePath"
                                    />
                                </svg>
                                <span class="text-sm font-medium">{{
                                    item.label
                                }}</span>
                            </div>
                            <svg
                                class="w-4 h-4 transition-transform duration-300 group-hover:text-cyan-700"
                                :class="{
                                    'rotate-180': expandedMenus[item.id],
                                }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3"
                                />
                            </svg>
                        </button>

                        <!-- Submenu items -->
                        <transition
                            enter-active-class="transition-all duration-200"
                            leave-active-class="transition-all duration-200"
                            enter-from-class="opacity-0 max-h-0"
                            enter-to-class="opacity-100 max-h-96"
                            leave-from-class="opacity-100 max-h-96"
                            leave-to-class="opacity-0 max-h-0"
                        >
                            <div
                                v-if="expandedMenus[item.id]"
                                class="pl-4 space-y-1 mt-2"
                            >
                                <NavLink
                                    v-for="child in item.children"
                                    :key="child.id"
                                    :href="route(child.route)"
                                    :active="route().current(child.route)"
                                    class="block px-4 py-2.5 rounded-lg text-sm text-slate-600 hover:bg-cyan-100 hover:text-cyan-800 transition-all duration-200 border border-transparent hover:border-cyan-200"
                                >
                                    <span class="flex items-center gap-2">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full bg-cyan-500/70"
                                        ></span>
                                        {{ child.label }}
                                    </span>
                                </NavLink>
                            </div>
                        </transition>
                    </div>

                    <!-- Simple menu item -->
                    <NavLink
                        v-else
                        :href="route(item.route)"
                        :active="route().current(item.route)"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-cyan-100 hover:text-cyan-800 transition-all duration-200 group"
                    >
                        <svg
                            class="w-5 h-5 text-cyan-600 group-hover:text-cyan-700 transition-colors"
                            :viewBox="iconDefinition(item).viewBox"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path
                                v-for="(strokePath, index) in iconDefinition(
                                    item,
                                ).paths"
                                :key="`${item.id}-single-path-${index}`"
                                :d="strokePath"
                            />
                        </svg>
                        <span class="text-sm font-medium flex-1">{{
                            item.label
                        }}</span>
                        <span
                            v-if="
                                item.route === 'carritos.index' &&
                                $page.props.cartCount > 0
                            "
                            class="inline-flex items-center rounded-full bg-cyan-500/80 text-white text-xs px-2 py-0.5 font-semibold shadow-lg shadow-cyan-500/30"
                            >{{ $page.props.cartCount }}</span
                        >
                    </NavLink>
                </template>
            </nav>

            <!-- Bottom Section -->
            <div
                class="border-t border-cyan-200/70 p-4 space-y-2 bg-cyan-50/60"
            >
                <Dropdown align="left" width="56">
                    <template #content>
                        <DropdownLink
                            :href="route('perfil.show')"
                            class="flex w-full items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-cyan-100 hover:text-cyan-800 transition-all duration-200 text-sm font-medium group"
                        >
                            <svg
                                class="w-5 h-5 text-cyan-600 group-hover:text-cyan-700 transition-colors"
                                :viewBox="iconLibrary.FaUser.viewBox"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M20 21a8 8 0 1 0-16 0" />
                                <path d="M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                            </svg>
                            <span>Mi Perfil</span>
                        </DropdownLink>
                        <DropdownLink
                            :href="route('profile.settings')"
                            class="flex w-full items-center gap-3 px-4 py-3 rounded-xl text-slate-700 hover:bg-cyan-100 hover:text-cyan-800 transition-all duration-200 text-sm font-medium group"
                        >
                            <svg
                                class="w-5 h-5 text-cyan-600 group-hover:text-cyan-700 transition-colors"
                                :viewBox="iconLibrary.FaKey.viewBox"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="m21 3-9.5 9.5" />
                                <path d="M7 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z" />
                                <path d="M14 7l3 3" />
                                <path d="M17 4l3 3" />
                            </svg>
                            <span>Configuración</span>
                        </DropdownLink>
                        <DropdownLink
                            v-if="$page.props.jetstream.hasApiFeatures"
                            :href="route('api-tokens.index')"
                        >
                            <span class="flex items-center gap-2">
                                <svg
                                    class="w-4 h-4"
                                    :viewBox="iconLibrary.FaKey.viewBox"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="m21 3-9.5 9.5" />
                                    <path
                                        d="M7 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8Z"
                                    />
                                    <path d="M14 7l3 3" />
                                    <path d="M17 4l3 3" />
                                </svg>
                                Tokens API
                            </span>
                        </DropdownLink>
                        <div class="border-t border-slate-200 my-2"></div>
                        <form @submit.prevent="logout">
                            <DropdownLink
                                as="button"
                                class="text-rose-600 hover:text-rose-700"
                            >
                                <span class="flex items-center gap-2">
                                    <svg
                                        class="w-4 h-4"
                                        :viewBox="
                                            iconLibrary.FaRightFromBracket
                                                .viewBox
                                        "
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M10 17 15 12 10 7" />
                                        <path d="M15 12H3" />
                                        <path
                                            d="M13 21h6a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-6"
                                        />
                                    </svg>
                                    Cerrar Sesión
                                </span>
                            </DropdownLink>
                        </form>
                    </template>
                </Dropdown>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="h-screen flex flex-col">
            <!-- Top Bar -->
            <div
                class="sticky top-0 z-30 bg-gradient-to-r from-white to-slate-50/80 border-b border-slate-200/60 shadow-sm backdrop-blur-sm"
            >
                <div class="px-6 py-4 flex items-center justify-between">
                    <button
                        @click="showingSidebar = !showingSidebar"
                        class="lg:hidden p-2 rounded-lg hover:bg-slate-100 transition-all duration-200 text-slate-700 hover:text-emerald-600"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>

                    <div class="hidden lg:flex flex-1 items-center gap-4">
                        <h1
                            class="text-xl font-bold text-slate-900 flex items-center gap-2"
                        >
                            <span
                                class="w-1 h-6 bg-gradient-to-b from-emerald-500 to-emerald-600 rounded-full"
                            ></span>
                            {{ title }}
                        </h1>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="hidden md:block w-64">
                            <SearchBox />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main
                class="flex-1 overflow-auto bg-gradient-to-br from-slate-50 via-slate-50 to-emerald-50/30"
            >
                <div class="p-6">
                    <slot />
                </div>
            </main>
        </div>

        <PageVisitCounter />
    </div>
</template>

<style scoped>
/* Sidebar nav scrollbar */
.sidebar-nav {
    scrollbar-width: thin;
    scrollbar-color: rgba(6, 182, 212, 0.45) transparent;
}

.sidebar-nav::-webkit-scrollbar {
    width: 8px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: linear-gradient(
        to bottom,
        rgba(34, 211, 238, 0.7),
        rgba(20, 184, 166, 0.7)
    );
    border-radius: 9999px;
    border: 2px solid transparent;
    background-clip: padding-box;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(
        to bottom,
        rgba(8, 145, 178, 0.85),
        rgba(13, 148, 136, 0.85)
    );
}

/* Main content scrollbar */
main::-webkit-scrollbar {
    width: 8px;
}

main::-webkit-scrollbar-track {
    background: transparent;
}

main::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.3);
    border-radius: 4px;
}

main::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.5);
}
</style>
