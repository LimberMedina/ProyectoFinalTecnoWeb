<template>
    <AppLayout title="Mis Créditos">
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
                            Mis créditos y cuotas
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Consulta el estado de tus créditos y realiza pagos
                            con QR.
                        </p>
                    </div>
                </div>

                <section
                    v-if="creditos.length === 0"
                    class="rounded-[2rem] border border-white bg-white/90 px-6 py-14 text-center shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <i class="bi bi-credit-card text-5xl text-slate-300"></i>
                    <p class="mt-4 text-base font-medium text-slate-600">
                        No tienes créditos activos.
                    </p>
                </section>

                <div v-else class="space-y-6">
                    <section
                        v-for="credito in creditos"
                        :key="credito.id"
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="flex flex-col gap-3 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <div
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700"
                                >
                                    Crédito #{{ credito.id }}
                                </div>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ credito.numero_cuotas }} cuotas
                                </p>
                            </div>
                            <span
                                class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold"
                                :class="
                                    credito.estado === 'pagado'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : credito.estado === 'activo'
                                          ? 'bg-sky-100 text-sky-700'
                                          : 'bg-rose-100 text-rose-700'
                                "
                                >{{ credito.estado }}</span
                            >
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Monto total
                                </p>
                                <p
                                    class="mt-2 text-xl font-black text-slate-900"
                                >
                                    Bs. {{ formatMoney(credito.monto_total) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700"
                                >
                                    Pagado
                                </p>
                                <p
                                    class="mt-2 text-xl font-black text-slate-900"
                                >
                                    Bs. {{ formatMoney(credito.monto_pagado) }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-rose-100 bg-rose-50 p-4"
                            >
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-rose-700"
                                >
                                    Saldo
                                </p>
                                <p
                                    class="mt-2 text-xl font-black text-slate-900"
                                >
                                    Bs.
                                    {{ formatMoney(credito.monto_pendiente) }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                        >
                            <div class="overflow-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead
                                        class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                    >
                                        <tr>
                                            <th class="px-4 py-4">N°</th>
                                            <th class="px-4 py-4">Monto</th>
                                            <th class="px-4 py-4">
                                                Vencimiento
                                            </th>
                                            <th class="px-4 py-4">Estado</th>
                                            <th class="px-4 py-4">Saldo</th>
                                            <th class="px-4 py-4 text-end">
                                                Acción
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="cuota in credito.cuotas"
                                            :key="cuota.id"
                                            class="border-t border-slate-100"
                                        >
                                            <td
                                                class="px-4 py-4 font-semibold text-slate-900"
                                            >
                                                {{ cuota.numero_cuota }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                Bs.
                                                {{ formatMoney(cuota.monto) }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{
                                                    formatDate(
                                                        cuota.fecha_vencimiento,
                                                    )
                                                }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                                    :class="
                                                        cuota.estado ===
                                                        'pagada'
                                                            ? 'bg-emerald-100 text-emerald-700'
                                                            : cuota.estado ===
                                                                'pendiente'
                                                              ? 'bg-amber-100 text-amber-700'
                                                              : 'bg-rose-100 text-rose-700'
                                                    "
                                                    >{{ cuota.estado }}</span
                                                >
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                Bs.
                                                {{
                                                    formatMoney(
                                                        cuota.monto_pendiente,
                                                    )
                                                }}
                                            </td>
                                            <td class="px-4 py-4 text-end">
                                                <button
                                                    v-if="
                                                        cuota.estado !==
                                                        'pagada'
                                                    "
                                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                                                    @click="
                                                        abrirModalPago(cuota)
                                                    "
                                                >
                                                    Pagar con QR
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <QRModal
            v-if="showQRModal"
            :cuota="cuotaParaPago"
            @close="showQRModal = false"
        />
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import QRModal from "@/Components/QRModal.vue";

const props = defineProps({
    creditos: Array,
});

const showQRModal = ref(false);
const cuotaParaPago = ref(null);

const abrirModalPago = (cuota) => {
    cuotaParaPago.value = cuota;
    showQRModal.value = true;
};

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
const formatDate = (date) => new Date(date).toLocaleDateString("es-ES");
</script>
