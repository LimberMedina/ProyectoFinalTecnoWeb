<script setup>
import { computed, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    compras: Object,
    filters: Object,
});

const page = usePage();
const search = ref(props.filters.search || "");
const showDeleteModal = ref(false);
const compraToDelete = ref(null);

const canCreateCompra = computed(() =>
    Boolean(page.props.auth?.permissions?.compras?.create),
);
const canUpdateCompra = computed(() =>
    Boolean(page.props.auth?.permissions?.compras?.update),
);
const canDeleteCompra = computed(() =>
    Boolean(page.props.auth?.permissions?.compras?.delete),
);

const performSearch = () => {
    router.get(
        route("compras.index"),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const confirmDelete = (compra) => {
    compraToDelete.value = compra;
    showDeleteModal.value = true;
};

const deleteCompra = () => {
    if (!compraToDelete.value) return;

    router.delete(route("compras.destroy", compraToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            compraToDelete.value = null;
        },
    });
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    compraToDelete.value = null;
};

const formatDate = (date) => {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("es-BO");
};

const formatPrice = (value) => `Bs. ${Number(value || 0).toFixed(2)}`;
</script>

<template>
    <Head title="Compras" />

    <AppLayout title="Compras">
        <FlashNotification />

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div
                class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-cyan-200 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-cyan-700 shadow-sm"
                    >
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Inventario
                    </div>
                    <h1
                        class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Compras
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        Registra compras a proveedores y actualiza stock
                        automáticamente con detalle de productos.
                    </p>
                </div>

                <Link
                    v-if="canCreateCompra"
                    :href="route('compras.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-200 transition hover:-translate-y-0.5 hover:bg-cyan-700"
                >
                    <i class="bi bi-plus-circle"></i>
                    Nueva compra
                </Link>
            </div>

            <div
                class="mb-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <label class="mb-2 block text-sm font-semibold text-slate-700"
                    >Buscar</label
                >
                <div class="flex flex-col gap-3 md:flex-row">
                    <input
                        v-model="search"
                        type="text"
                        class="w-full flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-100"
                        placeholder="Buscar por número de compra o proveedor..."
                        @keyup.enter="performSearch"
                    />
                    <button
                        type="button"
                        @click="performSearch"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700"
                    >
                        Buscar
                    </button>
                </div>
            </div>

            <div
                v-if="compras.data.length > 0"
                class="overflow-hidden rounded-[2rem] border border-white bg-white/90 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead
                            class="bg-slate-50 text-xs font-bold uppercase tracking-[0.18em] text-slate-500"
                        >
                            <tr>
                                <th class="px-6 py-4">N° compra</th>
                                <th class="px-6 py-4">Proveedor</th>
                                <th class="px-6 py-4">Fecha</th>
                                <th class="px-6 py-4 text-center">Items</th>
                                <th class="px-6 py-4 text-end">Total</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="compra in compras.data"
                                :key="compra.id"
                                class="transition hover:bg-slate-50/60"
                            >
                                <td class="px-6 py-4">
                                    <Link
                                        :href="route('compras.show', compra.id)"
                                        class="font-semibold text-slate-900 transition hover:text-cyan-700"
                                    >
                                        {{
                                            compra.num_compra || `#${compra.id}`
                                        }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{
                                        compra.proveedor?.nombre ||
                                        "Proveedor no disponible"
                                    }}
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{ formatDate(compra.fecha_compra) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700"
                                    >
                                        {{ compra.detalles_count || 0 }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-end font-bold text-cyan-700"
                                >
                                    {{ formatPrice(compra.precio_total) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <Link
                                            :href="
                                                route('compras.show', compra.id)
                                            "
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-700"
                                            title="Ver compra"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </Link>
                                        <Link
                                            v-if="canUpdateCompra"
                                            :href="
                                                route('compras.edit', compra.id)
                                            "
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-cyan-200 bg-white text-cyan-600 transition hover:bg-cyan-50"
                                            title="Editar compra"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </Link>
                                        <button
                                            v-if="canDeleteCompra"
                                            type="button"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-rose-200 bg-white text-rose-600 transition hover:bg-rose-50"
                                            title="Eliminar compra"
                                            @click="confirmDelete(compra)"
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                v-else
                class="rounded-[2rem] border border-dashed border-slate-300 bg-white/80 p-12 text-center"
            >
                <i class="bi bi-cart-plus text-4xl text-slate-300"></i>
                <h3 class="mt-4 text-lg font-bold text-slate-900">
                    No hay compras registradas
                </h3>
                <p class="mt-2 text-sm text-slate-500">
                    Registra la primera compra para empezar a aumentar stock.
                </p>
            </div>
        </div>

        <ConfirmationModal
            :show="showDeleteModal"
            @close="cancelDelete"
            max-width="sm"
        >
            <template #title>Eliminar compra</template>
            <template #content>
                <p class="text-center text-sm leading-6 text-slate-600">
                    ¿Seguro que deseas eliminar esta compra? Se revertirá el
                    stock ingresado.
                </p>
            </template>
            <template #footer>
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                    @click="cancelDelete"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-700 sm:w-auto"
                    @click="deleteCompra"
                >
                    Eliminar
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
