<script setup>
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    producto: Object,
    rol: String,
});

const page = usePage();
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));
const userRole = computed(() => page.props.auth?.user?.role || {});
const isOwnerOrSeller = computed(() => {
    const roleId = String(userRole.value.id || "");
    const roleName = (userRole.value.nombre || "").toLowerCase();

    return (
        ["1", "2"].includes(roleId) ||
        ["propietario", "vendedor"].includes(roleName)
    );
});
const currentLayout = computed(() =>
    isOwnerOrSeller.value ? AppLayout : PublicStoreLayout,
);
const layoutProps = computed(() =>
    isOwnerOrSeller.value ? { title: "Detalle del Producto" } : {},
);

const currentImageIndex = ref(0);
const showCantidadModal = ref(false);
const cantidadAgregar = ref(1);

const isCliente = computed(() => (props.rol || "").toLowerCase() === "cliente");

const getImageUrl = (url) => (url ? `/storage/${url}` : "/images/no-image.png");
const getQrUrl = (url) => (url ? `/storage/${url}` : null);

const imagenes = computed(() =>
    props.producto.imagen
        ? [{ url: props.producto.imagen }]
        : props.producto.imagenes && props.producto.imagenes.length > 0
          ? props.producto.imagenes
          : [{ url: null }],
);

const qrUrl = computed(() => getQrUrl(props.producto.qr));
const primaryVariant = computed(() => props.producto.variantes?.[0] || null);
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

const formatPrice = (price) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(price || 0);

const displayPrice = computed(
    () =>
        primaryVariant.value?.precio_venta ??
        props.producto.precio_venta_base ??
        0,
);
const displayMayorista = computed(
    () =>
        primaryVariant.value?.precio_venta_mayorista ??
        props.producto.precio_venta_mayorista_base ??
        null,
);
const displayStock = computed(() => getVariantStockTotal(props.producto));
const displayStockMin = computed(() =>
    getVariantStockMinimumTotal(props.producto),
);

const goToImage = (index) => (currentImageIndex.value = index);
const nextImage = () => {
    currentImageIndex.value =
        (currentImageIndex.value + 1) % imagenes.value.length;
};
const prevImage = () => {
    currentImageIndex.value =
        (currentImageIndex.value - 1 + imagenes.value.length) %
        imagenes.value.length;
};

const abrirModalCantidad = () => {
    cantidadAgregar.value = 1;
    showCantidadModal.value = true;
};

const abrirCarrito = () => {
    if (!isAuthenticated.value) {
        router.visit(route("login"));
        return;
    }
    abrirModalCantidad();
};

const agregarAlCarrito = () => {
    router.post(
        route("carritos.store"),
        { producto_id: props.producto.id, cantidad: cantidadAgregar.value },
        {
            preserveScroll: true,
            onSuccess: () => (showCantidadModal.value = false),
        },
    );
};
</script>

<template>
    <Head :title="producto.nombre" />

    <component :is="currentLayout" v-bind="layoutProps">
        <FlashNotification />
        <div class="max-w-5xl mx-auto px-4 py-8">
            <!-- Encabezado -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold">Detalle del Producto</h2>
                <Link
                    :href="
                        isCliente
                            ? route('tienda.productos')
                            : route('productos.index')
                    "
                    class="inline-flex items-center gap-2 text-sm text-slate-700 hover:text-slate-900"
                >
                    <svg
                        class="w-4 h-4"
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
                    Volver
                </Link>
            </div>

            <!-- Detalle -->
            <div class="bg-white border rounded-lg shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Galería de Imágenes -->
                    <div>
                        <div class="relative mb-4">
                            <div
                                class="overflow-hidden rounded-lg bg-slate-50 h-80 flex items-center justify-center"
                            >
                                <img
                                    :src="
                                        getImageUrl(
                                            imagenes[currentImageIndex].url,
                                        )
                                    "
                                    :alt="producto.nombre"
                                    class="max-h-72 object-contain p-4"
                                />
                            </div>
                        </div>

                        <div
                            v-if="imagenes.length > 1"
                            class="flex gap-2 flex-wrap"
                        >
                            <img
                                v-for="(img, index) in imagenes"
                                :key="index"
                                :src="getImageUrl(img.url)"
                                :alt="`${producto.nombre} ${index + 1}`"
                                class="w-16 h-16 object-cover rounded-md cursor-pointer border"
                                :class="
                                    index === currentImageIndex
                                        ? 'ring-2 ring-emerald-500'
                                        : ''
                                "
                                @click="goToImage(index)"
                            />
                        </div>
                    </div>

                    <!-- Información -->
                    <div>
                        <div class="mb-4">
                            <span
                                class="inline-block bg-slate-100 text-slate-700 px-2 py-1 rounded-md text-xs"
                                >{{
                                    producto.categoria?.nombre ||
                                    "Sin categoría"
                                }}</span
                            >
                            <span
                                v-if="producto.estado"
                                class="ml-2 inline-block bg-green-100 text-green-700 px-2 py-1 rounded-md text-xs"
                                >Activo</span
                            >
                            <span
                                v-else
                                class="ml-2 inline-block bg-red-100 text-red-700 px-2 py-1 rounded-md text-xs"
                                >Inactivo</span
                            >
                            <h3 class="text-xl font-semibold mt-3">
                                {{ producto.nombre }}
                            </h3>
                            <p class="text-sm text-slate-500">
                                Código:
                                <code class="bg-slate-50 px-1 rounded">{{
                                    producto.codigo
                                }}</code>
                                <span v-if="producto.marca" class="ml-3"
                                    >Marca:
                                    <strong>{{ producto.marca }}</strong></span
                                >
                            </p>
                        </div>

                        <div
                            v-if="qrUrl"
                            class="mb-4 rounded-lg border bg-slate-50 p-3"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2"
                            >
                                QR del producto
                            </p>
                            <img
                                :src="qrUrl"
                                alt="QR del producto"
                                class="h-40 w-40 rounded-lg border border-slate-200 bg-white object-contain p-2"
                            />
                        </div>

                        <!-- Precios -->
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="border rounded p-3 text-center">
                                <small class="text-xs text-slate-500 block"
                                    >Venta base</small
                                >
                                <strong class="text-slate-800">
                                    {{ formatPrice(displayPrice) }}
                                </strong>
                            </div>
                            <div
                                class="border rounded p-3 text-center bg-slate-50"
                            >
                                <small class="text-xs text-slate-500 block"
                                    >Venta</small
                                >
                                <strong class="text-2xl text-emerald-600"
                                    >Bs. {{ formatPrice(displayPrice) }}</strong
                                >
                            </div>
                            <div class="border rounded p-3 text-center">
                                <small class="text-xs text-slate-500 block"
                                    >Mayorista</small
                                >
                                <strong class="text-slate-800">
                                    {{
                                        displayMayorista
                                            ? formatPrice(displayMayorista)
                                            : "--"
                                    }}
                                </strong>
                            </div>
                        </div>

                        <!-- Stock -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="border rounded p-3">
                                <small class="text-xs text-slate-500 block mb-1"
                                    >Stock Actual</small
                                >
                                <h4 class="text-lg font-bold">
                                    {{ displayStock }} unidades
                                </h4>
                            </div>
                            <div class="border rounded p-3">
                                <small class="text-xs text-slate-500 block mb-1"
                                    >Stock Mínimo</small
                                >
                                <h4 class="text-lg text-slate-600">
                                    {{ displayStockMin }} unidades
                                </h4>
                            </div>
                        </div>

                        <div
                            v-if="producto.variantes?.length"
                            class="mb-4 rounded-lg border bg-white p-3"
                        >
                            <p
                                class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500"
                            >
                                Variantes del producto
                            </p>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-slate-50 text-slate-600">
                                        <tr>
                                            <th class="px-3 py-2 text-left">
                                                Talla
                                            </th>
                                            <th class="px-3 py-2 text-left">
                                                Color
                                            </th>
                                            <th class="px-3 py-2 text-left">
                                                SKU
                                            </th>
                                            <th class="px-3 py-2 text-left">
                                                Venta
                                            </th>
                                            <th class="px-3 py-2 text-left">
                                                Stock
                                            </th>
                                            <th class="px-3 py-2 text-left">
                                                Estado
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="variant in producto.variantes"
                                            :key="variant.id"
                                            class="border-t"
                                        >
                                            <td class="px-3 py-2 font-semibold">
                                                {{ variant.talla }}
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ variant.color }}
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ variant.sku || "-" }}
                                            </td>
                                            <td class="px-3 py-2">
                                                {{
                                                    formatPrice(
                                                        variant.precio_venta,
                                                    )
                                                }}
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ variant.stock_actual }}
                                            </td>
                                            <td class="px-3 py-2">
                                                {{ variant.estado }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex gap-3">
                            <button
                                v-if="isCliente"
                                type="button"
                                class="inline-flex items-center gap-2 rounded-md bg-emerald-600 text-white px-4 py-2"
                                @click="abrirModalCantidad"
                                :disabled="displayStock === 0"
                            >
                                {{
                                    displayStock === 0
                                        ? "Sin Stock"
                                        : "Agregar al Carrito"
                                }}
                            </button>

                            <Link
                                v-if="
                                    $page.props.auth.permissions?.productos
                                        ?.update
                                "
                                :href="route('productos.edit', producto.id)"
                                class="inline-flex items-center gap-2 rounded-md bg-slate-800 text-white px-4 py-2"
                                >Editar</Link
                            >

                            <Link
                                :href="
                                    isCliente
                                        ? route('tienda.productos')
                                        : route('productos.index')
                                "
                                class="inline-flex items-center gap-2 text-slate-700 hover:text-slate-900"
                                >Volver al Listado</Link
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para Agregar al Carrito (solo clientes) -->
            <ConfirmationModal
                :show="showCantidadModal"
                @close="showCantidadModal = false"
                max-width="sm"
            >
                <template #title>Agregar al Carrito</template>
                <template #content>
                    <div>
                        <p class="mb-3">
                            ¿Cuántas unidades de
                            <strong>{{ producto.nombre }}</strong> deseas
                            agregar?
                        </p>
                        <div class="mb-3">
                            <label class="block text-sm font-semibold mb-1"
                                >Cantidad</label
                            >
                            <input
                                type="number"
                                v-model.number="cantidadAgregar"
                                min="1"
                                :max="displayStock"
                                class="w-full rounded-md border px-3 py-2"
                            />
                            <small class="text-slate-500"
                                >Stock disponible:
                                {{ displayStock }} unidades</small
                            >
                        </div>
                        <div class="bg-slate-50 p-3 rounded">
                            <strong>Precio unitario:</strong>
                            {{ formatPrice(displayPrice) }}<br />
                            <strong>Total:</strong>
                            {{ formatPrice(displayPrice * cantidadAgregar) }}
                        </div>
                    </div>
                </template>
                <template #footer>
                    <button
                        type="button"
                        class="rounded-md px-4 py-2 border"
                        @click="showCantidadModal = false"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="rounded-md px-4 py-2 bg-emerald-600 text-white"
                        @click="agregarAlCarrito"
                        :disabled="
                            cantidadAgregar < 1 ||
                            cantidadAgregar > displayStock
                        "
                    >
                        Agregar al carrito
                    </button>
                </template>
            </ConfirmationModal>
        </div>
    </component>
</template>
