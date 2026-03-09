<template>
    <div class="qr-payment-container">
        <!-- Alerta de Pago Detectado -->
        <div
            v-if="paymentDetected"
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            <h4 class="alert-heading">
                <i class="bi bi-check-circle-fill me-2"></i>
                ¡Pago Confirmado!
            </h4>
            <p class="mb-0">
                Tu pago ha sido detectado exitosamente. Actualizando página...
            </p>
            <div class="spinner-border spinner-border-sm ms-2" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <div class="card shadow-sm" v-show="!paymentDetected">
            <div class="card-header bg-primary text-white text-center">
                <h5 class="mb-0">
                    <i class="bi bi-qr-code me-2"></i>
                    Escanea para Pagar
                </h5>
            </div>
            <div class="card-body text-center p-4">
                <!-- QR Code Image -->
                <div class="qr-image-container mb-3">
                    <img
                        :src="qrImage"
                        alt="Código QR para pago"
                        class="qr-image"
                        style="
                            max-width: 280px;
                            width: 100%;
                            height: auto;
                            border: 3px solid #f0f0f0;
                            border-radius: 8px;
                            padding: 10px;
                        "
                    />
                </div>

                <!-- Payment Info -->
                <div class="payment-info mb-3">
                    <div class="alert alert-info">
                        <h4 class="mb-2">
                            <strong>Bs. {{ formatMoney(monto) }}</strong>
                        </h4>
                        <p class="mb-0 small text-muted">
                            {{
                                descripcion ||
                                "Escanea el código QR con tu app bancaria para completar el pago"
                            }}
                        </p>
                    </div>
                </div>

                <!-- Transaction Info -->
                <div class="transaction-info text-muted small">
                    <p class="mb-1">
                        <strong>ID de Transacción:</strong><br />
                        <code class="text-dark">{{ transactionId }}</code>
                    </p>
                    <p v-if="expiration" class="mb-0">
                        <i class="bi bi-clock me-1"></i>
                        Expira: {{ formatExpiration(expiration) }}
                    </p>
                </div>

                <!-- Status Badge -->
                <div class="mt-3">
                    <span class="badge" :class="statusBadgeClass">
                        <i class="bi" :class="statusIcon"></i>
                        {{ statusText }}
                    </span>

                    <!-- Auto-check indicator -->
                    <div v-if="autoCheck && status === 'pending'" class="mt-2">
                        <small class="text-muted">
                            <i
                                class="bi bi-arrow-repeat"
                                :class="{ spin: checking }"
                            ></i>
                            Verificación automática cada 5 segundos
                        </small>
                    </div>
                </div>

                <!-- Refresh Button (if pending) -->
                <div
                    v-if="status === 'pending' && showRefreshButton"
                    class="mt-3"
                >
                    <button
                        @click="checkPaymentStatus"
                        class="btn btn-outline-primary btn-sm"
                        :disabled="checking"
                    >
                        <i
                            class="bi bi-arrow-clockwise me-1"
                            :class="{ spin: checking }"
                        ></i>
                        {{ checking ? "Verificando..." : "Verificar Pago" }}
                    </button>
                </div>

                <!-- Simulate Payment Button (ONLY FOR DEVELOPMENT) -->
                <div
                    v-if="showSimulateButton && status === 'pending'"
                    class="mt-3"
                >
                    <button
                        @click="simulatePayment"
                        class="btn btn-warning btn-sm"
                        :disabled="simulating"
                    >
                        <i class="bi bi-lightning-fill me-1"></i>
                        {{ simulating ? "Simulando..." : "Simular Pago (DEV)" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
    qrImage: {
        type: String,
        required: true,
    },
    transactionId: {
        type: String,
        required: true,
    },
    monto: {
        type: [Number, String],
        required: true,
    },
    descripcion: {
        type: String,
        default: "",
    },
    status: {
        type: String,
        default: "pending", // 'pending', 'completed', 'failed'
    },
    expiration: {
        type: String,
        default: null,
    },
    showRefreshButton: {
        type: Boolean,
        default: true,
    },
    showSimulateButton: {
        type: Boolean,
        default: true, // Cambiar a false en producción
    },
    onStatusChange: {
        type: Function,
        default: null,
    },
    autoCheck: {
        type: Boolean,
        default: true, // Habilitar verificación automática por defecto
    },
    checkInterval: {
        type: Number,
        default: 5000, // Verificar cada 5 segundos
    },
    ventaId: {
        type: [Number, String],
        required: true, // ID de la venta para verificar estado
    },
});

const checking = ref(false);
const simulating = ref(false);
const paymentDetected = ref(false);
let pollingInterval = null;

// Iniciar polling automático cuando se monta el componente
onMounted(() => {
    console.log("🔔 QRPayment montado:", {
        ventaId: props.ventaId,
        status: props.status,
        autoCheck: props.autoCheck,
        transactionId: props.transactionId,
    });

    if (props.autoCheck && props.status === "pending") {
        console.log(
            "✅ Iniciando polling automático cada",
            props.checkInterval,
            "ms",
        );
        startPolling();
    } else {
        console.log("⏸️ Polling no iniciado:", {
            autoCheck: props.autoCheck,
            status: props.status,
        });
    }
});

// Limpiar interval cuando se desmonta el componente
onUnmounted(() => {
    stopPolling();
});

const startPolling = () => {
    if (pollingInterval) {
        console.log("⚠️ Polling ya está corriendo");
        return;
    }

    console.log("🔄 Polling iniciado");
    pollingInterval = setInterval(async () => {
        if (props.status !== "pending") {
            console.log("⏹️ Estado cambió a:", props.status);
            stopPolling();
            return;
        }
        await checkPaymentStatus();
    }, props.checkInterval);
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
};

const statusBadgeClass = computed(() => {
    const classes = {
        pending: "bg-warning text-dark",
        completed: "bg-success",
        failed: "bg-danger",
    };
    return classes[props.status] || "bg-secondary";
});

const statusIcon = computed(() => {
    const icons = {
        pending: "bi-hourglass-split",
        completed: "bi-check-circle-fill",
        failed: "bi-x-circle-fill",
    };
    return icons[props.status] || "bi-question-circle";
});

const statusText = computed(() => {
    const texts = {
        pending: "Pendiente de Pago",
        completed: "Pago Confirmado",
        failed: "Pago Fallido",
    };
    return texts[props.status] || "Desconocido";
});

const formatMoney = (amount) => {
    return parseFloat(amount || 0).toFixed(2);
};

const formatExpiration = (datetime) => {
    if (!datetime) return "";
    const date = new Date(datetime);
    return date.toLocaleString("es-ES", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const checkPaymentStatus = async () => {
    if (checking.value) {
        console.log("⏸️ Ya hay una verificación en curso");
        return;
    }

    checking.value = true;
    console.log("🔍 Verificando pago...", { ventaId: props.ventaId });

    try {
        const response = await axios.get(
            `/api/ventas/${props.ventaId}/verificar-pago`,
        );

        console.log("✅ Respuesta de verificación:", response.data);

        // Verificar si el pago está completado
        if (
            response.data.status === "completed" ||
            response.data.status === "pagado"
        ) {
            // Pago confirmado!
            console.log("🎉🎉🎉 ¡PAGO CONFIRMADO! Recargando página...");
            paymentDetected.value = true;
            stopPolling(); // Detener polling antes de recargar

            // Dar feedback visual antes de recargar
            setTimeout(() => {
                console.log("🔄 Forzando recarga completa de la página...");
                // Forzar recarga completa para actualizar todos los datos
                router.visit(window.location.pathname, {
                    preserveScroll: false,
                    preserveState: false,
                });
            }, 800);
        } else if (
            response.data.status === "expired" ||
            response.data.status === "cancelled"
        ) {
            console.warn("❌ QR expirado o cancelado:", response.data.mensaje);
            stopPolling();
            alert(
                "El código QR ha expirado. Por favor, genera un nuevo pedido.",
            );
        } else if (
            response.data.status === "pending" ||
            response.data.status === "pendiente"
        ) {
            console.log("⏳ Pago aún pendiente - continuando polling");
            // Continuar polling
        } else if (response.data.status === "error") {
            console.error("❌ Error en verificación:", response.data.mensaje);
            stopPolling();
        }

        // Llamar callback si existe
        if (props.onStatusChange && response.data.status) {
            props.onStatusChange(response.data.status, response.data);
        }
    } catch (error) {
        console.error("❌ Error al verificar estado:", error);
        console.error("❌ Detalles del error:", {
            message: error.message,
            response: error.response?.data,
            status: error.response?.status,
        });
        // No detener polling en caso de error de red temporal
    } finally {
        checking.value = false;
    }
};

const simulatePayment = async () => {
    if (!confirm("¿Simular pago completado? (Solo para desarrollo)")) {
        return;
    }

    simulating.value = true;
    try {
        const response = await axios.post(
            "/pagofacil-simulado/confirmar-pago",
            {
                transaction_id: props.transactionId,
            },
        );

        if (response.data.success) {
            alert("Pago simulado exitosamente. Recargando página...");
            router.visit(window.location.pathname, {
                preserveScroll: false,
                preserveState: false,
            });
        }
    } catch (error) {
        console.error("Error al simular pago:", error);
        alert(
            "Error al simular pago: " +
                (error.response?.data?.error || error.message),
        );
    } finally {
        simulating.value = false;
    }
};
</script>

<style scoped>
.qr-payment-container {
    max-width: 400px;
    margin: 0 auto;
}

.qr-image-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

code {
    font-size: 0.75rem;
    word-break: break-all;
}
</style>
