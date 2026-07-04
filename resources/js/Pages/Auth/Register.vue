<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { watch } from "vue";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useRegisterValidation } from "@/composables/useRegisterValidation";

const {
    errors: validationErrors,
    updateError,
    validateAll,
    clearErrors,
    isVerifyingEmail,
    emailVerificationStatus,
} = useRegisterValidation();

const form = useForm({
    nombre: "",
    apellidos: "",
    ci: "",
    telefono: "",
    email: "",
    fecha_nacimiento: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

// Watchers para validación en tiempo real
watch(
    () => form.nombre,
    (newVal) => {
        updateError("nombre", newVal);
    },
);

watch(
    () => form.apellidos,
    (newVal) => {
        updateError("apellidos", newVal);
    },
);

watch(
    () => form.ci,
    (newVal) => {
        updateError("ci", newVal);
    },
);

watch(
    () => form.telefono,
    (newVal) => {
        updateError("telefono", newVal);
    },
);

watch(
    () => form.email,
    (newVal) => {
        updateError("email", newVal);
    },
);

watch(
    () => form.fecha_nacimiento,
    (newVal) => {
        updateError("fecha_nacimiento", newVal);
    },
);

watch(
    () => form.password,
    (newVal) => {
        updateError("password", newVal, form.password_confirmation);
    },
);

watch(
    () => form.password_confirmation,
    (newVal) => {
        updateError("password_confirmation", newVal, form.password);
    },
);

const submit = () => {
    // Validar todo antes de enviar
    if (!validateAll(form, { shouldValidateRoleId: false })) {
        return;
    }

    form.post(route("register"), {
        preserveScroll: true,
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Registrarse" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">
                    Crear Cuenta
                </h1>
                <p class="text-slate-500 text-sm">
                    Completa el formulario para crear tu cuenta
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Nombres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="nombre" value="Nombre" />
                        <TextInput
                            id="nombre"
                            v-model="form.nombre"
                            type="text"
                            required
                            autofocus
                            autocomplete="given-name"
                            placeholder="Tu nombre"
                            :class="{
                                'border-red-500': validationErrors.nombre,
                            }"
                        />
                        <InputError
                            :message="
                                validationErrors.nombre || form.errors.nombre
                            "
                        />
                    </div>

                    <div>
                        <InputLabel for="apellidos" value="Apellidos" />
                        <TextInput
                            id="apellidos"
                            v-model="form.apellidos"
                            type="text"
                            required
                            autocomplete="family-name"
                            placeholder="Tus apellidos"
                            :class="{
                                'border-red-500': validationErrors.apellidos,
                            }"
                        />
                        <InputError
                            :message="
                                validationErrors.apellidos ||
                                form.errors.apellidos
                            "
                        />
                    </div>
                </div>

                <!-- Documento e Información de Contacto -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="ci" value="CI / RUT" />
                        <TextInput
                            id="ci"
                            v-model="form.ci"
                            type="text"
                            required
                            placeholder="Tu documento"
                            :class="{ 'border-red-500': validationErrors.ci }"
                        />
                        <InputError
                            :message="validationErrors.ci || form.errors.ci"
                        />
                    </div>

                    <div>
                        <InputLabel for="telefono" value="Teléfono" />
                        <TextInput
                            id="telefono"
                            v-model="form.telefono"
                            type="tel"
                            required
                            autocomplete="tel"
                            placeholder="Tu teléfono"
                            :class="{
                                'border-red-500': validationErrors.telefono,
                            }"
                        />
                        <InputError
                            :message="
                                validationErrors.telefono ||
                                form.errors.telefono
                            "
                        />
                    </div>
                </div>

                <!-- Email y Fecha de Nacimiento -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="email" value="Correo Electrónico" />
                        <div class="relative">
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="username"
                                placeholder="tu@email.com"
                                :class="{
                                    'border-red-500': validationErrors.email,
                                    'border-green-500':
                                        emailVerificationStatus === 'valid' &&
                                        !validationErrors.email,
                                    'pr-10':
                                        isVerifyingEmail ||
                                        emailVerificationStatus,
                                }"
                            />
                            <!-- Indicador de carga -->
                            <div
                                v-if="isVerifyingEmail"
                                class="absolute right-3 top-1/2 -translate-y-1/2"
                            >
                                <svg
                                    class="animate-spin h-5 w-5 text-blue-500"
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
                            </div>
                            <!-- Icono de validación correcta -->
                            <div
                                v-else-if="emailVerificationStatus === 'valid'"
                                class="absolute right-3 top-1/2 -translate-y-1/2"
                            >
                                <svg
                                    class="h-5 w-5 text-green-500"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                            </div>
                            <!-- Icono de error -->
                            <div
                                v-else-if="
                                    emailVerificationStatus === 'invalid'
                                "
                                class="absolute right-3 top-1/2 -translate-y-1/2"
                            >
                                <svg
                                    class="h-5 w-5 text-red-500"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                        <InputError
                            :message="
                                validationErrors.email || form.errors.email
                            "
                        />
                        <!-- Mensaje de verificación -->
                        <p
                            v-if="isVerifyingEmail"
                            class="text-sm text-blue-600 mt-1"
                        >
                            Verificando correo...
                        </p>
                        <p
                            v-else-if="emailVerificationStatus === 'valid'"
                            class="text-sm text-green-600 mt-1"
                        >
                            ✓ Correo válido
                        </p>
                    </div>

                    <div>
                        <InputLabel
                            for="fecha_nacimiento"
                            value="Fecha de Nacimiento"
                        />
                        <TextInput
                            id="fecha_nacimiento"
                            v-model="form.fecha_nacimiento"
                            type="date"
                            autocomplete="bday"
                            :class="{
                                'border-red-500':
                                    validationErrors.fecha_nacimiento,
                            }"
                        />
                        <InputError
                            :message="
                                validationErrors.fecha_nacimiento ||
                                form.errors.fecha_nacimiento
                            "
                        />
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="password" value="Contraseña" />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                            :class="{
                                'border-red-500': validationErrors.password,
                            }"
                        />
                        <InputError
                            :message="
                                validationErrors.password ||
                                form.errors.password
                            "
                        />
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
                            :class="{
                                'border-red-500':
                                    validationErrors.password_confirmation,
                            }"
                        />
                        <InputError
                            :message="
                                validationErrors.password_confirmation ||
                                form.errors.password_confirmation
                            "
                        />
                    </div>
                </div>

                <!-- Términos -->
                <div
                    v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                    class="space-y-3"
                >
                    <label
                        class="flex items-start gap-3 cursor-pointer p-3 rounded-lg hover:bg-slate-50 transition"
                    >
                        <Checkbox
                            id="terms"
                            v-model:checked="form.terms"
                            required
                        />
                        <span class="text-sm text-slate-600">
                            Acepto los
                            <a
                                target="_blank"
                                :href="route('terms.show')"
                                class="font-semibold text-emerald-600 hover:text-emerald-700"
                                >Términos de Servicio</a
                            >
                            y la
                            <a
                                target="_blank"
                                :href="route('policy.show')"
                                class="font-semibold text-emerald-600 hover:text-emerald-700"
                                >Política de Privacidad</a
                            >
                        </span>
                    </label>
                    <InputError :message="form.errors.terms" />
                </div>

                <!-- Botón Submit -->
                <PrimaryButton
                    :disabled="
                        form.processing ||
                        validationErrors.nombre !== '' ||
                        validationErrors.apellidos !== '' ||
                        validationErrors.ci !== '' ||
                        validationErrors.telefono !== '' ||
                        validationErrors.email !== '' ||
                        validationErrors.password !== '' ||
                        validationErrors.password_confirmation !== ''
                    "
                    type="submit"
                >
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
                        Registrando...
                    </span>
                    <span v-else>Crear Cuenta</span>
                </PrimaryButton>
            </form>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-slate-500"
                        >¿Ya tienes cuenta?</span
                    >
                </div>
            </div>

            <Link
                :href="route('login')"
                class="w-full text-center px-4 py-2.5 rounded-lg border border-emerald-200 text-emerald-600 font-semibold text-sm transition hover:bg-emerald-50 hover:border-emerald-300"
            >
                Iniciar Sesión
            </Link>
        </div>
    </AuthenticationCard>
</template>
