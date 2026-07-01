<template>
    <AppLayout title="Reporte de Mora">
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
                            Reportes
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Reporte de créditos en mora
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Listado de créditos con días de mora y cuotas
                            vencidas.
                        </p>
                    </div>
                </div>

                <section
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        v-if="creditosMora.length === 0"
                        class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800"
                    >
                        No hay créditos en mora.
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
                                        <th class="px-4 py-4"># crédito</th>
                                        <th class="px-4 py-4">Cliente</th>
                                        <th class="px-4 py-4">Venta</th>
                                        <th class="px-4 py-4 text-end">
                                            Monto pendiente
                                        </th>
                                        <th class="px-4 py-4 text-center">
                                            Días mora
                                        </th>
                                        <th class="px-4 py-4">
                                            Cuotas vencidas
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="credito in creditosMora"
                                        :key="credito.id"
                                        class="border-t border-slate-100 align-top"
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
                                                class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700"
                                                >{{
                                                    credito.dias_mora
                                                }}
                                                días</span
                                            >
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="space-y-2">
                                                <div
                                                    v-for="c in credito.cuotas"
                                                    :key="c.id"
                                                    class="rounded-2xl border border-slate-100 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                                                >
                                                    <span
                                                        class="font-semibold text-slate-900"
                                                        >Cuota #{{
                                                            c.numero_cuota
                                                        }}</span
                                                    >
                                                    -
                                                    {{
                                                        formatearFecha(
                                                            c.fecha_vencimiento,
                                                        )
                                                    }}
                                                    -
                                                    {{
                                                        formatearMoneda(
                                                            c.monto_pendiente,
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6">
                        <Link
                            :href="route('creditos.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <i class="bi bi-arrow-left"></i>
                            Volver a créditos
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    creditosMora: Array,
});

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor || 0);
};

const formatearFecha = (fecha) => {
    if (!fecha) return "-";
    return new Date(fecha).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};
</script>
