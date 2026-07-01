<template>
    <AppLayout title="Gestión de Créditos">
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
                            Gestión de créditos
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Administra y visualiza los créditos otorgados a
                            clientes.
                        </p>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
                >
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-emerald-50 p-3 text-emerald-600"
                            >
                                <i class="bi bi-collection text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Total créditos
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    {{ indicadores.total_creditos }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-amber-50 p-3 text-amber-600"
                            >
                                <i class="bi bi-cash-coin text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Total pendiente
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    {{
                                        formatearMoneda(
                                            indicadores.total_pendiente,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-rose-50 p-3 text-rose-600"
                            >
                                <i
                                    class="bi bi-exclamation-octagon text-3xl"
                                ></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Créditos en mora
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    {{ indicadores.total_mora }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-slate-100 p-3 text-slate-600"
                            >
                                <i class="bi bi-currency-exchange text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Monto en mora
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    {{
                                        formatearMoneda(indicadores.monto_mora)
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <form
                        @submit.prevent="filtrar"
                        class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end"
                    >
                        <div class="lg:col-span-5">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Buscar</label
                            >
                            <input
                                v-model="form.search"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                placeholder="Cliente, CI, N° venta..."
                            />
                        </div>
                        <div class="lg:col-span-3">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Estado</label
                            >
                            <select
                                v-model="form.estado"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            >
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagado">Pagado</option>
                                <option value="vencido">Vencido</option>
                            </select>
                        </div>
                        <div class="flex gap-3 lg:col-span-4">
                            <button
                                type="submit"
                                class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                            >
                                <i class="bi bi-search"></i>Buscar
                            </button>
                            <button
                                type="button"
                                @click="limpiarFiltros"
                                class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                <i class="bi bi-x-circle"></i>Limpiar
                            </button>
                            <Link
                                :href="route('creditos.reporte-mora')"
                                class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700"
                                ><i class="bi bi-file-earmark-text"></i>Reporte
                                mora</Link
                            >
                        </div>
                    </form>
                </section>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                    >
                        <div class="overflow-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead
                                    class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                >
                                    <tr>
                                        <th class="px-4 py-4">N° crédito</th>
                                        <th class="px-4 py-4">Cliente</th>
                                        <th class="px-4 py-4">Venta</th>
                                        <th class="px-4 py-4 text-end">
                                            Monto total
                                        </th>
                                        <th class="px-4 py-4 text-center">
                                            Cuotas
                                        </th>
                                        <th class="px-4 py-4 text-end">
                                            Pagado
                                        </th>
                                        <th class="px-4 py-4 text-end">
                                            Pendiente
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
                                    <tr v-if="creditos.data.length === 0">
                                        <td
                                            colspan="10"
                                            class="px-4 py-10 text-center text-slate-500"
                                        >
                                            <i
                                                class="bi bi-inbox mb-3 block text-4xl text-slate-300"
                                            ></i>
                                            No se encontraron créditos.
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="credito in creditos.data"
                                        :key="credito.id"
                                        class="border-t border-slate-100"
                                    >
                                        <td
                                            class="px-4 py-4 font-semibold text-slate-900"
                                        >
                                            #{{ credito.id }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{
                                                    credito.venta?.user?.nombre
                                                }}
                                                {{
                                                    credito.venta?.user
                                                        ?.apellidos
                                                }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                CI:
                                                {{ credito.venta?.user?.ci }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <Link
                                                :href="
                                                    route(
                                                        'ventas.show',
                                                        credito.venta_id,
                                                    )
                                                "
                                                class="font-semibold text-emerald-700 transition hover:text-emerald-800"
                                                >{{
                                                    credito.venta?.numero_venta
                                                }}</Link
                                            >
                                        </td>
                                        <td
                                            class="px-4 py-4 text-end text-slate-700"
                                        >
                                            {{
                                                formatearMoneda(
                                                    credito.monto_credito,
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span
                                                class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700"
                                                >{{ cuotasPagadas(credito) }}/{{
                                                    credito.cuotas_total
                                                }}</span
                                            >
                                        </td>
                                        <td
                                            class="px-4 py-4 text-end font-semibold text-emerald-700"
                                        >
                                            {{
                                                formatearMoneda(
                                                    credito.monto_pagado,
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-end font-semibold text-rose-700"
                                        >
                                            {{
                                                formatearMoneda(
                                                    credito.monto_pendiente,
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span
                                                v-if="credito.dias_mora > 0"
                                                class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700"
                                                >{{
                                                    credito.dias_mora
                                                }}
                                                días</span
                                            ><span v-else class="text-slate-400"
                                                >-</span
                                            >
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span
                                                :class="
                                                    getEstadoBadge(
                                                        credito.estado,
                                                    )
                                                "
                                                class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                                >{{
                                                    credito.estado.toUpperCase()
                                                }}</span
                                            >
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <div class="inline-flex gap-2">
                                                <Link
                                                    :href="
                                                        route(
                                                            'creditos.show',
                                                            credito.id,
                                                        )
                                                    "
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100"
                                                    title="Ver detalles"
                                                    ><i class="bi bi-eye"></i
                                                ></Link>
                                                <button
                                                    v-if="
                                                        credito.estado !==
                                                        'pagado'
                                                    "
                                                    @click="
                                                        abrirModalPago(credito)
                                                    "
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                                                    title="Registrar pago"
                                                >
                                                    <i
                                                        class="bi bi-cash-coin"
                                                    ></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div
                            v-if="creditos.last_page > 1"
                            class="flex flex-col gap-4 border-t border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <nav>
                                <ul class="flex flex-wrap items-center gap-2">
                                    <li>
                                        <Link
                                            class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                            :href="
                                                creditos.prev_page_url || '#'
                                            "
                                            preserve-state
                                            >Anterior</Link
                                        >
                                    </li>
                                    <li
                                        v-for="page in paginasVisibles"
                                        :key="page"
                                    >
                                        <Link
                                            v-if="page !== '...'"
                                            class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border px-3 text-sm font-semibold transition"
                                            :class="
                                                page === creditos.current_page
                                                    ? 'border-emerald-500 bg-emerald-600 text-white'
                                                    : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                            "
                                            :href="
                                                creditos.links[page]?.url || '#'
                                            "
                                            preserve-state
                                            >{{ page }}</Link
                                        >
                                        <span
                                            v-else
                                            class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border-slate-200 bg-slate-100 px-3 text-sm font-semibold text-slate-400"
                                            >...</span
                                        >
                                    </li>
                                    <li>
                                        <Link
                                            class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                            :href="
                                                creditos.next_page_url || '#'
                                            "
                                            preserve-state
                                            >Siguiente</Link
                                        >
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Modal de Pago -->
        <PagoModal
            :show="mostrarModalPago"
            :cuotas="cuotasCredito"
            :metodosPago="metodosPago"
            @close="cerrarModalPago"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PagoModal from "@/Components/PagoModal.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    creditos: Object,
    indicadores: Object,
    filtros: Object,
    metodosPago: Array,
});

const form = ref({
    search: props.filtros?.search || "",
    estado: props.filtros?.estado || "",
});

const mostrarModalPago = ref(false);
const cuotasCredito = ref([]);

const abrirModalPago = (credito) => {
    cuotasCredito.value = credito.cuotas || [];
    mostrarModalPago.value = true;
};

const cerrarModalPago = () => {
    mostrarModalPago.value = false;
    cuotasCredito.value = [];
};

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor);
};

const getEstadoBadge = (estado) => {
    const badges = {
        pendiente: "bg-amber-100 text-amber-700",
        pagado: "bg-emerald-100 text-emerald-700",
        vencido: "bg-rose-100 text-rose-700",
    };
    return badges[estado] || "bg-slate-100 text-slate-600";
};

const cuotasPagadas = (credito) => {
    return credito.cuotas?.filter((c) => c.estado === "pagada").length || 0;
};

const filtrar = () => {
    router.get(route("creditos.index"), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    form.value = {
        search: "",
        estado: "",
    };
    filtrar();
};

const paginasVisibles = computed(() => {
    const current = props.creditos.current_page;
    const last = props.creditos.last_page;
    const delta = 2;
    const range = [];
    const rangeWithDots = [];

    for (
        let i = Math.max(2, current - delta);
        i <= Math.min(last - 1, current + delta);
        i++
    ) {
        range.push(i);
    }

    if (current - delta > 2) {
        rangeWithDots.push(1, "...");
    } else {
        rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (current + delta < last - 1) {
        rangeWithDots.push("...", last);
    } else if (last > 1) {
        rangeWithDots.push(last);
    }

    return rangeWithDots;
});
</script>
