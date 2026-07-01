<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

defineProps({
    sessions: Array,
});

const confirmingLogout = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmLogout = () => {
    confirmingLogout.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const logoutOtherBrowserSessions = () => {
    form.delete(route("other-browser-sessions.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingLogout.value = false;

    form.reset();
};
</script>

<template>
    <div class="bg-white border rounded-lg shadow-sm p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-slate-900">
                Sesiones del Navegador
            </h3>
            <p class="mt-1 text-sm text-slate-600">
                Gestiona y cierra tus sesiones activas en otros navegadores y
                dispositivos.
            </p>
        </div>

        <p class="text-sm text-slate-600 mb-6">
            Si es necesario, puedes cerrar sesión en todas las demás sesiones
            del navegador en todos tus dispositivos. Algunas de tus sesiones
            recientes aparecen a continuación; sin embargo, esta lista puede no
            ser exhaustiva. Si crees que tu cuenta ha sido comprometida, también
            debes actualizar tu contraseña.
        </p>

        <!-- Other Browser Sessions -->
        <div v-if="sessions.length > 0" class="space-y-4 mb-6">
            <div
                v-for="(session, i) in sessions"
                :key="i"
                class="flex items-start gap-4 p-4 border border-slate-200 rounded-lg"
            >
                <div class="flex-shrink-0 pt-1">
                    <svg
                        v-if="session.agent.is_desktop"
                        class="w-8 h-8 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"
                        />
                    </svg>

                    <svg
                        v-else
                        class="w-8 h-8 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"
                        />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-slate-900">
                        {{ session.agent.platform || "Desconocido" }} -
                        {{ session.agent.browser || "Desconocido" }}
                    </div>

                    <div class="mt-1 text-xs text-slate-600">
                        {{ session.ip_address }}
                        <span
                            v-if="session.is_current_device"
                            class="ml-2 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700"
                        >
                            Este dispositivo
                        </span>
                        <span v-else class="ml-2 text-slate-500">
                            Última actividad: {{ session.last_active }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <button
            @click="confirmLogout"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
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
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                />
            </svg>
            Cerrar Otras Sesiones
        </button>

        <!-- Log Out Other Devices Confirmation Modal -->
        <div
            v-if="confirmingLogout"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        >
            <div
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
            >
                <!-- Header -->
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Cerrar Otras Sesiones del Navegador
                    </h2>
                </div>

                <!-- Body -->
                <div class="px-6 py-4 space-y-4">
                    <p class="text-sm text-slate-600">
                        Ingresa tu contraseña para confirmar que deseas cerrar
                        sesión en todas las demás sesiones del navegador en
                        todos tus dispositivos.
                    </p>

                    <div>
                        <input
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            placeholder="Contraseña"
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition"
                            @keyup.enter="logoutOtherBrowserSessions"
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
                        @click="logoutOtherBrowserSessions"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
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
                            form.processing ? "Cerrando..." : "Cerrar Sesiones"
                        }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
