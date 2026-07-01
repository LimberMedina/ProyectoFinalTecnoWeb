<template>
    <AppLayout title="Detalle del Crédito">
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
                            Créditos
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Detalle del crédito #{{ credito.id }}
                        </h1>
                    </div>
                </div>

                <div
                    v-if="$page.props.flash?.success"
                    class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                >
                    <i class="bi bi-check-circle me-2"></i
                    >{{ $page.props.flash.success }}
                </div>
                <div
                    v-if="$page.props.errors?.error"
                    class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                >
                    <i class="bi bi-exclamation-triangle me-2"></i
                    >{{ $page.props.errors.error }}
                </div>

                <section
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-5"
                        >
                            <h2 class="mb-4 text-lg font-black text-slate-900">
                                Cliente
                            </h2>
                            <div class="space-y-3 text-sm text-slate-700">
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Nombre:</span
                                    >
                                    {{ credito.venta?.user?.nombre }}
                                    {{ credito.venta?.user?.apellidos }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >CI:</span
                                    >
                                    {{ credito.venta?.user?.ci }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Teléfono:</span
                                    >
                                    {{ credito.venta?.user?.telefono }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Email:</span
                                    >
                                    {{ credito.venta?.user?.email }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-5"
                        >
                            <h2 class="mb-4 text-lg font-black text-slate-900">
                                Información del crédito
                            </h2>
                            <div class="space-y-3 text-sm text-slate-700">
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Venta:</span
                                    >
                                    <Link
                                        :href="
                                            route(
                                                'ventas.show',
                                                credito.venta_id,
                                            )
                                        "
                                        class="font-semibold text-emerald-700 transition hover:text-emerald-800"
                                        >{{ credito.venta?.numero_venta }}</Link
                                    >
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Fecha otorgamiento:</span
                                    >
                                    {{
                                        formatearFecha(
                                            credito.fecha_otorgamiento,
                                        )
                                    }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Fecha vencimiento:</span
                                    >
                                    {{
                                        formatearFecha(
                                            credito.fecha_vencimiento,
                                        )
                                    }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Estado:</span
                                    >
                                    <span
                                        :class="getEstadoBadge(credito.estado)"
                                        class="ml-2 inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                        >{{
                                            credito.estado.toUpperCase()
                                        }}</span
                                    >
                                </p>
                                <p v-if="credito.dias_mora > 0">
                                    <span class="font-semibold text-rose-700"
                                        >Días de mora:</span
                                    >
                                    <span
                                        class="ml-2 inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700"
                                        >{{ credito.dias_mora }} días</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div
                            class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700"
                            >
                                Monto total
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ formatearMoneda(credito.monto_credito) }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500"
                            >
                                Total cuotas
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ credito.cuotas_total }} cuotas
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-sky-100 bg-sky-50 p-4"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700"
                            >
                                Monto pagado
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ formatearMoneda(credito.monto_pagado) }}
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-rose-100 bg-rose-50 p-4"
                        >
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700"
                            >
                                Monto pendiente
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ formatearMoneda(credito.monto_pendiente) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div
                            class="h-4 overflow-hidden rounded-full bg-slate-100"
                        >
                            <div
                                class="h-4 rounded-full bg-emerald-600 text-[10px] font-bold leading-4 text-white transition-all"
                                :style="{ width: porcentajePagado + '%' }"
                            >
                                <span class="px-2"
                                    >{{ porcentajePagado.toFixed(1) }}%
                                    pagado</span
                                >
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <h2 class="text-xl font-black text-slate-900">
                            Plan de cuotas
                        </h2>
                        <button
                            v-if="tieneCuotasPendientes"
                            @click="abrirModalPago()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                        >
                            <i class="bi bi-cash-coin"></i>Registrar pago
                        </button>
                    </div>

                    <div
                        class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                    >
                        <div class="overflow-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-slate-50 text-slate-500">
                                    <tr>
                                        <th class="px-4 py-4">Cuota</th>
                                        <th class="px-4 py-4 text-end">
                                            Monto
                                        </th>
                                        <th class="px-4 py-4">
                                            Fecha vencimiento
                                        </th>
                                        <th class="px-4 py-4 text-end">
                                            Monto pagado
                                        </th>
                                        <th class="px-4 py-4 text-end">
                                            Monto pendiente
                                        </th>
                                        <th class="px-4 py-4 text-center">
                                            Mora
                                        </th>
                                        <th class="px-4 py-4 text-center">
                                            Estado
                                        </th>
                                        <th class="px-4 py-4 text-center">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template
                                        v-for="cuota in credito.cuotas"
                                        :key="cuota.id"
                                    >
                                        <tr class="border-t border-slate-100">
                                            <td
                                                class="px-4 py-4 font-semibold text-slate-900"
                                            >
                                                Cuota {{ cuota.numero_cuota }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end text-slate-700"
                                            >
                                                {{
                                                    formatearMoneda(cuota.monto)
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{
                                                    formatearFecha(
                                                        cuota.fecha_vencimiento,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end text-emerald-700"
                                            >
                                                {{
                                                    formatearMoneda(
                                                        cuota.monto_pagado,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end text-rose-700"
                                            >
                                                {{
                                                    formatearMoneda(
                                                        cuota.monto_pendiente,
                                                    )
                                                }}
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span
                                                    v-if="cuota.dias_mora > 0"
                                                    class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700"
                                                    >{{
                                                        cuota.dias_mora
                                                    }}
                                                    días</span
                                                ><span
                                                    v-else
                                                    class="text-slate-400"
                                                    >-</span
                                                >
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span
                                                    :class="
                                                        getCuotaBadge(
                                                            cuota.estado,
                                                        )
                                                    "
                                                    class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                                    >{{
                                                        cuota.estado.toUpperCase()
                                                    }}</span
                                                >
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <div class="inline-flex gap-2">
                                                    <button
                                                        v-if="
                                                            cuota.estado !==
                                                            'pagada'
                                                        "
                                                        @click="
                                                            abrirModalPago(
                                                                cuota.id,
                                                            )
                                                        "
                                                        class="inline-flex h-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                                        title="Pagar cuota"
                                                    >
                                                        <i
                                                            class="bi bi-cash-coin me-2"
                                                        ></i
                                                        >Pagar
                                                    </button>
                                                    <button
                                                        v-if="
                                                            cuota.pagos &&
                                                            cuota.pagos.length >
                                                                0
                                                        "
                                                        @click="
                                                            togglePagos(
                                                                cuota.id,
                                                            )
                                                        "
                                                        class="inline-flex h-10 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-3 text-sm font-semibold text-sky-700 transition hover:bg-sky-100"
                                                        title="Ver pagos"
                                                    >
                                                        <i
                                                            class="bi bi-eye"
                                                        ></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr
                                            v-if="
                                                cuotaExpandida === cuota.id &&
                                                cuota.pagos?.length > 0
                                            "
                                        >
                                            <td
                                                colspan="8"
                                                class="bg-slate-50 px-4 py-4"
                                            >
                                                <div
                                                    class="rounded-[1.25rem] border border-slate-200 bg-white p-4"
                                                >
                                                    <h3
                                                        class="mb-3 text-base font-black text-slate-900"
                                                    >
                                                        Historial de pagos -
                                                        Cuota #{{
                                                            cuota.numero_cuota
                                                        }}
                                                    </h3>
                                                    <div
                                                        class="overflow-hidden rounded-xl border border-slate-100"
                                                    >
                                                        <table
                                                            class="min-w-full text-left text-sm"
                                                        >
                                                            <thead
                                                                class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                                            >
                                                                <tr>
                                                                    <th
                                                                        class="px-4 py-3"
                                                                    >
                                                                        Fecha
                                                                    </th>
                                                                    <th
                                                                        class="px-4 py-3"
                                                                    >
                                                                        Monto
                                                                    </th>
                                                                    <th
                                                                        class="px-4 py-3"
                                                                    >
                                                                        Método
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr
                                                                    v-for="pago in cuota.pagos"
                                                                    :key="
                                                                        pago.id
                                                                    "
                                                                    class="border-t border-slate-100"
                                                                >
                                                                    <td
                                                                        class="px-4 py-3 text-slate-700"
                                                                    >
                                                                        {{
                                                                            formatearFecha(
                                                                                pago.fecha,
                                                                            )
                                                                        }}
                                                                    </td>
                                                                    <td
                                                                        class="px-4 py-3 text-slate-700"
                                                                    >
                                                                        {{
                                                                            formatearMoneda(
                                                                                pago.monto,
                                                                            )
                                                                        }}
                                                                    </td>
                                                                    <td
                                                                        class="px-4 py-3 text-slate-700"
                                                                    >
                                                                        {{
                                                                            pago
                                                                                .metodoPago
                                                                                ?.nombre ||
                                                                            pago
                                                                                .metodo_pago
                                                                                ?.nombre
                                                                        }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <Link
                            :href="route('creditos.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            ><i class="bi bi-arrow-left"></i>Volver a
                            créditos</Link
                        >
                    </div>
                </section>
            </div>
        </div>

        <!-- Modal de Pago -->
        <PagoModal
            :show="mostrarModalPago"
            :cuotas="credito.cuotas"
            :metodosPago="metodosPago"
            :cuotaPreseleccionada="cuotaSeleccionadaId"
            @close="cerrarModalPago"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PagoModal from "@/Components/PagoModal.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    credito: Object,
    metodosPago: Array,
});

const mostrarModalPago = ref(false);
const cuotaExpandida = ref(null);
const cuotaSeleccionadaId = ref(null);

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor);
};

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getEstadoBadge = (estado) => {
    const badges = {
        pendiente: "bg-amber-100 text-amber-700",
        pagado: "bg-emerald-100 text-emerald-700",
        vencido: "bg-rose-100 text-rose-700",
    };
    return badges[estado] || "bg-slate-100 text-slate-600";
};

const getCuotaBadge = (estado) => {
    const badges = {
        pendiente: "bg-amber-100 text-amber-700",
        pagada: "bg-emerald-100 text-emerald-700",
        vencido: "bg-rose-100 text-rose-700",
    };
    return badges[estado] || "bg-slate-100 text-slate-600";
};

const porcentajePagado = computed(() => {
    if (props.credito.monto_credito === 0) return 0;
    return (props.credito.monto_pagado / props.credito.monto_credito) * 100;
});

const tieneCuotasPendientes = computed(() => {
    return props.credito.cuotas?.some(
        (c) => c.estado === "pendiente" || c.estado === "vencido",
    );
});

const abrirModalPago = (cuotaId = null) => {
    cuotaSeleccionadaId.value = cuotaId;
    mostrarModalPago.value = true;
};

const cerrarModalPago = () => {
    mostrarModalPago.value = false;
    cuotaSeleccionadaId.value = null;
};

const togglePagos = (cuotaId) => {
    cuotaExpandida.value = cuotaExpandida.value === cuotaId ? null : cuotaId;
};
</script>
