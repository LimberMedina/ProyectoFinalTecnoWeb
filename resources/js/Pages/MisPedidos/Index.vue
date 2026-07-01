<script setup>
import { computed } from "vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    pedidos: Object,
    filtro: {
        type: String,
        default: "pendiente",
    },
});

// Cambiar filtro de pestañas
const cambiarFiltro = (estado) => {
    router.get(
        route("mis-pedidos.index"),
        { estado },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Confirmar recepción del pedido
const confirmarRecepcion = (pedido) => {
    if (
        confirm(
            `¿Confirmas que recibiste el pedido #${pedido.numero_venta} conforme?`,
        )
    ) {
        router.post(
            route("mis-pedidos.confirmar-recepcion", { id: pedido.id }),
        );
    }
};

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);

const getBadgeClass = (estado) => {
    const badges = {
        pendiente: "bg-yellow-100 text-yellow-800",
        completada: "bg-emerald-100 text-emerald-800",
        pagado: "bg-emerald-100 text-emerald-800",
        enviado: "bg-sky-100 text-sky-800",
        entregado: "bg-emerald-100 text-emerald-800",
        anulada: "bg-rose-100 text-rose-800",
        anulado: "bg-rose-100 text-rose-800",
        cancelada: "bg-slate-100 text-slate-700",
    };
    return badges[estado] || "bg-slate-100 text-slate-700";
};

const getMetodoPagoLabel = (metodo) => {
    const labels = {
        contado: "Contado",
        credito: "Crédito",
    };
    return labels[metodo] || metodo;
};

// Calcular páginas visibles
const visiblePages = computed(() => {
    const current = props.pedidos.current_page;
    const last = props.pedidos.last_page;
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

<template>
    <PublicStoreLayout>
        <FlashNotification />

        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
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
                            Mis pedidos
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Mis pedidos
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Consulta el historial de tus pedidos realizados.
                        </p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="mb-6 flex flex-wrap gap-2">
                    <button
                        @click="cambiarFiltro('pendiente')"
                        :class="
                            filtro === 'pendiente'
                                ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                        "
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 8v4l3 3"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M21 12A9 9 0 113 12a9 9 0 0018 0z"
                            />
                        </svg>
                        Pendientes
                    </button>

                    <button
                        @click="cambiarFiltro('pagado')"
                        :class="
                            filtro === 'pagado'
                                ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                        "
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 12l2 2 4-4"
                            />
                        </svg>
                        Pagados
                    </button>

                    <button
                        @click="cambiarFiltro('enviado')"
                        :class="
                            filtro === 'enviado'
                                ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                        "
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M3 7h2l.4 2M7 13h10l4-8H5.4"
                            />
                        </svg>
                        Enviados
                    </button>

                    <button
                        @click="cambiarFiltro('entregado')"
                        :class="
                            filtro === 'entregado'
                                ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                                : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                        "
                        class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 12l2 2 4-4"
                            />
                        </svg>
                        Entregados
                    </button>
                </div>

                <!-- Lista de pedidos -->
                <div
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-4 py-4">N° Pedido</th>
                                    <th class="px-4 py-4">Fecha</th>
                                    <th class="px-4 py-4">Método de Pago</th>
                                    <th class="px-4 py-4 text-right">Total</th>
                                    <th class="px-4 py-4 text-center">
                                        Estado
                                    </th>
                                    <th
                                        class="px-4 py-4 text-center"
                                        style="width: 160px"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-if="pedidos.data.length === 0"
                                    class="border-t border-slate-100"
                                >
                                    <td
                                        colspan="6"
                                        class="px-4 py-12 text-center text-slate-500"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="w-6 h-6 inline-block mr-2 text-slate-400"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"
                                            />
                                        </svg>
                                        No tienes pedidos
                                        {{
                                            filtro === "pendiente"
                                                ? "pendientes"
                                                : filtro === "pagado"
                                                  ? "pagados"
                                                  : filtro === "enviado"
                                                    ? "enviados"
                                                    : "entregados"
                                        }}.
                                        <div class="mt-4">
                                            <Link
                                                :href="route('dashboard')"
                                                class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4 mr-2"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M3 7h18M3 7l3 12h12l3-12"
                                                    />
                                                </svg>
                                                Ver productos disponibles
                                            </Link>
                                        </div>
                                    </td>
                                </tr>

                                <tr
                                    v-for="pedido in pedidos.data"
                                    :key="pedido.id"
                                    class="border-t border-slate-100 hover:bg-slate-50/70"
                                >
                                    <td
                                        class="px-4 py-4 font-semibold text-slate-900"
                                    >
                                        #{{ pedido.numero_venta }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-600">
                                        {{
                                            new Date(
                                                pedido.created_at,
                                            ).toLocaleDateString("es-ES", {
                                                day: "2-digit",
                                                month: "short",
                                                year: "numeric",
                                            })
                                        }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <span
                                            :class="
                                                (pedido.metodo_pago?.nombre ||
                                                    pedido.metodo_pago) ===
                                                    'Crédito' ||
                                                (pedido.metodo_pago?.nombre ||
                                                    pedido.metodo_pago) ===
                                                    'credito'
                                                    ? 'inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold bg-sky-100 text-sky-800'
                                                    : 'inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold bg-emerald-100 text-emerald-800'
                                            "
                                        >
                                            {{
                                                pedido.metodo_pago?.nombre ||
                                                getMetodoPagoLabel(
                                                    pedido.metodo_pago,
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-4 py-4 text-right font-semibold text-slate-900"
                                    >
                                        Bs. {{ formatMoney(pedido.total) }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            :class="
                                                getBadgeClass(pedido.estado) +
                                                ' inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold'
                                            "
                                            >{{ pedido.estado }}</span
                                        >
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div
                                            class="flex items-center justify-center gap-2"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'mis-pedidos.show',
                                                        pedido.id,
                                                    )
                                                "
                                                class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
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
                                                v-if="
                                                    pedido.estado === 'enviado'
                                                "
                                                @click="
                                                    confirmarRecepcion(pedido)
                                                "
                                                type="button"
                                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M5 13l4 4L19 7"
                                                    />
                                                </svg>
                                                Recibido
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginación -->
                <div
                    v-if="pedidos.links && pedidos.links.length > 3"
                    class="mt-6"
                >
                    <nav>
                        <ul
                            class="flex flex-wrap items-center justify-center gap-2"
                        >
                            <li
                                v-for="(link, index) in pedidos.links"
                                :key="index"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="rounded-xl border px-3 py-2 text-sm font-semibold transition"
                                    :class="
                                        link.active
                                            ? 'border-emerald-600 bg-emerald-600 text-white shadow-lg shadow-emerald-200'
                                            : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                    "
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-400"
                                    v-html="link.label"
                                ></span>
                            </li>
                        </ul>
                    </nav>
                </div>
            </main>
        </div>
    </PublicStoreLayout>
</template>
