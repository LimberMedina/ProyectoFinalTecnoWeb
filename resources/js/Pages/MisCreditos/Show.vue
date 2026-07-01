<template>
    <div>
        <PublicStoreHeader />
        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <FlashNotification />

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2
                        class="flex items-center gap-2 text-2xl font-bold text-slate-800"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-emerald-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M3 10h18M7 6h10M7 14h10M7 18h10"
                            />
                        </svg>
                        Detalle del Crédito #{{ credito.id }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Información general, cuotas y pagos pendientes
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <section class="space-y-6 lg:col-span-1">
                    <div
                        class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <h3 class="text-base font-semibold text-slate-800">
                            Información del Crédito
                        </h3>
                        <dl class="mt-4 space-y-3 text-sm text-slate-700">
                            <div>
                                <dt class="font-medium text-slate-500">
                                    Cliente
                                </dt>
                                <dd class="mt-1">
                                    {{ credito.venta?.user?.nombre }}
                                    {{ credito.venta?.user?.apellidos }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">CI</dt>
                                <dd class="mt-1">
                                    {{ credito.venta?.user?.ci || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">
                                    Teléfono
                                </dt>
                                <dd class="mt-1">
                                    {{ credito.venta?.user?.telefono || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">
                                    Email
                                </dt>
                                <dd class="mt-1">
                                    {{ credito.venta?.user?.email || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">
                                    Venta
                                </dt>
                                <dd class="mt-1">
                                    <Link
                                        :href="
                                            route(
                                                'ventas.show',
                                                credito.venta_id,
                                            )
                                        "
                                        class="font-medium text-emerald-600 hover:text-emerald-700"
                                    >
                                        {{ credito.venta?.numero_venta }}
                                    </Link>
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">
                                    Fecha otorgamiento
                                </dt>
                                <dd class="mt-1">
                                    {{
                                        formatearFecha(
                                            credito.fecha_otorgamiento,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">
                                    Fecha vencimiento
                                </dt>
                                <dd class="mt-1">
                                    {{
                                        formatearFecha(
                                            credito.fecha_vencimiento,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">
                                    Estado
                                </dt>
                                <dd class="mt-1">
                                    <span
                                        :class="getEstadoBadge(credito.estado)"
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                    >
                                        {{ credito.estado.toUpperCase() }}
                                    </span>
                                </dd>
                            </div>
                            <div v-if="credito.dias_mora > 0">
                                <dt class="font-medium text-slate-500">
                                    Días de mora
                                </dt>
                                <dd
                                    class="mt-1 inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800"
                                >
                                    {{ credito.dias_mora }} días
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div
                        class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <h3 class="text-base font-semibold text-slate-800">
                            Resumen
                        </h3>
                        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-slate-500">Monto total</div>
                                <div
                                    class="mt-1 text-lg font-bold text-emerald-700"
                                >
                                    {{ formatearMoneda(credito.monto_credito) }}
                                </div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-slate-500">Cuotas</div>
                                <div
                                    class="mt-1 text-lg font-bold text-slate-800"
                                >
                                    {{ credito.cuotas_total }}
                                </div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-slate-500">Pagado</div>
                                <div
                                    class="mt-1 text-lg font-bold text-emerald-600"
                                >
                                    {{ formatearMoneda(credito.monto_pagado) }}
                                </div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="text-slate-500">Pendiente</div>
                                <div
                                    class="mt-1 text-lg font-bold text-rose-600"
                                >
                                    {{
                                        formatearMoneda(credito.monto_pendiente)
                                    }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <div
                                class="mb-2 flex items-center justify-between text-sm text-slate-500"
                            >
                                <span>Progreso de pago</span>
                                <span>{{ porcentajePagado.toFixed(1) }}%</span>
                            </div>
                            <div
                                class="h-3 overflow-hidden rounded-full bg-slate-100"
                            >
                                <div
                                    class="h-full rounded-full bg-emerald-600"
                                    :style="{ width: porcentajePagado + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-6 lg:col-span-2">
                    <div
                        class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-semibold text-slate-800">
                                Plan de Cuotas
                            </h3>
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                @click="abrirModalPago"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M4 4h5v5H4zM15 4h5v5h-5zM4 15h5v5H4zM15 15h5v5h-5z"
                                    />
                                </svg>
                                Pagar por QR
                            </button>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table
                                class="min-w-full divide-y divide-slate-200 text-sm"
                            >
                                <thead
                                    class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    <tr>
                                        <th class="px-3 py-3">#</th>
                                        <th class="px-3 py-3 text-right">
                                            Monto
                                        </th>
                                        <th class="px-3 py-3">Vencimiento</th>
                                        <th class="px-3 py-3 text-right">
                                            Pagado
                                        </th>
                                        <th class="px-3 py-3 text-right">
                                            Pendiente
                                        </th>
                                        <th class="px-3 py-3 text-center">
                                            Mora
                                        </th>
                                        <th class="px-3 py-3 text-center">
                                            Estado
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    <tr
                                        v-for="cuota in credito.cuotas"
                                        :key="cuota.id"
                                        class="hover:bg-slate-50/60"
                                    >
                                        <td
                                            class="px-3 py-3 font-medium text-slate-800"
                                        >
                                            Cuota {{ cuota.numero_cuota }}
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            {{ formatearMoneda(cuota.monto) }}
                                        </td>
                                        <td class="px-3 py-3">
                                            {{
                                                formatearFecha(
                                                    cuota.fecha_vencimiento,
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="px-3 py-3 text-right text-emerald-600"
                                        >
                                            {{
                                                formatearMoneda(
                                                    cuota.monto_pagado,
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="px-3 py-3 text-right text-rose-600"
                                        >
                                            {{
                                                formatearMoneda(
                                                    cuota.monto_pendiente,
                                                )
                                            }}
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span
                                                v-if="cuota.dias_mora > 0"
                                                class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800"
                                                >{{
                                                    cuota.dias_mora
                                                }}
                                                días</span
                                            >
                                            <span v-else class="text-slate-400"
                                                >-</span
                                            >
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span
                                                :class="
                                                    getCuotaBadge(cuota.estado)
                                                "
                                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                            >
                                                {{ cuota.estado.toUpperCase() }}
                                            </span>
                                            <button
                                                v-if="cuota.estado !== 'pagada'"
                                                type="button"
                                                class="ml-2 inline-flex items-center rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50"
                                                @click="
                                                    abrirModalPagoCuota(cuota)
                                                "
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M4 4h5v5H4zM15 4h5v5h-5zM4 15h5v5H4zM15 15h5v5h-5z"
                                                    />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <h3 class="text-base font-semibold text-slate-800">
                            Acciones
                        </h3>
                        <div class="mt-4">
                            <Link
                                :href="route('mis-creditos.index')"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M15 19l-7-7 7-7"
                                    />
                                </svg>
                                Volver a Mis Créditos
                            </Link>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- Modal de pago compartido -->
        <PaymentQRModal
            :credito="credito"
            :mostrar-modal="mostrarModalPago"
            @cerrar="cerrarModalPago"
            ref="modalPagoRef"
        />
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import PublicStoreHeader from "@/Components/PublicStoreHeader.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import PaymentQRModal from "@/Components/PaymentQRModal.vue";

const props = defineProps({
    credito: Object,
});

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
        pendiente: "bg-yellow-100 text-yellow-800",
        pagado: "bg-emerald-100 text-emerald-800",
        vencido: "bg-rose-100 text-rose-800",
    };
    return badges[estado] || "bg-slate-100 text-slate-700";
};

const getCuotaBadge = (estado) => {
    const badges = {
        pendiente: "bg-yellow-100 text-yellow-800",
        pagada: "bg-emerald-100 text-emerald-800",
        vencido: "bg-rose-100 text-rose-800",
    };
    return badges[estado] || "bg-slate-100 text-slate-700";
};

const porcentajePagado = computed(() => {
    if (!props.credito?.monto_credito) return 0;
    return (props.credito.monto_pagado / props.credito.monto_credito) * 100;
});

const mostrarModalPago = ref(false);
const modalPagoRef = ref(null);

const abrirModalPago = () => {
    mostrarModalPago.value = true;
};

const abrirModalPagoCuota = (cuota) => {
    mostrarModalPago.value = true;
    // Pasar la cuota al modal
    setTimeout(() => {
        if (modalPagoRef.value) {
            modalPagoRef.value.abrirConCuota(cuota);
        }
    }, 0);
};

const cerrarModalPago = () => {
    mostrarModalPago.value = false;
};
</script>
