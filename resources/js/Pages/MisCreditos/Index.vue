<template>
    <div
        class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
    >
        <PublicStoreHeader />

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <FlashNotification />

            <!-- Encabezado -->
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
                        Mi cuenta
                    </div>
                    <h1
                        class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Mis créditos
                    </h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Consulta y gestiona tus créditos personales.
                    </p>
                </div>
            </div>

            <!-- Filtros -->
            <div
                class="mb-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <div
                    class="mb-5 flex items-center justify-between gap-3 border-b border-slate-100 pb-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                        >
                            Búsqueda
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-900">
                            Filtrar créditos
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-emerald-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                    </div>
                </div>

                <form
                    @submit.prevent="filtrar"
                    class="grid gap-4 md:grid-cols-12 md:items-end"
                >
                    <div class="md:col-span-5">
                        <label
                            class="mb-2 block text-sm font-bold text-slate-700"
                            >Buscar</label
                        >
                        <input
                            v-model="form.search"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            placeholder="N° Venta..."
                        />
                    </div>

                    <div class="md:col-span-3">
                        <label
                            class="mb-2 block text-sm font-bold text-slate-700"
                            >Estado</label
                        >
                        <select
                            v-model="form.estado"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        >
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="pagado">Pagado</option>
                            <option value="vencido">Vencido</option>
                        </select>
                    </div>

                    <div class="md:col-span-4 flex flex-wrap items-end gap-2">
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
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
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                            Buscar
                        </button>
                        <button
                            type="button"
                            @click="limpiarFiltros"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
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
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla -->
            <div
                class="overflow-hidden rounded-[2rem] border border-white bg-white/90 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <div
                    class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                        >
                            Listado
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-900">
                            Créditos registrados
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-emerald-600"
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
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    N° Crédito
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Venta
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Monto total
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Cuotas
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Pagado
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Pendiente
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Mora
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Estado vacío -->
                            <tr v-if="creditos.data.length === 0">
                                <td colspan="9" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-8 w-8 text-slate-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M3 7.5A2.5 2.5 0 015.5 5h13A2.5 2.5 0 0121 7.5v9A2.5 2.5 0 0118.5 19h-13A2.5 2.5 0 013 16.5v-9z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M7 9h10M7 13h6"
                                            />
                                        </svg>
                                    </div>
                                    <p
                                        class="text-base font-black text-slate-700"
                                    >
                                        Aún no tienes créditos registrados
                                    </p>
                                    <p class="mt-1 text-sm text-slate-400">
                                        Cuando exista un crédito aparecerá aquí
                                        con su estado, cuotas y montos.
                                    </p>
                                </td>
                            </tr>

                            <!-- Filas -->
                            <tr
                                v-for="credito in creditos.data"
                                :key="credito.id"
                                class="border-b border-slate-50 transition hover:bg-emerald-50/30"
                            >
                                <td class="px-6 py-4 font-black text-slate-800">
                                    #{{ credito.id }}
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="
                                            route(
                                                'ventas.show',
                                                credito.venta_id,
                                            )
                                        "
                                        class="font-semibold text-emerald-600 transition hover:text-emerald-700"
                                    >
                                        {{ credito.venta?.numero_venta }}
                                    </Link>
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-semibold text-slate-700"
                                >
                                    {{ formatearMoneda(credito.monto_credito) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800"
                                    >
                                        {{ cuotasPagadas(credito) }}/{{
                                            credito.cuotas_total
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-semibold text-emerald-700"
                                >
                                    {{ formatearMoneda(credito.monto_pagado) }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-semibold text-rose-600"
                                >
                                    {{
                                        formatearMoneda(credito.monto_pendiente)
                                    }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        v-if="credito.dias_mora > 0"
                                        class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800"
                                    >
                                        {{ credito.dias_mora }} días
                                    </span>
                                    <span v-else class="text-slate-300">—</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        :class="getEstadoBadge(credito.estado)"
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                    >
                                        {{ credito.estado.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'mis-creditos.show',
                                                    credito.id,
                                                )
                                            "
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
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
                                                    d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            Ver
                                        </Link>
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                                            @click="abrirModalPago(credito)"
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
                                            QR
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="creditos.last_page > 1"
                    class="border-t border-slate-100 px-6 py-4"
                >
                    <nav>
                        <ul
                            class="flex flex-wrap items-center justify-center gap-2"
                        >
                            <li
                                v-for="(link, index) in creditos.links"
                                :key="index"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    preserve-state
                                    v-html="link.label"
                                    class="inline-flex items-center rounded-xl border px-3 py-2 text-sm font-semibold transition"
                                    :class="
                                        link.active
                                            ? 'border-emerald-600 bg-emerald-600 text-white shadow-sm shadow-emerald-200'
                                            : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700'
                                    "
                                />
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-sm text-slate-300"
                                    v-html="link.label"
                                />
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </main>

        <!-- Modal de pago compartido -->
        <PaymentQRModal
            :credito="creditoSeleccionado"
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
    creditos: Object,
    filtros: Object,
});

const form = ref({
    search: props.filtros?.search || "",
    estado: props.filtros?.estado || "",
});

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor);
};

const getEstadoBadge = (estado) => {
    const badges = {
        pendiente: "bg-yellow-100 text-yellow-800",
        pagado: "bg-emerald-100 text-emerald-800",
        vencido: "bg-rose-100 text-rose-800",
    };
    return badges[estado] || "bg-slate-100 text-slate-700";
};

const cuotasPagadas = (credito) => {
    return credito.cuotas?.filter((c) => c.estado === "pagada").length || 0;
};

const filtrar = () => {
    router.get(route("mis-creditos.index"), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    form.value = { search: "", estado: "" };
    filtrar();
};

const mostrarModalPago = ref(false);
const creditoSeleccionado = ref(null);
const modalPagoRef = ref(null);

const abrirModalPago = (credito) => {
    creditoSeleccionado.value = credito;
    mostrarModalPago.value = true;
};

const cerrarModalPago = () => {
    mostrarModalPago.value = false;
    creditoSeleccionado.value = null;
};
</script>
