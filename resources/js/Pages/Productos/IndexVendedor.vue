<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ProductoDetalleVariantesModal from "@/Components/Productos/ProductoDetalleVariantesModal.vue";
import ProductoVariantesVentaModal from "@/Components/Productos/ProductoVariantesVentaModal.vue";
import VenderProductoModal from "@/Components/Productos/VenderProductoModal.vue";

const props = defineProps({
    productos: Object,
    categorias: Array,
    clientes: Array,
    clienteFinalId: Number,
    metodosPago: Array,
    filters: Object,
    rol: String,
});

const search = ref(props.filters?.search || "");
const categoriaSeleccionada = ref(props.filters?.categoria || "");
const tallaFiltro = ref(props.filters?.talla || "");
const colorFiltro = ref(props.filters?.color || "");
const highlightedProductId = computed(() =>
    String(props.filters?.highlight || ""),
);

const showDetalleModal = ref(false);
const showVariantesVentaModal = ref(false);
const showVentaModal = ref(false);
const productoSeleccionado = ref(null);
const variantesEnEspera = ref([]);

const feedbackVenta = ref({ show: false, message: "" });
const feedbackCarga = ref({ show: false, message: "" });

const tieneFiltrosDeVariante = computed(() =>
    Boolean(tallaFiltro.value || colorFiltro.value),
);

const performSearch = () => {
    router.get(
        route("productos.index"),
        {
            search: search.value,
            categoria: categoriaSeleccionada.value,
            talla: tallaFiltro.value,
            color: colorFiltro.value,
        },
        { preserveState: true, replace: true, preserveScroll: true },
    );
};

const limpiarFiltros = () => {
    search.value = "";
    categoriaSeleccionada.value = "";
    tallaFiltro.value = "";
    colorFiltro.value = "";
    performSearch();
};

const formatPrice = (price) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(Number(price) || 0);

const getImageUrl = (producto) => {
    if (producto?.imagen) {
        return `/storage/${producto.imagen}`;
    }

    return "/images/no-image.png";
};

const getStockTotal = (producto) =>
    (producto?.variantes || []).reduce(
        (sum, variant) => sum + (Number(variant.stock_actual) || 0),
        0,
    );

const getPrecioDesde = (producto) => {
    const activeVariants = (producto?.variantes || []).filter(
        (variant) => (variant.estado || "").toUpperCase() === "ACTIVO",
    );

    if (!activeVariants.length) {
        return Number(producto?.precio_venta_base) || 0;
    }

    return Math.min(
        ...activeVariants.map((variant) => Number(variant.precio_venta) || 0),
    );
};

const getStockVariante = (variant) => Number(variant.stock_actual) || 0;

const getPrecioVariante = (variant) => Number(variant.precio_venta) || 0;
const getProductDomId = (productId) => `producto-${productId}`;
const isHighlightedProducto = (productId) =>
    highlightedProductId.value &&
    String(productId) === highlightedProductId.value;

const scrollToHighlightedProduct = async () => {
    if (!highlightedProductId.value) {
        return;
    }

    await nextTick();
    const target = document.getElementById(
        getProductDomId(highlightedProductId.value),
    );

    if (target) {
        target.scrollIntoView({ behavior: "smooth", block: "center" });
    }
};

onMounted(scrollToHighlightedProduct);

watch(highlightedProductId, scrollToHighlightedProduct);

const getVariantePendienteKey = (productoId, varianteId) =>
    `${productoId}-${varianteId}`;

const getVariantesPendientesCount = () => variantesEnEspera.value.length;

const getVariantesPendientesUnidades = () =>
    variantesEnEspera.value.reduce(
        (sum, item) => sum + (Number(item.cantidad) || 0),
        0,
    );

const mostrarFeedbackCarga = (message) => {
    feedbackCarga.value = { show: true, message };

    window.clearTimeout(mostrarFeedbackCarga._timer);
    mostrarFeedbackCarga._timer = window.setTimeout(() => {
        feedbackCarga.value = { show: false, message: "" };
    }, 1800);
};

const agregarVarianteAEspera = (card) => {
    const variante = card?.variante;
    const producto = card?.producto;

    if (!variante || !producto) {
        return;
    }

    const key = getVariantePendienteKey(producto.id, variante.id);
    const existente = variantesEnEspera.value.find((item) => item.key === key);

    if (existente) {
        if (existente.cantidad < existente.stock_actual) {
            existente.cantidad += 1;
        }
        mostrarFeedbackCarga(
            `Variante cargada: ${variantesEnEspera.value.length} item(s) en espera.`,
        );
        return;
    }

    variantesEnEspera.value.push({
        key,
        producto_variante_id: variante.id,
        producto_id: producto.id,
        producto_nombre: producto.nombre,
        categoria_nombre: producto.categoria?.nombre || "Sin categoria",
        imagen: producto.imagen || null,
        talla: variante.talla || "-",
        color: variante.color || "-",
        sku: variante.sku || "-",
        estado: (variante.estado || "").toUpperCase(),
        stock_actual: Number(variante.stock_actual) || 0,
        precio_venta: Number(variante.precio_venta) || 0,
        cantidad: 1,
    });

    mostrarFeedbackCarga(
        `Variante cargada: ${variantesEnEspera.value.length} item(s) en espera.`,
    );
};

const abrirModalVentaPendiente = () => {
    if (!variantesEnEspera.value.length) {
        return;
    }

    showVentaModal.value = true;
};

const getVariantCardItems = (producto) =>
    (producto.variantes || [])
        .filter((variant) => {
            const tallaMatch = tallaFiltro.value
                ? (variant.talla || "")
                      .toLowerCase()
                      .includes(tallaFiltro.value.toLowerCase())
                : true;
            const colorMatch = colorFiltro.value
                ? (variant.color || "")
                      .toLowerCase()
                      .includes(colorFiltro.value.toLowerCase())
                : true;
            return tallaMatch && colorMatch;
        })
        .map((variant) => ({
            id: `${producto.id}-${variant.id}`,
            producto,
            variante: variant,
            modo: "variante",
        }));

const cardsList = computed(() => {
    const productos = props.productos?.data || [];

    if (!tieneFiltrosDeVariante.value) {
        return productos.map((producto) => ({
            id: producto.id,
            producto,
            modo: "producto",
        }));
    }

    return productos.flatMap((producto) => getVariantCardItems(producto));
});

const productCardsList = computed(
    () =>
        props.productos?.data?.map((producto) => ({
            id: producto.id,
            producto,
            modo: "producto",
        })) || [],
);

const variantCardsList = computed(() =>
    cardsList.value.filter((card) => card.modo === "variante"),
);

const accionVentaCard = (card) => {
    if (card.modo === "variante") {
        agregarVarianteAEspera(card);
        return;
    }

    abrirSelectorVariantes(card.producto);
};

const abrirDetalle = (producto) => {
    productoSeleccionado.value = producto;
    showDetalleModal.value = true;
};

const abrirSelectorVariantes = (producto) => {
    productoSeleccionado.value = producto;
    showVariantesVentaModal.value = true;
};

const abrirVenta = (producto) => {
    productoSeleccionado.value = producto;
    showVentaModal.value = true;
};

const cerrarSelectorVariantes = () => {
    showVariantesVentaModal.value = false;
    productoSeleccionado.value = null;
};

const agregarVarianteDesdeSelector = (item) => {
    const key =
        item.key ||
        getVariantePendienteKey(item.producto_id, item.producto_variante_id);
    const existente = variantesEnEspera.value.find(
        (variant) => variant.key === key,
    );

    if (existente) {
        existente.cantidad = Math.min(
            (Number(existente.cantidad) || 0) + (Number(item.cantidad) || 0),
            Number(existente.stock_actual) || 0,
        );
    } else {
        variantesEnEspera.value.push({
            ...item,
            key,
        });
    }

    mostrarFeedbackCarga(
        `Variante cargada: ${variantesEnEspera.value.length} item(s) en espera.`,
    );
};

const venderVarianteDesdeSelector = (item) => {
    agregarVarianteDesdeSelector(item);
    showVariantesVentaModal.value = false;
    showVentaModal.value = true;
};

const cerrarModales = () => {
    showDetalleModal.value = false;
    showVentaModal.value = false;
    showVariantesVentaModal.value = false;
    productoSeleccionado.value = null;
};

const onVentaRegistrada = (payload) => {
    feedbackVenta.value = {
        show: true,
        message: `Venta #${payload.venta_id || ""} registrada correctamente.`,
    };

    if (!payload.isQr) {
        showVentaModal.value = false;
        showVariantesVentaModal.value = false;
        variantesEnEspera.value = [];
    }
};
</script>

<template>
    <Head title="Productos | Vendedor" />

    <AppLayout title="Productos">
        <FlashNotification />

        <div class="mx-auto max-w-7xl px-4 py-6 space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">
                    Productos para Venta
                </h2>
                <p class="mt-1 text-sm text-slate-600">
                    Vista simplificada para vendedor: tarjetas de producto y
                    venta por modal.
                </p>
            </div>

            <div
                v-if="feedbackVenta.show"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
            >
                {{ feedbackVenta.message }}
            </div>

            <div
                v-if="feedbackCarga.show"
                class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700"
            >
                {{ feedbackCarga.message }}
            </div>

            <section
                class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                    <div class="md:col-span-4">
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-600"
                        >
                            Nombre / Codigo / SKU
                        </label>
                        <input
                            v-model="search"
                            type="text"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                            placeholder="Buscar producto"
                            @keyup.enter="performSearch"
                        />
                    </div>

                    <div class="md:col-span-3">
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-600"
                        >
                            Categoria
                        </label>
                        <select
                            v-model="categoriaSeleccionada"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                            @change="performSearch"
                        >
                            <option value="">Todas</option>
                            <option
                                v-for="categoria in categorias"
                                :key="categoria.id"
                                :value="categoria.id"
                            >
                                {{ categoria.nombre }}
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-600"
                        >
                            Talla
                        </label>
                        <input
                            v-model="tallaFiltro"
                            type="text"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                            placeholder="M"
                            @keyup.enter="performSearch"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-600"
                        >
                            Color
                        </label>
                        <input
                            v-model="colorFiltro"
                            type="text"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                            placeholder="Negro"
                            @keyup.enter="performSearch"
                        />
                    </div>

                    <div class="md:col-span-1 flex items-end">
                        <button
                            type="button"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                            @click="limpiarFiltros"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        @click="performSearch"
                    >
                        Buscar
                    </button>
                </div>
            </section>

            <section>
                <div
                    v-if="!cardsList.length"
                    class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center text-slate-500"
                >
                    No hay productos para mostrar con los filtros aplicados.
                </div>

                <div
                    v-else-if="!tieneFiltrosDeVariante"
                    class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <article
                        v-for="card in productCardsList"
                        :key="card.id"
                        :id="getProductDomId(card.producto.id)"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                        :class="
                            isHighlightedProducto(card.producto.id)
                                ? 'ring-4 ring-emerald-300 shadow-2xl shadow-emerald-200/50'
                                : ''
                        "
                    >
                        <div
                            class="relative aspect-[4/3] overflow-hidden bg-slate-50"
                        >
                            <img
                                :src="getImageUrl(card.producto)"
                                :alt="card.producto.nombre"
                                class="h-full w-full object-cover"
                            />
                        </div>

                        <div class="space-y-3 p-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">
                                    {{ card.producto.nombre }}
                                </h3>
                                <p class="text-xs text-slate-500">
                                    {{
                                        card.producto.categoria?.nombre ||
                                        "Sin categoria"
                                    }}
                                </p>
                            </div>

                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <p
                                        class="text-xs uppercase tracking-wide text-slate-400"
                                    >
                                        Precio desde
                                    </p>
                                    <p
                                        class="text-lg font-black text-emerald-600"
                                    >
                                        {{
                                            formatPrice(
                                                getPrecioDesde(card.producto),
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl bg-slate-50 px-3 py-2 text-right"
                                >
                                    <p
                                        class="text-[11px] uppercase text-slate-400"
                                    >
                                        Stock total
                                    </p>
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ getStockTotal(card.producto) }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                    @click="abrirDetalle(card.producto)"
                                >
                                    Ver detalle
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                                    @click="accionVentaCard(card)"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <circle cx="9" cy="21" r="1" />
                                        <circle cx="20" cy="21" r="1" />
                                        <path
                                            d="M1 1h4l2.7 12.7a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.7L23 6H6"
                                        />
                                    </svg>
                                    Vender
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="card in variantCardsList"
                        :key="card.id"
                        :id="getProductDomId(card.producto.id)"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                        :class="
                            isHighlightedProducto(card.producto.id)
                                ? 'ring-4 ring-emerald-300 shadow-2xl shadow-emerald-200/50'
                                : ''
                        "
                    >
                        <div
                            class="relative aspect-[4/3] overflow-hidden bg-slate-50"
                        >
                            <img
                                :src="getImageUrl(card.producto)"
                                :alt="card.producto.nombre"
                                class="h-full w-full object-cover"
                            />
                            <div
                                class="absolute left-3 top-3 rounded-full bg-slate-900/80 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white"
                            >
                                Variante
                            </div>
                        </div>

                        <div class="space-y-3 p-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">
                                    {{ card.producto.nombre }}
                                </h3>
                                <p class="text-xs text-slate-500">
                                    {{
                                        card.producto.categoria?.nombre ||
                                        "Sin categoria"
                                    }}
                                </p>
                                <p
                                    class="mt-1 text-sm font-semibold text-slate-700"
                                >
                                    {{ card.variante.talla }} /
                                    {{ card.variante.color }}
                                </p>
                            </div>

                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <p
                                        class="text-xs uppercase tracking-wide text-slate-400"
                                    >
                                        Precio
                                    </p>
                                    <p
                                        class="text-lg font-black text-emerald-600"
                                    >
                                        {{
                                            formatPrice(
                                                getPrecioVariante(
                                                    card.variante,
                                                ),
                                            )
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl bg-slate-50 px-3 py-2 text-right"
                                >
                                    <p
                                        class="text-[11px] uppercase text-slate-400"
                                    >
                                        Stock
                                    </p>
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ getStockVariante(card.variante) }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                    @click="abrirDetalle(card.producto)"
                                >
                                    Ver detalle
                                </button>

                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                                    @click="accionVentaCard(card)"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <circle cx="9" cy="21" r="1" />
                                        <circle cx="20" cy="21" r="1" />
                                        <path
                                            d="M1 1h4l2.7 12.7a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.7L23 6H6"
                                        />
                                    </svg>
                                    Vender
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <div
                v-if="productos?.links && productos.links.length > 1"
                class="mt-8 flex justify-center"
            >
                <nav
                    class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white p-1"
                >
                    <template
                        v-for="(link, index) in productos.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-state
                            preserve-scroll
                            v-html="link.label"
                            class="rounded px-3 py-1.5 text-sm"
                            :class="
                                link.active
                                    ? 'bg-emerald-600 text-white'
                                    : 'text-slate-600 hover:bg-slate-50'
                            "
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="rounded px-3 py-1.5 text-sm text-slate-300"
                        />
                    </template>
                </nav>
            </div>

            <button
                v-if="variantesEnEspera.length > 0"
                type="button"
                class="fixed bottom-6 right-6 z-[80] flex items-center gap-3 rounded-full bg-slate-900 px-4 py-3 text-white shadow-2xl shadow-slate-900/30 transition hover:-translate-y-1 hover:bg-slate-800"
                @click="abrirModalVentaPendiente"
            >
                <span
                    class="relative inline-flex h-11 w-11 items-center justify-center rounded-full bg-emerald-600 text-white"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path
                            d="M1 1h4l2.7 12.7a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.7L23 6H6"
                        />
                    </svg>
                    <span
                        class="absolute -right-1 -top-1 inline-flex min-w-6 items-center justify-center rounded-full bg-rose-600 px-1.5 py-0.5 text-[11px] font-black leading-none text-white"
                    >
                        {{ variantesEnEspera.length }}
                    </span>
                </span>
                <span class="hidden sm:block text-left">
                    <span
                        class="block text-xs uppercase tracking-[0.18em] text-slate-300"
                        >Venta cargada</span
                    >
                    <span class="block text-sm font-semibold"
                        >{{ getVariantesPendientesCount() }} variantes ·
                        {{ getVariantesPendientesUnidades() }} unidades</span
                    >
                </span>
            </button>

            <ProductoDetalleVariantesModal
                :show="showDetalleModal"
                :producto="productoSeleccionado"
                @close="cerrarModales"
            />

            <ProductoVariantesVentaModal
                :show="showVariantesVentaModal"
                :producto="productoSeleccionado"
                @close="cerrarSelectorVariantes"
                @agregar="agregarVarianteDesdeSelector"
                @vender="venderVarianteDesdeSelector"
            />

            <VenderProductoModal
                :show="showVentaModal"
                :producto="productoSeleccionado"
                :items-iniciales="variantesEnEspera"
                :clientes="clientes"
                :cliente-final-id="clienteFinalId"
                :metodos-pago="metodosPago"
                @close="cerrarModales"
                @venta-registrada="onVentaRegistrada"
            />
        </div>
    </AppLayout>
</template>
