<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue";
import axios from "axios";
import { showToast } from "@/utils/toast";
import QRPayment from "@/Components/QRPayment.vue";

const props = defineProps({
    show: Boolean,
    producto: {
        type: Object,
        default: null,
    },
    itemsIniciales: {
        type: Array,
        default: () => [],
    },
    clientes: {
        type: Array,
        default: () => [],
    },
    metodosPago: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["close", "venta-registrada"]);

const varianteItems = ref([]);
const clienteTipo = ref("consumidor_final");
const clienteBusqueda = ref("");
const clienteIdSeleccionado = ref("");

const tipoPago = ref("contado");
const metodoPago = ref(props.metodosPago[0]?.id || "");
const numeroCuotas = ref(3);
const fechaInicioCredito = ref(new Date().toISOString().slice(0, 10));
const tasaInteres = ref(0);
const showQrModal = ref(false);
const qrPaymentData = ref({});
const qrStatusChecking = ref(false);

let qrPollingTimer = null;
let qrVerificationInProgress = false;

const procesandoVenta = ref(false);
const feedback = ref({ type: "", message: "" });

const setFeedback = (type, message) => {
    feedback.value = { type, message };
};

const resetForm = () => {
    varianteItems.value = [];
    clienteTipo.value = "consumidor_final";
    clienteBusqueda.value = "";
    clienteIdSeleccionado.value = "";
    tipoPago.value = "contado";
    metodoPago.value = props.metodosPago[0]?.id || "";
    numeroCuotas.value = 3;
    fechaInicioCredito.value = new Date().toISOString().slice(0, 10);
    tasaInteres.value = 0;
    feedback.value = { type: "", message: "" };
    qrStatusChecking.value = false;
};

watch(
    () => [props.show, props.producto, props.itemsIniciales],
    () => {
        if (!props.show) return;

        if (props.itemsIniciales.length > 0) {
            varianteItems.value = props.itemsIniciales.map((item) => ({
                id: item.producto_variante_id,
                key:
                    item.key ||
                    `${item.producto_id}-${item.producto_variante_id}`,
                producto_nombre:
                    item.producto_nombre ||
                    props.producto?.nombre ||
                    "Producto",
                categoria_nombre:
                    item.categoria_nombre ||
                    props.producto?.categoria?.nombre ||
                    "Sin categoria",
                talla: item.talla || "-",
                color: item.color || "-",
                sku: item.sku || "-",
                estado: (item.estado || "").toUpperCase(),
                stock_actual: Number(item.stock_actual) || 0,
                precio_venta: Number(item.precio_venta) || 0,
                cantidad: Number(item.cantidad) || 0,
            }));
        } else if (props.producto) {
            varianteItems.value = (props.producto.variantes || []).map(
                (variant) => ({
                    id: variant.id,
                    key: `${props.producto.id}-${variant.id}`,
                    producto_nombre: props.producto.nombre,
                    categoria_nombre:
                        props.producto.categoria?.nombre || "Sin categoria",
                    talla: variant.talla || "-",
                    color: variant.color || "-",
                    sku: variant.sku || "-",
                    estado: (variant.estado || "").toUpperCase(),
                    stock_actual: Number(variant.stock_actual) || 0,
                    precio_venta: Number(variant.precio_venta) || 0,
                    cantidad: 0,
                }),
            );
        } else {
            varianteItems.value = [];
        }
        feedback.value = { type: "", message: "" };
    },
    { immediate: true },
);

const formatPrice = (price) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(Number(price) || 0);

const metodosPagoDisponibles = computed(() => props.metodosPago || []);

const metodoPagoSeleccionado = computed(() => {
    return metodosPagoDisponibles.value.find(
        (metodo) => String(metodo.id) === String(metodoPago.value),
    );
});

const esMetodoQR = computed(() => {
    return (
        tipoPago.value === "contado" &&
        metodoPagoSeleccionado.value &&
        String(metodoPagoSeleccionado.value.nombre).toLowerCase().includes("qr")
    );
});

const actionButtonLabel = computed(() =>
    esMetodoQR.value ? "Generar QR" : "Confirmar venta",
);

const processingButtonLabel = computed(() =>
    esMetodoQR.value ? "Generando..." : "Registrando...",
);

const clientesFiltrados = computed(() => {
    const q = clienteBusqueda.value.trim().toLowerCase();
    if (!q) return props.clientes;
    return props.clientes.filter((cliente) => {
        const nombre = `${cliente.nombre || ""} ${cliente.apellidos || ""}`
            .trim()
            .toLowerCase();
        const email = (cliente.email || "").toLowerCase();
        const ci = (cliente.ci || "").toLowerCase();
        return nombre.includes(q) || email.includes(q) || ci.includes(q);
    });
});

const itemsSeleccionados = computed(() =>
    varianteItems.value.filter((item) => Number(item.cantidad) > 0),
);

const subtotal = computed(() =>
    itemsSeleccionados.value.reduce(
        (sum, item) =>
            sum +
            (Number(item.cantidad) || 0) * (Number(item.precio_venta) || 0),
        0,
    ),
);

const clienteIdParaVenta = computed(() => {
    if (clienteTipo.value !== "cliente_existente") return null;
    return clienteIdSeleccionado.value
        ? Number(clienteIdSeleccionado.value)
        : null;
});

const updateCantidad = (item, value) => {
    const parsed = Number(value);
    if (!Number.isFinite(parsed) || parsed < 0) {
        item.cantidad = 0;
        return;
    }
    item.cantidad = Math.min(Math.floor(parsed), item.stock_actual);
};

const validarVenta = () => {
    if (!itemsSeleccionados.value.length) {
        setFeedback(
            "error",
            "Selecciona al menos una variante con cantidad mayor a 0.",
        );
        return false;
    }
    const invalida = itemsSeleccionados.value.find(
        (item) =>
            item.estado !== "ACTIVO" ||
            item.stock_actual <= 0 ||
            item.cantidad <= 0 ||
            item.cantidad > item.stock_actual,
    );
    if (invalida) {
        setFeedback(
            "error",
            "Hay variantes con estado, stock o cantidad inválidos.",
        );
        return false;
    }
    if (tipoPago.value === "credito" && !clienteIdParaVenta.value) {
        setFeedback(
            "error",
            "Para crédito debes seleccionar un cliente existente.",
        );
        return false;
    }
    if (tipoPago.value === "contado" && !metodoPago.value) {
        setFeedback("error", "Selecciona un método de pago.");
        return false;
    }
    return true;
};

const quitarVariante = (itemId) => {
    varianteItems.value = varianteItems.value.filter(
        (item) => item.id !== itemId,
    );
};

watch(
    metodosPagoDisponibles,
    (metodos) => {
        if (!metodos.length) {
            metodoPago.value = "";
            return;
        }
        const existeActual = metodos.some(
            (metodo) => String(metodo.id) === String(metodoPago.value),
        );
        if (!existeActual) metodoPago.value = metodos[0]?.id || "";
    },
    { immediate: true },
);

watch(tipoPago, (value) => {
    if (value === "contado" && !metodoPago.value) {
        metodoPago.value = metodosPagoDisponibles.value[0]?.id || "";
    }
});

const normalizarEstadoPago = (status) =>
    String(status || "")
        .trim()
        .toLowerCase();

const pagoQrConfirmado = (status) =>
    ["completed", "pagado", "paid", "approved", "completado"].includes(
        normalizarEstadoPago(status),
    );

const detenerVerificacionAutomatica = () => {
    if (qrPollingTimer) {
        window.clearInterval(qrPollingTimer);
        qrPollingTimer = null;
    }
};

const finalizarPagoQr = (data = {}) => {
    detenerVerificacionAutomatica();

    const ventaConfirmada = {
        ...data,
        venta_id: data.venta_id || qrPaymentData.value.venta_id,
        transaction_id:
            data.transaction_id || qrPaymentData.value.transaction_id,
        status: data.status || "completed",
    };

    qrPaymentData.value = {
        ...qrPaymentData.value,
        status: ventaConfirmada.status,
    };

    showToast(
        "Pago QR confirmado y venta registrada correctamente.",
        "success",
    );

    showQrModal.value = false;
    emit("venta-registrada", ventaConfirmada);
    resetForm();
    qrPaymentData.value = {};
};

const handleQrPaymentStatusChange = (status, payload = {}) => {
    if (pagoQrConfirmado(status)) {
        finalizarPagoQr({
            ...payload,
            venta_id: payload.venta_id || qrPaymentData.value.venta_id,
            transaction_id:
                payload.transaction_id || qrPaymentData.value.transaction_id,
            status,
        });
    }
};

const verificarEstadoQr = async ({ manual = false } = {}) => {
    if (
        !qrPaymentData.value.venta_id ||
        qrVerificationInProgress ||
        !showQrModal.value
    ) {
        return;
    }

    qrVerificationInProgress = true;

    if (manual) {
        qrStatusChecking.value = true;
    }

    try {
        const response = await axios.get(
            `/api/ventas/${qrPaymentData.value.venta_id}/verificar-pago`,
        );

        const data = response.data || {};
        const status = data.status || "pending";

        qrPaymentData.value = {
            ...qrPaymentData.value,
            status,
        };

        // Este mensaje solo aparece cuando el backend ya confirmó el pago.
        // El endpoint debe leer el estado actualizado por el callback.
        if (pagoQrConfirmado(status)) {
            finalizarPagoQr(data);
        }
    } catch (error) {
        console.error("Error verificando estado QR:", error);

        // En la verificación automática no mostramos errores repetitivos.
        if (manual) {
            showToast(
                "No se pudo verificar el estado del pago. Intenta de nuevo.",
                "error",
            );
        }
    } finally {
        qrVerificationInProgress = false;

        if (manual) {
            qrStatusChecking.value = false;
        }
    }
};

const iniciarVerificacionAutomatica = () => {
    detenerVerificacionAutomatica();

    // El callback actualiza el pago en el backend. El frontend consulta
    // periódicamente ese estado y confirma la venta únicamente al verlo pagado.
    qrPollingTimer = window.setInterval(() => {
        verificarEstadoQr();
    }, 4000);
};

const confirmarVenta = async () => {
    feedback.value = { type: "", message: "" };

    if (!validarVenta() || procesandoVenta.value) return;

    const endpoint =
        tipoPago.value === "credito"
            ? route("ventas.credito")
            : route("ventas.contado");

    const selectedMetodoPagoId = metodoPago.value
        ? Number(metodoPago.value)
        : null;

    const payload = {
        items: itemsSeleccionados.value.map((item) => ({
            producto_variante_id: item.id,
            cantidad: Number(item.cantidad),
        })),
        cliente_id: clienteIdParaVenta.value,
        metodo_pago_id: selectedMetodoPagoId,
    };

    if (tipoPago.value === "credito") {
        payload.cuotas = Number(numeroCuotas.value);
        payload.fecha_inicio = fechaInicioCredito.value;
        payload.tasa_interes = Number(tasaInteres.value) || 0;
    }

    procesandoVenta.value = true;

    try {
        const response = await axios.post(endpoint, payload);
        const data = response.data || {};

        if (tipoPago.value === "contado" && esMetodoQR.value) {
            qrPaymentData.value = {
                qr_image: data.qr_image,
                transaction_id: data.transaction_id,
                monto: data.total,
                descripcion: data.descripcion,
                status: data.status || "pending",
                expiration: data.expiration,
                venta_id: data.venta_id,
                redirectUrl: window.location.pathname,
                verificationUrl: `/api/ventas/${data.venta_id}/verificar-pago`,
            };

            showQrModal.value = true;
            await nextTick();
            iniciarVerificacionAutomatica();

            return;
        }

        showToast("Venta registrada correctamente.", "success");
        setFeedback("success", "Venta registrada correctamente.");
        emit("venta-registrada", data);
    } catch (error) {
        const errorMessage =
            error.response?.data?.error ||
            error.response?.data?.message ||
            (esMetodoQR.value
                ? "Error de conexión al generar el código QR."
                : "Error de conexión al registrar la venta.");

        setFeedback("error", errorMessage);
        console.error("Error procesando venta:", error);
    } finally {
        procesandoVenta.value = false;
    }
};

const checkQrStatus = async () => {
    await verificarEstadoQr({ manual: true });
};

const cancelarQr = () => {
    detenerVerificacionAutomatica();
    showQrModal.value = false;
    qrStatusChecking.value = false;
    qrPaymentData.value = {};
};

const closeModal = () => {
    detenerVerificacionAutomatica();
    showQrModal.value = false;
    qrPaymentData.value = {};
    resetForm();
    emit("close");
};

watch(showQrModal, (visible) => {
    if (!visible) {
        detenerVerificacionAutomatica();
    }
});

onBeforeUnmount(() => {
    detenerVerificacionAutomatica();
});
</script>

<template>
    <teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[95] flex items-center justify-center bg-slate-950/50 px-4 py-6"
            @click.self="closeModal"
        >
            <div
                class="flex w-full max-w-4xl flex-col rounded-[2rem] border border-white bg-white/95 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.4)]"
                style="max-height: 90vh"
            >
                <!-- Cabecera -->
                <div
                    class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-slate-100 px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                        >
                            Venta
                        </p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">
                            Vender {{ producto?.nombre || "producto" }}
                        </h3>
                        <p class="mt-0.5 text-sm text-slate-500">
                            Selecciona variantes y completa los datos de pago.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="closeModal"
                        aria-label="Cerrar"
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
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Cuerpo scrolleable -->
                <div class="flex-1 space-y-5 overflow-y-auto p-6">
                    <!-- Feedback -->
                    <div
                        v-if="feedback.message"
                        class="flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm"
                        :class="
                            feedback.type === 'error'
                                ? 'border-rose-200 bg-rose-50 text-rose-700'
                                : 'border-emerald-200 bg-emerald-50 text-emerald-700'
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="mt-0.5 h-4 w-4 flex-shrink-0"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                v-if="feedback.type === 'error'"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 9v4m0 4h.01M10.29 3.86l-8.42 14.5A1.5 1.5 0 003.18 21h17.64a1.5 1.5 0 001.31-2.64l-8.42-14.5a1.5 1.5 0 00-2.62 0z"
                            />
                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        <span>{{ feedback.message }}</span>
                    </div>

                    <!-- Tabla de variantes -->
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-100"
                    >
                        <div
                            class="border-b border-slate-100 bg-slate-50 px-4 py-3"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                            >
                                Variantes del producto
                            </p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Producto
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Variante
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            SKU
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Precio
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Stock
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Estado
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Cantidad
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Acción
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!varianteItems.length">
                                        <td
                                            colspan="8"
                                            class="px-4 py-10 text-center"
                                        >
                                            <div
                                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-6 w-6 text-slate-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"
                                                    />
                                                </svg>
                                            </div>
                                            <p
                                                class="text-sm font-black text-slate-700"
                                            >
                                                Sin variantes disponibles
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-400"
                                            >
                                                Este producto no tiene variantes
                                                registradas.
                                            </p>
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="item in varianteItems"
                                        :key="item.id"
                                        class="border-b border-slate-50 transition"
                                        :class="
                                            Number(item.cantidad) > 0
                                                ? 'bg-emerald-50/40'
                                                : 'hover:bg-slate-50/60'
                                        "
                                    >
                                        <td
                                            class="px-4 py-3.5 font-black text-slate-800"
                                        >
                                            {{ item.producto_nombre }}
                                        </td>
                                        <td class="px-4 py-3.5 text-slate-600">
                                            {{ item.talla }} / {{ item.color }}
                                        </td>
                                        <td class="px-4 py-3.5 text-slate-600">
                                            <code
                                                class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-500"
                                                >{{ item.sku }}</code
                                            >
                                        </td>
                                        <td
                                            class="px-4 py-3.5 text-right font-black text-emerald-700"
                                        >
                                            {{ formatPrice(item.precio_venta) }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                                :class="
                                                    item.stock_actual > 0
                                                        ? 'bg-sky-100 text-sky-800'
                                                        : 'bg-rose-100 text-rose-700'
                                                "
                                            >
                                                {{ item.stock_actual }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                                :class="
                                                    item.estado === 'ACTIVO'
                                                        ? 'bg-emerald-100 text-emerald-700'
                                                        : 'bg-rose-100 text-rose-700'
                                                "
                                            >
                                                {{ item.estado }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <input
                                                type="number"
                                                min="0"
                                                :max="item.stock_actual"
                                                :value="item.cantidad"
                                                class="w-20 rounded-xl border border-slate-200 bg-white px-3 py-2 text-center text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                                :disabled="
                                                    item.estado !== 'ACTIVO' ||
                                                    item.stock_actual === 0
                                                "
                                                @input="
                                                    updateCantidad(
                                                        item,
                                                        $event.target.value,
                                                    )
                                                "
                                            />
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <button
                                                type="button"
                                                class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50"
                                                @click="quitarVariante(item.id)"
                                            >
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Cliente y Pago -->
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <!-- Cliente -->
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-100"
                        >
                            <div
                                class="border-b border-slate-100 bg-slate-50 px-4 py-3"
                            >
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Cliente
                                </p>
                            </div>
                            <div class="space-y-4 p-4">
                                <div class="flex flex-col gap-2">
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold transition"
                                        :class="
                                            clienteTipo === 'consumidor_final'
                                                ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                        "
                                    >
                                        <input
                                            v-model="clienteTipo"
                                            type="radio"
                                            value="consumidor_final"
                                            class="accent-emerald-600"
                                        />
                                        Consumidor final
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold transition"
                                        :class="
                                            clienteTipo === 'cliente_existente'
                                                ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                        "
                                    >
                                        <input
                                            v-model="clienteTipo"
                                            type="radio"
                                            value="cliente_existente"
                                            class="accent-emerald-600"
                                        />
                                        Cliente existente
                                    </label>
                                </div>

                                <div
                                    v-if="clienteTipo === 'cliente_existente'"
                                    class="space-y-3"
                                >
                                    <input
                                        v-model="clienteBusqueda"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                        placeholder="Buscar por nombre, CI o email..."
                                    />
                                    <select
                                        v-model="clienteIdSeleccionado"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                    >
                                        <option value="">
                                            Selecciona un cliente
                                        </option>
                                        <option
                                            v-for="cliente in clientesFiltrados"
                                            :key="cliente.id"
                                            :value="cliente.id"
                                        >
                                            {{ cliente.nombre }}
                                            {{ cliente.apellidos }} —
                                            {{ cliente.ci || cliente.email }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Pago -->
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-100"
                        >
                            <div
                                class="border-b border-slate-100 bg-slate-50 px-4 py-3"
                            >
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Método de pago
                                </p>
                            </div>
                            <div class="space-y-4 p-4">
                                <div class="flex flex-col gap-2">
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold transition"
                                        :class="
                                            tipoPago === 'contado'
                                                ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                        "
                                    >
                                        <input
                                            v-model="tipoPago"
                                            type="radio"
                                            value="contado"
                                            class="accent-emerald-600"
                                        />
                                        Contado
                                    </label>
                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold transition"
                                        :class="
                                            tipoPago === 'credito'
                                                ? 'border-sky-200 bg-sky-50 text-sky-800'
                                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                        "
                                    >
                                        <input
                                            v-model="tipoPago"
                                            type="radio"
                                            value="credito"
                                            class="accent-sky-600"
                                        />
                                        Crédito
                                    </label>
                                </div>

                                <div class="space-y-2">
                                    <label
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                        >Método de pago</label
                                    >
                                    <div v-if="tipoPago === 'contado'">
                                        <select
                                            v-model="metodoPago"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                        >
                                            <option
                                                v-for="metodo in metodosPagoDisponibles"
                                                :key="metodo.id"
                                                :value="metodo.id"
                                            >
                                                {{ metodo.nombre }}
                                            </option>
                                        </select>
                                        <p
                                            v-if="esMetodoQR"
                                            class="mt-2 text-sm text-slate-500"
                                        >
                                            Este método generará un QR para el
                                            pago al contado.
                                        </p>
                                        <p
                                            v-if="
                                                !metodosPagoDisponibles.length
                                            "
                                            class="text-xs font-semibold text-rose-600"
                                        >
                                            No hay métodos de pago activos.
                                        </p>
                                    </div>
                                    <div
                                        v-else
                                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600"
                                    >
                                        <p class="font-semibold text-slate-800">
                                            Venta a crédito
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            El método de pago se asigna
                                            automáticamente para la venta a
                                            crédito.
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="tipoPago === 'credito'"
                                    class="space-y-3"
                                >
                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-bold text-slate-700"
                                            >Número de cuotas</label
                                        >
                                        <input
                                            v-model.number="numeroCuotas"
                                            type="number"
                                            min="2"
                                            max="12"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-bold text-slate-700"
                                            >Fecha de inicio</label
                                        >
                                        <input
                                            v-model="fechaInicioCredito"
                                            type="date"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-bold text-slate-700"
                                            >Tasa de interés (%)</label
                                        >
                                        <input
                                            v-model.number="tasaInteres"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                            placeholder="0"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div
                        class="flex items-center justify-between rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4"
                    >
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700"
                            >
                                Total a cobrar
                            </p>
                            <p class="mt-0.5 text-xs text-emerald-600">
                                {{ itemsSeleccionados.length }} variante{{
                                    itemsSeleccionados.length !== 1 ? "s" : ""
                                }}
                                seleccionada{{
                                    itemsSeleccionados.length !== 1 ? "s" : ""
                                }}
                            </p>
                        </div>
                        <span class="text-2xl font-black text-emerald-700">{{
                            formatPrice(subtotal)
                        }}</span>
                    </div>
                </div>

                <!-- Pie -->
                <div
                    class="flex flex-shrink-0 items-center justify-end gap-3 border-t border-slate-100 px-6 py-4"
                >
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        @click="closeModal"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="
                            procesandoVenta || !itemsSeleccionados.length
                        "
                        @click="confirmarVenta"
                    >
                        <span
                            v-if="procesandoVenta"
                            class="inline-flex items-center gap-2"
                        >
                            <span
                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                            ></span>
                            {{ processingButtonLabel }}
                        </span>
                        <span v-else class="inline-flex items-center gap-2">
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
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            {{ actionButtonLabel }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </teleport>

    <teleport to="body">
        <div
            v-if="showQrModal"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 px-4 py-6"
            @click.self="cancelarQr"
        >
            <div
                class="w-full max-w-3xl rounded-[1.75rem] bg-white shadow-2xl p-6"
            >
                <div
                    class="flex items-start justify-between gap-4 pb-4 border-b border-slate-200"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                        >
                            Pago QR
                        </p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">
                            Escanea para completar el pago
                        </h3>
                    </div>
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="cancelarQr"
                        aria-label="Cerrar"
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
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
                <div class="mt-5">
                    <div
                        v-if="
                            qrPaymentData.qr_image ||
                            qrPaymentData.transaction_id
                        "
                        class="space-y-5"
                    >
                        <QRPayment
                            v-if="qrPaymentData.qr_image"
                            :qr-image="qrPaymentData.qr_image"
                            :transaction-id="qrPaymentData.transaction_id"
                            :monto="qrPaymentData.monto"
                            :descripcion="
                                qrPaymentData.descripcion || 'Pago QR'
                            "
                            :status="qrPaymentData.status || 'pending'"
                            :venta-id="qrPaymentData.venta_id"
                            :verification-url="qrPaymentData.verificationUrl"
                            :redirect-url="qrPaymentData.redirectUrl"
                            :auto-check="true"
                            :check-interval="5000"
                            :show-refresh-button="true"
                            :show-simulate-button="false"
                            :on-status-change="handleQrPaymentStatusChange"
                        />
                        <div
                            v-else
                            class="rounded-3xl border border-slate-200 bg-slate-50 p-6 text-center"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                            >
                                Pago QR
                            </p>
                            <h3 class="mt-3 text-xl font-black text-slate-900">
                                Escanea para completar el pago
                            </h3>

                            <div
                                class="mt-5 space-y-3 text-left text-sm text-slate-600"
                            >
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="font-semibold text-slate-900">
                                        Monto
                                    </p>
                                    <p
                                        class="mt-1 text-lg font-black text-emerald-700"
                                    >
                                        {{ formatPrice(qrPaymentData.monto) }}
                                    </p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="font-semibold text-slate-900">
                                        ID de Transacción
                                    </p>
                                    <p
                                        class="mt-1 text-sm text-slate-500 break-words"
                                    >
                                        {{ qrPaymentData.transaction_id }}
                                    </p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 shadow-sm">
                                    <p class="font-semibold text-slate-900">
                                        Estado
                                    </p>
                                    <p class="mt-1 text-sm text-emerald-700">
                                        {{ qrPaymentData.status || "pending" }}
                                    </p>
                                </div>
                                <p class="text-xs text-slate-500">
                                    Escanea el código QR con tu app bancaria
                                    para completar el pago.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>
