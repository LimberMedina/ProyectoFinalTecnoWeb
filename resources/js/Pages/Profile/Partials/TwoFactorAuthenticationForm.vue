<script setup>
import { ref, computed, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
    requiresConfirmation: Boolean,
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: "",
});

const twoFactorEnabled = computed(
    () => !enabling.value && page.props.auth.user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post(
        route("two-factor.enable"),
        {},
        {
            preserveScroll: true,
            onSuccess: () =>
                Promise.all([
                    showQrCode(),
                    showSetupKey(),
                    showRecoveryCodes(),
                ]),
            onFinish: () => {
                enabling.value = false;
                confirming.value = props.requiresConfirmation;
            },
        },
    );
};

const showQrCode = () => {
    return axios.get(route("two-factor.qr-code")).then((response) => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route("two-factor.secret-key")).then((response) => {
        setupKey.value = response.data.secretKey;
    });
};

const showRecoveryCodes = () => {
    return axios.get(route("two-factor.recovery-codes")).then((response) => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route("two-factor.confirm"), {
        errorBag: "confirmTwoFactorAuthentication",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios
        .post(route("two-factor.recovery-codes"))
        .then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route("two-factor.disable"), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>

<template>
    <div class="bg-white border rounded-lg shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-slate-900">
                Autenticación de Dos Factores
            </h3>
            <p class="mt-1 text-sm text-slate-600">
                Agrega seguridad adicional a tu cuenta usando autenticación de
                dos factores.
            </p>
        </div>

        <div class="space-y-6">
            <!-- Status -->
            <div>
                <div
                    v-if="twoFactorEnabled && !confirming"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 p-4"
                >
                    <p class="text-sm font-semibold text-emerald-900">
                        ✓ Autenticación de dos factores habilitada
                    </p>
                </div>

                <div
                    v-else-if="twoFactorEnabled && confirming"
                    class="rounded-lg border border-amber-200 bg-amber-50 p-4"
                >
                    <p class="text-sm font-semibold text-amber-900">
                        ⚠ Termina de habilitar la autenticación de dos factores
                    </p>
                </div>

                <div
                    v-else
                    class="rounded-lg border border-slate-200 bg-slate-50 p-4"
                >
                    <p class="text-sm font-semibold text-slate-700">
                        Autenticación de dos factores no habilitada
                    </p>
                </div>

                <p class="mt-4 text-sm text-slate-600">
                    Cuando la autenticación de dos factores esté habilitada, se
                    te pedirá un código de seguridad durante la autenticación.
                    Puedes obtener este código de la aplicación Google
                    Authenticator en tu teléfono.
                </p>
            </div>

            <!-- QR Code and Setup Key -->
            <div v-if="twoFactorEnabled && qrCode">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p
                        v-if="confirming"
                        class="text-sm font-semibold text-slate-900 mb-3"
                    >
                        Escanea el siguiente código QR usando tu aplicación de
                        autenticación:
                    </p>
                    <p v-else class="text-sm font-semibold text-slate-900 mb-3">
                        La autenticación de dos factores está habilitada. Guarda
                        este código:
                    </p>

                    <div
                        class="bg-white p-3 rounded-lg inline-block mb-4"
                        v-html="qrCode"
                    />

                    <div v-if="setupKey" class="mt-4">
                        <p class="text-xs font-semibold text-slate-600 mb-1">
                            Clave de configuración:
                        </p>
                        <code
                            class="block bg-white p-2 rounded border border-slate-200 text-sm font-mono"
                        >
                            {{ setupKey }}
                        </code>
                    </div>
                </div>

                <!-- Confirmation Code Input -->
                <div v-if="confirming" class="mt-4">
                    <label
                        for="code"
                        class="block text-sm font-semibold text-slate-700 mb-2"
                    >
                        Código de Verificación
                    </label>
                    <input
                        id="code"
                        v-model="confirmationForm.code"
                        type="text"
                        inputmode="numeric"
                        placeholder="000000"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition"
                        autocomplete="one-time-code"
                        @keyup.enter="confirmTwoFactorAuthentication"
                    />
                    <p
                        v-if="confirmationForm.errors.code"
                        class="mt-1 text-sm text-rose-600"
                    >
                        {{ confirmationForm.errors.code }}
                    </p>
                </div>
            </div>

            <!-- Recovery Codes -->
            <div v-if="recoveryCodes.length > 0 && !confirming">
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-900 mb-3">
                        ⚠ Guarda estos códigos de recuperación en un lugar
                        seguro
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <code
                            v-for="code in recoveryCodes"
                            :key="code"
                            class="bg-white p-2 rounded border border-amber-200 text-xs font-mono text-center"
                        >
                            {{ code }}
                        </code>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-3">
                <button
                    v-if="!twoFactorEnabled"
                    type="button"
                    :disabled="enabling"
                    @click="enableTwoFactorAuthentication"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg
                        v-if="enabling"
                        class="w-4 h-4 animate-spin"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                    </svg>
                    {{ enabling ? "Habilitando..." : "Habilitar" }}
                </button>

                <button
                    v-if="twoFactorEnabled && confirming"
                    type="button"
                    :disabled="confirming"
                    @click="confirmTwoFactorAuthentication"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Confirmar
                </button>

                <button
                    v-if="
                        twoFactorEnabled &&
                        recoveryCodes.length > 0 &&
                        !confirming
                    "
                    type="button"
                    @click="regenerateRecoveryCodes"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-6 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Regenerar Códigos
                </button>

                <button
                    v-if="
                        twoFactorEnabled &&
                        recoveryCodes.length === 0 &&
                        !confirming
                    "
                    type="button"
                    @click="showRecoveryCodes"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-6 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Ver Códigos de Recuperación
                </button>

                <button
                    v-if="twoFactorEnabled"
                    type="button"
                    :disabled="disabling || confirming"
                    @click="disableTwoFactorAuthentication"
                    class="inline-flex items-center gap-2 rounded-lg border border-rose-300 px-6 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ confirming ? "Cancelar" : "Deshabilitar" }}
                </button>
            </div>
        </div>
    </div>
</template>
