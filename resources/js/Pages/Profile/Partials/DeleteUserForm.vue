<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.delete(route("current-user.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <div class="bg-white border border-rose-200 rounded-lg shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-slate-900">
                Eliminar Cuenta
            </h3>
            <p class="mt-1 text-sm text-slate-600">
                Eliminar permanentemente tu cuenta.
            </p>
        </div>

        <div class="max-w-xl">
            <p class="text-sm text-slate-600">
                Una vez que se elimine tu cuenta, todos sus recursos y datos se
                eliminarán permanentemente. Antes de eliminar tu cuenta,
                descarga cualquier dato o información que desees conservar.
            </p>

            <button
                @click="confirmUserDeletion"
                class="mt-6 inline-flex items-center gap-2 rounded-lg border border-rose-300 px-6 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
            >
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
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                </svg>
                Eliminar Cuenta
            </button>
        </div>

        <!-- Delete Account Confirmation Modal -->
        <div
            v-if="confirmingUserDeletion"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
            >
                <!-- Header -->
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Eliminar Cuenta
                    </h2>
                </div>

                <!-- Body -->
                <div class="px-6 py-4 space-y-4">
                    <p class="text-sm text-slate-600">
                        ¿Estás seguro de que deseas eliminar tu cuenta? Una vez
                        que se elimine tu cuenta, todos sus recursos y datos se
                        eliminarán permanentemente. Ingresa tu contraseña para
                        confirmar que deseas eliminar permanentemente tu cuenta.
                    </p>

                    <div>
                        <input
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            placeholder="Contraseña"
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-rose-500 focus:ring-1 focus:ring-rose-500 outline-none transition"
                            @keyup.enter="deleteUser"
                        />

                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-sm text-rose-600"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="border-t border-slate-200 px-6 py-4 flex justify-end gap-3"
                >
                    <button
                        type="button"
                        @click="closeModal"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        :disabled="form.processing"
                        @click="deleteUser"
                        class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg
                            v-if="form.processing"
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
                        {{
                            form.processing
                                ? "Eliminando..."
                                : "Eliminar Cuenta"
                        }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
