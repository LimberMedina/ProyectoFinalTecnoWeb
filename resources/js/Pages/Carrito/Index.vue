<script setup>
import { computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    carrito: Object,
    detalles: Array,
    total: Number,
});

const hasItems = computed(() => props.detalles && props.detalles.length > 0);
const totalItems = computed(() =>
    (props.detalles || []).reduce(
        (sum, item) => sum + Number(item.cantidad || 0),
        0,
    ),
);

const formatMoney = (amount) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(Number(amount || 0));

const imageUrl = (producto) =>
    producto?.imagen
        ? `/storage/${producto.imagen}`
        : producto?.imagenes && producto.imagenes.length > 0
          ? `/storage/${producto.imagenes[0].url}`
          : "/images/no-image.png";

const varianteStock = (item) => Number(item?.variante?.stock_actual || 0);

const calcularSubtotal = () =>
    props.detalles.reduce((sum, item) => sum + Number(item.subtotal || 0), 0);

const calcularDescuentoTotal = () =>
    props.detalles.reduce(
        (sum, item) => sum + Number(item.descuento_monto || 0) * item.cantidad,
        0,
    );

const updateQuantity = (item, quantity) => {
    const nuevaCantidad = parseInt(quantity, 10);
    if (!nuevaCantidad || nuevaCantidad < 1) return;

    router.put(route("carritos.update", item.id), {
        cantidad: nuevaCantidad,
        producto_variante_id: item.variante?.id,
    });
};

const updateVariant = (item, varianteId) => {
    const varianteNumerica = parseInt(varianteId, 10);
    if (!varianteNumerica) return;

    router.put(route("carritos.update", item.id), {
        cantidad: item.cantidad,
        producto_variante_id: varianteNumerica,
    });
};

const eliminarItem = (itemId) => {
    router.delete(route("carritos.destroy", itemId));
};

const vaciarCarrito = () => {
    if (!confirm("¿Deseas vaciar todo el carrito?")) return;
    router.delete(route("carritos.destroy", "vaciar-todo"));
};

const irAProductos = () => {
    router.visit(route("tienda.productos"));
};

const irACheckout = () => {
    router.visit(route("pedidos.checkout"));
};
</script>

<template>
    <Head title="Mi Carrito" />

    <PublicStoreLayout>
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
                        Carrito
                    </div>
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <h1
                                class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                            >
                                Mi carrito de compras
                            </h1>
                            <p
                                class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                            >
                                Revisa tus productos, ajusta cantidades y
                                continúa al checkout con una interfaz clara y
                                consistente.
                            </p>
                        </div>
                        <Link
                            :href="route('tienda.productos')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50"
                        >
                            <i class="bi bi-arrow-left"></i>
                            Seguir comprando
                        </Link>
                    </div>
                </div>

                <div
                    v-if="!hasItems"
                    class="rounded-[2rem] border border-dashed border-slate-200 bg-white p-10 text-center shadow-[0_18px_60px_-30px_rgba(15,23,42,0.18)]"
                >
                    <div
                        class="mx-auto flex h-20 w-20 items-center justify-center rounded-[1.5rem] bg-emerald-100 text-emerald-700"
                    >
                        <i class="bi bi-cart-x text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-2xl font-black text-slate-900">
                        Tu carrito está vacío
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Agrega productos para comenzar tu compra.
                    </p>
                    <button
                        type="button"
                        @click="irAProductos"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                    >
                        <i class="bi bi-bag"></i>
                        Ver productos
                    </button>
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_360px]"
                >
                    <section class="space-y-5">
                        <article
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div
                                class="mb-5 flex items-center justify-between gap-4 border-b border-slate-200 pb-4"
                            >
                                <div>
                                    <h2
                                        class="text-xl font-black text-slate-900"
                                    >
                                        Productos
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ totalItems }} artículos en tu carrito
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="vaciarCarrito"
                                    class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
                                >
                                    <i class="bi bi-trash3"></i>
                                    Vaciar carrito
                                </button>
                            </div>

                            <div class="space-y-4">
                                <article
                                    v-for="item in detalles"
                                    :key="item.id"
                                    class="rounded-[1.5rem] border border-slate-100 bg-slate-50/70 p-4 transition hover:border-emerald-200 hover:bg-white"
                                >
                                    <div
                                        class="grid grid-cols-1 gap-4 md:grid-cols-[120px_minmax(0,1fr)_180px] md:items-center"
                                    >
                                        <div
                                            class="overflow-hidden rounded-2xl bg-white shadow-sm"
                                        >
                                            <img
                                                :src="imageUrl(item.producto)"
                                                :alt="item.producto.nombre"
                                                class="h-28 w-full object-cover"
                                                loading="lazy"
                                            />
                                        </div>

                                        <div>
                                            <div
                                                class="mb-2 flex flex-wrap items-center gap-2"
                                            >
                                                <span
                                                    v-if="
                                                        item.producto.categoria
                                                    "
                                                    class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"
                                                    >{{
                                                        item.producto.categoria
                                                            .nombre
                                                    }}</span
                                                >
                                                <span
                                                    class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"
                                                    >Stock:
                                                    {{
                                                        varianteStock(item)
                                                    }}</span
                                                >
                                            </div>
                                            <h3
                                                class="text-lg font-bold text-slate-900"
                                            >
                                                {{ item.producto.nombre }}
                                            </h3>
                                            <p
                                                class="mt-2 text-sm font-semibold text-slate-700"
                                            >
                                                Variante:
                                                {{
                                                    item.variante?.talla || "-"
                                                }}
                                                /
                                                {{
                                                    item.variante?.color || "-"
                                                }}
                                            </p>
                                            <div class="mt-2">
                                                <label
                                                    class="block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500"
                                                >
                                                    Cambiar talla/color
                                                </label>
                                                <select
                                                    :value="item.variante?.id"
                                                    @change="
                                                        updateVariant(
                                                            item,
                                                            $event.target.value,
                                                        )
                                                    "
                                                    class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700"
                                                >
                                                    <option
                                                        v-for="variant in item.variantes_disponibles"
                                                        :key="variant.id"
                                                        :value="variant.id"
                                                    >
                                                        {{ variant.talla }} /
                                                        {{ variant.color }} ·
                                                        Stock
                                                        {{
                                                            variant.stock_actual
                                                        }}
                                                    </option>
                                                </select>
                                            </div>
                                            <p
                                                class="mt-2 text-sm leading-6 text-slate-500"
                                            >
                                                Cantidad: {{ item.cantidad }} ·
                                                Precio unitario:
                                                <span
                                                    class="font-semibold text-slate-800"
                                                >
                                                    {{
                                                        formatMoney(
                                                            item.precio_con_descuento ||
                                                                item.precio_unitario,
                                                        )
                                                    }}
                                                </span>
                                                <span
                                                    v-if="
                                                        item.descuento_porcentaje >
                                                        0
                                                    "
                                                    class="ml-2 text-xs font-semibold text-emerald-600"
                                                >
                                                    con
                                                    {{
                                                        item.descuento_porcentaje
                                                    }}% de descuento
                                                </span>
                                            </p>
                                            <div
                                                v-if="
                                                    item.descuento_porcentaje >
                                                    0
                                                "
                                                class="mt-3 inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700"
                                            >
                                                <i class="bi bi-tag"></i>
                                                Descuento
                                                {{ item.descuento_porcentaje }}%
                                            </div>
                                        </div>

                                        <div
                                            class="flex flex-col items-start gap-3 md:items-end"
                                        >
                                            <div
                                                class="w-full rounded-2xl bg-white p-3 text-right shadow-sm"
                                            >
                                                <p
                                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                                >
                                                    Subtotal
                                                </p>
                                                <p
                                                    class="mt-1 text-2xl font-black text-emerald-700"
                                                >
                                                    {{
                                                        formatMoney(
                                                            item.subtotal,
                                                        )
                                                    }}
                                                </p>
                                            </div>

                                            <div
                                                class="flex w-full items-center justify-between gap-2 md:justify-end"
                                            >
                                                <div
                                                    class="inline-flex items-center rounded-2xl border border-slate-200 bg-white p-1 shadow-sm"
                                                >
                                                    <button
                                                        type="button"
                                                        class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-300"
                                                        @click="
                                                            updateQuantity(
                                                                item,
                                                                item.cantidad -
                                                                    1,
                                                            )
                                                        "
                                                        :disabled="
                                                            item.cantidad <= 1
                                                        "
                                                    >
                                                        <i
                                                            class="bi bi-dash-lg"
                                                        ></i>
                                                    </button>
                                                    <input
                                                        type="number"
                                                        :value="item.cantidad"
                                                        min="1"
                                                        :max="
                                                            varianteStock(item)
                                                        "
                                                        @change="
                                                            updateQuantity(
                                                                item,
                                                                $event.target
                                                                    .value,
                                                            )
                                                        "
                                                        class="w-16 border-0 bg-transparent text-center text-sm font-semibold text-slate-900 outline-none focus:ring-0"
                                                    />
                                                    <button
                                                        type="button"
                                                        class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-300"
                                                        @click="
                                                            updateQuantity(
                                                                item,
                                                                item.cantidad +
                                                                    1,
                                                            )
                                                        "
                                                        :disabled="
                                                            item.cantidad >=
                                                            varianteStock(item)
                                                        "
                                                    >
                                                        <i
                                                            class="bi bi-plus-lg"
                                                        ></i>
                                                    </button>
                                                </div>

                                                <button
                                                    type="button"
                                                    @click="
                                                        eliminarItem(item.id)
                                                    "
                                                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-700 transition hover:bg-rose-100"
                                                >
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </article>
                    </section>

                    <aside class="lg:sticky lg:top-24 h-fit">
                        <div
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div class="mb-5 border-b border-slate-200 pb-4">
                                <h2 class="text-xl font-black text-slate-900">
                                    Resumen del pedido
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Finaliza tu compra cuando estés listo.
                                </p>
                            </div>

                            <div class="space-y-3 text-sm text-slate-600">
                                <div class="flex items-center justify-between">
                                    <span>Productos</span>
                                    <span
                                        class="font-semibold text-slate-900"
                                        >{{ totalItems }}</span
                                    >
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Subtotal</span>
                                    <span
                                        class="font-semibold text-slate-900"
                                        >{{
                                            formatMoney(calcularSubtotal())
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="calcularDescuentoTotal() > 0"
                                    class="flex items-center justify-between text-rose-700"
                                >
                                    <span>Descuentos</span>
                                    <span class="font-semibold"
                                        >-{{
                                            formatMoney(
                                                calcularDescuentoTotal(),
                                            )
                                        }}</span
                                    >
                                </div>
                            </div>

                            <div class="my-5 border-t border-slate-200"></div>

                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-slate-900"
                                    >Total</span
                                >
                                <span
                                    class="text-2xl font-black text-emerald-700"
                                    >{{ formatMoney(total) }}</span
                                >
                            </div>

                            <button
                                type="button"
                                @click="irACheckout"
                                class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                            >
                                <i class="bi bi-credit-card"></i>
                                Continuar al pago
                            </button>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </PublicStoreLayout>
</template>
