<template>
    <div
        class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
    >
        <PublicStoreHeader />

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
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
                        Mis pagos
                    </h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Consulta tus cuotas pendientes, ventas pagadas e
                        historial de pagos.
                    </p>
                </div>
            </div>

            <!-- Cuotas pendientes: con datos -->
            <section
                v-if="cuotasPendientes.length > 0"
                class="mb-6 overflow-hidden rounded-[2rem] border border-white bg-white/90 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <div
                    class="flex items-center justify-between gap-3 border-b border-amber-100 bg-amber-50/60 px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-amber-700"
                        >
                            Atención
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-900">
                            Cuotas pendientes
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-amber-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 9v4m0 4h.01M10.29 3.86l-8.42 14.5A1.5 1.5 0 003.18 21h17.64a1.5 1.5 0 001.31-2.64l-8.42-14.5a1.5 1.5 0 00-2.62 0z"
                            />
                        </svg>
                    </div>
                </div>

                <div class="p-6">
                    <div
                        class="mb-5 flex items-center justify-between rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3"
                    >
                        <span class="text-sm font-bold text-amber-800"
                            >Total pendiente</span
                        >
                        <span class="text-lg font-black text-amber-700"
                            >Bs. {{ formatMoney(totalPendiente) }}</span
                        >
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th
                                        class="pb-3 pr-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        N° Cuota
                                    </th>
                                    <th
                                        class="pb-3 pr-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Venta
                                    </th>
                                    <th
                                        class="pb-3 pr-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Vencimiento
                                    </th>
                                    <th
                                        class="pb-3 pr-4 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Monto
                                    </th>
                                    <th
                                        class="pb-3 pr-4 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Pagado
                                    </th>
                                    <th
                                        class="pb-3 pr-4 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Saldo
                                    </th>
                                    <th
                                        class="pb-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="cuota in cuotasPendientes"
                                    :key="cuota.id"
                                    class="border-b border-slate-50 transition hover:bg-amber-50/40"
                                >
                                    <td
                                        class="py-3.5 pr-4 font-black text-slate-800"
                                    >
                                        #{{ cuota.numero_cuota }}
                                    </td>
                                    <td
                                        class="py-3.5 pr-4 font-semibold text-slate-700"
                                    >
                                        #{{
                                            cuota.credito?.venta?.numero_venta
                                        }}
                                    </td>
                                    <td class="py-3.5 pr-4 text-slate-500">
                                        {{
                                            new Date(
                                                cuota.fecha_vencimiento,
                                            ).toLocaleDateString("es-ES")
                                        }}
                                    </td>
                                    <td
                                        class="py-3.5 pr-4 text-right font-semibold text-slate-700"
                                    >
                                        Bs. {{ formatMoney(cuota.monto) }}
                                    </td>
                                    <td
                                        class="py-3.5 pr-4 text-right font-semibold text-emerald-700"
                                    >
                                        Bs.
                                        {{
                                            formatMoney(
                                                cuota.pagos?.reduce(
                                                    (sum, p) =>
                                                        sum +
                                                        parseFloat(p.monto),
                                                    0,
                                                ) || 0,
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="py-3.5 pr-4 text-right font-black text-rose-600"
                                    >
                                        Bs.
                                        {{
                                            formatMoney(
                                                parseFloat(cuota.monto) -
                                                    (cuota.pagos?.reduce(
                                                        (sum, p) =>
                                                            sum +
                                                            parseFloat(p.monto),
                                                        0,
                                                    ) || 0),
                                            )
                                        }}
                                    </td>
                                    <td class="py-3.5 text-center">
                                        <span
                                            :class="getBadgeClass(cuota.estado)"
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                        >
                                            {{ cuota.estado }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Cuotas pendientes: vacío -->
            <section
                v-else
                class="mb-6 rounded-[2rem] border border-white bg-white/90 p-8 text-center shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 text-emerald-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>
                <p class="text-base font-black text-slate-700">
                    No tienes cuotas pendientes
                </p>
                <p class="mt-1 text-sm text-slate-400">
                    Cuando tengas créditos activos con cuotas, aparecerán aquí.
                </p>
            </section>

            <!-- Ventas pagadas: con datos -->
            <section
                v-if="ventasPagadas && ventasPagadas.length > 0"
                class="mb-6 overflow-hidden rounded-[2rem] border border-white bg-white/90 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <div
                    class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                        >
                            Contado
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-900">
                            Ventas pagadas
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
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                </div>

                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th
                                        class="pb-3 pr-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Fecha
                                    </th>
                                    <th
                                        class="pb-3 pr-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        N° Venta
                                    </th>
                                    <th
                                        class="pb-3 pr-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Método
                                    </th>
                                    <th
                                        class="pb-3 pr-4 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Monto
                                    </th>
                                    <th
                                        class="pb-3 pr-4 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        class="pb-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                    >
                                        Acción
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="venta in ventasPagadas"
                                    :key="venta.id"
                                    class="border-b border-slate-50 transition hover:bg-emerald-50/30"
                                >
                                    <td class="py-3.5 pr-4 text-slate-500">
                                        {{
                                            new Date(
                                                venta.updated_at,
                                            ).toLocaleDateString("es-ES", {
                                                day: "2-digit",
                                                month: "short",
                                                year: "numeric",
                                                hour: "2-digit",
                                                minute: "2-digit",
                                            })
                                        }}
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <Link
                                            :href="
                                                route(
                                                    'mis-pedidos.show',
                                                    venta.id,
                                                )
                                            "
                                            class="font-semibold text-emerald-600 transition hover:text-emerald-700"
                                        >
                                            #{{ venta.numero_venta }}
                                        </Link>
                                    </td>
                                    <td class="py-3.5 pr-4">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800"
                                            >
                                                {{
                                                    venta.metodo_pago?.nombre ||
                                                    "Contado"
                                                }}
                                            </span>
                                            <span
                                                v-if="
                                                    venta.pago_facil_transaction_id
                                                "
                                                class="inline-flex items-center gap-1 text-xs text-slate-400"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3.5 w-3.5"
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
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="py-3.5 pr-4 text-right font-black text-emerald-700"
                                    >
                                        Bs. {{ formatMoney(venta.total) }}
                                    </td>
                                    <td class="py-3.5 pr-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                            Pagado
                                        </span>
                                    </td>
                                    <td class="py-3.5 text-center">
                                        <Link
                                            :href="
                                                route(
                                                    'mis-pedidos.show',
                                                    venta.id,
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
                                            Ver detalle
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Ventas pagadas: vacío -->
            <section
                v-if="!(ventasPagadas && ventasPagadas.length > 0)"
                class="mb-6 rounded-[2rem] border border-white bg-white/90 p-8 text-center shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
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
                            d="M5 3l14 9-14 9V3z"
                        />
                    </svg>
                </div>
                <p class="text-base font-black text-slate-700">
                    Aún no hay ventas pagadas
                </p>
                <p class="mt-1 text-sm text-slate-400">
                    Cuando realices compras al contado, aparecerán aquí.
                </p>
            </section>

            <!-- Historial de pagos -->
            <section
                class="overflow-hidden rounded-[2rem] border border-white bg-white/90 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
            >
                <div
                    class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-600"
                        >
                            Historial
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-900">
                            Historial de pagos
                        </h2>
                    </div>
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-indigo-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M8 7V3m8 4V3M5 11h14M5 7h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"
                            />
                        </svg>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Con datos -->
                    <div v-if="pagos.data.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th
                                            class="pb-3 pr-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Fecha
                                        </th>
                                        <th
                                            class="pb-3 pr-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            N° Cuota
                                        </th>
                                        <th
                                            class="pb-3 pr-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Venta
                                        </th>
                                        <th
                                            class="pb-3 pr-4 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Método
                                        </th>
                                        <th
                                            class="pb-3 pr-4 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Monto
                                        </th>
                                        <th
                                            class="pb-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Observaciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="pago in pagos.data"
                                        :key="pago.id"
                                        class="border-b border-slate-50 transition hover:bg-slate-50/60"
                                    >
                                        <td class="py-3.5 pr-4 text-slate-500">
                                            {{
                                                new Date(
                                                    pago.fecha,
                                                ).toLocaleDateString("es-ES", {
                                                    day: "2-digit",
                                                    month: "short",
                                                    year: "numeric",
                                                })
                                            }}
                                        </td>
                                        <td
                                            class="py-3.5 pr-4 font-black text-slate-800"
                                        >
                                            #{{ pago.cuota?.numero_cuota }}
                                        </td>
                                        <td class="py-3.5 pr-4">
                                            <Link
                                                :href="
                                                    route(
                                                        'mis-pedidos.show',
                                                        pago.cuota?.credito
                                                            ?.venta?.id,
                                                    )
                                                "
                                                class="font-semibold text-emerald-600 transition hover:text-emerald-700"
                                            >
                                                #{{
                                                    pago.cuota?.credito?.venta
                                                        ?.numero_venta
                                                }}
                                            </Link>
                                        </td>
                                        <td class="py-3.5 pr-4">
                                            <span
                                                class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800"
                                            >
                                                {{
                                                    pago.metodo_pago?.nombre ||
                                                    "Efectivo"
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="py-3.5 pr-4 text-right font-black text-slate-800"
                                        >
                                            Bs. {{ formatMoney(pago.monto) }}
                                        </td>
                                        <td class="py-3.5 text-slate-400">
                                            {{ pago.observaciones || "—" }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <nav
                            v-if="pagos.last_page > 1"
                            class="mt-5 border-t border-slate-100 pt-5"
                        >
                            <ul
                                class="flex flex-wrap items-center justify-center gap-2"
                            >
                                <li
                                    v-for="(link, index) in pagos.links"
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

                    <!-- Vacío -->
                    <div
                        v-else
                        class="rounded-2xl border border-slate-100 bg-slate-50 p-10 text-center"
                    >
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
                                    d="M4 7h16M4 12h16M4 17h16"
                                />
                            </svg>
                        </div>
                        <p class="text-base font-black text-slate-700">
                            No has realizado ningún pago todavía
                        </p>
                        <p class="mt-1 text-sm text-slate-400">
                            Aquí se mostrará tu historial cuando empieces a
                            pagar cuotas o compras.
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import PublicStoreHeader from "@/Components/PublicStoreHeader.vue";

const props = defineProps({
    pagos: Object,
    ventasPagadas: Array,
    cuotasPendientes: Array,
});

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);

const getBadgeClass = (estado) => {
    const badges = {
        pendiente: "bg-amber-100 text-amber-800",
        vencida: "bg-rose-100 text-rose-800",
        pagada: "bg-emerald-100 text-emerald-800",
    };
    return badges[estado] || "bg-slate-100 text-slate-700";
};

const totalPendiente = computed(() => {
    return props.cuotasPendientes.reduce((sum, cuota) => {
        const montoPagado =
            cuota.pagos?.reduce((s, p) => s + parseFloat(p.monto), 0) || 0;
        return sum + (parseFloat(cuota.monto) - montoPagado);
    }, 0);
});
</script>
