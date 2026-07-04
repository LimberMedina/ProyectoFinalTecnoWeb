<script setup>
import { computed, ref } from "vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import { Link, router } from "@inertiajs/vue3";
import QRPayment from "@/Components/QRPayment.vue";
import axios from "axios";
import { showToast } from "@/utils/toast";

const props = defineProps({
    pedido: Object,
    metodosPago: Array,
});

const mostrarModalPago = ref(false);
const metodoPagoSeleccionado = ref(props.pedido.metodo_pago_id || "");
const pagoQRLoading = ref(false);
const noRecibidoLoading = ref(false);

const isMetodoQRSeleccionado = computed(() => {
    const metodo = props.metodosPago?.find(
        (m) => String(m.id) === String(metodoPagoSeleccionado.value),
    );
    return metodo && String(metodo.nombre).toLowerCase().includes("qr");
});

const abrirModalPago = () => {
    metodoPagoSeleccionado.value = props.pedido.metodo_pago_id || "";
    mostrarModalPago.value = true;
};

const cerrarModalPago = () => {
    mostrarModalPago.value = false;
};

const generarQRPedido = async () => {
    if (!metodoPagoSeleccionado.value) {
        showToast("Selecciona un método de pago primero.", "error");
        return;
    }

    pagoQRLoading.value = true;

    try {
        const response = await axios.post(
            route("mis-pedidos.generar-qr", { id: props.pedido.id }),
            {
                metodo_pago_id: metodoPagoSeleccionado.value,
            },
        );

        const data = response.data;

        if (data.success) {
            if (isMetodoQRSeleccionado.value) {
                showToast(
                    "Se generó un nuevo QR. Escanea para completar el pago.",
                    "success",
                );
            } else {
                showToast(
                    data.message || "Método de pago actualizado.",
                    "success",
                );
            }

            cerrarModalPago();
            setTimeout(() => {
                router.reload({ preserveScroll: false });
            }, 600);
        } else {
            showToast(data.message || "No fue posible generar el QR.", "error");
        }
    } catch (error) {
        console.error("Error generando QR del pedido:", error);
        showToast(
            error.response?.data?.message ||
                error.response?.data?.error ||
                "Error al generar el QR del pedido.",
            "error",
        );
    } finally {
        pagoQRLoading.value = false;
    }
};

const formatMoney = (amount) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(parseFloat(amount || 0));

const getBadgeClass = (estado) => {
    const badges = {
        pendiente: "bg-yellow-100 text-yellow-800",
        completada: "bg-emerald-100 text-emerald-800",
        pagado: "bg-emerald-100 text-emerald-800",
        enviado: "bg-sky-100 text-sky-800",
        entregado: "bg-emerald-100 text-emerald-800",
        anulada: "bg-rose-100 text-rose-800",
        cancelada: "bg-slate-100 text-slate-700",
    };
    return badges[estado] || "bg-slate-100 text-slate-700";
};

const totalProductos = computed(() =>
    props.pedido.detalles.reduce((sum, d) => sum + d.cantidad, 0),
);

const confirmarRecepcion = () => {
    if (
        confirm(
            `¿Confirmas que recibiste el pedido #${props.pedido.numero_venta} conforme?`,
        )
    ) {
        router.post(
            route("mis-pedidos.confirmar-recepcion", { id: props.pedido.id }),
        );
    }
};

const marcarNoRecibido = async () => {
    if (
        !confirm(
            `¿Deseas notificar al propietario que no recibiste el pedido #${props.pedido.numero_venta}?`,
        )
    ) {
        return;
    }

    noRecibidoLoading.value = true;

    try {
        const response = await axios.post(
            route("mis-pedidos.no-recibido", { id: props.pedido.id }),
        );

        showToast(
            response.data.message ||
                "Se notificó al propietario que no recibiste el pedido.",
            "success",
        );

        setTimeout(() => {
            router.reload({ preserveScroll: false });
        }, 600);
    } catch (error) {
        console.error("Error al reportar pedido no recibido:", error);
        showToast(
            error.response?.data?.message ||
                "Error al reportar el pedido no recibido.",
            "error",
        );
    } finally {
        noRecibidoLoading.value = false;
    }
};
</script>

<template>
    <PublicStoreLayout>
        <FlashNotification />

        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
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
                            Mis pedidos
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Pedido #{{ pedido.numero_venta }}
                        </h1>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Revisa el estado y detalle de tu pedido.
                        </p>
                    </div>

                    <Link
                        :href="route('mis-pedidos.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                        Volver a Mis Pedidos
                    </Link>
                </div>

                <!-- Grid principal -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <!-- Columna izquierda -->
                    <aside class="space-y-6 lg:col-span-4">
                        <!-- Información general -->
                        <div
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div
                                class="mb-6 flex items-center justify-between gap-3 border-b border-slate-100 pb-5"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                    >
                                        Pedido
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-black text-slate-900"
                                    >
                                        Información general
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
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <dl class="space-y-3 text-sm">
                                <div class="flex items-center justify-between">
                                    <dt class="text-slate-500">Número</dt>
                                    <dd class="font-semibold text-slate-900">
                                        #{{ pedido.numero_venta }}
                                    </dd>
                                </div>

                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <dt class="text-slate-500">Fecha</dt>
                                    <dd
                                        class="text-right font-semibold text-slate-900"
                                    >
                                        {{
                                            new Date(
                                                pedido.created_at,
                                            ).toLocaleDateString("es-ES", {
                                                day: "2-digit",
                                                month: "long",
                                                year: "numeric",
                                                hour: "2-digit",
                                                minute: "2-digit",
                                            })
                                        }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between">
                                    <dt class="text-slate-500">Estado</dt>
                                    <dd>
                                        <span
                                            :class="
                                                getBadgeClass(pedido.estado) +
                                                ' inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold'
                                            "
                                        >
                                            {{ pedido.estado }}
                                        </span>
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between">
                                    <dt class="text-slate-500">
                                        Método de pago
                                    </dt>
                                    <dd>
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                            :class="
                                                pedido.metodo_pago === 'credito'
                                                    ? 'bg-sky-100 text-sky-800'
                                                    : 'bg-emerald-100 text-emerald-800'
                                            "
                                        >
                                            {{
                                                pedido.metodo_pago === "credito"
                                                    ? "Crédito"
                                                    : "Contado"
                                            }}
                                        </span>
                                    </dd>
                                </div>

                                <div
                                    v-if="pedido.vendedor"
                                    class="flex items-center justify-between"
                                >
                                    <dt class="text-slate-500">Atendido por</dt>
                                    <dd class="font-semibold text-slate-900">
                                        {{ pedido.vendedor.nombre }}
                                        {{ pedido.vendedor.apellidos }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Pedido en camino -->
                        <div
                            v-if="pedido.estado === 'enviado'"
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div class="mb-4 flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-sky-600"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.22em] text-sky-700"
                                    >
                                        Envío
                                    </p>
                                    <h2
                                        class="mt-0.5 text-lg font-black text-slate-900"
                                    >
                                        Pedido en camino
                                    </h2>
                                </div>
                            </div>

                            <p class="mb-5 text-sm text-slate-600">
                                Pronto recibirás tu pedido en la dirección
                                indicada.
                            </p>

                            <button
                                @click="confirmarRecepcion"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="noRecibidoLoading"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
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
                                Confirmar recepción conforme
                            </button>
                            <button
                                @click="marcarNoRecibido"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60 mt-3"
                                :disabled="pagoQRLoading || noRecibidoLoading"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M12 8v8m4-4H8"
                                    />
                                </svg>
                                {{
                                    noRecibidoLoading
                                        ? "Reportando..."
                                        : "Pedido no recibido"
                                }}
                            </button>
                        </div>

                        <!-- Pedido entregado -->
                        <div
                            v-if="pedido.estado === 'entregado'"
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)] text-center"
                        >
                            <div
                                class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-7 w-7 text-emerald-600"
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
                            </div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                            >
                                Completado
                            </p>
                            <h2 class="mt-1 text-lg font-black text-slate-900">
                                Pedido entregado
                            </h2>
                            <p class="mt-2 text-sm text-slate-500">
                                ¡Gracias por confirmar la recepción! Tu pedido
                                ha sido entregado exitosamente.
                            </p>
                        </div>

                        <!-- Información de crédito -->
                        <div
                            v-if="pedido.credito"
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div
                                class="mb-6 flex items-center justify-between gap-3 border-b border-slate-100 pb-5"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.22em] text-sky-700"
                                    >
                                        Crédito
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-black text-slate-900"
                                    >
                                        Información de crédito
                                    </h2>
                                </div>
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-sky-600"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <dl class="space-y-3 text-sm">
                                <div class="flex items-center justify-between">
                                    <dt class="text-slate-500">Monto total</dt>
                                    <dd class="font-semibold text-slate-900">
                                        {{
                                            formatMoney(
                                                pedido.credito.monto_total,
                                            )
                                        }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-slate-500">
                                        Saldo pendiente
                                    </dt>
                                    <dd class="font-semibold text-rose-600">
                                        {{
                                            formatMoney(
                                                pedido.credito.monto_pendiente,
                                            )
                                        }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-slate-500">Cuotas</dt>
                                    <dd class="font-semibold text-slate-900">
                                        {{ pedido.credito.numero_cuotas }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-slate-500">Estado</dt>
                                    <dd>
                                        <span
                                            :class="
                                                getBadgeClass(
                                                    pedido.credito.estado,
                                                ) +
                                                ' inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold'
                                            "
                                        >
                                            {{ pedido.credito.estado }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>

                            <!-- Cuotas -->
                            <div
                                v-if="pedido.credito.cuotas"
                                class="mt-5 border-t border-slate-100 pt-5"
                            >
                                <p
                                    class="mb-3 text-xs font-bold uppercase tracking-[0.18em] text-slate-500"
                                >
                                    Detalle de cuotas
                                </p>
                                <ul class="space-y-2">
                                    <li
                                        v-for="cuota in pedido.credito.cuotas"
                                        :key="cuota.id"
                                        class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5 text-sm"
                                    >
                                        <div>
                                            <p
                                                class="font-semibold text-slate-800"
                                            >
                                                Cuota {{ cuota.numero_cuota }}
                                            </p>
                                            <p class="text-xs text-slate-400">
                                                {{
                                                    new Date(
                                                        cuota.fecha_vencimiento,
                                                    ).toLocaleDateString(
                                                        "es-ES",
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="font-black text-slate-900"
                                            >
                                                {{ formatMoney(cuota.monto) }}
                                            </p>
                                            <span
                                                :class="
                                                    getBadgeClass(
                                                        cuota.estado,
                                                    ) +
                                                    ' mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold'
                                                "
                                            >
                                                {{ cuota.estado }}
                                            </span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </aside>

                    <!-- Columna derecha -->
                    <section class="space-y-6 lg:col-span-8">
                        <!-- Tabla de productos -->
                        <div
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div
                                class="mb-6 flex items-center justify-between gap-3 border-b border-slate-100 pb-5"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                    >
                                        Resumen
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-black text-slate-900"
                                    >
                                        Productos
                                        <span
                                            class="ml-2 inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600"
                                        >
                                            {{ totalProductos }} items
                                        </span>
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
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-100">
                                            <th
                                                class="pb-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                            >
                                                Producto
                                            </th>
                                            <th
                                                class="pb-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                            >
                                                Código
                                            </th>
                                            <th
                                                class="pb-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                            >
                                                Cant.
                                            </th>
                                            <th
                                                class="pb-3 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                            >
                                                Precio unit.
                                            </th>
                                            <th
                                                class="pb-3 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                            >
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="detalle in pedido.detalles"
                                            :key="detalle.id"
                                            class="border-b border-slate-50"
                                        >
                                            <td class="py-3.5">
                                                <p
                                                    class="font-semibold text-slate-900"
                                                >
                                                    {{
                                                        detalle.variante
                                                            ?.producto
                                                            ?.nombre ||
                                                        detalle.producto?.nombre
                                                    }}
                                                </p>
                                                <p
                                                    class="mt-0.5 text-xs text-slate-400"
                                                >
                                                    {{
                                                        detalle.variante
                                                            ?.talla || "—"
                                                    }}
                                                    /
                                                    {{
                                                        detalle.variante
                                                            ?.color || "—"
                                                    }}
                                                </p>
                                            </td>
                                            <td class="py-3.5">
                                                <code
                                                    class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-500"
                                                >
                                                    {{
                                                        detalle.variante
                                                            ?.producto
                                                            ?.codigo ||
                                                        detalle.producto?.codigo
                                                    }}
                                                </code>
                                            </td>
                                            <td
                                                class="py-3.5 text-center font-semibold text-slate-700"
                                            >
                                                {{ detalle.cantidad }}
                                            </td>
                                            <td
                                                class="py-3.5 text-right text-slate-600"
                                            >
                                                {{
                                                    formatMoney(
                                                        detalle.precio_unitario,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="py-3.5 text-right font-black text-emerald-700"
                                            >
                                                {{
                                                    formatMoney(
                                                        detalle.subtotal,
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td
                                                colspan="4"
                                                class="pt-4 text-right text-sm font-semibold text-slate-500"
                                            >
                                                Total del pedido
                                            </td>
                                            <td
                                                class="pt-4 text-right text-xl font-black text-emerald-700"
                                            >
                                                {{ formatMoney(pedido.total) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- QR de pago -->
                        <div
                            v-if="pedido.estado === 'pendiente'"
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div
                                class="mb-6 flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                    >
                                        Pago
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-black text-slate-900"
                                    >
                                        Escanea para pagar
                                    </h2>
                                </div>
                                <button
                                    type="button"
                                    @click="abrirModalPago"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                                        />
                                    </svg>
                                    Pagar este pedido
                                </button>
                            </div>

                            <div class="space-y-4">
                                <QRPayment
                                    v-if="pedido.pago_facil_qr_image"
                                    :qr-image="pedido.pago_facil_qr_image"
                                    :transaction-id="
                                        pedido.pago_facil_transaction_id
                                    "
                                    :monto="pedido.total"
                                    :descripcion="`Pedido #${pedido.numero_venta}`"
                                    :status="
                                        pedido.pago_facil_status || 'pending'
                                    "
                                    :venta-id="pedido.id"
                                    :auto-check="true"
                                    :check-interval="5000"
                                />

                                <div
                                    v-else
                                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-700"
                                >
                                    <p class="font-semibold text-slate-900">
                                        No hay QR activo.
                                    </p>
                                    <p class="mt-2 text-slate-600">
                                        Pulsa el botón "Pagar este pedido" para
                                        generar un nuevo QR de PagoFácil.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Estado: pagado o enviado -->
                        <div
                            v-else-if="
                                pedido.estado === 'pagado' ||
                                pedido.estado === 'enviado'
                            "
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div
                                class="flex flex-col items-center gap-3 text-center"
                            >
                                <div
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl"
                                    :class="
                                        pedido.estado === 'enviado'
                                            ? 'bg-sky-50'
                                            : 'bg-emerald-50'
                                    "
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-7 w-7"
                                        :class="
                                            pedido.estado === 'enviado'
                                                ? 'text-sky-600'
                                                : 'text-emerald-600'
                                        "
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
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.22em]"
                                        :class="
                                            pedido.estado === 'enviado'
                                                ? 'text-sky-700'
                                                : 'text-emerald-700'
                                        "
                                    >
                                        {{
                                            pedido.estado === "enviado"
                                                ? "Envío"
                                                : "Pago"
                                        }}
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-black text-slate-900"
                                    >
                                        {{
                                            pedido.estado === "enviado"
                                                ? "¡Tu pedido está en camino!"
                                                : "¡Pago confirmado!"
                                        }}
                                    </h2>
                                    <p class="mt-2 text-sm text-slate-500">
                                        {{
                                            pedido.estado === "enviado"
                                                ? "Pronto recibirás tu pedido en la dirección indicada."
                                                : "Tu pago ha sido confirmado. Pronto tu pedido será enviado."
                                        }}
                                    </p>
                                </div>

                                <div
                                    v-if="pedido.direccion_entrega"
                                    class="mt-2 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-left"
                                >
                                    <p
                                        class="mb-1 text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                    >
                                        Dirección de entrega
                                    </p>
                                    <p class="text-sm text-slate-700">
                                        {{ pedido.direccion_entrega }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <transition name="fade">
                            <div
                                v-if="mostrarModalPago"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6"
                            >
                                <div
                                    class="w-full max-w-2xl rounded-[2rem] bg-white p-6 shadow-[0_24px_80px_-40px_rgba(15,23,42,0.35)]"
                                >
                                    <div
                                        class="mb-6 flex items-start justify-between gap-4"
                                    >
                                        <div>
                                            <p
                                                class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                            >
                                                Pago del pedido
                                            </p>
                                            <h3
                                                class="mt-1 text-xl font-black text-slate-900"
                                            >
                                                Selecciona el método de pago
                                            </h3>
                                            <p
                                                class="mt-1 text-sm text-slate-500"
                                            >
                                                Si eliges QR, se generará un
                                                nuevo código de PagoFácil para
                                                este pedido.
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                            @click="cerrarModalPago"
                                            :disabled="pagoQRLoading"
                                            aria-label="Cerrar modal"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-5 w-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="space-y-5">
                                        <div>
                                            <label
                                                class="mb-2 block text-sm font-bold text-slate-700"
                                            >
                                                Método de pago
                                            </label>
                                            <select
                                                v-model="metodoPagoSeleccionado"
                                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                            >
                                                <option value="" disabled>
                                                    Selecciona un método de pago
                                                </option>
                                                <option
                                                    v-for="metodo in props.metodosPago"
                                                    :key="metodo.id"
                                                    :value="metodo.id"
                                                >
                                                    {{ metodo.nombre }}
                                                </option>
                                            </select>
                                        </div>

                                        <div
                                            v-if="isMetodoQRSeleccionado"
                                            class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm text-emerald-800"
                                        >
                                            <p class="font-semibold">
                                                QR seleccionado
                                            </p>
                                            <p class="mt-1">
                                                Se generará un nuevo código QR
                                                de PagoFácil para este pedido y
                                                podrás verificar el pago
                                                automáticamente.
                                            </p>
                                        </div>

                                        <div
                                            v-else
                                            class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-700"
                                        >
                                            <p class="font-semibold">
                                                Método distinto a QR
                                            </p>
                                            <p class="mt-1">
                                                Se actualizará el método de
                                                pago. Si quieres pagar con QR,
                                                elige una opción que incluya
                                                "QR".
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end"
                                    >
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                                            @click="cerrarModalPago"
                                            :disabled="pagoQRLoading"
                                        >
                                            Cancelar
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                                            @click="generarQRPedido"
                                            :disabled="
                                                !metodoPagoSeleccionado ||
                                                pagoQRLoading
                                            "
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                                                />
                                            </svg>
                                            {{
                                                pagoQRLoading
                                                    ? "Procesando..."
                                                    : "Confirmar pago"
                                            }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </section>
                </div>
            </main>
        </div>
    </PublicStoreLayout>
</template>
