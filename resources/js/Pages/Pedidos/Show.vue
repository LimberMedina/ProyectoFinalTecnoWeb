<template>
    <AppLayout title="Detalle del Pedido">
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
                            Pedidos
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Detalle del pedido #{{ pedido.id }}
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Información completa del pedido.
                        </p>
                    </div>
                </div>

                <section
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                            >
                                Resumen
                            </p>
                            <h2 class="mt-2 text-xl font-black text-slate-900">
                                Información general
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
                                        >N° pedido:</span
                                    >
                                    #{{ pedido.id }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Fecha:</span
                                    >
                                    {{ formatearFecha(pedido.created_at) }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Estado:</span
                                    >
                                    <span
                                        class="ml-2 inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                                        :class="
                                            pedido.tipo_pago === 'credito' &&
                                            pedido.credito &&
                                            pedido.credito.estado ===
                                                'pendiente'
                                                ? 'bg-amber-100 text-amber-700'
                                                : getBadgeClass(pedido.estado)
                                        "
                                    >
                                        {{
                                            pedido.tipo_pago === "credito" &&
                                            pedido.credito &&
                                            pedido.credito.estado ===
                                                "pendiente"
                                                ? `Crédito (${pedido.credito.cuotas.filter((c) => c.estado === "pagada").length}/${pedido.credito.cuotas.length})`
                                                : pedido.estado.toUpperCase()
                                        }}
                                    </span>
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Tipo de pago:</span
                                    >
                                    <span
                                        class="ml-2 inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                                        :class="
                                            pedido.tipo_pago === 'credito'
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-emerald-100 text-emerald-700'
                                        "
                                    >
                                        {{
                                            pedido.tipo_pago === "credito"
                                                ? "A crédito"
                                                : "Al contado"
                                        }}
                                    </span>
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Método de pago:</span
                                    >
                                    {{
                                        pedido.metodo_pago?.nombre ||
                                        pedido.metodoPago?.nombre ||
                                        "N/A"
                                    }}
                                </p>
                                <p v-if="pedido.observaciones">
                                    <span class="font-semibold text-slate-900"
                                        >Observaciones:</span
                                    >
                                    {{ pedido.observaciones }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4"
                        >
                            <div class="space-y-3 text-sm text-slate-700">
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Cliente:</span
                                    >
                                    {{ pedido.user?.nombre || "N/A" }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Email:</span
                                    >
                                    {{ pedido.user?.email || "N/A" }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Teléfono:</span
                                    >
                                    {{ pedido.user?.telefono || "N/A" }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Dirección:</span
                                    >
                                    {{ pedido.user?.direccion || "N/A" }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    v-if="
                        pedido.estado === 'pendiente' &&
                        (qrCuota || pedido.pago_facil_qr_image)
                    "
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                            >
                                Pago
                            </p>
                            <h2 class="mt-2 text-xl font-black text-slate-900">
                                Escanea el QR para pagar
                            </h2>
                        </div>
                        <i class="bi bi-qr-code text-2xl text-emerald-600"></i>
                    </div>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4"
                        >
                            <QRPayment
                                v-if="qrCuota"
                                :qr-image="qrCuota.qr_image"
                                :transaction-id="qrCuota.transaction_id"
                                :monto="qrCuota.monto"
                                :descripcion="`Pago de la primera cuota del pedido #${pedido.numero_venta}`"
                                :status="qrCuota.status || 'pending'"
                                :venta-id="pedido.id"
                                :verification-url="
                                    qrCuota.pago_id
                                        ? route('pagos.verificar-estado-pago', {
                                              pagoId: qrCuota.pago_id,
                                          })
                                        : null
                                "
                                :auto-check="true"
                                :check-interval="5000"
                            />
                            <QRPayment
                                v-else
                                :qr-image="pedido.pago_facil_qr_image"
                                :transaction-id="
                                    pedido.pago_facil_transaction_id
                                "
                                :monto="pedido.total"
                                :descripcion="`Pedido #${pedido.numero_venta}`"
                                :status="pedido.pago_facil_status || 'pending'"
                                :venta-id="pedido.id"
                                :auto-check="true"
                                :check-interval="5000"
                            />
                        </div>

                        <div
                            class="rounded-[1.5rem] border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-800"
                        >
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Instrucciones:</strong> Escanea el código QR
                            con tu app bancaria para completar el pago.
                            <p v-if="qrCuota" class="mt-2">
                                Este QR corresponde a la primera cuota. Una vez
                                pagada, se registrará como cuota pagada en el
                                crédito.
                            </p>
                            <p v-else class="mt-2">
                                Una vez confirmado el pago, el pedido pasará
                                automáticamente a estado "Pagado".
                            </p>
                        </div>
                    </div>
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
                                Productos del pedido
                            </h2>
                        </div>
                        <i class="bi bi-box-seam text-2xl text-emerald-600"></i>
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
                                        <th class="px-4 py-4">Producto</th>
                                        <th class="px-4 py-4">Categoría</th>
                                        <th class="px-4 py-4 text-end">
                                            Precio unit.
                                        </th>
                                        <th class="px-4 py-4 text-center">
                                            Cantidad
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
                                        v-for="detalle in pedido.detalles"
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
                                                    detalle.producto?.nombre ||
                                                    "Producto no disponible"
                                                }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                {{
                                                    detalle.variante?.producto
                                                        ?.descripcion ||
                                                    detalle.producto
                                                        ?.descripcion ||
                                                    ""
                                                }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-4 text-slate-600">
                                            {{
                                                detalle.variante?.producto
                                                    ?.categoria?.nombre || "N/A"
                                            }}
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
                                            class="px-4 py-4 text-center text-slate-700"
                                        >
                                            {{ detalle.cantidad }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-end text-slate-700"
                                        >
                                            {{
                                                formatearMoneda(
                                                    detalle.descuento *
                                                        detalle.cantidad,
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
                                            colspan="5"
                                            class="px-4 py-4 text-end font-bold text-slate-700"
                                        >
                                            TOTAL:
                                        </td>
                                        <td
                                            class="px-4 py-4 text-end font-black text-emerald-700"
                                        >
                                            {{ formatearMoneda(pedido.total) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </section>

                <section
                    v-if="
                        pedido.estado === 'pendiente' ||
                        (pedido.estado === 'pagado' &&
                            pedido.origen === 'online')
                    "
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                            >
                                Acciones
                            </p>
                            <h2 class="mt-2 text-xl font-black text-slate-900">
                                Acciones del pedido
                            </h2>
                        </div>
                        <i class="bi bi-gear text-2xl text-emerald-600"></i>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button
                            v-if="pedido.estado === 'pendiente'"
                            @click="confirmarPedido"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                        >
                            <i class="bi bi-check-circle"></i>
                            Confirmar pedido
                        </button>

                        <button
                            v-if="
                                pedido.estado === 'pagado' &&
                                pedido.origen === 'online'
                            "
                            @click="marcarEnviado"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-200 transition hover:-translate-y-0.5 hover:bg-sky-700"
                        >
                            <i class="bi bi-truck"></i>
                            Marcar como enviado
                        </button>

                        <button
                            v-if="pedido.estado === 'pendiente'"
                            @click="cancelarPedido"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700"
                        >
                            <i class="bi bi-x-circle"></i>
                            Cancelar pedido
                        </button>

                        <Link
                            v-if="pedido.estado === 'pendiente'"
                            :href="route('pedidos.edit', pedido.id)"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-100"
                        >
                            <i class="bi bi-pencil"></i>
                            Editar pedido
                        </Link>

                        <Link
                            :href="route('pedidos.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <i class="bi bi-arrow-left"></i>
                            Volver a pedidos
                        </Link>

                        <button
                            v-if="
                                pedido.estado === 'pendiente' &&
                                (
                                    pedido.metodo_pago?.nombre ||
                                    pedido.metodoPago?.nombre ||
                                    ''
                                )
                                    .toUpperCase()
                                    .includes('QR') &&
                                pedido.pago_facil_transaction_id
                            "
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-700 transition hover:bg-sky-100 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="verificandoPago"
                            @click="verificarPagoQr"
                        >
                            <span v-if="verificandoPago">
                                <span
                                    class="mr-2 inline-block h-4 w-4 animate-spin rounded-full border-2 border-sky-700 border-t-transparent"
                                ></span>
                                Verificando...
                            </span>
                            <span v-else>
                                <i class="bi bi-arrow-repeat"></i>
                                Verificar pago QR
                            </span>
                        </button>
                    </div>

                    <div
                        v-if="pedido.tipo_pago === 'credito'"
                        class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                    >
                        <i class="bi bi-info-circle me-2"></i>
                        Al confirmar este pedido se generará automáticamente un
                        crédito.
                    </div>
                </section>

                <section
                    v-else
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        class="rounded-2xl px-4 py-3"
                        :class="
                            pedido.tipo_pago === 'credito' &&
                            pedido.credito &&
                            pedido.credito.estado === 'pendiente'
                                ? 'bg-sky-50 text-sky-800'
                                : pedido.estado === 'pagado' ||
                                    pedido.estado === 'enviado' ||
                                    pedido.estado === 'completada'
                                  ? 'bg-emerald-50 text-emerald-800'
                                  : 'bg-amber-50 text-amber-800'
                        "
                    >
                        <i
                            :class="
                                pedido.tipo_pago === 'credito' &&
                                pedido.credito &&
                                pedido.credito.estado === 'pendiente'
                                    ? 'bi bi-clock-history'
                                    : pedido.estado === 'pagado' ||
                                        pedido.estado === 'enviado' ||
                                        pedido.estado === 'completada'
                                      ? 'bi bi-check-circle'
                                      : 'bi bi-exclamation-triangle'
                            "
                            class="me-2"
                        ></i>
                        <strong>
                            {{
                                pedido.tipo_pago === "credito" &&
                                pedido.credito &&
                                pedido.credito.estado === "pendiente"
                                    ? `Crédito en curso (${pedido.credito.cuotas.filter((c) => c.estado === "pagada").length}/${pedido.credito.cuotas.length} cuotas pagadas)`
                                    : pedido.estado === "pagado"
                                      ? "Pedido pagado"
                                      : pedido.estado === "enviado"
                                        ? "Pedido enviado"
                                        : pedido.estado === "completada"
                                          ? "Pedido confirmado"
                                          : pedido.estado === "anulado"
                                            ? "Pedido cancelado"
                                            : "Pedido procesado"
                            }}
                        </strong>
                        -
                        {{
                            pedido.tipo_pago === "credito" &&
                            pedido.credito &&
                            pedido.credito.estado === "pendiente"
                                ? "El pedido fue entregado pero el crédito sigue pendiente de pago."
                                : "Este pedido ya fue procesado y no se puede modificar."
                        }}
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <Link
                            :href="route('pedidos.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <i class="bi bi-arrow-left"></i>
                            Volver a pedidos
                        </Link>

                        <Link
                            v-if="
                                pedido.tipo_pago === 'credito' && pedido.credito
                            "
                            :href="route('creditos.show', pedido.credito.id)"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                        >
                            <i class="bi bi-credit-card"></i>
                            Ver detalle del crédito
                        </Link>
                    </div>
                </section>
            </div>
        </div>

        <CreditoModal
            :show="mostrarModalCredito"
            :total="pedido.total"
            :cuotas-iniciales="3"
            @close="mostrarModalCredito = false"
            @confirmar="confirmarConCuotas"
        />
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import CreditoModal from "@/Components/CreditoModal.vue";
import QRPayment from "@/Components/QRPayment.vue";
import { Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    pedido: {
        type: Object,
        required: true,
    },
    qrCuota: {
        type: Object,
        default: null,
    },
});

const mostrarModalCredito = ref(false);
const verificandoPago = ref(false);

const formatearMoneda = (valor) => {
    const numero = Number(valor || 0);

    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(numero);
};

const formatearFecha = (fecha) => {
    if (!fecha) {
        return "N/A";
    }

    return new Intl.DateTimeFormat("es-BO", {
        dateStyle: "short",
        timeStyle: "short",
    }).format(new Date(fecha));
};

const getBadgeClass = (estado) => {
    const clases = {
        pendiente: "bg-amber-100 text-amber-700",
        pagado: "bg-emerald-100 text-emerald-700",
        anulado: "bg-rose-100 text-rose-700",
        vencido: "bg-rose-100 text-rose-700",
    };

    return clases[estado] || "bg-slate-100 text-slate-600";
};

const confirmarPedido = () => {
    if (props.pedido.tipo_pago === "credito") {
        mostrarModalCredito.value = true;
        return;
    }

    router.post(
        route("pedidos.confirmar", props.pedido.id),
        {},
        {
            preserveScroll: true,
        },
    );
};

const confirmarConCuotas = (cuotas) => {
    mostrarModalCredito.value = false;

    router.post(
        route("pedidos.confirmar", props.pedido.id),
        {
            cuotas,
        },
        {
            preserveScroll: true,
        },
    );
};

const marcarEnviado = () => {
    router.post(
        route("pedidos.marcar-enviado", props.pedido.id),
        {},
        {
            preserveScroll: true,
        },
    );
};

const cancelarPedido = () => {
    if (!confirm("¿Estás seguro de cancelar este pedido?")) {
        return;
    }

    router.delete(route("pedidos.destroy", props.pedido.id), {
        preserveScroll: true,
    });
};

const verificarPagoQr = async () => {
    if (!props.pedido.pago_facil_transaction_id) {
        return;
    }

    verificandoPago.value = true;

    router.post(
        route("pedidos.verificar-pago", props.pedido.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                verificandoPago.value = false;
            },
        },
    );
};
</script>
