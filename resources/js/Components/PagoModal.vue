<template>
    <DialogModal :show="show" @close="closeModal" max-width="md">
        <template #title>
            <i class="bi bi-cash-coin me-2"></i>
            Registrar Pago de Cuota
        </template>

        <template #content>
            <form @submit.prevent="confirmar" class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">
                        Cuota a Pagar
                    </label>
                    <select
                        v-model="form.cuota_id"
                        :disabled="!!cuotaPreseleccionada"
                        required
                        class="w-full rounded-2xl border px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        :class="{
                            'border-rose-500 ring-rose-100':
                                form.errors.cuota_id,
                            'border-slate-200': !form.errors.cuota_id,
                        }"
                    >
                        <option value="">Seleccione una cuota...</option>
                        <option
                            v-for="cuota in cuotasPendientes"
                            :key="cuota.id"
                            :value="cuota.id"
                        >
                            Cuota #{{ cuota.numero_cuota }} - Pendiente:
                            {{ formatearMoneda(cuota.monto_pendiente) }}
                            (Vence:
                            {{ formatearFecha(cuota.fecha_vencimiento) }})
                        </option>
                    </select>
                    <p
                        v-if="form.errors.cuota_id"
                        class="text-sm text-rose-600"
                    >
                        {{ form.errors.cuota_id }}
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">
                            Monto a Pagar *
                        </label>
                        <input
                            v-model="form.monto"
                            type="number"
                            step="0.01"
                            :max="montoPendienteCuota"
                            required
                            class="w-full rounded-2xl border px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            :class="{
                                'border-rose-500 ring-rose-100':
                                    form.errors.monto,
                                'border-slate-200': !form.errors.monto,
                            }"
                        />
                        <p
                            v-if="cuotaSeleccionada"
                            class="text-sm text-slate-500"
                        >
                            Máximo:
                            {{
                                formatearMoneda(
                                    cuotaSeleccionada.monto_pendiente,
                                )
                            }}
                        </p>
                        <p
                            v-if="form.errors.monto"
                            class="text-sm text-rose-600"
                        >
                            {{ form.errors.monto }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">
                            Fecha de Pago *
                        </label>
                        <input
                            v-model="form.fecha"
                            type="date"
                            :max="new Date().toISOString().split('T')[0]"
                            required
                            class="w-full rounded-2xl border px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            :class="{
                                'border-rose-500 ring-rose-100':
                                    form.errors.fecha,
                                'border-slate-200': !form.errors.fecha,
                            }"
                        />
                        <p
                            v-if="form.errors.fecha"
                            class="text-sm text-rose-600"
                        >
                            {{ form.errors.fecha }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold text-slate-700">
                        Método de Pago *
                    </label>
                    <select
                        v-model="form.metodo_pago_id"
                        required
                        class="w-full rounded-2xl border px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        :class="{
                            'border-rose-500 ring-rose-100':
                                form.errors.metodo_pago_id,
                            'border-slate-200': !form.errors.metodo_pago_id,
                        }"
                    >
                        <option value="">Seleccione...</option>
                        <option
                            v-for="metodo in metodosPago"
                            :key="metodo.id"
                            :value="metodo.id"
                        >
                            {{ metodo.nombre }}
                        </option>
                    </select>
                    <p
                        v-if="form.errors.metodo_pago_id"
                        class="text-sm text-rose-600"
                    >
                        {{ form.errors.metodo_pago_id }}
                    </p>
                </div>

                <div
                    v-if="isMetodoQrSelected"
                    class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50 p-4"
                >
                    <div
                        v-if="!qrImage"
                        class="flex items-center justify-between gap-3"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-2xl border border-emerald-300 bg-white px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:border-emerald-400 hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="generarQr"
                            :disabled="generatingQr"
                        >
                            <i class="bi bi-qr-code mr-2"></i>
                            {{
                                generatingQr
                                    ? "Generando QR..."
                                    : "Generar QR (PagoFácil)"
                            }}
                        </button>
                    </div>

                    <div v-else class="space-y-4">
                        <p class="text-sm text-slate-600">
                            Escanee este código QR con su app bancaria:
                        </p>
                        <div class="flex justify-center">
                            <img
                                :src="qrImage"
                                alt="QR Pago"
                                class="max-w-[260px] rounded-3xl border border-slate-200 bg-white p-3"
                            />
                        </div>
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:justify-center"
                        >
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                                @click="verificarEstado(true)"
                                :disabled="verifying"
                            >
                                <i class="bi bi-check2-circle mr-2"></i>
                                {{
                                    verifying
                                        ? "Verificando..."
                                        : "Verificar pago"
                                }}
                            </button>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                                @click="
                                    qrImage = null;
                                    if (pollInterval) {
                                        clearInterval(pollInterval);
                                        pollInterval = null;
                                    }
                                "
                            >
                                <i class="bi bi-x-circle mr-2"></i>
                                Cancelar QR
                            </button>
                        </div>
                        <p class="text-sm text-slate-500">
                            Estado:
                            <span class="font-semibold text-slate-700">{{
                                verifyStatus || "pendiente"
                            }}</span>
                        </p>
                    </div>
                </div>
            </form>
        </template>

        <template #footer>
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    @click="closeModal"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50"
                >
                    <i class="bi bi-x-circle mr-2"></i>
                    Cancelar
                </button>

                <button
                    type="button"
                    @click="confirmar"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <i class="bi bi-check-circle mr-2"></i>
                    Registrar Pago
                </button>
            </div>
        </template>
    </DialogModal>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    show: Boolean,
    cuotas: Array,
    metodosPago: Array,
    cuotaPreseleccionada: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(["close"]);

const form = useForm({
    cuota_id: "",
    metodo_pago_id: "",
    monto: "",
    fecha: new Date().toISOString().split("T")[0],
    // observaciones eliminado
});

// Estado para integración con PagoFácil QR
const qrImage = ref(null);
const qrPagoId = ref(null);
const qrTransactionId = ref(null);
const generatingQr = ref(false);
const verifying = ref(false);
const verifyStatus = ref(null);
let pollInterval = null;

const isMetodoQrSelected = computed(() => {
    const metodo = props.metodosPago?.find(
        (m) => String(m.id) === String(form.metodo_pago_id),
    );
    return metodo && String(metodo.nombre).toLowerCase().includes("qr");
});

const getCsrfToken = () =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

const generarQr = async () => {
    if (!form.cuota_id || !form.monto) {
        alert("Seleccione una cuota y monto antes de generar el QR");
        return;
    }

    generatingQr.value = true;
    qrImage.value = null;
    qrPagoId.value = null;
    qrTransactionId.value = null;

    try {
        const cuotaId = form.cuota_id;
        // route is declared inside a `prefix('carrito')->name('cart.')` group,
        // so the actual route name is `cart.cuotas.generar-qr`.
        const url = route("cart.cuotas.generar-qr", cuotaId);

        const resp = await axios.post(url, { monto: form.monto });

        const data = resp.data;

        qrImage.value = data.qr_image;
        qrPagoId.value = data.pago_id;
        qrTransactionId.value = data.transaction_id;
        verifyStatus.value = "pending";

        // empezar polling automático cada 5s
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(() => verificarEstado(), 5000);
    } catch (e) {
        console.error("Error generando QR:", e);
        // Manejar errores de axios y errores de validación
        const msg =
            e.response?.data?.error || e.response?.data?.message || e.message;
        alert("Error generando QR: " + (msg || e));
    } finally {
        generatingQr.value = false;
    }
};

const verificarEstado = async (manual = false) => {
    if (!qrPagoId.value) return;
    verifying.value = true;

    try {
        // the named route is prefixed with `cart.` (see routes/web.php)
        const url = route("cart.pagos.verificar-estado", qrPagoId.value);
        const resp = await axios.get(url);
        const data = resp.data;
        verifyStatus.value = data.status;

        if (data.status === "completed") {
            // Detener polling
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
            // Cerrar modal y refrescar página para reflejar cambios
            closeModal();
            // Recargar la página para mostrar el pago aplicado (si la app espera esto)
            setTimeout(() => location.reload(), 300);
        } else if (manual) {
            alert("Estado actual: " + data.status);
        }
    } catch (e) {
        console.error("Error verificando estado:", e);
    } finally {
        verifying.value = false;
    }
};

// Preseleccionar cuota si se pasa
watch(
    () => props.cuotaPreseleccionada,
    (newVal) => {
        if (newVal) {
            form.cuota_id = newVal;
            const cuota = props.cuotas?.find((c) => c.id === newVal);
            if (cuota) {
                form.monto = cuota.monto_pendiente;
            }
        }
    },
    { immediate: true },
);

const cuotasPendientes = computed(() => {
    return (
        props.cuotas?.filter(
            (c) => c.estado === "pendiente" || c.estado === "vencido",
        ) || []
    );
});

const cuotaSeleccionada = computed(() => {
    return props.cuotas?.find((c) => c.id === form.cuota_id);
});

const montoPendienteCuota = computed(() => {
    return cuotaSeleccionada.value?.monto_pendiente || 0;
});

watch(
    () => form.cuota_id,
    (newVal) => {
        if (newVal) {
            const cuota = props.cuotas?.find((c) => c.id === newVal);
            if (cuota) {
                form.monto = cuota.monto_pendiente;
            }
        }
    },
);

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

const closeModal = () => {
    form.reset();
    form.clearErrors();
    // limpiar estado de QR si existe
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
    qrImage.value = null;
    qrPagoId.value = null;
    qrTransactionId.value = null;
    verifyStatus.value = null;
    emit("close");
};

const confirmar = () => {
    // Validación antes de enviar
    if (!form.cuota_id) {
        alert("Debe seleccionar una cuota");
        return;
    }
    if (!form.metodo_pago_id) {
        alert("Debe seleccionar un método de pago");
        return;
    }
    if (!form.monto || form.monto <= 0) {
        alert("Debe ingresar un monto válido");
        return;
    }
    if (!form.fecha) {
        alert("Debe seleccionar la fecha de pago");
        return;
    }

    form.post(route("creditos.registrar-pago"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: (errors) => {
            console.error("Errores al registrar pago:", errors);
        },
    });
};
</script>
