<script setup>
import { computed, ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    categoria: {
        type: Object,
        required: true,
    },
    productos: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close"]);

const search = ref("");

const form = useForm({
    producto_ids: [],
});

const closeModal = () => {
    emit("close");
};

const resetState = () => {
    search.value = "";
    form.clearErrors();
    form.reset();
    form.producto_ids = [];
};

watch(
    () => props.show,
    (visible) => {
        if (visible) {
            resetState();
        }
    },
);

const selectedIds = computed(() => new Set(form.producto_ids));

const filteredProductos = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return props.productos;
    }

    return props.productos.filter((producto) => {
        const categoryName = producto.categoria?.nombre || "";

        return [producto.nombre, producto.codigo, producto.marca, categoryName]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(term));
    });
});

const selectableCount = computed(
    () =>
        filteredProductos.value.filter((producto) => !producto.categoria_id)
            .length,
);

const toggleProduct = (producto) => {
    if (producto.categoria_id) {
        return;
    }

    const id = producto.id;
    const index = form.producto_ids.indexOf(id);

    if (index === -1) {
        form.producto_ids.push(id);
        return;
    }

    form.producto_ids.splice(index, 1);
};

const submit = () => {
    if (form.producto_ids.length === 0) {
        return;
    }

    form.post(route("categorias.productos.store", props.categoria.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50">
            <div
                class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm"
                @click="closeModal"
            ></div>

            <div
                class="relative flex h-full items-center justify-center p-4 sm:p-6"
            >
                <div
                    class="w-full max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-2xl ring-1 ring-slate-200"
                >
                    <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-600"
                                >
                                    Selección de productos
                                </p>
                                <h2
                                    class="mt-2 text-2xl font-black text-slate-900"
                                >
                                    Agregar productos a {{ categoria.nombre }}
                                </h2>
                                <p class="mt-2 text-sm text-slate-500">
                                    Busca uno o varios productos. Los que ya
                                    tienen categoría aparecen marcados y no se
                                    pueden seleccionar.
                                </p>
                            </div>

                            <button
                                type="button"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                                @click="closeModal"
                                aria-label="Cerrar"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M18 6L6 18" />
                                    <path d="M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div
                            class="mt-5 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center"
                        >
                            <label class="relative block">
                                <span class="sr-only">Buscar producto</span>
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400"
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
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="M21 21l-4.3-4.3" />
                                    </svg>
                                </span>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Buscar por nombre, código, marca o categoría"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                />
                            </label>

                            <div
                                class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-600"
                            >
                                <span class="text-emerald-600">{{
                                    form.producto_ids.length
                                }}</span>
                                seleccionados
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid max-h-[62vh] grid-cols-1 gap-0 lg:grid-cols-[minmax(0,1fr)_280px]"
                    >
                        <div class="overflow-y-auto p-6">
                            <div
                                v-if="filteredProductos.length > 0"
                                class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
                            >
                                <button
                                    v-for="producto in filteredProductos"
                                    :key="producto.id"
                                    type="button"
                                    @click="toggleProduct(producto)"
                                    :disabled="!!producto.categoria_id"
                                    class="group rounded-[1.4rem] border p-4 text-left transition"
                                    :class="
                                        producto.categoria_id
                                            ? 'cursor-not-allowed border-rose-200 bg-rose-50/70 opacity-80'
                                            : selectedIds.has(producto.id)
                                              ? 'border-emerald-400 bg-emerald-50 shadow-[0_12px_30px_-20px_rgba(16,185,129,0.6)]'
                                              : 'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-lg'
                                    "
                                >
                                    <div
                                        class="flex items-start justify-between gap-3"
                                    >
                                        <div>
                                            <p
                                                class="text-sm font-bold text-slate-900 line-clamp-2"
                                            >
                                                {{ producto.nombre }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-400"
                                            >
                                                {{ producto.codigo }}
                                            </p>
                                        </div>

                                        <span
                                            v-if="
                                                selectedIds.has(producto.id) &&
                                                !producto.categoria_id
                                            "
                                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-white"
                                        >
                                            <svg
                                                class="h-3.5 w-3.5"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="3"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M20 6L9 17l-5-5" />
                                            </svg>
                                        </span>
                                    </div>

                                    <div
                                        class="mt-4 flex items-center justify-between gap-2"
                                    >
                                        <div class="text-left">
                                            <p
                                                class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                            >
                                                Categoría
                                            </p>
                                            <p
                                                class="text-sm font-semibold text-slate-700"
                                            >
                                                {{
                                                    producto.categoria
                                                        ?.nombre ||
                                                    "Sin categoría"
                                                }}
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p
                                                class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                            >
                                                Stock
                                            </p>
                                            <p
                                                class="text-sm font-semibold text-slate-700"
                                            >
                                                {{ producto.stock_actual }}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-3 flex items-center justify-between text-sm"
                                    >
                                        <span
                                            class="font-black text-emerald-700"
                                        >
                                            {{ producto.precio_venta }}
                                        </span>
                                        <span
                                            v-if="producto.categoria_id"
                                            class="inline-flex items-center rounded-full border border-rose-200 bg-white px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-rose-600"
                                        >
                                            Ya asignado
                                        </span>
                                        <span
                                            v-else-if="
                                                selectedIds.has(producto.id)
                                            "
                                            class="inline-flex items-center rounded-full border border-emerald-200 bg-white px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-emerald-700"
                                        >
                                            Seleccionado
                                        </span>
                                    </div>
                                </button>
                            </div>

                            <div
                                v-else
                                class="flex min-h-[28rem] items-center justify-center rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 px-6 text-center text-slate-500"
                            >
                                <div>
                                    <i
                                        class="bi bi-search text-3xl text-emerald-500"
                                    ></i>
                                    <p
                                        class="mt-3 font-semibold text-slate-800"
                                    >
                                        No hay productos que coincidan con la
                                        búsqueda
                                    </p>
                                </div>
                            </div>
                        </div>

                        <aside
                            class="border-t border-slate-100 bg-slate-50 p-6 lg:border-l lg:border-t-0"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400"
                            >
                                Resumen
                            </p>
                            <div
                                class="mt-3 rounded-[1.4rem] border border-slate-200 bg-white p-4"
                            >
                                <div
                                    class="flex items-center justify-between text-sm text-slate-600"
                                >
                                    <span>Encontrados</span>
                                    <span
                                        class="font-semibold text-slate-900"
                                        >{{ filteredProductos.length }}</span
                                    >
                                </div>
                                <div
                                    class="mt-2 flex items-center justify-between text-sm text-slate-600"
                                >
                                    <span>Seleccionables</span>
                                    <span
                                        class="font-semibold text-emerald-700"
                                        >{{ selectableCount }}</span
                                    >
                                </div>
                                <div
                                    class="mt-2 flex items-center justify-between text-sm text-slate-600"
                                >
                                    <span>Seleccionados</span>
                                    <span
                                        class="font-semibold text-slate-900"
                                        >{{ form.producto_ids.length }}</span
                                    >
                                </div>
                            </div>

                            <div class="mt-4">
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.22em] text-slate-400"
                                >
                                    Seleccionados
                                </p>
                                <div
                                    class="mt-3 flex min-h-32 flex-wrap gap-2 rounded-[1.4rem] border border-dashed border-slate-200 bg-white p-3"
                                >
                                    <template
                                        v-if="form.producto_ids.length > 0"
                                    >
                                        <span
                                            v-for="productoId in form.producto_ids"
                                            :key="productoId"
                                            class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-800"
                                        >
                                            #{{ productoId }}
                                        </span>
                                    </template>
                                    <p v-else class="text-sm text-slate-400">
                                        Todavía no seleccionas productos.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex gap-3">
                                <button
                                    type="button"
                                    class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                    @click="closeModal"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-emerald-300"
                                    :disabled="
                                        form.processing ||
                                        form.producto_ids.length === 0
                                    "
                                    @click="submit"
                                >
                                    {{
                                        form.processing
                                            ? "Guardando..."
                                            : "Agregar"
                                    }}
                                </button>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
