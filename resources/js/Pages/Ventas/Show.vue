<script setup>
import { computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";

const props = defineProps({
    venta: Object,
    qrData: Object,
    rol: String,
});

const isOwnerOrSeller = computed(() => {
    const role = (props.rol || "").toLowerCase();
    return role === "propietario" || role === "vendedor";
});

const isOwner = computed(
    () => (props.rol || "").toLowerCase() === "propietario",
);

const currentLayout = computed(() =>
    isOwnerOrSeller.value ? AppLayout : PublicStoreLayout,
);

const layoutProps = computed(() =>
    isOwnerOrSeller.value ? { title: "Boleta de Venta" } : {},
);

const formatPrice = (price) =>
    `Bs. ${Number.parseFloat(price || 0).toFixed(2)}`;

const formatDate = (date) => {
    if (!date) return "—";

    return new Date(date).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const descargarPDF = () =>
    window.open(route("invoices.pdf", props.venta.id), "_blank");
const descargarTicket = () =>
    window.open(route("invoices.ticket", props.venta.id), "_blank");

const formatLot = (value) => `Bs. ${Number.parseFloat(value || 0).toFixed(2)}`;
</script>

<template>
    <component :is="currentLayout" v-bind="layoutProps">
        <Head :title="`Boleta: ${venta.numero_venta}`" />

        <div class="mx-auto max-w-4xl px-4 py-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">Boleta de Venta</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ venta.numero_venta }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button
                        @click="descargarPDF"
                        class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                    >
                        Descargar PDF
                    </button>
                    <button
                        @click="descargarTicket"
                        class="inline-flex items-center gap-2 rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50"
                    >
                        Imprimir Ticket
                    </button>
                </div>
            </div>

            <div class="mb-6 rounded-lg border bg-white shadow-sm">
                <div class="border-b px-6 py-4">
                    <h3 class="font-semibold">Información General</h3>
                </div>
                <div class="grid grid-cols-1 gap-4 px-6 py-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <div>
                            <strong>Cliente:</strong> {{ venta.user?.name }}
                        </div>
                        <div>
                            <strong>Email:</strong> {{ venta.user?.email }}
                        </div>
                        <div v-if="venta.user?.ci">
                            <strong>CI/NIT:</strong> {{ venta.user.ci }}
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <strong>Fecha:</strong>
                            {{ formatDate(venta.created_at) }}
                        </div>
                        <div v-if="venta.vendedor">
                            <strong>Vendedor:</strong>
                            {{ venta.vendedor.nombre }}
                            {{ venta.vendedor.apellidos }}
                        </div>
                        <div>
                            <strong>Tipo de Pago:</strong>
                            {{
                                venta.tipo_pago === "credito"
                                    ? "A Crédito"
                                    : "Al Contado"
                            }}
                        </div>
                        <div><strong>Estado:</strong> {{ venta.estado }}</div>
                    </div>
                </div>
            </div>

            <div class="mb-6 rounded-lg border bg-white shadow-sm">
                <div class="border-b px-6 py-4">
                    <h3 class="font-semibold">Detalle de Productos</h3>
                </div>
                <div class="overflow-x-auto p-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b text-left text-xs font-semibold uppercase text-slate-600"
                            >
                                <th class="py-2">Producto</th>
                                <th class="py-2">Código</th>
                                <th class="py-2 text-center">Cantidad</th>
                                <th class="py-2 text-right">P. Unitario</th>
                                <th class="py-2 text-center">Desc.</th>
                                <th class="py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="detalle in venta.detalles || []"
                                :key="detalle.id"
                                class="border-b last:border-0"
                            >
                                <td class="py-3">
                                    <div class="font-medium text-slate-900">
                                        {{ detalle.producto?.nombre }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ detalle.variante?.talla || "-" }} /
                                        {{ detalle.variante?.color || "-" }}
                                        <span
                                            v-if="detalle.variante?.sku"
                                            class="ml-2 inline-flex rounded-full bg-slate-100 px-2 py-0.5 font-mono text-[10px] text-slate-600"
                                        >
                                            {{ detalle.variante.sku }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    {{
                                        detalle.variante?.sku ||
                                        detalle.producto?.codigo
                                    }}
                                </td>
                                <td class="py-3 text-center">
                                    {{ detalle.cantidad }}
                                </td>
                                <td class="py-3 text-right">
                                    {{ formatPrice(detalle.precio_unitario) }}
                                </td>
                                <td class="py-3 text-center">
                                    {{
                                        detalle.descuento > 0
                                            ? `-${formatPrice(detalle.descuento)}`
                                            : "-"
                                    }}
                                </td>
                                <td class="py-3 text-right font-semibold">
                                    {{ formatPrice(detalle.subtotal) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                v-if="
                    isOwner &&
                    venta.detalles?.some(
                        (detalle) => detalle.salidasLote?.length,
                    )
                "
                class="mb-6 rounded-lg border bg-white shadow-sm"
            >
                <div class="border-b px-6 py-4">
                    <h3 class="font-semibold">Lotes utilizados</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Solo visible para propietario y vendedor.
                    </p>
                </div>

                <div class="space-y-4 p-4">
                    <div
                        v-for="detalle in venta.detalles || []"
                        :key="`lotes-${detalle.id}`"
                        v-if="detalle.salidasLote && detalle.salidasLote.length"
                        class="rounded-xl border border-slate-100 bg-slate-50"
                    >
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="font-semibold text-slate-900">
                                {{ detalle.producto?.nombre }}
                                <span class="text-slate-400">·</span>
                                {{ detalle.variante?.talla || "-" }} /
                                {{ detalle.variante?.color || "-" }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Cantidad vendida: {{ detalle.cantidad }}
                            </p>
                        </div>

                        <div class="overflow-x-auto p-4">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr
                                        class="border-b text-left text-xs font-semibold uppercase text-slate-600"
                                    >
                                        <th class="py-2">Lote</th>
                                        <th class="py-2">Fecha ingreso</th>
                                        <th class="py-2 text-center">
                                            Cantidad
                                        </th>
                                        <th class="py-2 text-right">
                                            Costo unit.
                                        </th>
                                        <th class="py-2 text-right">
                                            Costo total
                                        </th>
                                        <th class="py-2">Variante origen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="salida in detalle.salidasLote"
                                        :key="salida.id"
                                        class="border-b last:border-0"
                                    >
                                        <td
                                            class="py-3 font-mono text-xs text-slate-700"
                                        >
                                            #{{
                                                salida.lote?.id ||
                                                salida.id_lote
                                            }}
                                        </td>
                                        <td class="py-3 text-slate-700">
                                            {{
                                                salida.lote?.fecha_ingreso
                                                    ? new Date(
                                                          salida.lote
                                                              .fecha_ingreso,
                                                      ).toLocaleDateString(
                                                          "es-ES",
                                                      )
                                                    : "—"
                                            }}
                                        </td>
                                        <td
                                            class="py-3 text-center text-slate-700"
                                        >
                                            {{ salida.cantidad }}
                                        </td>
                                        <td
                                            class="py-3 text-right text-slate-700"
                                        >
                                            {{
                                                formatLot(
                                                    salida.costo_unitario_aplicado,
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="py-3 text-right font-semibold text-slate-900"
                                        >
                                            {{ formatLot(salida.costo_total) }}
                                        </td>
                                        <td class="py-3 text-slate-700">
                                            {{
                                                salida.lote?.variante?.producto
                                                    ?.nombre ||
                                                detalle.producto?.nombre
                                            }}
                                            ·
                                            {{
                                                salida.lote?.variante?.talla ||
                                                detalle.variante?.talla ||
                                                "-"
                                            }}
                                            /
                                            {{
                                                salida.lote?.variante?.color ||
                                                detalle.variante?.color ||
                                                "-"
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-lg border bg-white p-4 shadow-sm">
                    <h6 class="mb-2 font-medium">Código de Verificación</h6>
                    <p class="mb-2 text-xs text-slate-500">
                        UUID de transacción:
                    </p>
                    <div class="break-words font-mono text-xs text-slate-700">
                        {{ qrData?.uuid || "N/A" }}
                    </div>
                </div>

                <div class="rounded-lg border bg-white p-4 shadow-sm">
                    <h6 class="mb-2 font-medium">Resumen de Pago</h6>
                    <div class="flex justify-between py-1">
                        <span>Subtotal:</span
                        ><strong>{{
                            formatPrice(venta.subtotal || venta.total)
                        }}</strong>
                    </div>
                    <div class="flex justify-between py-1">
                        <span>Descuento:</span
                        ><strong class="text-rose-600">{{
                            formatPrice(venta.descuento || 0)
                        }}</strong>
                    </div>
                    <hr class="my-3" />
                    <div class="flex items-center justify-between">
                        <strong class="text-lg">TOTAL:</strong
                        ><strong class="text-2xl text-emerald-700">{{
                            formatPrice(venta.total)
                        }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>
