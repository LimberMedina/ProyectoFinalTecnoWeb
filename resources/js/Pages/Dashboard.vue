<script setup>
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import { computed, ref } from "vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import homeSvg from "./assets/Home.svg";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    rol: String,
    kpis: Object,
    indicadores: Object,
    productos: Object,
    promociones: Array,
    carrito: Object,
    movimientosRecientes: Array,
});

const isCliente = computed(() => props.rol === "cliente");
const dashboardLayout = computed(() =>
    isCliente.value ? PublicStoreLayout : AppLayout,
);
const dashboardLayoutProps = computed(() =>
    isCliente.value ? {} : { title: "Dashboard" },
);

const showCantidadModal = ref(false);
const productoSeleccionado = ref(null);
const cantidadAgregar = ref(1);

const abrirModalCantidad = (producto) => {
    productoSeleccionado.value = producto;
    cantidadAgregar.value = 1;
    showCantidadModal.value = true;
};

const agregarAlCarrito = () => {
    if (!productoSeleccionado.value) return;
    router.post(
        route("carritos.store"),
        {
            producto_id: productoSeleccionado.value.id,
            cantidad: cantidadAgregar.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showCantidadModal.value = false;
                productoSeleccionado.value = null;
            },
        },
    );
};

const calcularPrecioConDescuento = (producto) => {
    if (producto.promociones && producto.promociones.length > 0) {
        const descuento = producto.promociones[0].valor_descuento_decimal || 0;
        return parseFloat(producto.precio_venta) * (1 - descuento / 100);
    }
    return parseFloat(producto.precio_venta);
};

const tienePromocion = (producto) => {
    return (
        producto.promociones &&
        producto.promociones.length > 0 &&
        producto.promociones[0].valor_descuento_decimal > 0
    );
};

const getImageUrl = (producto) => {
    if (producto.imagenes && producto.imagenes.length > 0) {
        return `/storage/${producto.imagenes[0].url}`;
    }
    return "/images/no-image.png";
};

const formatMoney = (amount) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(parseFloat(amount || 0));
</script>

<template>
    <component :is="dashboardLayout" v-bind="dashboardLayoutProps">
        <FlashNotification />

        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-8 flex flex-col gap-3">
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-emerald-700 shadow-sm"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-500"
                        ></span>
                        Dashboard
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            {{
                                isCliente
                                    ? "Bienvenido a tu espacio en Tienda Elena"
                                    : "Panel de control"
                            }}
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            {{
                                isCliente
                                    ? "Este es tu resumen personal: carrito, compras y promociones especiales. Sigue navegando con confianza y conveniencia."
                                    : "Monitorea ventas, promociones y productos con una vista rápida, limpia y predecible."
                            }}
                        </p>
                    </div>
                </div>

                <div v-if="isCliente" class="space-y-8">
                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
                            <div class="rounded-[2rem] bg-emerald-600/5 p-6">
                                <span
                                    class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700"
                                >
                                    Bienvenida a Tienda Elena
                                </span>
                                <h2
                                    class="mt-6 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                                >
                                    Disfruta de una experiencia cómoda y segura
                                </h2>
                                <p
                                    class="mt-4 max-w-xl text-sm leading-6 text-slate-600"
                                >
                                    Aquí encontrarás un resumen de tu carrito,
                                    tus compras y tus beneficios. Todo diseñado
                                    para que sigas con una experiencia fluida y
                                    agradable.
                                </p>
                                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                    <div
                                        class="rounded-3xl bg-white p-4 shadow-sm"
                                    >
                                        <p
                                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                        >
                                            Total en carrito
                                        </p>
                                        <p
                                            class="mt-3 text-2xl font-black text-slate-900"
                                        >
                                            {{ formatMoney(carrito?.total) }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-3xl bg-white p-4 shadow-sm"
                                    >
                                        <p
                                            class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                        >
                                            Productos en carrito
                                        </p>
                                        <p
                                            class="mt-3 text-2xl font-black text-slate-900"
                                        >
                                            {{ carrito?.cantidad_items || 0 }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid place-items-center">
                                <div
                                    class="w-full rounded-[2rem] border border-slate-100 bg-white p-6 shadow-sm"
                                >
                                    <img
                                        :src="homeSvg"
                                        alt="Bienvenida Tienda Elena"
                                        class="mx-auto h-[360px] w-full object-contain"
                                    />
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="promociones && promociones.length > 0"
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center justify-between gap-4"
                        >
                            <div>
                                <h2 class="text-xl font-black text-slate-900">
                                    Promociones para ti
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Descubre las ofertas vigentes y ahorra en tu
                                    próxima compra.
                                </p>
                            </div>
                            <i
                                class="bi bi-lightning-charge-fill text-2xl text-emerald-600"
                            ></i>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <article
                                v-for="promo in promociones"
                                :key="promo.id"
                                class="rounded-3xl border border-rose-100 bg-gradient-to-br from-white to-rose-50 p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div>
                                        <h3
                                            class="text-lg font-bold text-rose-700"
                                        >
                                            {{ promo.nombre }}
                                        </h3>
                                        <p
                                            class="mt-2 text-sm leading-6 text-slate-600"
                                        >
                                            {{ promo.descripcion }}
                                        </p>
                                        <p
                                            class="mt-3 text-sm font-semibold text-slate-700"
                                        >
                                            Descuento: {{ promo.descuento }}%
                                        </p>
                                    </div>
                                    <span
                                        class="inline-flex shrink-0 items-center rounded-full bg-rose-600 px-3 py-1 text-xs font-bold text-white"
                                        >-{{ promo.descuento }}%</span
                                    >
                                </div>
                                <p
                                    class="mt-4 text-xs font-medium uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Válido hasta
                                    {{
                                        new Date(
                                            promo.fecha_fin,
                                        ).toLocaleDateString("es-ES")
                                    }}
                                </p>
                            </article>
                        </div>
                    </section>
                </div>

                <div v-else class="space-y-8">
                    <section
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
                    >
                        <article
                            class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-[0_12px_40px_-24px_rgba(16,185,129,0.45)] transition hover:-translate-y-1"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"
                            >
                                Ventas hoy
                            </p>
                            <p class="mt-2 text-3xl font-black text-slate-900">
                                {{ kpis?.ventas_hoy || 0 }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                movimientos recientes
                            </p>
                        </article>
                        <article
                            class="rounded-[1.75rem] border border-sky-100 bg-white p-5 shadow-[0_12px_40px_-24px_rgba(14,165,233,0.45)] transition hover:-translate-y-1"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"
                            >
                                Ingresos hoy
                            </p>
                            <p class="mt-2 text-3xl font-black text-slate-900">
                                {{ formatMoney(kpis?.ingresos_hoy) }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                resumen diario
                            </p>
                        </article>
                        <article
                            class="rounded-[1.75rem] border border-amber-100 bg-white p-5 shadow-[0_12px_40px_-24px_rgba(245,158,11,0.45)] transition hover:-translate-y-1"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"
                            >
                                Productos activos
                            </p>
                            <p class="mt-2 text-3xl font-black text-slate-900">
                                {{ kpis?.productos_activos || 0 }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                items disponibles
                            </p>
                        </article>
                        <article
                            class="rounded-[1.75rem] border border-violet-100 bg-white p-5 shadow-[0_12px_40px_-24px_rgba(139,92,246,0.40)] transition hover:-translate-y-1"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"
                            >
                                Promociones activas
                            </p>
                            <p class="mt-2 text-3xl font-black text-slate-900">
                                {{ kpis?.promociones_activas || 0 }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">vigentes</p>
                        </article>
                    </section>

                    <section
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
                    >
                        <article
                            class="rounded-[1.75rem] border border-slate-100 bg-white p-5 shadow-[0_12px_40px_-24px_rgba(15,23,42,0.10)] transition hover:-translate-y-1"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"
                            >
                                Ventas esta semana
                            </p>
                            <p class="mt-2 text-3xl font-black text-slate-900">
                                {{ kpis?.ventas_semana || 0 }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                últimos 7 días
                            </p>
                        </article>
                        <article
                            class="rounded-[1.75rem] border border-slate-100 bg-white p-5 shadow-[0_12px_40px_-24px_rgba(15,23,42,0.10)] transition hover:-translate-y-1"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"
                            >
                                Ingresos este mes
                            </p>
                            <p class="mt-2 text-3xl font-black text-slate-900">
                                {{ formatMoney(kpis?.ingresos_mes) }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                importe acumulado
                            </p>
                        </article>
                        <article
                            class="rounded-[1.75rem] border border-slate-100 bg-white p-5 shadow-[0_12px_40px_-24px_rgba(15,23,42,0.10)] transition hover:-translate-y-1"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"
                            >
                                Créditos pendientes
                            </p>
                            <p class="mt-2 text-3xl font-black text-slate-900">
                                {{ kpis?.creditos_pendientes || 0 }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                clientes con saldo activo
                            </p>
                        </article>
                        <article
                            class="rounded-[1.75rem] border border-slate-100 bg-white p-5 shadow-[0_12px_40px_-24px_rgba(15,23,42,0.10)] transition hover:-translate-y-1"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"
                            >
                                Stock crítico
                            </p>
                            <p class="mt-2 text-3xl font-black text-slate-900">
                                {{ kpis?.stock_critico || 0 }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                productos con stock bajo
                            </p>
                        </article>
                    </section>

                    <section
                        v-if="
                            movimientosRecientes &&
                            movimientosRecientes.length > 0
                        "
                        class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center justify-between gap-4"
                        >
                            <div>
                                <h2 class="text-xl font-black text-slate-900">
                                    Movimientos recientes
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Entradas y salidas registradas por el
                                    sistema.
                                </p>
                            </div>
                            <i
                                class="bi bi-box-seam text-2xl text-emerald-600"
                            ></i>
                        </div>

                        <div class="space-y-3">
                            <article
                                v-for="movimiento in movimientosRecientes"
                                :key="movimiento.id"
                                class="flex flex-col gap-2 rounded-3xl border border-slate-100 bg-slate-50/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p class="font-bold text-slate-900">
                                        {{
                                            movimiento.variante?.producto
                                                ?.nombre ||
                                            "Producto sin nombre"
                                        }}
                                        <span class="text-slate-400">·</span>
                                        {{
                                            movimiento.variante?.nombre ||
                                            "Variante"
                                        }}
                                    </p>
                                    <p class="text-sm text-slate-500">
                                        {{ movimiento.motivo }}
                                    </p>
                                </div>
                                <div
                                    class="flex items-center gap-3 text-sm font-semibold"
                                >
                                    <span
                                        class="rounded-full px-3 py-1"
                                        :class="
                                            movimiento.tipo_movimiento ===
                                            'salida'
                                                ? 'bg-rose-100 text-rose-700'
                                                : movimiento.tipo_movimiento ===
                                                    'ingreso'
                                                  ? 'bg-emerald-100 text-emerald-700'
                                                  : 'bg-amber-100 text-amber-700'
                                        "
                                    >
                                        {{ movimiento.tipo_movimiento }}
                                    </span>
                                    <span class="text-slate-700">
                                        {{ movimiento.cantidad }} unid.
                                    </span>
                                    <span class="text-slate-400">
                                        {{
                                            new Date(
                                                movimiento.fecha,
                                            ).toLocaleDateString("es-ES")
                                        }}
                                    </span>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <ConfirmationModal
            :show="showCantidadModal"
            @close="showCantidadModal = false"
            max-width="sm"
        >
            <template #title>Agregar al carrito</template>
            <template #content>
                <div v-if="productoSeleccionado">
                    <p class="mb-3 text-sm text-slate-600">
                        ¿Cuántas unidades de
                        <strong class="text-slate-900">{{
                            productoSeleccionado.nombre
                        }}</strong>
                        deseas agregar?
                    </p>
                    <input
                        v-model.number="cantidadAgregar"
                        type="number"
                        min="1"
                        :max="productoSeleccionado.stock_actual"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                    />
                    <p class="mt-2 text-xs text-slate-500">
                        Stock disponible:
                        {{ productoSeleccionado.stock_actual }}
                    </p>
                </div>
            </template>
            <template #footer>
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    @click="showCantidadModal = false"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    @click="agregarAlCarrito"
                    :disabled="
                        cantidadAgregar < 1 ||
                        cantidadAgregar >
                            (productoSeleccionado?.stock_actual || 1)
                    "
                >
                    Agregar
                </button>
            </template>
        </ConfirmationModal>
    </component>
</template>
