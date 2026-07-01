<template>
    <component :is="currentLayout" v-bind="layoutProps">
        <Head title="Confirmación de Compra" />

        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
            <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
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
                            Compras
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Compra confirmada
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Tu pedido fue procesado exitosamente.
                        </p>
                    </div>
                </div>

                <section
                    v-if="venta.pago_facil_qr_image && !isPagoConfirmado"
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                            >
                                Pago QR
                            </p>
                            <h2 class="mt-2 text-xl font-black text-slate-900">
                                Escanea para completar el pago
                            </h2>
                        </div>
                        <i class="bi bi-qr-code text-2xl text-emerald-600"></i>
                    </div>

                    <QRPayment
                        :qr-image="venta.pago_facil_qr_image"
                        :transaction-id="venta.pago_facil_transaction_id"
                        :monto="venta.total"
                        :status="venta.pago_facil_status || 'pending'"
                        :show-simulate-button="false"
                        :venta-id="venta.id"
                        :auto-check="true"
                        :redirect-url="detallePedidoUrl"
                    />
                </section>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                            >
                                Detalle
                            </p>
                            <h2 class="mt-2 text-xl font-black text-slate-900">
                                Información de la venta
                            </h2>
                        </div>
                        <i
                            class="bi bi-info-circle text-2xl text-emerald-600"
                        ></i>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4"
                        >
                            <div class="space-y-3 text-sm text-slate-700">
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Fecha:</span
                                    >
                                    {{
                                        new Date(
                                            venta.created_at,
                                        ).toLocaleDateString("es-ES")
                                    }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Método de pago:</span
                                    >
                                    {{
                                        venta.metodoPago?.nombre ||
                                        venta.metodo_pago?.nombre ||
                                        "-"
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4"
                        >
                            <div class="space-y-3 text-sm text-slate-700">
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Estado:</span
                                    >
                                    <span
                                        :class="
                                            getEstadoBadgeClass(venta.estado)
                                        "
                                        class="ml-2 inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                                    >
                                        {{ venta.estado }}
                                    </span>
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Total:</span
                                    >
                                    <span class="font-black text-emerald-700">{{
                                        formatearMoneda(venta.total)
                                    }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                    >
                        <div
                            class="border-b border-slate-100 bg-slate-50 px-4 py-4"
                        >
                            <h3
                                class="flex items-center gap-2 text-base font-black text-slate-900"
                            >
                                <i class="bi bi-bag text-emerald-600"></i>
                                Productos
                            </h3>
                        </div>

                        <div class="overflow-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead
                                    class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                >
                                    <tr>
                                        <th class="px-4 py-4">Producto</th>
                                        <th class="px-4 py-4 text-center">
                                            Cantidad
                                        </th>
                                        <th class="px-4 py-4 text-end">
                                            Precio
                                        </th>
                                        <th class="px-4 py-4 text-end">
                                            Descuento
                                        </th>
                                        <th class="px-4 py-4 text-end">
                                            Subtotal
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="detalle in venta.detalles"
                                        :key="detalle.id"
                                        class="border-t border-slate-100"
                                    >
                                        <td class="px-4 py-4">
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{
                                                    detalle.variante?.producto
                                                        ?.nombre ||
                                                    detalle.producto?.nombre
                                                }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                {{
                                                    detalle.variante?.talla ||
                                                    "-"
                                                }}
                                                /
                                                {{
                                                    detalle.variante?.color ||
                                                    "-"
                                                }}
                                            </p>
                                        </td>
                                        <td
                                            class="px-4 py-4 text-center text-slate-700"
                                        >
                                            {{ detalle.cantidad }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-end text-slate-700"
                                        >
                                            {{
                                                formatearMoneda(
                                                    detalle.precio_unitario,
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-end text-slate-700"
                                        >
                                            {{
                                                formatearMoneda(
                                                    detalle.descuento,
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-end font-bold text-emerald-700"
                                        >
                                            {{
                                                formatearMoneda(
                                                    detalle.subtotal,
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-slate-50">
                                    <tr>
                                        <td
                                            colspan="4"
                                            class="px-4 py-4 text-end font-bold text-slate-700"
                                        >
                                            Total:
                                        </td>
                                        <td
                                            class="px-4 py-4 text-end font-black text-emerald-700"
                                        >
                                            {{ formatearMoneda(venta.total) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </section>

                <section
                    v-if="venta.credito"
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                            >
                                Crédito
                            </p>
                            <h2 class="mt-2 text-xl font-black text-slate-900">
                                Información del crédito
                            </h2>
                        </div>
                        <i
                            class="bi bi-credit-card text-2xl text-emerald-600"
                        ></i>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4 text-sm text-slate-700"
                        >
                            <p class="font-semibold text-slate-900">
                                Monto del crédito
                            </p>
                            <p class="mt-2 text-lg font-black text-emerald-700">
                                {{
                                    formatearMoneda(venta.credito.monto_credito)
                                }}
                            </p>
                        </div>
                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4 text-sm text-slate-700"
                        >
                            <p class="font-semibold text-slate-900">Interés</p>
                            <p class="mt-2 text-lg font-black text-emerald-700">
                                {{ formatearMoneda(venta.credito.interes) }}
                            </p>
                        </div>
                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4 text-sm text-slate-700"
                        >
                            <p class="font-semibold text-slate-900">
                                Total cuotas
                            </p>
                            <p class="mt-2 text-lg font-black text-emerald-700">
                                {{ venta.credito.cuotas_total }} cuotas
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4 text-sm text-slate-700"
                        >
                            <p class="font-semibold text-slate-900">
                                Fecha otorgamiento
                            </p>
                            <p class="mt-2">
                                {{
                                    new Date(
                                        venta.credito.fecha_otorgamiento,
                                    ).toLocaleDateString("es-ES")
                                }}
                            </p>
                        </div>
                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4 text-sm text-slate-700"
                        >
                            <p class="font-semibold text-slate-900">
                                Fecha vencimiento final
                            </p>
                            <p class="mt-2">
                                {{
                                    new Date(
                                        venta.credito.fecha_vencimiento,
                                    ).toLocaleDateString("es-ES")
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                    >
                        <div
                            class="border-b border-slate-100 bg-slate-50 px-4 py-4"
                        >
                            <h3 class="text-base font-black text-slate-900">
                                Plan de cuotas
                            </h3>
                        </div>
                        <div class="overflow-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead
                                    class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                >
                                    <tr>
                                        <th class="px-4 py-4">#</th>
                                        <th class="px-4 py-4">
                                            Fecha vencimiento
                                        </th>
                                        <th class="px-4 py-4 text-end">
                                            Monto cuota
                                        </th>
                                        <th class="px-4 py-4 text-center">
                                            Estado
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="cuota in venta.credito.cuotas"
                                        :key="cuota.id"
                                        class="border-t border-slate-100"
                                    >
                                        <td class="px-4 py-4 text-slate-700">
                                            {{ cuota.numero_cuota }}
                                        </td>
                                        <td class="px-4 py-4 text-slate-700">
                                            {{
                                                new Date(
                                                    cuota.fecha_vencimiento,
                                                ).toLocaleDateString("es-ES")
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-end text-slate-700"
                                        >
                                            {{ formatearMoneda(cuota.monto) }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span
                                                :class="
                                                    getEstadoBadgeClass(
                                                        cuota.estado,
                                                    )
                                                "
                                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
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

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)] text-center"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:justify-center"
                    >
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                        >
                            <i class="bi bi-house-door"></i>
                            Ir al dashboard
                        </Link>
                        <Link
                            :href="route('carritos.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <i class="bi bi-cart"></i>
                            Continuar comprando
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </component>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import QRPayment from "@/Components/QRPayment.vue";

const props = defineProps({
    venta: Object,
});

const page = usePage();

const isOwnerOrSeller = computed(() => {
    const roleId = Number(page.props.auth?.user?.role?.id || 0);
    const roleName = (page.props.auth?.user?.role?.nombre || "").toLowerCase();

    return (
        roleId === 1 ||
        roleId === 2 ||
        roleName === "propietario" ||
        roleName === "vendedor"
    );
});

const currentLayout = computed(() =>
    isOwnerOrSeller.value ? AppLayout : PublicStoreLayout,
);

const layoutProps = computed(() =>
    isOwnerOrSeller.value ? { title: "Confirmación de Compra" } : {},
);

const isPagoConfirmado = computed(() => {
    const estado = String(props.venta?.estado || "").toLowerCase();
    const pagoEstado = String(
        props.venta?.pago_facil_status || "",
    ).toLowerCase();

    return estado === "pagado" || pagoEstado === "completed";
});

const detallePedidoUrl = computed(
    () => `/mis-pedidos/${props.venta.id}/detalles`,
);

onMounted(() => {
    if (isPagoConfirmado.value && !isOwnerOrSeller.value) {
        router.visit(detallePedidoUrl.value, {
            replace: true,
            preserveScroll: false,
        });
    }
});

// Función para obtener clases de badge según estado
const getEstadoBadgeClass = (estado) => {
    const clases = {
        pendiente: "bg-amber-100 text-amber-700",
        pagado: "bg-emerald-100 text-emerald-700",
        anulado: "bg-rose-100 text-rose-700",
        vencido: "bg-rose-100 text-rose-700",
    };
    return clases[estado] || "bg-slate-100 text-slate-600";
};

const formatearMoneda = (valor) => {
    const numero = Number(valor ?? 0);

    return new Intl.NumberFormat("es-BO", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number.isFinite(numero) ? numero : 0);
};
</script>
