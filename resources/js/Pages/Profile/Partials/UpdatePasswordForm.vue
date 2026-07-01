<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { showToast } from "@/utils/toast";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    form.put(route("user-password.update"), {
        errorBag: "updatePassword",
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showToast("Contraseña actualizada correctamente.", "success");
        },
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
                passwordInput.value.focus();
            }

            if (form.errors.current_password) {
                form.reset("current_password");
                currentPasswordInput.value.focus();
            }

            const firstError = Object.values(form.errors)[0];
            showToast(
                Array.isArray(firstError)
                    ? firstError[0]
                    : firstError ||
                          "Error al actualizar la contraseña. Verifica los campos e intenta nuevamente.",
                "error",
            );
        },
    });
};
</script>

<template>
    <form
        @submit.prevent="updatePassword"
        class="bg-white border rounded-lg shadow-sm p-6"
    >
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-slate-900">
                Actualizar Contraseña
            </h3>
            <p class="mt-1 text-sm text-slate-600">
                Asegúrate de que tu cuenta use una contraseña larga y aleatoria
                para mantenerse segura.
            </p>
        </div>

        <div class="space-y-6">
            <!-- Current Password -->
            <div>
                <label
                    for="current_password"
                    class="block text-sm font-semibold text-slate-700 mb-2"
                >
                    Contraseña Actual
                </label>
                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition"
                />
                <p
                    v-if="form.errors.current_password"
                    class="mt-1 text-sm text-rose-600"
                >
                    {{ form.errors.current_password }}
                </p>
            </div>

            <!-- New Password -->
            <div>
                <label
                    for="password"
                    class="block text-sm font-semibold text-slate-700 mb-2"
                >
                    Nueva Contraseña
                </label>
                <input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition"
                />
                <p
                    v-if="form.errors.password"
                    class="mt-1 text-sm text-rose-600"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label
                    for="password_confirmation"
                    class="block text-sm font-semibold text-slate-700 mb-2"
                >
                    Confirmar Contraseña
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition"
                />
                <p
                    v-if="form.errors.password_confirmation"
                    class="mt-1 text-sm text-rose-600"
                >
                    {{ form.errors.password_confirmation }}
                </p>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-end gap-3">
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span v-if="form.processing" class="inline-block animate-spin">
                    <svg
                        class="w-4 h-4"
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
                </span>
                {{ form.processing ? "Guardando..." : "Guardar Contraseña" }}
            </button>
        </div>
    </form>
</template>
