<template>
    <AppLayout title="Gestión de Pagos">
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
                            Gestión de pagos
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Historial de pagos registrados en el sistema.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-emerald-50 p-3 text-emerald-600"
                            >
                                <i class="bi bi-receipt text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Total pagos
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    {{ estadisticas.total_pagos }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div class="rounded-2xl bg-sky-50 p-3 text-sky-600">
                                <i class="bi bi-cash-stack text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Total recaudado
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    Bs.
                                    {{ formatMoney(estadisticas.total_monto) }}
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
                                <i class="bi bi-calendar-month text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Pagos del mes
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    Bs.
                                    {{ formatMoney(estadisticas.pagos_mes) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end"
                    >
                        <div class="lg:col-span-4">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Buscar</label
                            >
                            <input
                                v-model="form.buscar"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                placeholder="Cliente o CI..."
                                @input="buscarConRetraso"
                            />
                        </div>
                        <div class="lg:col-span-3">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Fecha desde</label
                            >
                            <input
                                v-model="form.fecha_desde"
                                type="date"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                @change="aplicarFiltros"
                            />
                        </div>
                        <div class="lg:col-span-3">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Fecha hasta</label
                            >
                            <input
                                v-model="form.fecha_hasta"
                                type="date"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                @change="aplicarFiltros"
                            />
                        </div>
                        <div class="lg:col-span-2">
                            <button
                                @click="limpiarFiltros"
                                class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                <i class="bi bi-x-circle"></i>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <h2 class="text-xl font-black text-slate-900">
                            Listado de pagos
                        </h2>
                    </div>

                    <div
                        v-if="pagos.data.length === 0"
                        class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center text-slate-500"
                    >
                        <i
                            class="bi bi-inbox mb-3 block text-4xl text-slate-300"
                        ></i>
                        No hay pagos registrados.
                    </div>

                    <div
                        v-else
                        class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                    >
                        <div class="overflow-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead
                                    class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                >
                                    <tr>
                                        <th class="px-4 py-4">#</th>
                                        <th class="px-4 py-4">Fecha</th>
                                        <th class="px-4 py-4">Cliente</th>
                                        <th class="px-4 py-4">Crédito</th>
                                        <th class="px-4 py-4">Cuota</th>
                                        <th class="px-4 py-4">Monto</th>
                                        <th class="px-4 py-4">Método pago</th>
                                        <th class="px-4 py-4 text-center">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="pago in pagos.data"
                                        :key="pago.id"
                                        class="border-t border-slate-100"
                                    >
                                        <td class="px-4 py-4">
                                            <span
                                                class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700"
                                                >{{ pago.id }}</span
                                            >
                                        </td>
                                        <td class="px-4 py-4 text-slate-700">
                                            {{ formatDate(pago.fecha) }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{
                                                    pago.venta?.user?.name ||
                                                    pago.cuota?.credito?.venta
                                                        ?.user?.name ||
                                                    "N/A"
                                                }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                CI:
                                                {{
                                                    pago.venta?.user?.ci ||
                                                    pago.cuota?.credito?.venta
                                                        ?.user?.ci ||
                                                    "N/A"
                                                }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <template
                                                v-if="pago.cuota?.credito?.id"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'creditos.show',
                                                            pago.cuota?.credito
                                                                ?.id,
                                                        )
                                                    "
                                                    class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700"
                                                    >#{{
                                                        pago.cuota?.credito?.id
                                                    }}</Link
                                                >
                                            </template>
                                            <template
                                                v-else-if="pago.venta?.id"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'ventas.show',
                                                            pago.venta?.id,
                                                        )
                                                    "
                                                    class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700"
                                                    >Venta #{{
                                                        pago.venta?.id
                                                    }}</Link
                                                >
                                            </template>
                                            <template v-else> — </template>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span
                                                class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700"
                                                >{{
                                                    pago.cuota?.numero_cuota
                                                }}/{{
                                                    pago.cuota?.credito
                                                        ?.cuotas_total
                                                }}</span
                                            >
                                        </td>
                                        <td
                                            class="px-4 py-4 font-bold text-emerald-700"
                                        >
                                            Bs. {{ formatMoney(pago.monto) }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <span
                                                class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"
                                                >{{
                                                    pago.metodo_pago?.nombre ||
                                                    "Efectivo"
                                                }}</span
                                            >
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <Link
                                                :href="
                                                    route('pagos.show', pago.id)
                                                "
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100"
                                                title="Ver detalles"
                                                ><i class="bi bi-eye"></i
                                            ></Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div
                            v-if="pagos.data.length > 0"
                            class="flex flex-col gap-4 border-t border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="text-sm text-slate-600">
                                Mostrando {{ pagos.from }} a {{ pagos.to }} de
                                {{ pagos.total }} pagos
                            </div>
                            <nav>
                                <ul class="flex flex-wrap items-center gap-2">
                                    <li>
                                        <Link
                                            class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                            :href="pagos.prev_page_url || '#'"
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
                                                page === pagos.current_page
                                                    ? 'border-emerald-500 bg-emerald-600 text-white'
                                                    : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                            "
                                            :href="pagos.path + '?page=' + page"
                                            preserve-state
                                            >{{ page }}</Link
                                        >
                                        <span
                                            v-else
                                            class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-3 text-sm font-semibold text-slate-400"
                                            >...</span
                                        >
                                    </li>
                                    <li>
                                        <Link
                                            class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                            :href="pagos.next_page_url || '#'"
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
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    pagos: Object,
    filters: Object,
    estadisticas: Object,
});

const form = ref({
    buscar: props.filters?.buscar || "",
    fecha_desde: props.filters?.fecha_desde || "",
    fecha_hasta: props.filters?.fecha_hasta || "",
});

let buscarTimeout = null;

const buscarConRetraso = () => {
    clearTimeout(buscarTimeout);
    buscarTimeout = setTimeout(() => {
        aplicarFiltros();
    }, 500);
};

const aplicarFiltros = () => {
    router.get(
        route("pagos.index"),
        {
            buscar: form.value.buscar || undefined,
            fecha_desde: form.value.fecha_desde || undefined,
            fecha_hasta: form.value.fecha_hasta || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const limpiarFiltros = () => {
    form.value = {
        buscar: "",
        fecha_desde: "",
        fecha_hasta: "",
    };
    router.get(route("pagos.index"));
};

const paginasVisibles = computed(() => {
    const paginas = [];
    const total = props.pagos.last_page;
    const actual = props.pagos.current_page;
    const rango = 2;

    let inicio = Math.max(1, actual - rango);
    let fin = Math.min(total, actual + rango);

    if (inicio > 1) {
        paginas.push(1);
        if (inicio > 2) paginas.push("...");
    }

    for (let i = inicio; i <= fin; i++) {
        paginas.push(i);
    }

    if (fin < total) {
        if (fin < total - 1) paginas.push("...");
        paginas.push(total);
    }

    return paginas;
});

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    });
};
</script>
