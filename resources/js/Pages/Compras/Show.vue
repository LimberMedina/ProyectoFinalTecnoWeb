<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    compra: Object,
});

const formatDate = (date) => {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("es-BO");
};

const formatPrice = (value) => `Bs. ${Number(value || 0).toFixed(2)}`;

const lineTotal = (detalle) => {
    const precio = Number(detalle.precio || 0);
    const descuento = Number(detalle.descuento || 0);
    const cantidad = Number(detalle.cantidad || 0);
    return Math.max(0, (precio - descuento) * cantidad);
};

const getDetalleVariant = (detalle) => detalle.variante || null;

const getDetalleTitle = (detalle) => {
    const variante = getDetalleVariant(detalle);
    const producto = variante?.producto || detalle.producto;

    if (!variante && !producto) {
        return "Producto no disponible";
    }

    const codigo = producto?.codigo || variante?.sku || "Sin código";
    const nombre = producto?.nombre || "Producto";
    const talla = variante?.talla ? ` · ${variante.talla}` : "";
    const color = variante?.color ? ` / ${variante.color}` : "";

    return `${codigo} - ${nombre}${talla}${color}`;
};
</script>

<template>
    <Head :title="`Compra ${compra.num_compra || `#${compra.id}`}`" />

    <AppLayout :title="`Compra ${compra.num_compra || `#${compra.id}`}`">
        <FlashNotification />

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div
                class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-cyan-200 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-cyan-700 shadow-sm"
                    >
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Detalle de compra
                    </div>
                    <h1
                        class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                    >
                        {{ compra.num_compra || `Compra #${compra.id}` }}
                    </h1>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="route('compras.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </Link>
                    <Link
                        :href="route('compras.edit', compra.id)"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-200 transition hover:bg-cyan-700"
                    >
                        <i class="bi bi-pencil-square"></i>
                        Editar
                    </Link>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                    >
                        Proveedor
                    </p>
                    <p class="mt-3 text-lg font-bold text-slate-900">
                        {{ compra.proveedor?.nombre || "No disponible" }}
                    </p>
                </div>
                <div
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                    >
                        Fecha
                    </p>
                    <p class="mt-3 text-lg font-bold text-slate-900">
                        {{ formatDate(compra.fecha_compra) }}
                    </p>
                </div>
                <div
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                    >
                        Total
                    </p>
                    <p class="mt-3 text-3xl font-black text-cyan-700">
                        {{ formatPrice(compra.precio_total) }}
                    </p>
                </div>
            </div>

            <section
                class="mt-8 overflow-hidden rounded-[2rem] border border-white bg-white/90 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead
                            class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                        >
                            <tr>
                                <th class="px-6 py-4">Variante</th>
                                <th class="px-6 py-4 text-center">Cantidad</th>
                                <th class="px-6 py-4 text-end">Precio</th>
                                <th class="px-6 py-4 text-end">Descuento</th>
                                <th class="px-6 py-4 text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="detalle in compra.detalles"
                                :key="detalle.id_detalle"
                            >
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ getDetalleTitle(detalle) }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        SKU:
                                        {{
                                            getDetalleVariant(detalle)?.sku ||
                                            detalle.producto?.codigo ||
                                            "Sin SKU"
                                        }}
                                    </p>
                                </td>
                                <td
                                    class="px-6 py-4 text-center text-slate-700"
                                >
                                    {{ detalle.cantidad }}
                                </td>
                                <td class="px-6 py-4 text-end text-slate-700">
                                    {{ formatPrice(detalle.precio) }}
                                </td>
                                <td class="px-6 py-4 text-end text-slate-700">
                                    {{ formatPrice(detalle.descuento) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-end font-bold text-cyan-700"
                                >
                                    {{ formatPrice(lineTotal(detalle)) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td
                                    colspan="4"
                                    class="px-6 py-4 text-end font-bold text-slate-700"
                                >
                                    TOTAL:
                                </td>
                                <td
                                    class="px-6 py-4 text-end text-xl font-black text-cyan-700"
                                >
                                    {{ formatPrice(compra.precio_total) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
