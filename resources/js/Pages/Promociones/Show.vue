<script setup>
import { computed, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    promocion: Object,
});

const page = usePage();
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));

const showCantidadModal = ref(false);
const productoSeleccionado = ref(null);
const varianteItems = ref([]);

const formatDate = (date) => {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("es-BO");
};

const formatPrice = (price) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(price || 0);

const isActive = computed(() => {
    const now = new Date();
    const inicio = new Date(props.promocion.fecha_inicio);
    const fin = new Date(props.promocion.fecha_fin);
    return now >= inicio && now <= fin && props.promocion.estado;
});

const getImageUrl = (producto) =>
    producto.imagen
        ? `/storage/${producto.imagen}`
        : producto.imagenes && producto.imagenes.length > 0
          ? `/storage/${producto.imagenes[0].url}`
          : "/images/no-image.png";

const getPrimaryVariant = (producto) => producto.variantes?.[0] || null;
const getVariantStockTotal = (producto) => {
    const total = (producto.variantes || []).reduce(
        (sum, variant) => sum + (Number(variant.stock_actual) || 0),
        0,
    );
    return total > 0 ? total : Number(producto.stock_actual || 0);
};
const getVariantStockMinimumTotal = (producto) => {
    const total = (producto.variantes || []).reduce(
        (sum, variant) => sum + (Number(variant.stock_minimo) || 0),
        0,
    );
    return total > 0 ? total : Number(producto.stock_minimo || 0);
};
const getDisplayPrice = (producto) =>
    getPrimaryVariant(producto)?.precio_venta ??
    producto.precio_venta_base ??
    Number(producto.precio_venta || 0);
const getDisplayStock = (producto) => getVariantStockTotal(producto);
const getDisplayStockMin = (producto) => getVariantStockMinimumTotal(producto);

const getDiscountedPrice = (producto) => {
    const discount = Number(props.promocion.valor_descuento_decimal || 0);
    const price = Number(getDisplayPrice(producto) || 0);
    return price - price * (discount / 100);
};

const promocionProducts = computed(() => {
    const directProducts = props.promocion.productos || [];
    const categoryProducts = (props.promocion.categorias || []).flatMap(
        (categoria) => categoria.productos || [],
    );
    const uniqueProducts = [];
    const seen = new Set();

    [...directProducts, ...categoryProducts].forEach((producto) => {
        if (!producto || !producto.id) return;
        if (!seen.has(producto.id)) {
            seen.add(producto.id);
            uniqueProducts.push(producto);
        }
    });

    return uniqueProducts;
});

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

const getDiscountedVariantPrice = (variant) => {
    const discount = Number(props.promocion.valor_descuento_decimal || 0);
    const price = Number(variant.precio_venta || 0);
    return price - price * (discount / 100);
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
            promocion_id: props.promocion.id,
            descuento_porcentaje: Number(
                props.promocion.valor_descuento_decimal || 0,
            ),
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
                getDiscountedVariantPrice(variant),
        0,
    ),
);
</script>

<template>
    <Head :title="promocion.nombre" />

    <PublicStoreLayout>
        <FlashNotification />

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <div class="flex items-center gap-3">
                        <h2
                            class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl"
                        >
                            {{ promocion.nombre }}
                        </h2>
                        <span
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                            :class="
                                isActive
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-slate-100 text-slate-600'
                            "
                        >
                            {{
                                isActive
                                    ? "Promoción Activa"
                                    : "Promoción Inactiva"
                            }}
                        </span>
                    </div>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        {{ promocion.descripcion || "Sin descripción" }}
                    </p>
                </div>

                <Link
                    :href="route('promociones.index')"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 transition hover:text-emerald-700"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                    >
                        <path
                            d="M15 18l-6-6 6-6"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    Volver a promociones
                </Link>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div
                    class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                    >
                        Descuento
                    </p>
                    <p class="mt-2 text-3xl font-black text-emerald-600">
                        {{ promocion.valor_descuento_decimal }}%
                    </p>
                </div>
                <div
                    class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                    >
                        Inicio
                    </p>
                    <p class="mt-2 text-base font-semibold text-slate-900">
                        {{ formatDate(promocion.fecha_inicio) }}
                    </p>
                </div>
                <div
                    class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                    >
                        Fin
                    </p>
                    <p class="mt-2 text-base font-semibold text-slate-900">
                        {{ formatDate(promocion.fecha_fin) }}
                    </p>
                </div>
            </div>

            <section
                class="mt-8 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            Productos en promoción
                        </h3>
                        <p class="text-sm text-slate-500">
                            Las tarjetas mantienen la vista de catálogo y
                            muestran el descuento aplicado.
                        </p>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"
                    >
                        {{ promocionProducts.length || 0 }}
                    </span>
                </div>

                <div
                    v-if="promocionProducts.length > 0"
                    class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <article
                        v-for="producto in promocionProducts"
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
                                class="absolute left-3 top-3 inline-flex rounded-full bg-rose-600 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white shadow-lg"
                            >
                                -{{ promocion.valor_descuento_decimal }}%
                            </div>
                            <div
                                class="absolute right-3 top-3 inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white shadow-lg"
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
                                class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                            >
                                {{
                                    producto.categoria?.nombre ||
                                    "Sin categoría"
                                }}
                            </p>
                            <p
                                class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500"
                            >
                                {{
                                    producto.marca ||
                                    "Producto destacado con descuento especial."
                                }}
                            </p>

                            <div
                                class="mt-4 flex items-end justify-between gap-3"
                            >
                                <div>
                                    <p
                                        class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                    >
                                        Antes
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-slate-400 line-through"
                                    >
                                        {{
                                            formatPrice(
                                                getDisplayPrice(producto),
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="text-xl font-black text-emerald-600"
                                    >
                                        {{
                                            formatPrice(
                                                getDiscountedPrice(producto),
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
                                        route('tienda.producto', producto.id)
                                    "
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-50"
                                >
                                    Ver detalles
                                </Link>
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

                <div
                    v-else
                    class="rounded-[1.75rem] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-slate-500"
                >
                    <p class="font-semibold text-slate-800">
                        No hay productos aplicados en esta promoción
                    </p>
                </div>
            </section>
        </div>

        <ConfirmationModal
            :show="showCantidadModal"
            @close="showCantidadModal = false"
            max-width="sm"
        >
            <template #title>Agregar al carrito</template>
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
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ variant.talla }} / {{ variant.color }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    Stock: {{ variant.stock_actual }} · Precio:
                                    {{
                                        formatPrice(
                                            getDiscountedVariantPrice(variant),
                                        )
                                    }}
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
    </PublicStoreLayout>
</template>
