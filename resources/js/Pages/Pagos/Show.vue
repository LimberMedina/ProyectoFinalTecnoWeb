<template>
    <AppLayout title="Detalle del Pago">
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
                            Pagos
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Detalle del pago
                        </h1>
                    </div>

                    <Link
                        :href="route('pagos.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Volver al listado
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <h2 class="mb-5 text-lg font-black text-slate-900">
                            Información del cliente
                        </h2>
                        <div class="space-y-3 text-sm text-slate-700">
                            <p>
                                <span class="font-semibold text-slate-900"
                                    >Nombre:</span
                                >
                                {{
                                    pago.cuota?.credito?.venta?.user?.nombre ||
                                    pago.venta?.user?.nombre ||
                                    "—"
                                }}
                                {{
                                    pago.cuota?.credito?.venta?.user
                                        ?.apellidos ||
                                    pago.venta?.user?.apellidos ||
                                    ""
                                }}
                            </p>
                            <p>
                                <span class="font-semibold text-slate-900"
                                    >CI:</span
                                >
                                {{
                                    pago.cuota?.credito?.venta?.user?.ci ||
                                    pago.venta?.user?.ci ||
                                    "—"
                                }}
                            </p>
                            <p>
                                <span class="font-semibold text-slate-900"
                                    >Teléfono:</span
                                >
                                {{
                                    pago.cuota?.credito?.venta?.user
                                        ?.telefono ||
                                    pago.venta?.user?.telefono ||
                                    "—"
                                }}
                            </p>
                            <p>
                                <span class="font-semibold text-slate-900"
                                    >Email:</span
                                >
                                {{
                                    pago.cuota?.credito?.venta?.user?.email ||
                                    pago.venta?.user?.email ||
                                    "—"
                                }}
                            </p>
                        </div>
                    </section>

                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <h2 class="mb-5 text-lg font-black text-slate-900">
                            Información del pago
                        </h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    ID pago
                                </p>
                                <p
                                    class="mt-2 text-xl font-black text-slate-900"
                                >
                                    #{{ pago.id }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Fecha de pago
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold text-slate-900"
                                >
                                    {{ formatDate(pago.fecha) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700"
                                >
                                    Monto pagado
                                </p>
                                <p
                                    class="mt-2 text-2xl font-black text-slate-900"
                                >
                                    Bs. {{ formatMoney(pago.monto) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-sky-100 bg-sky-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700"
                                >
                                    Método de pago
                                </p>
                                <p class="mt-2">
                                    <span
                                        class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700"
                                        >{{
                                            pago.metodo_pago?.nombre ||
                                            "Efectivo"
                                        }}</span
                                    >
                                </p>
                            </div>
                            <div
                                v-if="pago.recargo_extra > 0"
                                class="rounded-2xl border border-amber-100 bg-amber-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700"
                                >
                                    Recargo extra
                                </p>
                                <p
                                    class="mt-2 text-lg font-black text-slate-900"
                                >
                                    Bs. {{ formatMoney(pago.recargo_extra) }}
                                </p>
                            </div>
                            <div
                                v-if="pago.interes_mora_cobrado > 0"
                                class="rounded-2xl border border-rose-100 bg-rose-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700"
                                >
                                    Interés por mora
                                </p>
                                <p
                                    class="mt-2 text-lg font-black text-slate-900"
                                >
                                    Bs.
                                    {{ formatMoney(pago.interes_mora_cobrado) }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section
                        v-if="pago.cuota"
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)] lg:col-span-2"
                    >
                        <h2 class="mb-5 text-lg font-black text-slate-900">
                            Información del crédito y cuota
                        </h2>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Crédito N°
                                </p>
                                <template v-if="pago.cuota?.credito?.id">
                                    <Link
                                        :href="
                                            route(
                                                'creditos.show',
                                                pago.cuota?.credito?.id,
                                            )
                                        "
                                        class="mt-2 inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700"
                                        >#{{ pago.cuota?.credito?.id }}</Link
                                    >
                                </template>
                                <template v-else>—</template>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Cuota N°
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold text-slate-900"
                                >
                                    {{
                                        pago.cuota
                                            ? pago.cuota.numero_cuota +
                                              " / " +
                                              pago.cuota.credito?.cuotas_total
                                            : "—"
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Monto cuota
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold text-slate-900"
                                >
                                    Bs.
                                    {{ formatMoney(pago.cuota?.monto || 0) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Estado cuota
                                </p>
                                <p class="mt-2">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                        :class="
                                            pago.cuota?.estado === 'pagada'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : pago.cuota?.estado ===
                                                    'pendiente'
                                                  ? 'bg-amber-100 text-amber-700'
                                                  : 'bg-rose-100 text-rose-700'
                                        "
                                        >{{
                                            pago.cuota?.estado?.toUpperCase() ||
                                            "N/A"
                                        }}</span
                                    >
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700"
                                >
                                    Monto total crédito
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold text-slate-900"
                                >
                                    Bs.
                                    {{
                                        formatMoney(
                                            pago.cuota?.credito
                                                ?.monto_credito || 0,
                                        )
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-sky-100 bg-sky-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700"
                                >
                                    Monto pagado crédito
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold text-slate-900"
                                >
                                    Bs.
                                    {{
                                        formatMoney(
                                            pago.cuota?.credito?.monto_pagado ||
                                                0,
                                        )
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-amber-100 bg-amber-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700"
                                >
                                    Saldo pendiente
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold text-slate-900"
                                >
                                    Bs.
                                    {{
                                        formatMoney(
                                            pago.cuota?.credito
                                                ?.monto_pendiente || 0,
                                        )
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Total venta
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold text-slate-900"
                                >
                                    Bs.
                                    {{
                                        formatMoney(
                                            pago.cuota?.credito?.venta?.total ||
                                                pago.venta?.total ||
                                                0,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section
                        v-else-if="pago.venta"
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)] lg:col-span-2"
                    >
                        <h2 class="mb-5 text-lg font-black text-slate-900">
                            Información de la venta
                        </h2>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Venta N°
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold text-slate-900"
                                >
                                    Venta #{{ pago.venta?.id }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Total venta
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold text-slate-900"
                                >
                                    Bs.
                                    {{ formatMoney(pago.venta?.total || 0) }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)] lg:col-span-2"
                    >
                        <h2 class="mb-5 text-lg font-black text-slate-900">
                            Productos de la venta
                        </h2>
                        <div
                            class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                        >
                            <div class="overflow-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead
                                        class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                    >
                                        <tr>
                                            <th class="px-4 py-4">Producto</th>
                                            <th class="px-4 py-4 text-center">
                                                Cantidad
                                            </th>
                                            <th class="px-4 py-4 text-end">
                                                Precio unit.
                                            </th>
                                            <th class="px-4 py-4 text-end">
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="detalle in pago.cuota
                                                ?.credito?.venta?.detalles ||
                                            pago.venta?.detalles ||
                                            []"
                                            :key="detalle.id"
                                            class="border-t border-slate-100"
                                        >
                                            <td
                                                class="px-4 py-4 font-semibold text-slate-900"
                                            >
                                                {{ detalle.producto?.nombre }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-center text-slate-700"
                                            >
                                                {{ detalle.cantidad }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end text-slate-700"
                                            >
                                                Bs.
                                                {{
                                                    formatMoney(
                                                        detalle.precio_unitario,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end font-semibold text-emerald-700"
                                            >
                                                Bs.
                                                {{
                                                    formatMoney(
                                                        detalle.subtotal,
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-slate-50">
                                        <tr>
                                            <td
                                                colspan="3"
                                                class="px-4 py-4 text-end font-black text-slate-700"
                                            >
                                                Total venta:
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end font-black text-emerald-700"
                                            >
                                                Bs.
                                                {{
                                                    formatMoney(
                                                        pago.cuota?.credito
                                                            ?.venta?.total,
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    pago: Object,
});

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>
