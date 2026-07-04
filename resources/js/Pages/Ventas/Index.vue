<script setup>
import { computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";

const props = defineProps({
    ventas: Object,
    filtro_origen: String,
    rol: String,
});

const isOwnerOrSeller = computed(() => {
    const rol = (props.rol || "").toLowerCase();
    return rol === "propietario" || rol === "vendedor";
});

const currentLayout = computed(() =>
    isOwnerOrSeller.value ? AppLayout : PublicStoreLayout,
);

const layoutProps = computed(() =>
    isOwnerOrSeller.value ? { title: "Ventas" } : {},
);

const filtroOrigen = computed(() => props.filtro_origen || "tienda");

const filteredVentas = computed(() => props.ventas?.data || []);

const cambiarFiltroOrigen = (origen) => {
    router.get(
        route("ventas.index"),
        { origen },
        {
            preserveState: false,
            preserveScroll: false,
        },
    );
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("es-ES", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatTotal = (value) => Number(value || 0).toFixed(2);

const getEstadoClass = (estado) => {
    const styles = {
        pendiente: "bg-amber-100 text-amber-700",
        pagado: "bg-emerald-100 text-emerald-700",
        enviado: "bg-sky-100 text-sky-700",
        anulado: "bg-rose-100 text-rose-700",
    };

    return styles[estado] || "bg-slate-100 text-slate-600";
};
</script>

<template>
    <Head title="Ventas" />

    <component :is="currentLayout" v-bind="layoutProps">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div
                class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4"
            >
                <div>
                    <h2 class="text-2xl font-bold mb-1">Gestión de Ventas</h2>
                    <p class="text-sm text-slate-500">
                        Visualiza el historial de ventas realizadas
                    </p>
                </div>
            </div>

            <div class="bg-white border rounded-lg shadow-sm p-4 mb-6">
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="cambiarFiltroOrigen('tienda')"
                        class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition border"
                        :class="
                            filtroOrigen === 'tienda'
                                ? 'bg-emerald-600 border-emerald-600 text-white'
                                : 'bg-white border-slate-300 text-slate-700 hover:border-emerald-300 hover:text-emerald-700'
                        "
                    >
                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M3 9l9-6 9 6" />
                            <path d="M5 10v10h14V10" />
                        </svg>
                        Tienda
                    </button>

                    <button
                        type="button"
                        @click="cambiarFiltroOrigen('online')"
                        class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition border"
                        :class="
                            filtroOrigen === 'online'
                                ? 'bg-emerald-600 border-emerald-600 text-white'
                                : 'bg-white border-slate-300 text-slate-700 hover:border-emerald-300 hover:text-emerald-700'
                        "
                    >
                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path d="M3 12h18" />
                            <path d="M12 3a15 15 0 010 18" />
                            <path d="M12 3a15 15 0 000 18" />
                        </svg>
                        Online
                    </button>
                </div>
            </div>

            <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600 font-semibold"
                            >
                                <th class="px-6 py-4">Número</th>
                                <th class="px-6 py-4">Fecha</th>
                                <th class="px-6 py-4">Cliente</th>
                                <th class="px-6 py-4">Método Pago</th>
                                <th class="px-6 py-4">Tipo de Pago</th>
                                <th class="px-6 py-4">Tipo de Venta</th>
                                <th
                                    v-if="filtroOrigen === 'online'"
                                    class="px-6 py-4"
                                >
                                    Dirección de Entrega
                                </th>
                                <th class="px-6 py-4 text-right">Total</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredVentas.length === 0">
                                <td
                                    :colspan="filtroOrigen === 'online' ? 9 : 8"
                                    class="px-6 py-10 text-center text-slate-500"
                                >
                                    No se encontraron ventas
                                </td>
                            </tr>

                            <tr
                                v-for="venta in filteredVentas"
                                :key="venta.id"
                                class="border-b border-slate-100 last:border-0 hover:bg-slate-50/50 transition"
                            >
                                <td
                                    class="px-6 py-4 font-semibold text-slate-900"
                                >
                                    #{{ venta.id }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ formatDate(venta.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{ venta.user?.nombre || "N/A" }}
                                </td>
                                <td class="px-6 py-4 text-slate-700">
                                    {{
                                        venta.metodo_pago?.nombre ||
                                        venta.metodoPago?.nombre ||
                                        "N/A"
                                    }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            venta.tipo_pago === 'credito'
                                                ? 'bg-sky-100 text-sky-700'
                                                : 'bg-emerald-100 text-emerald-700'
                                        "
                                    >
                                        {{
                                            venta.tipo_pago === "credito"
                                                ? "A Crédito"
                                                : "Al Contado"
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            venta.tipo_venta === 'mayorista'
                                                ? 'bg-violet-100 text-violet-700'
                                                : 'bg-emerald-100 text-emerald-700'
                                        "
                                    >
                                        {{
                                            venta.tipo_venta === "mayorista"
                                                ? "Mayorista"
                                                : "Minorista"
                                        }}
                                    </span>
                                </td>
                                <td
                                    v-if="filtroOrigen === 'online'"
                                    class="px-6 py-4 text-slate-600"
                                >
                                    {{ venta.direccion_entrega || "N/A" }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-bold text-emerald-700"
                                >
                                    Bs. {{ formatTotal(venta.total) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <template
                                        v-if="
                                            filtroOrigen === 'tienda' &&
                                            venta.estado === 'pagado' &&
                                            venta.tipo_pago === 'credito'
                                        "
                                    >
                                        <span
                                            v-if="venta.cuotas_pendientes > 0"
                                            class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700"
                                        >
                                            Pagos en proceso
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"
                                        >
                                            Pagado
                                        </span>
                                    </template>
                                    <template v-else>
                                        <span
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                            :class="
                                                getEstadoClass(venta.estado)
                                            "
                                        >
                                            {{ venta.estado }}
                                        </span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <Link
                                            :href="
                                                route('ventas.show', venta.id)
                                            "
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:border-emerald-200 hover:text-emerald-700 hover:bg-emerald-50"
                                            title="Ver detalles"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            <span class="sr-only">Ver</span>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="ventas.links && ventas.links.length > 3"
                    class="border-t border-slate-100 px-6 py-4"
                >
                    <nav class="flex justify-center">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white p-1.5 shadow-sm"
                        >
                            <template
                                v-for="(link, index) in ventas.links"
                                :key="index"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    preserve-state
                                    v-html="link.label"
                                    class="rounded-md px-3 py-2 text-sm font-medium transition"
                                    :class="
                                        link.active
                                            ? 'bg-emerald-600 text-white'
                                            : 'text-slate-600 hover:bg-slate-50'
                                    "
                                />
                                <span
                                    v-else
                                    v-html="link.label"
                                    class="rounded-md px-3 py-2 text-sm font-medium text-slate-300"
                                />
                            </template>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </component>
</template>
