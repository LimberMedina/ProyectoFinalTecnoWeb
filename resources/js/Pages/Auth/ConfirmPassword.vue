<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const form = useForm({
    password: "",
});

const passwordInput = ref(null);

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => {
            form.reset();

            passwordInput.value.focus();
        },
    });
};
</script>

<template>
    <Head title="Secure Area" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">
                    Área Segura
                </h1>
                <p class="text-slate-600 text-sm">
                    Esta es un área segura de la aplicación. Por favor confirma
                    tu contraseña antes de continuar.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="password" value="Contraseña" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        autofocus
                        placeholder="••••••••"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <PrimaryButton :disabled="form.processing" type="submit">
                    <span
                        v-if="form.processing"
                        class="flex items-center justify-center gap-2"
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
                        Confirmando...
                    </span>
                    <span v-else>Confirmar</span>
                </PrimaryButton>
            </form>
        </div>
    </AuthenticationCard>
</template>
