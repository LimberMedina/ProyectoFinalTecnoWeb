<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    categorias: Object,
    filters: Object,
    rol: String,
});

const page = usePage();

const search = ref(props.filters.search || "");
const showDeleteModal = ref(false);
const categoryToDelete = ref(null);
const highlightedCategoryId = computed(() =>
    String(props.filters?.highlight || ""),
);

const isCliente = computed(() => (props.rol || "").toLowerCase() === "cliente");
const isOwnerOrSeller = computed(() => {
    const rol = (props.rol || "").toLowerCase();
    return rol === "propietario" || rol === "vendedor";
});
const canCreateCategoria = computed(() =>
    Boolean(page.props.auth?.permissions?.categorias?.create),
);
const canUpdateCategoria = computed(() =>
    Boolean(page.props.auth?.permissions?.categorias?.update),
);
const canDeleteCategoria = computed(() =>
    Boolean(page.props.auth?.permissions?.categorias?.delete),
);
const currentLayout = computed(() =>
    isOwnerOrSeller.value ? AppLayout : PublicStoreLayout,
);
const layoutProps = computed(() =>
    isOwnerOrSeller.value ? { title: "Categorías" } : {},
);

const getCategoryImageUrl = (categoria) =>
    categoria?.imagen ? `/storage/${categoria.imagen}` : "/images/no-image.png";

const getCategoryDomId = (categoryId) => `categoria-${categoryId}`;
const isHighlightedCategory = (categoryId) =>
    highlightedCategoryId.value &&
    String(categoryId) === highlightedCategoryId.value;

const scrollToHighlightedCategory = async () => {
    if (!highlightedCategoryId.value) {
        return;
    }

    await nextTick();
    const target = document.getElementById(
        getCategoryDomId(highlightedCategoryId.value),
    );

    if (target) {
        target.scrollIntoView({ behavior: "smooth", block: "center" });
    }
};

onMounted(scrollToHighlightedCategory);

watch(highlightedCategoryId, scrollToHighlightedCategory);

const performSearch = () => {
    router.get(
        route("categorias.index"),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const confirmDelete = (categoria) => {
    categoryToDelete.value = categoria;
    showDeleteModal.value = true;
};

const deleteCategoria = () => {
    if (categoryToDelete.value) {
        router.delete(route("categorias.destroy", categoryToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                categoryToDelete.value = null;
            },
        });
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    categoryToDelete.value = null;
};
</script>

<template>
    <Head title="Categorías" />

    <component :is="currentLayout" v-bind="layoutProps">
        <FlashNotification />

        <div class="mx-auto max-w-7xl px-4 py-8">
            <div
                class="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center"
            >
                <div>
                    <h2 class="mb-1 text-2xl font-bold">Categorías</h2>
                    <p class="text-sm text-slate-500">
                        {{
                            isCliente
                                ? "Explora nuestras categorías de productos"
                                : "Administra las categorías de la tienda"
                        }}
                    </p>
                </div>
                <div v-if="isOwnerOrSeller && canCreateCategoria">
                    <Link
                        :href="route('categorias.create')"
                        class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    >
                        <svg
                            class="h-4 w-4"
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
                        Nueva Categoría
                    </Link>
                </div>
            </div>

            <div class="mb-6 rounded-lg border bg-white p-4 shadow-sm">
                <div class="flex flex-col items-center gap-3 md:flex-row">
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
                                class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                            >
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="categorias.data.length > 0 && isOwnerOrSeller"
                class="overflow-hidden rounded-lg border bg-white shadow-sm"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-[0.12em] text-slate-600"
                            >
                                <th class="px-6 py-4">Imagen</th>
                                <th class="px-6 py-4">Nombre</th>
                                <th class="px-6 py-4">Descripción</th>
                                <th class="px-6 py-4 text-center">Productos</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="categoria in categorias.data"
                                :key="categoria.id"
                                :id="getCategoryDomId(categoria.id)"
                                class="border-b border-slate-100 last:border-0 transition"
                                :class="
                                    isHighlightedCategory(categoria.id)
                                        ? 'bg-emerald-50 ring-2 ring-inset ring-emerald-300'
                                        : 'hover:bg-slate-50/50'
                                "
                            >
                                <td class="px-6 py-4">
                                    <Link
                                        :href="
                                            route(
                                                'categorias.show',
                                                categoria.id,
                                            )
                                        "
                                    >
                                        <img
                                            :src="
                                                getCategoryImageUrl(categoria)
                                            "
                                            :alt="categoria.nombre"
                                            class="h-14 w-14 rounded-lg border border-slate-200 object-cover"
                                        />
                                    </Link>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="
                                            route(
                                                'categorias.show',
                                                categoria.id,
                                            )
                                        "
                                        class="font-semibold text-slate-900 transition hover:text-emerald-700"
                                    >
                                        {{ categoria.nombre }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{
                                        categoria.descripcion ||
                                        "Sin descripción"
                                    }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"
                                    >
                                        {{ categoria.productos_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <Link
                                            :href="
                                                route(
                                                    'categorias.show',
                                                    categoria.id,
                                                )
                                            "
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                                            title="Ver detalles"
                                        >
                                            <svg
                                                class="h-4 w-4"
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
                                            v-if="canUpdateCategoria"
                                            :href="
                                                route(
                                                    'categorias.edit',
                                                    categoria.id,
                                                )
                                            "
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-emerald-200 bg-white text-emerald-600 transition hover:bg-emerald-50"
                                            title="Editar categoría"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M12 20h9" />
                                                <path
                                                    d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"
                                                />
                                            </svg>
                                        </Link>
                                        <button
                                            v-if="canDeleteCategoria"
                                            type="button"
                                            @click="confirmDelete(categoria)"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-rose-200 bg-white text-rose-600 transition hover:bg-rose-50"
                                            title="Eliminar categoría"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M3 6h18" />
                                                <path d="M8 6V4h8v2" />
                                                <path d="M19 6l-1 14H6L5 6" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                v-else-if="categorias.data.length > 0"
                class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <article
                    v-for="categoria in categorias.data"
                    :key="categoria.id"
                    :id="getCategoryDomId(categoria.id)"
                    class="group overflow-hidden rounded-[1.6rem] border border-slate-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
                    :class="
                        isHighlightedCategory(categoria.id)
                            ? 'ring-4 ring-emerald-300 shadow-2xl shadow-emerald-200/50'
                            : ''
                    "
                >
                    <Link :href="route('categorias.show', categoria.id)">
                        <div
                            class="relative flex h-44 items-center justify-center bg-gradient-to-br from-emerald-50 to-sky-50 p-3"
                        >
                            <img
                                :src="getCategoryImageUrl(categoria)"
                                :alt="categoria.nombre"
                                class="h-full w-full rounded-xl object-cover transition duration-300 group-hover:scale-105"
                            />
                        </div>
                    </Link>

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <Link
                                :href="route('categorias.show', categoria.id)"
                                class="line-clamp-1 text-base font-bold text-slate-900 transition hover:text-emerald-700"
                            >
                                {{ categoria.nombre }}
                            </Link>
                            <span
                                class="inline-flex shrink-0 items-center rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-700"
                            >
                                {{ categoria.productos_count }}
                            </span>
                        </div>

                        <p class="mt-2 line-clamp-2 text-sm text-slate-600">
                            {{ categoria.descripcion || "Sin descripción" }}
                        </p>

                        <div
                            class="mt-4 flex items-center justify-between gap-2"
                        >
                            <Link
                                :href="route('categorias.show', categoria.id)"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                            >
                                Ver productos
                            </Link>
                        </div>
                    </div>
                </article>
            </div>

            <div
                v-else
                class="rounded-[1.75rem] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-slate-500"
            >
                No se encontraron categorías
            </div>

            <div
                v-if="categorias.links.length > 3"
                class="mt-8 border-t border-slate-100 px-6 py-4"
            >
                <nav class="flex justify-center">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white p-1.5 shadow-sm"
                    >
                        <template
                            v-for="(link, index) in categorias.links"
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

        <ConfirmationModal
            :show="showDeleteModal"
            @close="cancelDelete"
            max-width="sm"
        >
            <template #title>Confirmar eliminación</template>
            <template #content>
                <p class="text-sm text-slate-600">
                    ¿Está seguro de que desea eliminar la categoría
                    <strong class="text-slate-900">{{
                        categoryToDelete?.nombre
                    }}</strong
                    >?
                </p>
                <p
                    v-if="categoryToDelete?.productos_count > 0"
                    class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800"
                >
                    Esta categoría tiene
                    <strong>{{ categoryToDelete.productos_count }}</strong>
                    producto(s) asociado(s).
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
                    @click="cancelDelete"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700"
                    @click="deleteCategoria"
                >
                    <svg
                        class="h-4 w-4"
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
