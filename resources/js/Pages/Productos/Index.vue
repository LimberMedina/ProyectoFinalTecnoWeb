<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import ProductoVariantesModal from "@/Components/ProductoVariantesModal.vue";

const props = defineProps({
    productos: Object,
    categorias: Array,
    filters: Object,
    rol: String,
});

const search = ref(props.filters?.search || "");
const categoriaSeleccionada = ref(props.filters?.categoria || "");
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const showVariantesModal = ref(false);
const productoVariantes = ref(null);

const productoSeleccionado = ref(null);
const showCantidadModal = ref(false);
const varianteItems = ref([]);

const page = usePage();
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));
const canManageProducts = computed(() =>
    Boolean(page.props.auth?.permissions?.productos?.create),
);
const isCliente = computed(() => (props.rol || "").toLowerCase() === "cliente");
const isOwner = computed(
    () => (props.rol || "").toLowerCase() === "propietario",
);
const isOwnerOrSeller = computed(() => {
    const rol = (props.rol || "").toLowerCase();
    return rol === "propietario" || rol === "vendedor";
});
const highlightedProductId = computed(() =>
    String(props.filters?.highlight || ""),
);
const currentLayout = computed(() =>
    isOwnerOrSeller.value ? AppLayout : PublicStoreLayout,
);
const layoutProps = computed(() =>
    isOwnerOrSeller.value ? { title: "Productos" } : {},
);

const performSearch = () => {
    const url = isCliente.value
        ? route("tienda.productos")
        : route("productos.index");
    router.get(
        url,
        { search: search.value, categoria: categoriaSeleccionada.value },
        { preserveState: true, replace: true },
    );
};

const filtrarPorCategoria = (categoria) => {
    categoriaSeleccionada.value = categoria;
    performSearch();
};

const limpiarFiltros = () => {
    search.value = "";
    categoriaSeleccionada.value = "";
    performSearch();
};

const confirmDelete = (producto) => {
    productToDelete.value = producto;
    showDeleteModal.value = true;
};
const deleteProducto = () => {
    if (productToDelete.value) {
        router.delete(route("productos.destroy", productToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                productToDelete.value = null;
            },
        });
    }
};
const cancelDelete = () => {
    showDeleteModal.value = false;
    productToDelete.value = null;
};

const formatPrice = (price) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(price || 0);
const getPrimaryVariant = (producto) => producto.variantes?.[0] || null;
const getVariantStockTotal = (producto) =>
    (producto.variantes || []).reduce(
        (total, variant) => total + (Number(variant.stock_actual) || 0),
        0,
    );
const getVariantStockMinimumTotal = (producto) =>
    (producto.variantes || []).reduce(
        (total, variant) => total + (Number(variant.stock_minimo) || 0),
        0,
    );
const getDisplayPrice = (producto) =>
    getPrimaryVariant(producto)?.precio_venta ??
    producto.precio_venta_base ??
    0;
const getDisplayStock = (producto) => getVariantStockTotal(producto);
const getDisplayStockMin = (producto) => getVariantStockMinimumTotal(producto);
const getImageUrl = (producto) =>
    producto.imagen
        ? `/storage/${producto.imagen}`
        : producto.imagenes && producto.imagenes.length > 0
          ? `/storage/${producto.imagenes[0].url}`
          : "/images/no-image.png";
const getProductDomId = (productoId) => `producto-${productoId}`;
const isHighlightedProducto = (producto) =>
    highlightedProductId.value &&
    String(producto.id) === highlightedProductId.value;

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

const abrirVariantes = (producto) => {
    productoVariantes.value = producto;
    showVariantesModal.value = true;
};

const cerrarVariantes = () => {
    showVariantesModal.value = false;
    productoVariantes.value = null;
};

const abrirModalCantidad = (producto) => {
    productoSeleccionado.value = producto;
    varianteItems.value = (producto?.variantes || [])
        .filter((variant) => (variant.estado || "").toUpperCase() === "ACTIVO")
        .map((variant) => ({
            id: variant.id,
            talla: variant.talla,
            color: variant.color,
            stock_actual: Number(variant.stock_actual) || 0,
            precio_venta: Number(variant.precio_venta) || 0,
            cantidad: 0,
        }));
    showCantidadModal.value = true;
};
const abrirCarrito = (producto) => {
    if (!isAuthenticated.value) {
        router.visit(route("login"));
        return;
    }
    abrirModalCantidad(producto);
};
const agregarAlCarrito = () => {
    if (!productoSeleccionado.value) return;

    const items = varianteItems.value
        .filter((variant) => Number(variant.cantidad) > 0)
        .map((variant) => ({
            producto_variante_id: variant.id,
            cantidad: Number(variant.cantidad),
        }));

    if (items.length === 0) {
        return;
    }

    router.post(
        route("carritos.store"),
        {
            items,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showCantidadModal.value = false;
                productoSeleccionado.value = null;
                varianteItems.value = [];
            },
        },
    );
};

const updateVarianteCantidad = (id, value) => {
    const item = varianteItems.value.find((variant) => variant.id === id);
    if (!item) return;

    const parsed = Number(value);
    if (!Number.isFinite(parsed) || parsed < 0) {
        item.cantidad = 0;
        return;
    }

    item.cantidad = Math.min(Math.floor(parsed), item.stock_actual);
};

const totalSeleccionado = computed(() =>
    varianteItems.value.reduce(
        (sum, variant) => sum + (Number(variant.cantidad) || 0),
        0,
    ),
);

const totalMontoSeleccionado = computed(() =>
    varianteItems.value.reduce(
        (sum, variant) =>
            sum +
            (Number(variant.cantidad) || 0) *
                (Number(variant.precio_venta) || 0),
        0,
    ),
);
</script>

<template>
    <Head title="Productos" />

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
                            isCliente
                                ? "Catálogo de Productos"
                                : "Gestión de Productos"
                        }}
                    </h2>
                    <p class="text-sm text-slate-500">
                        {{
                            isCliente
                                ? "Explora nuestro catálogo y encuentra lo que necesitas"
                                : "Administra tu catálogo de productos"
                        }}
                    </p>
                </div>
                <div>
                    <Link
                        v-if="canManageProducts"
                        :href="route('productos.create')"
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
                        Nuevo Producto
                    </Link>
                </div>
            </div>

            <!-- Barra de búsqueda y filtros -->
            <div class="bg-white border rounded-lg shadow-sm p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-5">
                        <label
                            class="block text-xs font-semibold text-slate-600 mb-1"
                            >Buscar</label
                        >
                        <div class="flex gap-2">
                            <input
                                v-model="search"
                                type="text"
                                class="w-full rounded-md border px-3 py-2 text-sm"
                                placeholder="Buscar por nombre o código..."
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

                    <div class="md:col-span-5">
                        <label
                            class="block text-xs font-semibold text-slate-600 mb-1"
                            >Categoría</label
                        >
                        <select
                            v-model="categoriaSeleccionada"
                            class="w-full rounded-md border px-3 py-2 text-sm"
                            @change="performSearch"
                        >
                            <option value="">Todas las categorías</option>
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
                        <button
                            class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                            type="button"
                            @click="limpiarFiltros"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Vista de tarjetas para clientes, tabla para administradores -->
            <div v-if="isCliente">
                <div
                    v-if="productos.data.length === 0"
                    class="rounded-[2rem] border border-dashed border-emerald-200 bg-white/80 px-6 py-16 text-center shadow-sm"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600"
                    >
                        <svg
                            class="w-8 h-8"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                        >
                            <path d="M3 3h18v18H3z" stroke-width="1" />
                        </svg>
                    </div>
                    <h4 class="mt-4 text-lg font-semibold text-slate-900">
                        No se encontraron productos
                    </h4>
                    <p class="mt-2 text-slate-500">
                        Intenta con otros filtros de búsqueda.
                    </p>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="producto in productos.data"
                        :key="producto.id"
                        :id="getProductDomId(producto.id)"
                        class="group overflow-hidden rounded-[2rem] border border-white bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-emerald-100/60"
                        :class="
                            isHighlightedProducto(producto)
                                ? 'ring-4 ring-emerald-300 shadow-2xl shadow-emerald-200/50'
                                : ''
                        "
                    >
                        <div
                            class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-emerald-50 to-sky-50"
                        >
                            <img
                                :src="getImageUrl(producto)"
                                :alt="producto.nombre"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            />
                            <div
                                class="absolute left-3 top-3 inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white shadow-lg"
                                :class="
                                    getDisplayStock(producto) >
                                    getDisplayStockMin(producto)
                                        ? 'bg-emerald-600'
                                        : getDisplayStock(producto) > 0
                                          ? 'bg-amber-500'
                                          : 'bg-rose-500'
                                "
                            >
                                {{
                                    getDisplayStock(producto) === 0
                                        ? "Sin stock"
                                        : `${getDisplayStock(producto)} disponibles`
                                }}
                            </div>
                        </div>
                        <div class="p-5">
                            <h3
                                class="text-base font-black tracking-tight text-slate-900"
                            >
                                {{ producto.nombre }}
                            </h3>
                            <p
                                class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500"
                            >
                                {{
                                    producto.descripcion ||
                                    "Producto seleccionado para ti con disponibilidad y precio claro."
                                }}
                            </p>
                            <div
                                class="mt-4 flex items-end justify-between gap-3"
                            >
                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                    >
                                        Precio
                                    </p>
                                    <p
                                        class="text-xl font-black text-emerald-600"
                                    >
                                        {{
                                            formatPrice(
                                                getDisplayPrice(producto),
                                            )
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl bg-slate-50 px-3 py-2 text-right text-xs font-semibold text-slate-500"
                                >
                                    Stock
                                    <div
                                        class="text-sm font-black text-slate-900"
                                    >
                                        {{ getDisplayStock(producto) }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-2">
                                <Link
                                    :href="
                                        isCliente
                                            ? route(
                                                  'tienda.producto',
                                                  producto.id,
                                              )
                                            : route(
                                                  'productos.show',
                                                  producto.id,
                                              )
                                    "
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-50"
                                    >Ver detalles</Link
                                >
                                <button
                                    type="button"
                                    @click="abrirCarrito(producto)"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none"
                                    :disabled="getDisplayStock(producto) === 0"
                                >
                                    <span v-if="getDisplayStock(producto) === 0"
                                        >Sin stock</span
                                    >
                                    <span v-else>
                                        <i class="bi bi-cart-plus-fill"></i>
                                        <span class="sr-only"
                                            >Agregar al carrito</span
                                        >
                                    </span>
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <!-- Vista de tabla para administradores -->
            <div v-else class="bg-white border rounded-lg shadow-sm p-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="text-xs text-slate-600 uppercase">
                                <th class="px-4 py-2">Imagen</th>
                                <th class="px-4 py-2">Nombre</th>
                                <th class="px-4 py-2">Categoría</th>
                                <th class="px-4 py-2">Precio</th>
                                <th class="px-4 py-2">Stock</th>
                                <th class="px-4 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="productos.data.length === 0">
                                <td class="p-4" colspan="6">
                                    No hay productos
                                </td>
                            </tr>
                            <tr
                                v-for="producto in productos.data"
                                :key="producto.id"
                                :id="getProductDomId(producto.id)"
                                class="border-t transition"
                                :class="
                                    isHighlightedProducto(producto)
                                        ? 'bg-emerald-50/70 ring-2 ring-inset ring-emerald-300'
                                        : 'hover:bg-slate-50'
                                "
                            >
                                <td class="px-4 py-3">
                                    <img
                                        :src="getImageUrl(producto)"
                                        :alt="producto.nombre"
                                        class="h-12 w-12 rounded-lg object-cover border border-slate-200 bg-slate-100"
                                    />
                                </td>
                                <td class="px-4 py-3">{{ producto.nombre }}</td>
                                <td class="px-4 py-3">
                                    {{
                                        producto.categoria?.nombre ||
                                        "Sin categoría"
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ formatPrice(getDisplayPrice(producto)) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ getDisplayStock(producto) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button
                                            v-if="isOwner"
                                            type="button"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-cyan-200 text-cyan-600 hover:bg-cyan-50"
                                            title="Gestionar variantes"
                                            @click="abrirVariantes(producto)"
                                        >
                                            <i class="bi bi-layers"></i>
                                            <span class="sr-only"
                                                >Variantes</span
                                            >
                                        </button>
                                        <Link
                                            :href="
                                                route(
                                                    'productos.edit',
                                                    producto.id,
                                                )
                                            "
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50"
                                            title="Editar producto"
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
                                                <path d="M12 20h9" />
                                                <path
                                                    d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"
                                                />
                                            </svg>
                                            <span class="sr-only">Editar</span>
                                        </Link>
                                        <button
                                            @click="confirmDelete(producto)"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50"
                                            title="Eliminar producto"
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
                                                <path d="M3 6h18" />
                                                <path d="M8 6V4h8v2" />
                                                <path d="M19 6l-1 14H6L5 6" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
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
            </div>

            <!-- Paginación -->
            <div
                v-if="productos.links && productos.links.length > 1"
                class="mt-8 flex justify-center"
            >
                <nav
                    class="inline-flex items-center space-x-2 bg-white border rounded-md px-3 py-2 shadow-sm"
                >
                    <template
                        v-for="(link, index) in productos.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-1 rounded-md text-sm"
                            :class="
                                link.active
                                    ? 'bg-emerald-600 text-white'
                                    : 'text-slate-700 hover:bg-slate-50'
                            "
                            v-html="link.label"
                            preserve-state
                        />
                        <span
                            v-else
                            class="px-3 py-1 rounded-md text-sm text-slate-400"
                            v-html="link.label"
                        ></span>
                    </template>
                </nav>
            </div>

            <!-- Modales -->
            <ConfirmationModal
                :show="showDeleteModal"
                @close="cancelDelete"
                max-width="sm"
            >
                <template #title>Confirmar Eliminación</template>
                <template #content>
                    <p>
                        ¿Está seguro de que desea eliminar
                        <strong>{{ productToDelete?.nombre }}</strong
                        >?
                    </p>
                </template>
                <template #footer>
                    <button
                        class="px-4 py-2 rounded-md border"
                        @click="cancelDelete"
                    >
                        Cancelar
                    </button>
                    <button
                        class="px-4 py-2 rounded-md bg-rose-600 text-white"
                        @click="deleteProducto"
                    >
                        Eliminar
                    </button>
                </template>
            </ConfirmationModal>

            <ConfirmationModal
                :show="showCantidadModal"
                @close="showCantidadModal = false"
                max-width="sm"
            >
                <template #title>Agregar al Carrito</template>
                <template #content>
                    <div>
                        <p class="mb-4">
                            Selecciona tallas y colores para
                            <strong>{{ productoSeleccionado?.nombre }}</strong
                            >.
                        </p>

                        <div
                            v-if="varianteItems.length === 0"
                            class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600"
                        >
                            No hay variantes disponibles para este producto.
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="variant in varianteItems"
                                :key="variant.id"
                                class="grid grid-cols-[minmax(0,1fr)_92px] items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        {{ variant.talla }} /
                                        {{ variant.color }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Stock: {{ variant.stock_actual }} ·
                                        Precio:
                                        {{ formatPrice(variant.precio_venta) }}
                                    </p>
                                </div>

                                <input
                                    type="number"
                                    min="0"
                                    :max="variant.stock_actual"
                                    :value="variant.cantidad"
                                    @input="
                                        updateVarianteCantidad(
                                            variant.id,
                                            $event.target.value,
                                        )
                                    "
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-center text-sm font-semibold"
                                />
                            </div>

                            <div
                                class="rounded-xl bg-slate-100 px-3 py-2 text-sm text-slate-700"
                            >
                                <span class="font-semibold"
                                    >Items seleccionados:</span
                                >
                                {{ totalSeleccionado }}
                                <span class="mx-2">|</span>
                                <span class="font-semibold">Total:</span>
                                {{ formatPrice(totalMontoSeleccionado) }}
                            </div>
                        </div>
                    </div>
                </template>
                <template #footer>
                    <button
                        class="px-4 py-2 rounded-md border"
                        @click="showCantidadModal = false"
                    >
                        Cancelar
                    </button>
                    <button
                        class="px-4 py-2 rounded-md bg-emerald-600 text-white"
                        @click="agregarAlCarrito"
                        :disabled="totalSeleccionado < 1"
                    >
                        Agregar al carrito
                    </button>
                </template>
            </ConfirmationModal>

            <ProductoVariantesModal
                :show="showVariantesModal"
                :producto="productoVariantes"
                @close="cerrarVariantes"
            />
        </div>
    </component>
</template>

<style scoped>
.product-card {
    transition:
        transform 0.2s,
        box-shadow 0.2s;
    border: none;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.card-img-top {
    transition: transform 0.3s;
}
.product-card:hover .card-img-top {
    transform: scale(1.05);
}
</style>
