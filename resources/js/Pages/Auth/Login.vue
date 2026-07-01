<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Iniciar Sesión" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div
            v-if="status"
            class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200"
        >
            <p
                class="text-sm font-medium text-emerald-700 flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"
                    />
                </svg>
                {{ status }}
            </p>
        </div>

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">
                    Bienvenido
                </h1>
                <p class="text-slate-500 text-sm">
                    Ingresa tus datos para acceder a tu cuenta
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
                    <InputLabel for="password" value="Contraseña" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label
                        :for="'remember'"
                        class="flex items-center gap-2 cursor-pointer"
                    >
                        <Checkbox
                            v-model:checked="form.remember"
                            id="remember"
                        />
                        <span class="text-sm text-slate-600 font-medium"
                            >Recuérdame</span
                        >
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition"
                    >
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>

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
                        Cargando...
                    </span>
                    <span v-else>Iniciar Sesión</span>
                </PrimaryButton>
            </form>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-slate-500"
                        >¿No tienes cuenta?</span
                    >
                </div>
            </div>

            <Link
                :href="route('register')"
                class="w-full text-center px-4 py-2.5 rounded-lg border border-emerald-200 text-emerald-600 font-semibold text-sm transition hover:bg-emerald-50 hover:border-emerald-300"
            >
                Crear Cuenta
            </Link>
        </div>
    </AuthenticationCard>
</template>
