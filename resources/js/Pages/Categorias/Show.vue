<script setup>
import { computed, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import CategoryProductsModal from "@/Components/CategoryProductsModal.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    categoria: Object,
    productos: Object,
    productosCatalogo: Array,
    rol: String,
});

const isOwnerOrSeller = computed(() => {
    const rol = (props.rol || "").toLowerCase();
    return rol === "propietario" || rol === "vendedor";
});

const isOwner = computed(
    () => (props.rol || "").toLowerCase() === "propietario",
);

const isClient = computed(() => (props.rol || "").toLowerCase() === "cliente");

const currentLayout = computed(() =>
    isOwnerOrSeller.value ? AppLayout : PublicStoreLayout,
);

const layoutProps = computed(() =>
    isOwnerOrSeller.value
        ? { title: `Categoría: ${props.categoria.nombre}` }
        : {},
);

const showProductsModal = ref(false);
const showCantidadModal = ref(false);
const productoSeleccionado = ref(null);
const varianteItems = ref([]);

const getImageUrl = (producto) =>
    producto?.imagen
        ? `/storage/${producto.imagen}`
        : producto?.imagenes && producto.imagenes.length > 0
          ? `/storage/${producto.imagenes[0].url}`
          : "/images/no-image.png";

const getPrimaryVariant = (producto) => producto?.variantes?.[0] || null;
const getVariantStockTotal = (producto) =>
    (producto?.variantes || []).reduce(
        (total, variant) => total + (Number(variant.stock_actual) || 0),
        0,
    );
const getVariantStockMinimumTotal = (producto) =>
    (producto?.variantes || []).reduce(
        (total, variant) => total + (Number(variant.stock_minimo) || 0),
        0,
    );
const getDisplayPrice = (producto) =>
    getPrimaryVariant(producto)?.precio_venta ??
    producto?.precio_venta_base ??
    0;
const getDisplayStock = (producto) => getVariantStockTotal(producto);
const getDisplayStockMin = (producto) => getVariantStockMinimumTotal(producto);
const formatPrice = (price) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(Number(price || 0));

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

const cerrarModalCantidad = () => {
    showCantidadModal.value = false;
    productoSeleccionado.value = null;
    varianteItems.value = [];
};

const removeProductFromCategory = (producto) => {
    if (!window.confirm(`¿Quitar ${producto.nombre} de esta categoría?`)) {
        return;
    }

    router.delete(
        route("categorias.productos.destroy", [
            props.categoria.id,
            producto.id,
        ]),
        {
            preserveScroll: true,
        },
    );
};

const addProductToCart = (producto) => {
    if (getDisplayStock(producto) <= 0) {
        return;
    }

    abrirModalCantidad(producto);
};

const confirmarAgregarAlCarrito = () => {
    if (!productoSeleccionado.value) {
        return;
    }

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
        { items },
        {
            preserveScroll: true,
            onSuccess: () => {
                cerrarModalCantidad();
            },
        },
    );
};

const updateVarianteCantidad = (id, value) => {
    const item = varianteItems.value.find((variant) => variant.id === id);
    if (!item) {
        return;
    }

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

const openProductsModal = () => {
    showProductsModal.value = true;
};

const closeProductsModal = () => {
    showProductsModal.value = false;
};
</script>

<template>
    <Head :title="categoria.nombre" />

    <component :is="currentLayout" v-bind="layoutProps">
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
                            Detalle de categoría
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            {{ categoria.nombre }}
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            {{ categoria.descripcion || "Sin descripción" }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="route('categorias.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <i class="bi bi-arrow-left"></i>
                            Volver
                        </Link>
                    </div>
                </div>

                <section
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                            >
                                Resumen
                            </p>
                            <div class="mt-2 flex items-center gap-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"
                                >
                                    {{ categoria.productos_count }} producto{{
                                        categoria.productos_count !== 1
                                            ? "s"
                                            : ""
                                    }}
                                </span>
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500"
                                >
                                    {{ productos.data.length }} visibles
                                </span>
                            </div>
                        </div>

                        <button
                            v-if="isOwner"
                            type="button"
                            @click="openProductsModal"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                        >
                            <i class="bi bi-plus-circle"></i>
                            Agregar producto
                        </button>
                    </div>

                    <template v-if="productos.data.length > 0">
                        <template v-if="isOwner">
                            <div
                                class="mt-8 overflow-hidden rounded-[1.5rem] border border-slate-100"
                            >
                                <table
                                    class="min-w-full divide-y divide-slate-200 bg-white"
                                >
                                    <thead class="bg-slate-50">
                                        <tr
                                            class="text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500"
                                        >
                                            <th class="px-4 py-3">Producto</th>
                                            <th class="px-4 py-3">Código</th>
                                            <th class="px-4 py-3">Precio</th>
                                            <th class="px-4 py-3">Stock</th>
                                            <th class="px-4 py-3 text-right">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr
                                            v-for="producto in productos.data"
                                            :key="producto.id"
                                            class="hover:bg-slate-50/70"
                                        >
                                            <td class="px-4 py-4">
                                                <div
                                                    class="flex items-center gap-3"
                                                >
                                                    <div
                                                        class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100"
                                                    >
                                                        <img
                                                            v-if="
                                                                producto
                                                                    .imagenes
                                                                    ?.length > 0
                                                            "
                                                            :src="`/storage/${producto.imagenes[0].url}`"
                                                            :alt="
                                                                producto.nombre
                                                            "
                                                            class="h-full w-full object-cover"
                                                        />
                                                        <i
                                                            v-else
                                                            class="bi bi-box text-xl text-slate-300"
                                                        ></i>
                                                    </div>
                                                    <div>
                                                        <p
                                                            class="font-semibold text-slate-900"
                                                        >
                                                            {{
                                                                producto.nombre
                                                            }}
                                                        </p>
                                                        <p
                                                            class="text-sm text-slate-500"
                                                        >
                                                            {{
                                                                producto.marca ||
                                                                "Sin marca"
                                                            }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td
                                                class="px-4 py-4 text-sm text-slate-600"
                                            >
                                                <code
                                                    class="rounded-lg bg-slate-100 px-2 py-1"
                                                >
                                                    {{ producto.codigo }}
                                                </code>
                                            </td>
                                            <td
                                                class="px-4 py-4 text-sm font-semibold text-emerald-700"
                                            >
                                                {{ producto.precio_venta }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-sm text-slate-600"
                                            >
                                                {{ producto.stock_actual }}
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <button
                                                    type="button"
                                                    @click="
                                                        removeProductFromCategory(
                                                            producto,
                                                        )
                                                    "
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-rose-200 bg-white text-rose-600 transition hover:bg-rose-50"
                                                    title="Quitar de la categoría"
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
                                                        <path
                                                            d="M19 6l-1 14H6L5 6"
                                                        />
                                                        <path d="M10 11v6" />
                                                        <path d="M14 11v6" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div
                                v-if="productos.links.length > 3"
                                class="mt-8 border-t border-slate-100 pt-5"
                            >
                                <nav class="flex justify-center">
                                    <div
                                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white p-2 shadow-sm"
                                    >
                                        <template
                                            v-for="(
                                                link, index
                                            ) in productos.links"
                                            :key="index"
                                        >
                                            <Link
                                                v-if="link.url"
                                                :href="link.url"
                                                v-html="link.label"
                                                class="rounded-full px-3 py-2 text-sm font-medium transition"
                                                :class="
                                                    link.active
                                                        ? 'bg-emerald-600 text-white'
                                                        : 'text-slate-600 hover:bg-slate-50'
                                                "
                                            />
                                            <span
                                                v-else
                                                v-html="link.label"
                                                class="rounded-full px-3 py-2 text-sm font-medium text-slate-300"
                                            ></span>
                                        </template>
                                    </div>
                                </nav>
                            </div>
                        </template>

                        <template v-else>
                            <div
                                class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-4"
                            >
                                <article
                                    v-for="producto in productos.data"
                                    :key="producto.id"
                                    class="group overflow-hidden rounded-[2rem] border border-white bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-emerald-100/60"
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
                                                    : getDisplayStock(
                                                            producto,
                                                        ) > 0
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
                                                            getDisplayPrice(
                                                                producto,
                                                            ),
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
                                                    {{
                                                        getDisplayStock(
                                                            producto,
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="mt-5 grid grid-cols-2 gap-2"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'productos.show',
                                                        producto.id,
                                                    )
                                                "
                                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-50"
                                            >
                                                <i class="bi bi-eye"></i>
                                                Ver detalles
                                            </Link>
                                            <button
                                                type="button"
                                                @click="
                                                    addProductToCart(producto)
                                                "
                                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none"
                                                :disabled="
                                                    getDisplayStock(
                                                        producto,
                                                    ) === 0
                                                "
                                            >
                                                <i
                                                    class="bi bi-cart-plus-fill"
                                                ></i>
                                                <span class="sr-only"
                                                    >Agregar al carrito</span
                                                >
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            </div>

                            <div
                                v-if="productos.links.length > 3"
                                class="mt-8 border-t border-slate-100 pt-5"
                            >
                                <nav class="flex justify-center">
                                    <div
                                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white p-2 shadow-sm"
                                    >
                                        <template
                                            v-for="(
                                                link, index
                                            ) in productos.links"
                                            :key="index"
                                        >
                                            <Link
                                                v-if="link.url"
                                                :href="link.url"
                                                v-html="link.label"
                                                class="rounded-full px-3 py-2 text-sm font-medium transition"
                                                :class="
                                                    link.active
                                                        ? 'bg-emerald-600 text-white'
                                                        : 'text-slate-600 hover:bg-slate-50'
                                                "
                                            />
                                            <span
                                                v-else
                                                v-html="link.label"
                                                class="rounded-full px-3 py-2 text-sm font-medium text-slate-300"
                                            ></span>
                                        </template>
                                    </div>
                                </nav>
                            </div>
                        </template>
                    </template>

                    <div
                        v-else
                        class="mt-8 rounded-[1.75rem] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-slate-500"
                    >
                        <i class="bi bi-inbox text-3xl text-emerald-500"></i>
                        <p class="mt-3 font-semibold text-slate-800">
                            No hay productos en esta categoría
                        </p>
                    </div>
                </section>
            </div>

            <CategoryProductsModal
                :show="showProductsModal"
                :categoria="categoria"
                :productos="productosCatalogo"
                @close="closeProductsModal"
            />

            <ConfirmationModal
                :show="showCantidadModal"
                @close="cerrarModalCantidad"
                max-width="lg"
            >
                <template #title>Agregar al carrito</template>
                <template #content>
                    <div class="space-y-4">
                        <div
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <p class="text-sm font-semibold text-slate-900">
                                {{ productoSeleccionado?.nombre }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                Selecciona la cantidad para cada variante
                                disponible.
                            </p>
                        </div>

                        <div v-if="varianteItems.length" class="space-y-3">
                            <div
                                v-for="variant in varianteItems"
                                :key="variant.id"
                                class="rounded-2xl border border-slate-200 p-4"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            {{ variant.talla || "Sin talla" }} /
                                            {{ variant.color || "Sin color" }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{
                                                formatPrice(
                                                    variant.precio_venta,
                                                )
                                            }}
                                            c/u
                                        </p>
                                    </div>
                                    <div class="w-28">
                                        <label
                                            class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                                        >
                                            Cantidad
                                        </label>
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
                                </div>
                                <p class="mt-2 text-xs text-slate-500">
                                    Stock disponible: {{ variant.stock_actual }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-else
                            class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center text-sm text-slate-500"
                        >
                            No hay variantes activas disponibles para este
                            producto.
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <div
                                class="flex items-center justify-between text-sm text-slate-600"
                            >
                                <span>Items seleccionados:</span>
                                <span class="font-semibold text-slate-900">
                                    {{ totalSeleccionado }}
                                </span>
                            </div>
                            <div
                                class="mt-2 flex items-center justify-between text-sm text-slate-600"
                            >
                                <span>Total:</span>
                                <span
                                    class="text-base font-black text-emerald-600"
                                >
                                    {{ formatPrice(totalMontoSeleccionado) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
                <template #footer>
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                        @click="cerrarModalCantidad"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                        :disabled="totalSeleccionado < 1"
                        @click="confirmarAgregarAlCarrito"
                    >
                        Agregar al carrito
                    </button>
                </template>
            </ConfirmationModal>
        </div>
    </component>
</template>
