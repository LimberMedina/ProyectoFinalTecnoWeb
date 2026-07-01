<script setup>
import { nextTick, ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const recovery = ref(false);

const form = useForm({
    code: "",
    recovery_code: "",
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value ^= true;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value.focus();
        form.code = "";
    } else {
        codeInput.value.focus();
        form.recovery_code = "";
    }
};

const submit = () => {
    form.post(route("two-factor.login"));
};
</script>

<template>
    <Head title="Two-factor Confirmation" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">
                    Verificación de Dos Factores
                </h1>
                <p class="text-slate-600 text-sm">
                    <template v-if="!recovery">
                        Ingresa el código de autenticación de tu aplicación
                        autenticadora para confirmar tu acceso.
                    </template>
                    <template v-else>
                        Ingresa uno de tus códigos de recuperación de
                        emergencia.
                    </template>
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div v-if="!recovery">
                    <InputLabel for="code" value="Código de Autenticación" />
                    <TextInput
                        id="code"
                        ref="codeInput"
                        v-model="form.code"
                        type="text"
                        inputmode="numeric"
                        autofocus
                        autocomplete="one-time-code"
                        placeholder="000000"
                    />
                    <InputError :message="form.errors.code" />
                </div>

                <div v-else>
                    <InputLabel
                        for="recovery_code"
                        value="Código de Recuperación"
                    />
                    <TextInput
                        id="recovery_code"
                        ref="recoveryCodeInput"
                        v-model="form.recovery_code"
                        type="text"
                        autocomplete="one-time-code"
                        placeholder="XXXX-XXXX-XXXX"
                    />
                    <InputError :message="form.errors.recovery_code" />
                </div>

                <div class="flex items-center justify-between gap-4">
                    <button
                        type="button"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition"
                        @click.prevent="toggleRecovery"
                    >
                        <template v-if="!recovery">
                            Usar código de recuperación
                        </template>
                        <template v-else>
                            Usar código de autenticación
                        </template>
                    </button>

                    <PrimaryButton :disabled="form.processing" type="submit">
                        <span
                            v-if="form.processing"
                            class="flex items-center gap-2"
                        >
                            <svg
                                class="animate-spin h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Verificando...
                        </span>
                        <span v-else>Iniciar Sesión</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticationCard>
</template>
