<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent",
);
</script>

<template>
    <Head title="Verificar Email" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">
                    Verificar Email
                </h1>
                <p class="text-slate-600 text-sm">
                    Hemos enviado un enlace de verificación a tu correo. Por
                    favor revisa tu bandeja de entrada y haz clic en el enlace
                    para continuar.
                </p>
            </div>

            <div
                v-if="verificationLinkSent"
                class="p-4 rounded-lg bg-emerald-50 border border-emerald-200"
            >
                <p
                    class="text-sm font-medium text-emerald-700 flex items-center gap-2"
                >
                    <svg
                        class="w-5 h-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    Se ha enviado un nuevo enlace de verificación a tu correo.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
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
                        Reenviando...
                    </span>
                    <span v-else>Reenviar Enlace de Verificación</span>
                </PrimaryButton>
            </form>

            <div class="flex gap-3">
                <Link
                    :href="route('profile.settings')"
                    class="flex-1 text-center px-4 py-2.5 rounded-lg border border-blue-200 text-blue-600 font-semibold text-sm transition hover:bg-blue-50"
                >
                    Editar Perfil
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex-1 text-center px-4 py-2.5 rounded-lg border border-slate-200 text-slate-600 font-semibold text-sm transition hover:bg-slate-50"
                >
                    Cerrar Sesión
                </Link>
            </div>
        </div>
    </AuthenticationCard>
</template>
