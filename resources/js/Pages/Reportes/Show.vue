<script setup>
import { computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    tipo: String,
    titulo: String,
    datos: [Array, Object],
    parametros: Object,
});

const formatPrice = (price) => `Bs. ${parseFloat(price).toFixed(2)}`;
const formatDate = (date) => new Date(date).toLocaleDateString("es-ES");
const formatMetodoPago = (venta) => {
    if (!venta) return "N/A";

    const método =
        venta.metodo_pago ||
        venta.nombre ||
        venta.method ||
        venta.metodoPago?.nombre ||
        venta?.metodo?.nombre ||
        venta.metodoPago ||
        venta.metodo;

    if (!método) return "N/A";
    if (typeof método === "string") return método;
    if (typeof método === "object") {
        return método.nombre || método.name || "N/A";
    }
    return String(método);
};

const descargarPDF = () => {
    const params = new URLSearchParams(props.parametros);
    window.open(
        route("reportes.pdf", props.tipo) + "?" + params.toString(),
        "_blank",
    );
};

const hayDatos = computed(() => {
    return Array.isArray(props.datos)
        ? props.datos.length > 0
        : Object.keys(props.datos).length > 0;
});
</script>

<template>
    <AppLayout :title="titulo">
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
                            {{ titulo }}
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Período: {{ parametros.fecha_inicio }} -
                            {{ parametros.fecha_fin }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a
                            :href="route('reportes.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <i class="bi bi-arrow-left"></i>
                            Volver
                        </a>
                        <button
                            @click="descargarPDF"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700"
                            :disabled="!hayDatos"
                        >
                            <i class="bi bi-file-pdf"></i>
                            Descargar PDF
                        </button>
                    </div>
                </div>

                <section
                    v-if="!hayDatos"
                    class="rounded-[2rem] border border-white bg-white/90 px-6 py-14 text-center shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <i class="bi bi-info-circle text-5xl text-sky-300"></i>
                    <h2 class="mt-4 text-2xl font-black text-slate-900">
                        Sin datos
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        No se encontraron resultados para los parámetros
                        seleccionados.
                    </p>
                </section>

                <template v-else>
                    <section
                        v-if="tipo === 'ventas-fecha'"
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
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
                                            <th class="px-4 py-4">#</th>
                                            <th class="px-4 py-4">Número</th>
                                            <th class="px-4 py-4">Fecha</th>
                                            <th class="px-4 py-4">Cliente</th>
                                            <th class="px-4 py-4">Vendedor</th>
                                            <th class="px-4 py-4">Método</th>
                                            <th class="px-4 py-4 text-end">
                                                Total
                                            </th>
                                            <th class="px-4 py-4">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(venta, index) in datos"
                                            :key="venta.id"
                                            class="border-t border-slate-100"
                                        >
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{ index + 1 }}
                                            </td>
                                            <td
                                                class="px-4 py-4 font-semibold text-slate-900"
                                            >
                                                {{
                                                    venta.numero_venta || "N/A"
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{
                                                    formatDate(venta.created_at)
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{ venta.user?.name || "N/A" }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{
                                                    venta.vendedor?.name ||
                                                    "Sistema"
                                                }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700"
                                                    >{{
                                                        formatMetodoPago(venta)
                                                    }}</span
                                                >
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end font-bold text-emerald-700"
                                            >
                                                {{ formatPrice(venta.total) }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"
                                                    >{{ venta.estado }}</span
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section
                        v-else-if="tipo === 'ventas-metodo'"
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
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
                                            <th class="px-4 py-4">
                                                Método de pago
                                            </th>
                                            <th class="px-4 py-4 text-center">
                                                Cantidad
                                            </th>
                                            <th class="px-4 py-4 text-end">
                                                Monto total
                                            </th>
                                            <th class="px-4 py-4 text-end">
                                                Promedio
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="metodo in datos"
                                            :key="
                                                metodo.metodo_pago ||
                                                metodo.nombre ||
                                                metodo.id
                                            "
                                            class="border-t border-slate-100"
                                        >
                                            <td
                                                class="px-4 py-4 font-semibold text-slate-900"
                                            >
                                                {{ formatMetodoPago(metodo) }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-center text-slate-700"
                                            >
                                                {{ metodo.cantidad }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end font-bold text-emerald-700"
                                            >
                                                {{
                                                    formatPrice(
                                                        metodo.monto_total,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end text-slate-700"
                                            >
                                                {{
                                                    formatPrice(
                                                        metodo.monto_total /
                                                            metodo.cantidad,
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section
                        v-else-if="tipo === 'creditos-estado'"
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
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
                                            <th class="px-4 py-4">Estado</th>
                                            <th class="px-4 py-4 text-center">
                                                Cantidad
                                            </th>
                                            <th class="px-4 py-4 text-end">
                                                Monto total
                                            </th>
                                            <th class="px-4 py-4 text-end">
                                                Saldo pendiente
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="credito in datos"
                                            :key="credito.estado"
                                            class="border-t border-slate-100"
                                        >
                                            <td
                                                class="px-4 py-4 font-semibold text-slate-900"
                                            >
                                                {{ credito.estado }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-center text-slate-700"
                                            >
                                                {{ credito.cantidad }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end font-bold text-emerald-700"
                                            >
                                                {{
                                                    formatPrice(
                                                        credito.monto_total,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end font-bold text-rose-700"
                                            >
                                                {{
                                                    formatPrice(
                                                        credito.monto_pendiente,
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section
                        v-else-if="tipo === 'productos-vendidos'"
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
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
                                            <th class="px-4 py-4">#</th>
                                            <th class="px-4 py-4">Producto</th>
                                            <th class="px-4 py-4">Código</th>
                                            <th class="px-4 py-4 text-center">
                                                Stock
                                            </th>
                                            <th class="px-4 py-4 text-center">
                                                Vendido
                                            </th>
                                            <th class="px-4 py-4 text-end">
                                                Ingresos
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(producto, index) in datos"
                                            :key="producto.codigo"
                                            class="border-t border-slate-100"
                                        >
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{ index + 1 }}
                                            </td>
                                            <td
                                                class="px-4 py-4 font-semibold text-slate-900"
                                            >
                                                {{ producto.nombre }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{ producto.codigo }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-center text-slate-700"
                                            >
                                                {{ producto.stock }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-center text-slate-700"
                                            >
                                                {{ producto.total_vendido }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end font-bold text-emerald-700"
                                            >
                                                {{
                                                    formatPrice(
                                                        producto.ingresos,
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section
                        v-else-if="tipo === 'clientes-top'"
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
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
                                            <th class="px-4 py-4">#</th>
                                            <th class="px-4 py-4">Cliente</th>
                                            <th class="px-4 py-4">Email</th>
                                            <th class="px-4 py-4 text-center">
                                                Compras
                                            </th>
                                            <th class="px-4 py-4 text-end">
                                                Monto total
                                            </th>
                                            <th class="px-4 py-4 text-end">
                                                Promedio
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(cliente, index) in datos"
                                            :key="cliente.email"
                                            class="border-t border-slate-100"
                                        >
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{ index + 1 }}
                                            </td>
                                            <td
                                                class="px-4 py-4 font-semibold text-slate-900"
                                            >
                                                {{ cliente.name }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{ cliente.email }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-center text-slate-700"
                                            >
                                                {{ cliente.total_compras }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end font-bold text-emerald-700"
                                            >
                                                {{
                                                    formatPrice(
                                                        cliente.monto_total,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end text-slate-700"
                                            >
                                                {{
                                                    formatPrice(
                                                        cliente.monto_total /
                                                            cliente.total_compras,
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section
                        v-else-if="tipo === 'inventario-critico'"
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-4 flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800"
                        >
                            <i class="bi bi-exclamation-triangle"></i>
                            Productos con stock bajo o agotado
                        </div>

                        <div
                            class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                        >
                            <div class="overflow-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead
                                        class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                    >
                                        <tr>
                                            <th class="px-4 py-4">#</th>
                                            <th class="px-4 py-4">Producto</th>
                                            <th class="px-4 py-4">Talla</th>
                                            <th class="px-4 py-4">Color</th>
                                            <th class="px-4 py-4">SKU</th>
                                            <th class="px-4 py-4 text-center">
                                                Stock
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(producto, index) in datos"
                                            :key="producto.id"
                                            class="border-t border-slate-100"
                                            :class="{
                                                'bg-rose-50/60':
                                                    producto.stock_actual === 0,
                                            }"
                                        >
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{ index + 1 }}
                                            </td>
                                            <td
                                                class="px-4 py-4 font-semibold text-slate-900"
                                            >
                                                {{
                                                    producto.producto?.nombre ||
                                                    "N/A"
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{ producto.talla || "N/A" }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{ producto.color || "N/A" }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{ producto.sku || "N/A" }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-center text-slate-700"
                                            >
                                                {{ producto.stock_actual }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
