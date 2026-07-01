<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import PageVisitCounter from "@/Components/PageVisitCounter.vue";
import GlobalSearch from "@/Components/GlobalSearch.vue";

const page = usePage();

const isAuthenticated = computed(() => Boolean(page.props.auth?.user));
const user = computed(() => page.props.auth?.user || null);
const displayName = computed(
    () => user.value?.name || user.value?.nombre || "Usuario",
);
const profilePhotoUrl = computed(() => {
    if (user.value?.profile_photo_path) {
        return `/storage/${user.value.profile_photo_path}`;
    }

    if (user.value?.profile_photo_url) {
        return user.value.profile_photo_url;
    }

    return `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName.value)}&background=10b981&color=ffffff`;
});
const cartCount = computed(() => page.props.cartCount || 0);
const unreadNotificationsCount = computed(
    () => page.props.unreadNotificationsCount || 0,
);
const showProfileMenu = ref(false);
const showNotificationsPanel = ref(false);
const notificationsLoading = ref(false);
const recentNotifications = ref([]);

const homeHref = computed(() =>
    isAuthenticated.value ? route("dashboard") : "/",
);
const productsHref = "/productos";
const categoriesHref = "/categorias";
const offersHref = "/promociones";

const fetchUnreadNotifications = async () => {
    if (!isAuthenticated.value) {
        return;
    }

    notificationsLoading.value = true;

    try {
        const response = await fetch(route("notifications.unread"), {
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!response.ok) {
            throw new Error("No se pudieron cargar las notificaciones");
        }

        const payload = await response.json();
        recentNotifications.value = payload.notifications || [];
    } catch (error) {
        recentNotifications.value = [];
    } finally {
        notificationsLoading.value = false;
    }
};

const toggleNotificationsPanel = async () => {
    if (!isAuthenticated.value) {
        router.visit(route("login"));
        return;
    }

    showNotificationsPanel.value = !showNotificationsPanel.value;

    if (
        showNotificationsPanel.value &&
        recentNotifications.value.length === 0
    ) {
        await fetchUnreadNotifications();
    }
};

const closeNotificationsPanel = () => {
    showNotificationsPanel.value = false;
};

const handleClickOutside = (event) => {
    const panel = document.querySelector("[data-notifications-panel]");
    const button = document.querySelector("[data-notifications-button]");

    if (!panel || !button) {
        return;
    }

    if (panel.contains(event.target) || button.contains(event.target)) {
        return;
    }

    closeNotificationsPanel();
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});

const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value;
};

const closeProfileMenu = () => {
    showProfileMenu.value = false;
};

const handleLogout = () => {
    router.post(
        route("logout"),
        {},
        {
            onSuccess: () => {
                showProfileMenu.value = false;
            },
        },
    );
};

const currentUrl = computed(() => page.url);
</script>

<template>
    <header
        class="sticky top-0 z-50 overflow-visible border-b border-emerald-100/80 bg-white/90 backdrop-blur"
    >
        <div
            class="mx-auto grid max-w-7xl grid-cols-[auto_minmax(0,1fr)_auto] items-start gap-3 px-4 py-4 sm:px-6 lg:px-8"
        >
            <Link :href="homeHref" class="flex items-center gap-3">
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600 text-lg font-black text-white shadow-lg shadow-emerald-200"
                >
                    TE
                </span>
                <span
                    class="text-lg font-extrabold tracking-tight text-emerald-700"
                >
                    Tienda Elena
                </span>
            </Link>

            <div class="hidden flex-1 items-center gap-6 px-4 lg:flex">
                <nav class="hidden items-center gap-8 lg:flex">
                    <div class="relative">
                        <Link
                            :href="homeHref"
                            class="text-sm font-medium text-slate-600 transition hover:text-emerald-600 pb-2 block"
                        >
                            Inicio
                        </Link>
                        <div
                            v-if="
                                currentUrl.split('?')[0] === '/' &&
                                !isAuthenticated
                            "
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-600 rounded-full"
                        ></div>
                    </div>

                    <div class="relative">
                        <Link
                            :href="productsHref"
                            class="text-sm font-medium text-slate-600 transition hover:text-emerald-600 pb-2 block"
                        >
                            Productos
                        </Link>
                        <div
                            v-if="currentUrl.split('?')[0] === '/productos'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-600 rounded-full"
                        ></div>
                    </div>

                    <div class="relative">
                        <Link
                            :href="categoriesHref"
                            class="text-sm font-medium text-slate-600 transition hover:text-emerald-600 pb-2 block"
                        >
                            Categorías
                        </Link>
                        <div
                            v-if="currentUrl.split('?')[0] === '/categorias'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-600 rounded-full"
                        ></div>
                    </div>

                    <div class="relative">
                        <Link
                            :href="offersHref"
                            class="text-sm font-medium text-slate-600 transition hover:text-emerald-600 pb-2 block"
                        >
                            Promociones
                        </Link>
                        <div
                            v-if="currentUrl.split('?')[0] === '/promociones'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-600 rounded-full"
                        ></div>
                    </div>
                </nav>

                <div class="flex-1 min-w-0 lg:max-w-[28rem]">
                    <GlobalSearch />
                </div>
            </div>

            <div class="flex items-start justify-end gap-2 sm:gap-3">
                <div class="relative">
                    <button
                        data-notifications-button
                        type="button"
                        class="relative inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-600 hover:shadow-sm"
                        :aria-label="
                            isAuthenticated
                                ? 'Abrir notificaciones'
                                : 'Iniciar sesión para ver notificaciones'
                        "
                        title="Notificaciones"
                        @click.stop="toggleNotificationsPanel"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"
                            />
                            <path d="M9 17a3 3 0 0 0 6 0" />
                        </svg>
                        <span
                            v-if="unreadNotificationsCount > 0"
                            class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[11px] font-bold leading-none text-white"
                        >
                            {{ unreadNotificationsCount }}
                        </span>
                    </button>

                    <transition
                        enter-active-class="transition duration-150 ease-out"
                        enter-from-class="opacity-0 translate-y-2 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition duration-120 ease-in"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 translate-y-2 scale-95"
                    >
                        <div
                            v-if="showNotificationsPanel"
                            data-notifications-panel
                            class="absolute right-0 top-full z-[70] mt-3 w-80 overflow-hidden rounded-[1.75rem] border border-slate-100 bg-white shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)]"
                        >
                            <div
                                class="absolute -top-2 right-6 h-4 w-4 rotate-45 border-l border-t border-slate-100 bg-white"
                            ></div>

                            <div class="border-b border-slate-100 px-4 py-3">
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <div>
                                        <p
                                            class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700"
                                        >
                                            Notificaciones
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            Tus avisos más recientes
                                        </p>
                                    </div>

                                    <Link
                                        :href="route('notifications.index')"
                                        class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                        @click="closeNotificationsPanel"
                                    >
                                        Ver todas
                                    </Link>
                                </div>
                            </div>

                            <div class="max-h-96 overflow-y-auto">
                                <div
                                    v-if="notificationsLoading"
                                    class="px-4 py-8 text-center text-sm text-slate-500"
                                >
                                    Cargando notificaciones...
                                </div>

                                <div
                                    v-else-if="recentNotifications.length === 0"
                                    class="px-4 py-8 text-center text-sm text-slate-500"
                                >
                                    No tienes notificaciones nuevas.
                                </div>

                                <div v-else class="divide-y divide-slate-100">
                                    <Link
                                        v-for="notification in recentNotifications"
                                        :key="notification.id"
                                        :href="route('notifications.index')"
                                        class="block px-4 py-3 transition hover:bg-slate-50"
                                        @click="closeNotificationsPanel"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                                            >
                                                <i
                                                    :class="[
                                                        'bi',
                                                        notification.type ===
                                                        'pedido_enviado'
                                                            ? 'bi-truck'
                                                            : notification.type ===
                                                                'pedido_entregado'
                                                              ? 'bi-check-circle'
                                                              : notification.type ===
                                                                  'pago_confirmado'
                                                                ? 'bi-credit-card'
                                                                : 'bi-bell',
                                                        'text-base',
                                                    ]"
                                                ></i>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <p
                                                        class="truncate text-sm font-semibold text-slate-900"
                                                    >
                                                        {{ notification.title }}
                                                    </p>
                                                    <span
                                                        v-if="
                                                            !notification.read
                                                        "
                                                        class="inline-flex shrink-0 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-rose-700"
                                                    >
                                                        Nueva
                                                    </span>
                                                </div>
                                                <p
                                                    class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500"
                                                >
                                                    {{ notification.message }}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>

                <Link
                    :href="
                        isAuthenticated
                            ? route('carritos.index')
                            : route('login')
                    "
                    class="relative inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:text-emerald-600 hover:shadow-sm"
                    :aria-label="
                        isAuthenticated
                            ? 'Ver carrito'
                            : 'Iniciar sesión para usar el carrito'
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path
                            d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"
                        />
                    </svg>
                    <span
                        v-if="cartCount > 0"
                        class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-emerald-600 px-1 text-[11px] font-bold leading-none text-white"
                    >
                        {{ cartCount }}
                    </span>
                </Link>

                <template v-if="isAuthenticated">
                    <div class="relative overflow-visible">
                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center overflow-hidden rounded-full border border-emerald-200 bg-white text-emerald-700 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-sm"
                            :aria-label="`Abrir menú de ${displayName}`"
                            @click.stop="toggleProfileMenu"
                        >
                            <img
                                :src="profilePhotoUrl"
                                :alt="displayName"
                                class="h-full w-full object-cover"
                            />
                        </button>

                        <div
                            v-if="showProfileMenu"
                            class="absolute right-0 top-14 z-[60] w-72 overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-[0_20px_60px_-24px_rgba(15,23,42,0.35)]"
                        >
                            <div class="border-b border-slate-100 px-5 py-4">
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Sesión activa
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold text-slate-900"
                                >
                                    {{ displayName }}
                                </p>
                                <p class="text-sm text-slate-500">
                                    {{ user?.email || "" }}
                                </p>
                            </div>

                            <div class="p-2">
                                <Link
                                    href="/mi-perfil"
                                    @click="closeProfileMenu"
                                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-700"
                                >
                                    <svg
                                        class="w-5 h-5 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M2.458 20C3.732 16.943 6.61 15 12 15s8.268 1.943 9.542 5"
                                        />
                                    </svg>
                                    Mi perfil
                                </Link>
                                <Link
                                    href="/mis-pedidos"
                                    @click="closeProfileMenu"
                                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-700"
                                >
                                    <svg
                                        class="w-5 h-5 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M9 12h6M9 16h6M7 8h10M5 21h14a1 1 0 001-1V7a1 1 0 00-1-1H5a1 1 0 00-1 1v13a1 1 0 001 1z"
                                        />
                                    </svg>
                                    Mis pedidos
                                </Link>
                                <Link
                                    href="/mis-creditos"
                                    @click="closeProfileMenu"
                                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-700"
                                >
                                    <svg
                                        class="w-5 h-5 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <rect
                                            x="2"
                                            y="5"
                                            width="20"
                                            height="14"
                                            rx="2"
                                            ry="2"
                                            stroke-width="1.5"
                                        />
                                        <path d="M2 10h20" stroke-width="1.5" />
                                    </svg>
                                    Mis créditos
                                </Link>
                                <Link
                                    href="/mis-pagos"
                                    @click="closeProfileMenu"
                                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-700"
                                >
                                    <svg
                                        class="w-5 h-5 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M21 12v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6"
                                        />
                                    </svg>
                                    Mis pagos
                                </Link>
                                <Link
                                    href="/user/settings"
                                    @click="closeProfileMenu"
                                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-700"
                                >
                                    <svg
                                        class="w-5 h-5 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M12 15.5A3.5 3.5 0 1112 8.5a3.5 3.5 0 010 7z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06A2 2 0 012.3 17.88l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82L4.21 4.93A2 2 0 016.04 2.1l.06.06a1.65 1.65 0 001.82.33h.09A1.65 1.65 0 0011 1.07V1a2 2 0 014 0v.07c.36.09.7.25 1 .46.3.21.55.48.75.79l.06.09a2 2 0 002.83 0l.06-.06a2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82v.09c.16.5.45.95.85 1.35.4.4.85.69 1.35.85h.09a2 2 0 010 4h-.09c-.5.16-.95.45-1.35.85-.4.4-.69.85-.85 1.35v.09z"
                                        />
                                    </svg>
                                    Configuraciones
                                </Link>

                                <div
                                    class="my-2 border-t border-slate-100"
                                ></div>

                                <button
                                    @click="handleLogout"
                                    class="flex w-full items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                                    type="button"
                                >
                                    <svg
                                        class="w-5 h-5 text-rose-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M15 12H3"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M10 17l5-5-5-5"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M21 21H13a2 2 0 01-2-2V5a2 2 0 012-2h8"
                                        />
                                    </svg>
                                    Cerrar sesión
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="hidden text-sm font-semibold text-slate-600 transition hover:text-emerald-600 sm:inline-flex"
                    >
                        Iniciar sesión
                    </Link>
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                    >
                        Registrarse
                    </Link>
                </template>
            </div>
        </div>
    </header>

    <PageVisitCounter />
</template>
