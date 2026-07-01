<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.update"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Restablecer Contraseña" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">
                    Restablecer Contraseña
                </h1>
                <p class="text-slate-600 text-sm">
                    Ingresa tu nueva contraseña a continuación
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="email" value="Correo Electrónico" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="tu@email.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Nueva Contraseña" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel
                        for="password_confirmation"
                        value="Confirmar Contraseña"
                    />
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError :message="form.errors.password_confirmation" />
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
                        Restableciendo...
                    </span>
                    <span v-else>Restablecer Contraseña</span>
                </PrimaryButton>
            </form>

            <div class="text-center">
                <router-link
                    :to="{ name: 'login' }"
                    class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition"
                >
                    Volver al Login
                </router-link>
            </div>
        </div>
    </AuthenticationCard>
</template>
