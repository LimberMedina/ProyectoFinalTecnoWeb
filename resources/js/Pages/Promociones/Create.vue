<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import { ref, computed } from "vue";

const props = defineProps({
    productos: Array,
    categorias: Array,
});

const form = useForm({
    nombre: "",
    descripcion: "",
    valor_descuento_decimal: "",
    fecha_inicio: "",
    fecha_fin: "",
    estado: true,
    productos: [],
    categorias: [],
});

const searchProducto = ref("");

const productosFiltrados = computed(() => {
    if (!searchProducto.value) return props.productos;

    const search = searchProducto.value.toLowerCase();
    return props.productos.filter((producto) =>
        `${producto.nombre} ${producto.codigo}`.toLowerCase().includes(search),
    );
});

const productosSeleccionados = ref([]);
const categoriasSeleccionadas = ref([]);

const agregarProducto = (producto) => {
    if (productosSeleccionados.value.some((item) => item.id === producto.id)) {
        searchProducto.value = "";
        return;
    }

    productosSeleccionados.value.push({
        id: producto.id,
        nombre: producto.nombre,
        codigo: producto.codigo,
        aplica_mayorista: false,
        aplica_minorista: false,
    });

    searchProducto.value = "";
};

const eliminarProducto = (index) => {
    productosSeleccionados.value.splice(index, 1);
};

const toggleCategoria = (categoria) => {
    const index = categoriasSeleccionadas.value.findIndex(
        (item) => item.id === categoria.id,
    );

    if (index > -1) {
        categoriasSeleccionadas.value.splice(index, 1);
        return;
    }

    categoriasSeleccionadas.value.push({
        id: categoria.id,
        nombre: categoria.nombre,
        aplica_mayorista: false,
        aplica_minorista: false,
    });
};

const isCategoriaSelected = (categoriaId) =>
    categoriasSeleccionadas.value.some((item) => item.id === categoriaId);

const submitForm = () => {
    form.productos = productosSeleccionados.value.map((producto) => ({
        id: producto.id,
        aplica_mayorista: producto.aplica_mayorista,
        aplica_minorista: producto.aplica_minorista,
    }));

    form.categorias = categoriasSeleccionadas.value.map((categoria) => ({
        id: categoria.id,
        aplica_mayorista: categoria.aplica_mayorista,
        aplica_minorista: categoria.aplica_minorista,
    }));

    form.post(route("promociones.store"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Crear Promoción" />

    <AppLayout title="Crear Promoción">
        <FlashNotification />

        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
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
                            Promociones
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Crear promoción
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Registra una nueva oferta o descuento con un
                            formulario claro, moderno y predecible.
                        </p>
                    </div>

                    <Link
                        :href="route('promociones.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </Link>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center justify-between gap-3"
                        >
                            <h2 class="text-xl font-black text-slate-900">
                                Información general
                            </h2>
                            <i
                                class="bi bi-badge-ad text-2xl text-emerald-600"
                            ></i>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Nombre
                                    <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.nombre,
                                    }"
                                    placeholder="Ej: Descuento Navidad 2025"
                                />
                                <p
                                    v-if="form.errors.nombre"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.nombre }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Descuento (%)
                                    <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.valor_descuento_decimal"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.valor_descuento_decimal,
                                    }"
                                    placeholder="15"
                                />
                                <p
                                    v-if="form.errors.valor_descuento_decimal"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.valor_descuento_decimal }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Descripción</label
                                >
                                <textarea
                                    v-model="form.descripcion"
                                    rows="3"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.descripcion,
                                    }"
                                    placeholder="Descripción opcional de la promoción"
                                ></textarea>
                                <p
                                    v-if="form.errors.descripcion"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.descripcion }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Fecha inicio
                                    <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.fecha_inicio"
                                    type="date"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.fecha_inicio,
                                    }"
                                />
                                <p
                                    v-if="form.errors.fecha_inicio"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.fecha_inicio }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Fecha fin
                                    <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.fecha_fin"
                                    type="date"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.fecha_fin,
                                    }"
                                />
                                <p
                                    v-if="form.errors.fecha_fin"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.fecha_fin }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700"
                                >
                                    <input
                                        v-model="form.estado"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                    />
                                    {{
                                        form.estado
                                            ? "Promoción activa"
                                            : "Promoción inactiva"
                                    }}
                                </label>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center justify-between gap-3"
                        >
                            <h2 class="text-xl font-black text-slate-900">
                                Productos específicos
                            </h2>
                            <i
                                class="bi bi-box-seam text-2xl text-emerald-600"
                            ></i>
                        </div>

                        <div class="mb-4">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Buscar producto</label
                            >
                            <input
                                v-model="searchProducto"
                                type="text"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                placeholder="Buscar por nombre o código..."
                            />
                        </div>

                        <div
                            class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-3"
                        >
                            <button
                                v-for="producto in productosFiltrados.slice(
                                    0,
                                    10,
                                )"
                                :key="producto.id"
                                type="button"
                                @click="agregarProducto(producto)"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-medium text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50"
                            >
                                <i
                                    class="bi bi-plus-circle me-2 text-emerald-600"
                                ></i>
                                {{ producto.codigo }} - {{ producto.nombre }}
                            </button>
                        </div>

                        <div
                            v-if="productosSeleccionados.length > 0"
                            class="mt-6 space-y-3"
                        >
                            <h3 class="text-base font-bold text-slate-900">
                                Productos seleccionados
                            </h3>
                            <article
                                v-for="(
                                    producto, index
                                ) in productosSeleccionados"
                                :key="producto.id"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <div
                                    class="mb-3 flex items-start justify-between gap-3"
                                >
                                    <p class="font-semibold text-slate-900">
                                        {{ producto.codigo }} -
                                        {{ producto.nombre }}
                                    </p>
                                    <button
                                        type="button"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-rose-600 transition hover:bg-rose-50"
                                        @click="eliminarProducto(index)"
                                    >
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                                <div
                                    class="flex flex-wrap gap-4 text-sm text-slate-700"
                                >
                                    <label
                                        class="inline-flex items-center gap-2"
                                    >
                                        <input
                                            v-model="producto.aplica_minorista"
                                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                            type="checkbox"
                                            :id="`prod-minorista-${producto.id}`"
                                        />
                                        Precio minorista
                                    </label>
                                    <label
                                        class="inline-flex items-center gap-2"
                                    >
                                        <input
                                            v-model="producto.aplica_mayorista"
                                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                            type="checkbox"
                                            :id="`prod-mayorista-${producto.id}`"
                                        />
                                        Precio mayorista
                                    </label>
                                </div>
                            </article>
                        </div>

                        <p
                            v-else
                            class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center text-slate-500"
                        >
                            No hay productos seleccionados
                        </p>
                    </section>

                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center justify-between gap-3"
                        >
                            <h2 class="text-xl font-black text-slate-900">
                                Categorías completas
                            </h2>
                            <i class="bi bi-tags text-2xl text-emerald-600"></i>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-3"
                        >
                            <button
                                v-for="categoria in categorias"
                                :key="categoria.id"
                                type="button"
                                @click="toggleCategoria(categoria)"
                                class="rounded-2xl border px-4 py-3 text-left text-sm font-medium transition"
                                :class="
                                    isCategoriaSelected(categoria.id)
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                                        : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                "
                            >
                                <i
                                    class="bi me-2"
                                    :class="
                                        isCategoriaSelected(categoria.id)
                                            ? 'bi-check-circle-fill text-emerald-600'
                                            : 'bi-circle text-slate-400'
                                    "
                                ></i>
                                {{ categoria.nombre }}
                            </button>
                        </div>

                        <div
                            v-if="categoriasSeleccionadas.length > 0"
                            class="mt-6 space-y-3"
                        >
                            <h3 class="text-base font-bold text-slate-900">
                                Categorías seleccionadas
                            </h3>
                            <article
                                v-for="categoria in categoriasSeleccionadas"
                                :key="categoria.id"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p class="mb-3 font-semibold text-slate-900">
                                    {{ categoria.nombre }}
                                </p>
                                <div
                                    class="flex flex-wrap gap-4 text-sm text-slate-700"
                                >
                                    <label
                                        class="inline-flex items-center gap-2"
                                    >
                                        <input
                                            v-model="categoria.aplica_minorista"
                                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                            type="checkbox"
                                            :id="`cat-minorista-${categoria.id}`"
                                        />
                                        Precio minorista
                                    </label>
                                    <label
                                        class="inline-flex items-center gap-2"
                                    >
                                        <input
                                            v-model="categoria.aplica_mayorista"
                                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                            type="checkbox"
                                            :id="`cat-mayorista-${categoria.id}`"
                                        />
                                        Precio mayorista
                                    </label>
                                </div>
                            </article>
                        </div>

                        <p
                            v-else
                            class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center text-slate-500"
                        >
                            No hay categorías seleccionadas
                        </p>
                    </section>

                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:justify-end"
                        >
                            <Link
                                :href="route('promociones.index')"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                >Cancelar</Link
                            >
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing"
                                    ><i class="bi bi-hourglass-split"></i>
                                    Guardando...</span
                                >
                                <span v-else
                                    ><i class="bi bi-save"></i> Guardar
                                    promoción</span
                                >
                            </button>
                        </div>
                    </section>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
