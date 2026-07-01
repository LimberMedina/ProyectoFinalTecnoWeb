<template>
    <!-- Modal de pago por QR -->
    <div
        v-if="mostrarModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70 px-4 py-6"
    >
        <div
            class="w-full max-w-lg rounded-[2rem] border border-white bg-white/95 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.4)]"
        >
            <!-- Cabecera modal -->
            <div
                class="mb-5 flex items-start justify-between gap-4 border-b border-slate-100 pb-5"
            >
                <div>
                    <p
                        class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                    >
                        Pago
                    </p>
                    <h3 class="mt-1 text-xl font-black text-slate-900">
                        {{
                            qrGenerado
                                ? "Escanea el QR para pagar"
                                : "Registrar pago por QR"
                        }}
                    </h3>
                    <p class="mt-0.5 text-sm text-slate-500">
                        {{
                            qrGenerado
                                ? "Escanea con tu app bancaria"
                                : "Selecciona la cuota y completa el pago"
                        }}
                    </p>
                </div>
                <button
                    type="button"
                    @click="cerrar"
                    class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Cerrar modal"
                    :disabled="cargandoPago || generandoQR"
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

            <!-- Info del crédito -->
            <div
                v-if="creditoSeleccionado"
                class="mb-5 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3"
            >
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="text-sm font-black text-slate-800"
                        >Crédito #{{ creditoSeleccionado.id }}</span
                    >
                    <span
                        v-if="cuotaSeleccionada"
                        class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-800"
                    >
                        Cuota #{{ cuotaSeleccionada.numero_cuota }}
                    </span>
                </div>
                <dl class="space-y-1.5 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Venta</dt>
                        <dd class="font-semibold text-slate-800">
                            {{ creditoSeleccionado.venta?.numero_venta }}
                        </dd>
                    </div>
                    <div
                        v-if="cuotaSeleccionada"
                        class="flex items-center justify-between"
                    >
                        <dt class="text-slate-500">Vencimiento</dt>
                        <dd class="font-semibold text-slate-800">
                            {{
                                formatearFecha(
                                    cuotaSeleccionada.fecha_vencimiento,
                                )
                            }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Vista con QR generado -->
            <div v-if="qrGenerado" class="mb-5 space-y-4">
                <div
                    class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-center"
                >
                    <p class="mb-3 text-sm font-semibold text-slate-700">
                        Monto a pagar:
                    </p>
                    <p class="text-2xl font-black text-emerald-700">
                        {{ formatearMoneda(montoPago) }}
                    </p>
                </div>

                <!-- QR -->
                <div class="flex flex-col items-center gap-4">
                    <div
                        v-if="qrImage"
                        class="rounded-2xl border border-slate-200 bg-white p-4"
                    >
                        <img
                            :src="qrImage"
                            alt="QR de pago"
                            class="h-64 w-64 object-contain"
                        />
                    </div>
                    <div
                        v-else
                        class="flex h-64 w-64 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50"
                    >
                        <div class="text-center">
                            <svg
                                class="mx-auto h-12 w-12 animate-spin text-emerald-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <p class="mt-2 text-sm text-slate-600">
                                Generando QR...
                            </p>
                        </div>
                    </div>

                    <!-- Estado de pago -->
                    <div v-if="estadoVerificacion" class="w-full">
                        <div
                            :class="[
                                'rounded-2xl border px-4 py-3 text-center text-sm font-semibold',
                                estadoVerificacion === 'pagado'
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                                    : estadoVerificacion === 'error'
                                      ? 'border-rose-200 bg-rose-50 text-rose-800'
                                      : 'border-slate-200 bg-slate-50 text-slate-800',
                            ]"
                        >
                            <span v-if="estadoVerificacion === 'pendiente'"
                                >Esperando confirmación...</span
                            >
                            <span v-else-if="estadoVerificacion === 'pagado'"
                                >¡Pago confirmado!</span
                            >
                            <span v-else-if="estadoVerificacion === 'error'"
                                >Error al verificar el pago</span
                            >
                        </div>
                    </div>

                    <p class="text-xs text-slate-500">
                        Escanea con tu app bancaria o billetera QR
                    </p>
                </div>
            </div>

            <!-- Vista de selección de cuota (sin QR) -->
            <div v-else class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700"
                        >Seleccionar cuota</label
                    >
                    <select
                        v-model="cuotaSeleccionadaId"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        @change="seleccionarCuota($event.target.value)"
                        :disabled="cargandoPago || generandoQR"
                    >
                        <option value="">Seleccione una cuota</option>
                        <option
                            v-for="cuota in cuotasDisponibles"
                            :key="cuota.id"
                            :value="cuota.id"
                        >
                            Cuota #{{ cuota.numero_cuota }} —
                            {{ formatearMoneda(cuota.monto_pendiente) }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700"
                        >Monto a pagar</label
                    >
                    <input
                        v-model.number="montoPago"
                        type="number"
                        min="0.01"
                        step="0.01"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        placeholder="Ingrese el monto"
                        :max="cuotaSeleccionada?.monto_pendiente"
                        :disabled="
                            !cuotaSeleccionada || cargandoPago || generandoQR
                        "
                    />
                    <p
                        v-if="cuotaSeleccionada"
                        class="mt-1 text-xs text-slate-500"
                    >
                        Pendiente:
                        {{ formatearMoneda(cuotaSeleccionada.monto_pendiente) }}
                    </p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-bold text-slate-700"
                        >Fecha de pago</label
                    >
                    <input
                        v-model="fechaPago"
                        type="date"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        :disabled="
                            !cuotaSeleccionada || cargandoPago || generandoQR
                        "
                    />
                </div>

                <div
                    v-if="generandoQR"
                    class="flex items-center justify-center gap-2 py-1 text-sm text-slate-500"
                >
                    <span
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-emerald-500 border-t-transparent"
                    ></span>
                    Generando QR...
                </div>
            </div>

            <!-- Botones -->
            <div
                class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5"
            >
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:opacity-60 disabled:cursor-not-allowed"
                    @click="cerrar"
                    :disabled="cargandoPago || generandoQR"
                >
                    {{ qrGenerado ? "Cerrar" : "Cancelar" }}
                </button>
                <button
                    v-if="!qrGenerado"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="
                        !cuotaSeleccionada ||
                        !montoPago ||
                        montoPago < 0.01 ||
                        cargandoPago ||
                        generandoQR
                    "
                    @click="generarQR"
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
                            d="M4 4h5v5H4zM15 4h5v5h-5zM4 15h5v5H4zM15 15h5v5h-5z"
                        />
                    </svg>
                    Generar QR
                </button>
                <button
                    v-if="qrGenerado"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:opacity-60 disabled:cursor-not-allowed"
                    @click="volverASeleccionar"
                    :disabled="cargandoPago"
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
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                    Volver
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { showToast } from "@/utils/toast";
import axios from "axios";

const props = defineProps({
    credito: Object,
    mostrarModal: Boolean,
});

const emit = defineEmits(["cerrar"]);

// Estados
const cuotaSeleccionada = ref(null);
const cuotaSeleccionadaId = ref("");
const montoPago = ref(0);
const fechaPago = ref(new Date().toISOString().slice(0, 10));
const qrGenerado = ref(false);
const qrImage = ref(null);
const qrTransactionId = ref(null);
const pagoId = ref(null);
const generandoQR = ref(false);
const cargandoPago = ref(false);
const estadoVerificacion = ref(null);
let intervaloVerificacion = null;

// Computed
const creditoSeleccionado = computed(() => props.credito);

const cuotasDisponibles = computed(() => {
    if (!creditoSeleccionado.value) return [];
    return (
        creditoSeleccionado.value.cuotas?.filter(
            (c) => c.estado !== "pagada",
        ) || []
    );
});

// Métodos
const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor);
};

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const calcularMontoPendiente = (cuota) => {
    if (!cuota) return 0;
    // Si existe monto_pendiente, usarlo
    if (cuota.monto_pendiente && cuota.monto_pendiente > 0) {
        return cuota.monto_pendiente;
    }
    // Si no, calcular: monto - monto_pagado
    const monto = cuota.monto || 0;
    const montoPagado = cuota.monto_pagado || 0;
    return Math.max(0, monto - montoPagado);
};

const seleccionarCuota = (cuotaId) => {
    const cuota = cuotasDisponibles.value.find(
        (c) => c.id === parseInt(cuotaId),
    );
    cuotaSeleccionada.value = cuota || null;
    if (cuota) {
        montoPago.value = calcularMontoPendiente(cuota);
    } else {
        montoPago.value = 0;
    }
};

const generarQR = async () => {
    if (
        !cuotaSeleccionada.value ||
        !montoPago.value ||
        montoPago.value < 0.01
    ) {
        return;
    }

    generandoQR.value = true;
    estadoVerificacion.value = "pendiente";

    try {
        const cuotaId = cuotaSeleccionada.value.id;
        const response = await axios.post(
            `/pagos/generar-qr-cuota/${cuotaId}`,
            {
                monto: montoPago.value,
            },
        );

        const data = response.data;

        qrImage.value = data.qr_image;
        qrTransactionId.value = data.transaction_id;
        pagoId.value = data.pago_id;
        qrGenerado.value = true;
        generandoQR.value = false;

        // Iniciar verificación automática
        iniciarVerificacion();
    } catch (error) {
        console.error("Error al generar QR:", error);
        estadoVerificacion.value = "error";
        generandoQR.value = false;
        alert(
            "Error al generar QR: " +
                (error.response?.data?.error || error.message),
        );
    }
};

const iniciarVerificacion = () => {
    if (intervaloVerificacion) clearInterval(intervaloVerificacion);

    intervaloVerificacion = setInterval(() => {
        verificarEstadoPago();
    }, 3000);
};

const verificarEstadoPago = async () => {
    if (!pagoId.value || !qrTransactionId.value) return;

    try {
        const response = await axios.get(
            `/pagos/verificar-estado/${pagoId.value}`,
        );

        const data = response.data;

        const status = String(
            data.status || data.pago_facil_status || data.estado || "",
        ).toLowerCase();

        const pagoConfirmado = [
            "completed",
            "pagado",
            "paid",
            "approved",
            "completado",
        ].includes(status);

        if (pagoConfirmado) {
            estadoVerificacion.value = "pagado";
            clearInterval(intervaloVerificacion);

            // Mostrar toast de éxito
            try {
                showToast("Pago de cuota confirmado con éxito.", "success");
            } catch (e) {
                console.warn("showToast falló:", e);
            }

            // Esperar 2 segundos antes de cerrar y recargar (recarga completa de Inertia)
            setTimeout(() => {
                cerrar();
                // Forzar recarga completa para asegurar props actualizados en todas las vistas
                router.reload();
            }, 2000);
        }
    } catch (error) {
        console.error("Error al verificar estado:", error);
    }
};

const volverASeleccionar = () => {
    qrGenerado.value = false;
    qrImage.value = null;
    qrTransactionId.value = null;
    pagoId.value = null;
    estadoVerificacion.value = null;
    generandoQR.value = false;
    clearInterval(intervaloVerificacion);
};

const cerrar = () => {
    volverASeleccionar();
    cuotaSeleccionada.value = null;
    cuotaSeleccionadaId.value = "";
    montoPago.value = 0;
    cargandoPago.value = false;
    emit("cerrar");
};

// Watchers
watch(
    () => props.mostrarModal,
    (newVal) => {
        if (!newVal) {
            volverASeleccionar();
        }
    },
);

// Permitir pasar una cuota específica al abrir el modal
const abrirConCuota = (cuota) => {
    cuotaSeleccionada.value = cuota;
    cuotaSeleccionadaId.value = cuota.id;
    montoPago.value = calcularMontoPendiente(cuota);
};

defineExpose({
    abrirConCuota,
});
</script>
