<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    proveedores: Object,
    filters: Object,
});

const page = usePage();

const search = ref(props.filters.search || "");
const showDeleteModal = ref(false);
const proveedorToDelete = ref(null);
const highlightedProveedorId = computed(() =>
    String(props.filters?.highlight || ""),
);

const canCreateProveedor = computed(() =>
    Boolean(page.props.auth?.permissions?.proveedores?.create),
);
const canUpdateProveedor = computed(() =>
    Boolean(page.props.auth?.permissions?.proveedores?.update),
);
const canDeleteProveedor = computed(() =>
    Boolean(page.props.auth?.permissions?.proveedores?.delete),
);

const performSearch = () => {
    router.get(
        route("proveedores.index"),
        { search: search.value },
        { preserveState: true, replace: true },
    );
};

const confirmDelete = (proveedor) => {
    proveedorToDelete.value = proveedor;
    showDeleteModal.value = true;
};

const deleteProveedor = () => {
    if (!proveedorToDelete.value) return;

    router.delete(route("proveedores.destroy", proveedorToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            proveedorToDelete.value = null;
        },
    });
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    proveedorToDelete.value = null;
};

const isActive = (estado) => (estado || "").toLowerCase() === "activo";

const getProveedorDomId = (proveedorId) => `proveedor-${proveedorId}`;
const isHighlightedProveedor = (proveedorId) =>
    highlightedProveedorId.value &&
    String(proveedorId) === highlightedProveedorId.value;

const scrollToHighlightedProveedor = async () => {
    if (!highlightedProveedorId.value) {
        return;
    }

    await nextTick();
    const target = document.getElementById(
        getProveedorDomId(highlightedProveedorId.value),
    );

    if (target) {
        target.scrollIntoView({ behavior: "smooth", block: "center" });
    }
};

onMounted(scrollToHighlightedProveedor);

watch(highlightedProveedorId, scrollToHighlightedProveedor);
</script>

<template>
    <Head title="Proveedores" />

    <AppLayout title="Proveedores">
        <FlashNotification />

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div
                class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-emerald-700 shadow-sm"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-500"
                        ></span>
                        Gestión
                    </div>
                    <h1
                        class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Proveedores
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        Administra los proveedores y sus datos de contacto desde
                        un panel ordenado.
                    </p>
                </div>

                <Link
                    v-if="canCreateProveedor"
                    :href="route('proveedores.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                >
                    <i class="bi bi-plus-circle"></i>
                    Nuevo proveedor
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
                        class="w-full flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                        placeholder="Buscar por nombre, NIT, teléfono o correo..."
                        @keyup.enter="performSearch"
                    />
                    <button
                        type="button"
                        @click="performSearch"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    >
                        Buscar
                    </button>
                </div>
            </div>

            <div
                v-if="proveedores.data.length > 0"
                class="overflow-hidden rounded-[2rem] border border-white bg-white/90 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead
                            class="bg-slate-50 text-xs font-bold uppercase tracking-[0.18em] text-slate-500"
                        >
                            <tr>
                                <th class="px-6 py-4">Proveedor</th>
                                <th class="px-6 py-4">Contacto</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4 text-center">Compras</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="proveedor in proveedores.data"
                                :key="proveedor.id"
                                :id="getProveedorDomId(proveedor.id)"
                                class="transition"
                                :class="
                                    isHighlightedProveedor(proveedor.id)
                                        ? 'bg-emerald-50 ring-2 ring-inset ring-emerald-300'
                                        : 'hover:bg-slate-50/60'
                                "
                            >
                                <td class="px-6 py-4">
                                    <Link
                                        :href="
                                            route(
                                                'proveedores.show',
                                                proveedor.id,
                                            )
                                        "
                                        class="font-semibold text-slate-900 transition hover:text-emerald-700"
                                    >
                                        {{ proveedor.nombre }}
                                    </Link>
                                    <p class="mt-1 text-xs text-slate-500">
                                        NIT:
                                        {{ proveedor.nit || "No registrado" }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <p>
                                        {{
                                            proveedor.telefono || "Sin teléfono"
                                        }}
                                    </p>
                                    <p class="mt-1 break-words">
                                        {{ proveedor.email || "Sin correo" }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                                        :class="
                                            isActive(proveedor.estado)
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-rose-100 text-rose-700'
                                        "
                                    >
                                        {{ proveedor.estado || "Sin estado" }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700"
                                    >
                                        {{ proveedor.compras_count || 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <Link
                                            :href="
                                                route(
                                                    'proveedores.show',
                                                    proveedor.id,
                                                )
                                            "
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                                            title="Ver proveedor"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </Link>
                                        <Link
                                            v-if="canUpdateProveedor"
                                            :href="
                                                route(
                                                    'proveedores.edit',
                                                    proveedor.id,
                                                )
                                            "
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-emerald-200 bg-white text-emerald-600 transition hover:bg-emerald-50"
                                            title="Editar proveedor"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                        </Link>
                                        <button
                                            v-if="canDeleteProveedor"
                                            type="button"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-rose-200 bg-white text-rose-600 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-40"
                                            title="Eliminar proveedor"
                                            :disabled="
                                                Number(
                                                    proveedor.compras_count ||
                                                        0,
                                                ) > 0
                                            "
                                            @click="confirmDelete(proveedor)"
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
                <i class="bi bi-truck text-4xl text-slate-300"></i>
                <h3 class="mt-4 text-lg font-bold text-slate-900">
                    No hay proveedores registrados
                </h3>
                <p class="mt-2 text-sm text-slate-500">
                    Crea el primero para comenzar a gestionar compras.
                </p>
            </div>
        </div>

        <ConfirmationModal
            :show="showDeleteModal"
            @close="cancelDelete"
            max-width="sm"
        >
            <template #title>Eliminar proveedor</template>

            <template #content>
                <div class="text-center">
                    <p class="text-sm leading-6 text-slate-600">
                        ¿Seguro que deseas eliminar este proveedor?
                    </p>
                    <p class="mt-2 text-xs font-medium text-slate-500">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            </template>

            <template #footer>
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto"
                    @click="cancelDelete"
                >
                    <i class="bi bi-x-circle"></i>
                    Cancelar
                </button>
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700 sm:w-auto"
                    @click="deleteProveedor"
                >
                    <i class="bi bi-trash"></i>
                    Eliminar
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
