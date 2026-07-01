<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    tab: String,
    search: String,
    stockVariantes: Object,
    lotes: Object,
    movimientos: Object,
    salidas: Object,
    alertas: Object,
    tecnicas: Array,
    summary: Object,
});

const activeTab = computed(() => props.tab || "stock");

const tabs = [
    { key: "stock", label: "Stock por Variante" },
    { key: "lotes", label: "Lotes" },
    { key: "salidas", label: "Salidas Lote" },
    { key: "movimientos", label: "Movimientos" },
    { key: "alertas", label: "Alertas de Stock" },
];

const setTab = (tab) => {
    router.get(
        route("inventarios.index"),
        { tab, search: props.search || "" },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};
</script>

<template>
    <Head title="Inventario" />

    <AppLayout title="Inventario">
        <div class="p-6 space-y-6">
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">
                        Inventario
                    </h1>
                    <p class="text-sm text-slate-500">
                        Stock por variante, FIFO, lotes, movimientos y alertas.
                    </p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="text-xs uppercase tracking-wide text-slate-500">
                        Variantes
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">
                        {{ summary?.variantes ?? 0 }}
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="text-xs uppercase tracking-wide text-slate-500">
                        Lotes
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">
                        {{ summary?.lotes ?? 0 }}
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="text-xs uppercase tracking-wide text-slate-500">
                        Movimientos
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">
                        {{ summary?.movimientos ?? 0 }}
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="text-xs uppercase tracking-wide text-slate-500">
                        Salidas lote
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">
                        {{ summary?.salidas ?? 0 }}
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="text-xs uppercase tracking-wide text-slate-500">
                        Alertas
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">
                        {{ summary?.alertas ?? 0 }}
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="text-xs uppercase tracking-wide text-slate-500">
                        FIFO
                    </div>
                    <div class="mt-2 text-2xl font-bold text-slate-900">
                        Activo
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2">
                <button
                    v-for="tabItem in tabs"
                    :key="tabItem.key"
                    type="button"
                    class="rounded-t-xl px-4 py-2 text-sm font-semibold transition"
                    :class="
                        activeTab === tabItem.key
                            ? 'bg-cyan-600 text-white'
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                    "
                    @click="setTab(tabItem.key)"
                >
                    {{ tabItem.label }}
                </button>
            </div>

            <section v-if="activeTab === 'stock'" class="space-y-4">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Producto</th>
                                <th class="px-4 py-3 text-left">Talla</th>
                                <th class="px-4 py-3 text-left">Color</th>
                                <th class="px-4 py-3 text-left">SKU</th>
                                <th class="px-4 py-3 text-left">
                                    Stock actual
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Stock mínimo
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Punto reorden
                                </th>
                                <th class="px-4 py-3 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="variante in stockVariantes.data"
                                :key="variante.id"
                            >
                                <td
                                    class="px-4 py-3 font-medium text-slate-900"
                                >
                                    {{ variante.producto?.nombre }}
                                </td>
                                <td class="px-4 py-3">{{ variante.talla }}</td>
                                <td class="px-4 py-3">{{ variante.color }}</td>
                                <td class="px-4 py-3">
                                    {{ variante.sku || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ variante.stock_actual }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ variante.stock_minimo }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ variante.punto_reorden }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-semibold"
                                        :class="
                                            variante.stock_actual <=
                                            variante.stock_minimo
                                                ? 'bg-red-100 text-red-700'
                                                : 'bg-emerald-100 text-emerald-700'
                                        "
                                    >
                                        {{
                                            variante.stock_actual <=
                                            variante.stock_minimo
                                                ? "Crítico"
                                                : "Normal"
                                        }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-else-if="activeTab === 'lotes'" class="space-y-4">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Lote</th>
                                <th class="px-4 py-3 text-left">Producto</th>
                                <th class="px-4 py-3 text-left">Talla</th>
                                <th class="px-4 py-3 text-left">Color</th>
                                <th class="px-4 py-3 text-left">
                                    Fecha ingreso
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Cantidad inicial
                                </th>
                                <th class="px-4 py-3 text-left">Disponible</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="lote in lotes.data" :key="lote.id">
                                <td class="px-4 py-3">{{ lote.id }}</td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-900"
                                >
                                    {{ lote.variante?.producto?.nombre }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ lote.variante?.talla }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ lote.variante?.color }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ lote.fecha_ingreso }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ lote.cantidad_inicial }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ lote.cantidad_disponible }}
                                </td>
                                <td class="px-4 py-3">{{ lote.estado }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-else-if="activeTab === 'movimientos'" class="space-y-4">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Fecha</th>
                                <th class="px-4 py-3 text-left">Producto</th>
                                <th class="px-4 py-3 text-left">Talla</th>
                                <th class="px-4 py-3 text-left">Color</th>
                                <th class="px-4 py-3 text-left">Movimiento</th>
                                <th class="px-4 py-3 text-left">Cantidad</th>
                                <th class="px-4 py-3 text-left">
                                    Stock anterior
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Stock resultante
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="movimiento in movimientos.data"
                                :key="movimiento.id"
                            >
                                <td class="px-4 py-3">
                                    {{ movimiento.fecha }}
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-900"
                                >
                                    {{ movimiento.variante?.producto?.nombre }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ movimiento.variante?.talla }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ movimiento.variante?.color }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ movimiento.tipo_movimiento }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ movimiento.cantidad }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ movimiento.stock_anterior }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ movimiento.stock_resultante }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-else-if="activeTab === 'salidas'" class="space-y-4">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Salida</th>
                                <th class="px-4 py-3 text-left">Venta</th>
                                <th class="px-4 py-3 text-left">Producto</th>
                                <th class="px-4 py-3 text-left">Talla</th>
                                <th class="px-4 py-3 text-left">Color</th>
                                <th class="px-4 py-3 text-left">Lote</th>
                                <th class="px-4 py-3 text-left">Cantidad</th>
                                <th class="px-4 py-3 text-left">
                                    Costo unitario
                                </th>
                                <th class="px-4 py-3 text-left">Costo total</th>
                                <th class="px-4 py-3 text-left">
                                    Fecha registro
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="salida in salidas.data" :key="salida.id">
                                <td class="px-4 py-3">{{ salida.id }}</td>
                                <td class="px-4 py-3">
                                    {{
                                        salida.detalleVenta?.venta
                                            ?.numero_venta || "-"
                                    }}
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-900"
                                >
                                    {{
                                        salida.lote?.variante?.producto
                                            ?.nombre || "-"
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ salida.lote?.variante?.talla || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ salida.lote?.variante?.color || "-" }}
                                </td>
                                <td class="px-4 py-3">{{ salida.id_lote }}</td>
                                <td class="px-4 py-3">{{ salida.cantidad }}</td>
                                <td class="px-4 py-3">
                                    Bs.
                                    {{
                                        Number(
                                            salida.costo_unitario_aplicado || 0,
                                        ).toFixed(2)
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    Bs.
                                    {{
                                        Number(salida.costo_total || 0).toFixed(
                                            2,
                                        )
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    {{
                                        salida.created_at
                                            ? new Date(
                                                  salida.created_at,
                                              ).toLocaleString("es-ES")
                                            : "-"
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="salidas.links && salidas.links.length > 3"
                    class="flex justify-center"
                >
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white p-1.5 shadow-sm"
                    >
                        <template
                            v-for="(link, index) in salidas.links"
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
                                        ? 'bg-cyan-600 text-white'
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
                </div>
            </section>

            <section v-else-if="activeTab === 'alertas'" class="space-y-4">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Producto</th>
                                <th class="px-4 py-3 text-left">Talla</th>
                                <th class="px-4 py-3 text-left">Color</th>
                                <th class="px-4 py-3 text-left">
                                    Stock actual
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Stock mínimo
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Punto reorden
                                </th>
                                <th class="px-4 py-3 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="variante in alertas.data"
                                :key="variante.id"
                            >
                                <td
                                    class="px-4 py-3 font-medium text-slate-900"
                                >
                                    {{ variante.producto?.nombre }}
                                </td>
                                <td class="px-4 py-3">{{ variante.talla }}</td>
                                <td class="px-4 py-3">{{ variante.color }}</td>
                                <td class="px-4 py-3">
                                    {{ variante.stock_actual }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ variante.stock_minimo }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ variante.punto_reorden }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700"
                                        >Reponer</span
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-else class="grid gap-4 lg:grid-cols-2">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <h2 class="text-lg font-bold text-slate-900">
                        Configuración de inventario
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">
                        FIFO es automático. Los lotes se crean desde compras y
                        las salidas se registran desde ventas.
                    </p>
                    <div class="mt-4 space-y-3 text-sm text-slate-700">
                        <div
                            class="flex items-center justify-between border-b border-slate-100 pb-2"
                        >
                            <span>FIFO</span>
                            <span class="font-semibold text-emerald-700"
                                >Activo</span
                            >
                        </div>
                        <div
                            class="flex items-center justify-between border-b border-slate-100 pb-2"
                        >
                            <span>Stock mínimo</span>
                            <span class="font-semibold">Por variante</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Punto de reorden</span>
                            <span class="font-semibold">Por variante</span>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <h2 class="text-lg font-bold text-slate-900">
                        Técnicas de inventario
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Las técnicas base se muestran aquí sin ocupar el
                        sidebar.
                    </p>
                    <div class="mt-4 space-y-3">
                        <div
                            v-for="tecnica in tecnicas"
                            :key="tecnica.id"
                            class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3"
                        >
                            <div>
                                <div class="font-medium text-slate-900">
                                    {{ tecnica.nombre }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{
                                        tecnica.descripcion || "Sin descripción"
                                    }}
                                </div>
                            </div>
                            <span
                                class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700"
                                >Activa</span
                            >
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
