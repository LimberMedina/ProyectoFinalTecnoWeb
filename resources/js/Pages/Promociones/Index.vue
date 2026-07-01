<script setup>
import { computed, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    promociones: Object,
    filters: Object,
    rol: String,
});

const page = usePage();

const search = ref(props.filters.search || "");
const showDeleteModal = ref(false);
const promocionToDelete = ref(null);

const isCliente = computed(() => (props.rol || "").toLowerCase() === "cliente");
const isOwnerOrSeller = computed(() => {
    const rol = (props.rol || "").toLowerCase();
    return rol === "propietario" || rol === "vendedor";
});
const promocionesList = computed(() => props.promociones?.data || []);
const activePromocionesCount = computed(
    () =>
        promocionesList.value.filter((promocion) => isActive(promocion)).length,
);
const featuredPromocion = computed(
    () =>
        promocionesList.value.find((promocion) => isActive(promocion)) ||
        promocionesList.value[0] ||
        null,
);
const canCreatePromocion = computed(() =>
    Boolean(page.props.auth?.permissions?.promociones?.create),
);
const canUpdatePromocion = computed(() =>
    Boolean(page.props.auth?.permissions?.promociones?.update),
);
const canDeletePromocion = computed(() =>
    Boolean(page.props.auth?.permissions?.promociones?.delete),
);
const currentLayout = computed(() =>
    isOwnerOrSeller.value ? AppLayout : PublicStoreLayout,
);
const layoutProps = computed(() =>
    isOwnerOrSeller.value ? { title: "Promociones" } : {},
);

const performSearch = () => {
    router.get(
        route("promociones.index"),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const confirmDelete = (promocion) => {
    promocionToDelete.value = promocion;
    showDeleteModal.value = true;
};

const deletePromocion = () => {
    if (promocionToDelete.value) {
        router.delete(
            route("promociones.destroy", promocionToDelete.value.id),
            {
                preserveScroll: true,
                onSuccess: () => {
                    showDeleteModal.value = false;
                    promocionToDelete.value = null;
                },
            },
        );
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO");
};

const isActive = (promocion) => {
    const now = new Date();
    const inicio = new Date(promocion.fecha_inicio);
    const fin = new Date(promocion.fecha_fin);
    return now >= inicio && now <= fin && promocion.estado;
};
</script>

<template>
    <Head title="Promociones" />

    <component :is="currentLayout" v-bind="layoutProps">
        <FlashNotification />

        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Encabezado -->
            <div
                class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4"
            >
                <div>
                    <h2 class="text-2xl font-bold mb-1">
                        {{
                            isCliente ? "Promociones" : "Gestión de Promociones"
                        }}
                    </h2>
                    <p class="text-sm text-slate-500">
                        {{
                            isCliente
                                ? "Descubre descuentos y ofertas especiales disponibles"
                                : "Administra descuentos y ofertas especiales con una vista ordenada y moderna"
                        }}
                    </p>
                </div>
                <div>
                    <Link
                        v-if="isOwnerOrSeller && canCreatePromocion"
                        :href="route('promociones.create')"
                        class="inline-flex items-center gap-2 rounded-md bg-emerald-600 text-white px-4 py-2 text-sm font-semibold hover:bg-emerald-700 transition"
                    >
                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                        >
                            <path
                                d="M12 5v14M5 12h14"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Nueva Promoción
                    </Link>
                </div>
            </div>

            <!-- Barra de búsqueda -->
            <div
                class="mb-6 rounded-[1.6rem] border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div class="flex flex-col gap-3 md:flex-row md:items-end">
                    <div class="w-full flex-1">
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-600"
                            >Buscar</label
                        >
                        <div class="flex gap-2">
                            <input
                                v-model="search"
                                type="text"
                                class="flex-1 rounded-md border px-3 py-2 text-sm"
                                placeholder="Buscar por nombre..."
                                @keyup.enter="performSearch"
                            />
                            <button
                                @click="performSearch"
                                class="inline-flex items-center gap-2 rounded-md bg-emerald-600 text-white px-4 py-2 text-sm font-semibold hover:bg-emerald-700 transition"
                            >
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="isCliente">
                <div
                    v-if="promociones.data.length === 0"
                    class="rounded-[2rem] border border-dashed border-emerald-200 bg-white/80 px-6 py-16 text-center shadow-sm"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600"
                    >
                        <svg
                            class="h-8 w-8"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                        >
                            <path d="M3 3h18v18H3z" stroke-width="1" />
                        </svg>
                    </div>
                    <h4 class="mt-4 text-lg font-semibold text-slate-900">
                        No se encontraron promociones
                    </h4>
                    <p class="mt-2 text-slate-500">
                        Intenta con otra búsqueda o revisa más adelante.
                    </p>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="promocion in promociones.data"
                        :key="promocion.id"
                        class="group overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-emerald-100/50"
                        :class="
                            isActive(promocion) ? 'ring-2 ring-emerald-200' : ''
                        "
                    >
                        <div class="p-7 text-center">
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                            >
                                Promoción
                            </p>
                            <h3
                                class="mt-4 text-2xl font-black tracking-tight text-slate-900"
                            >
                                {{ promocion.nombre }}
                            </h3>

                            <div
                                class="mt-7 inline-flex flex-col items-center justify-center rounded-[2rem] bg-emerald-50 px-6 py-8"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600"
                                >
                                    Descuento
                                </p>
                                <p
                                    class="mt-3 text-5xl font-black text-emerald-700"
                                >
                                    -{{ promocion.valor_descuento_decimal }}%
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 p-6">
                            <Link
                                :href="route('promociones.show', promocion.id)"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                            >
                                Ver productos
                            </Link>
                        </div>
                    </article>
                </div>
            </div>

            <div
                v-else
                class="bg-white border rounded-lg shadow-sm overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600 font-semibold"
                            >
                                <th class="px-6 py-4">Nombre</th>
                                <th class="px-6 py-4 text-center">Descuento</th>
                                <th class="px-6 py-4">Fecha inicio</th>
                                <th class="px-6 py-4">Fecha fin</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="promociones.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-10 text-center text-slate-500"
                                >
                                    No se encontraron promociones
                                </td>
                            </tr>
                            <tr
                                v-for="promocion in promociones.data"
                                :key="promocion.id"
                                class="border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition"
                            >
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900">
                                        {{ promocion.nombre }}
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{
                                            promocion.descripcion ||
                                            "Sin descripción"
                                        }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"
                                    >
                                        {{ promocion.valor_descuento_decimal }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 text-sm">
                                    {{ formatDate(promocion.fecha_inicio) }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 text-sm">
                                    {{ formatDate(promocion.fecha_fin) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            isActive(promocion)
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-slate-100 text-slate-500'
                                        "
                                    >
                                        {{
                                            isActive(promocion)
                                                ? "Activa"
                                                : "Inactiva"
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <Link
                                            :href="
                                                route(
                                                    'promociones.show',
                                                    promocion.id,
                                                )
                                            "
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:border-emerald-200 hover:text-emerald-700 hover:bg-emerald-50"
                                            title="Ver detalles"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                        </Link>
                                        <Link
                                            v-if="
                                                isOwnerOrSeller &&
                                                canUpdatePromocion
                                            "
                                            :href="
                                                route(
                                                    'promociones.edit',
                                                    promocion.id,
                                                )
                                            "
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:border-amber-200 hover:text-amber-700 hover:bg-amber-50"
                                            title="Editar promoción"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                            <span class="sr-only">Editar</span>
                                        </Link>
                                        <button
                                            v-if="
                                                isOwnerOrSeller &&
                                                canDeletePromocion
                                            "
                                            @click="confirmDelete(promocion)"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:border-rose-200 hover:text-rose-700 hover:bg-rose-50"
                                            title="Eliminar promoción"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            <span class="sr-only"
                                                >Eliminar</span
                                            >
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <div
                    v-if="promociones.links.length > 3"
                    class="border-t border-slate-100 px-6 py-4"
                >
                    <nav class="flex justify-center">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white p-1.5 shadow-sm"
                        >
                            <template
                                v-for="(link, index) in promociones.links"
                                :key="index"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    preserve-state
                                    v-html="link.label"
                                    class="rounded-md px-3 py-2 text-sm font-medium transition"
                                    :class="
                                        link.active
                                            ? 'bg-emerald-600 text-white'
                                            : 'text-slate-600 hover:bg-slate-50'
                                    "
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="rounded-md px-3 py-2 text-sm font-medium text-slate-300"
                                ></span>
                            </template>
                        </div>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <ConfirmationModal
            :show="showDeleteModal"
            @close="showDeleteModal = false"
            max-width="sm"
        >
            <template #title>Confirmar eliminación</template>
            <template #content>
                <p v-if="promocionToDelete" class="text-sm text-slate-600">
                    ¿Está seguro de eliminar la promoción
                    <strong class="text-slate-900">{{
                        promocionToDelete.nombre
                    }}</strong
                    >?
                </p>
                <p
                    class="mt-3 text-xs uppercase tracking-[0.18em] text-slate-400"
                >
                    Esta acción no se puede deshacer.
                </p>
            </template>
            <template #footer>
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    @click="showDeleteModal = false"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 inline-flex items-center gap-2"
                    @click="deletePromocion"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                    </svg>
                    Eliminar
                </button>
            </template>
        </ConfirmationModal>
    </component>
</template>
