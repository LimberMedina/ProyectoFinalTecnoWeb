<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";
import TwoFactorAuthenticationForm from "@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
import LogoutOtherBrowserSessionsForm from "@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import { useTheme } from "@/composables/useTheme";
import { showToast } from "@/utils/toast";

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

const page = usePage();
const userRole = computed(() => page.props.auth?.user?.role || {});
const isOwnerOrSeller = computed(() => {
    const roleId = String(userRole.value.id || "");
    const roleName = (userRole.value.nombre || "").toLowerCase();

    return (
        ["1", "2"].includes(roleId) ||
        ["propietario", "vendedor"].includes(roleName)
    );
});
const currentLayout = computed(() =>
    isOwnerOrSeller.value ? AppLayout : PublicStoreLayout,
);
const layoutProps = computed(() =>
    isOwnerOrSeller.value ? { title: "Configuración de Cuenta" } : {},
);

const {
    theme,
    mode,
    fontSize,
    contrast,
    setTheme,
    setMode,
    setFontSize,
    setContrast,
} = useTheme();

const resetPreferences = () => {
    setTheme("");
    setMode("dia");
    setFontSize("normal");
    setContrast("normal");

    showToast(
        "Configuraciones visuales restablecidas exitosamente.",
        "success",
    );
};

const themeOptions = [
    {
        value: "ninos",
        label: "Niños",
        description: "Colores cálidos y divertidos",
        icon: "FaChild",
    },
    {
        value: "jovenes",
        label: "Jóvenes",
        description: "Azules intensos y modernos",
        icon: "FaUserGraduate",
    },
    {
        value: "adultos",
        label: "Adultos",
        description: "Paleta neutra y elegante",
        icon: "FaUserTie",
    },
];

const modeOptions = [
    { value: "dia", label: "Día", icon: "FaSun" },
    { value: "noche", label: "Noche", icon: "FaMoon" },
];

const fontSizeOptions = [
    { value: "small", label: "Pequeño" },
    { value: "normal", label: "Normal" },
    { value: "large", label: "Grande" },
];

const contrastOptions = [
    { value: "normal", label: "Normal", icon: "FaEye" },
    { value: "high", label: "Alto", icon: "FaAdjust" },
];

const iconLibrary = {
    FaChild: {
        viewBox: "0 0 24 24",
        paths: [
            "M12 4a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z",
            "M9 11c-1 1-1.5 2.8-1.5 4.5V19h9v-3.5c0-1.7-.5-3.5-1.5-4.5-1-1-2.5-1-4.5-1s-3.5 0-4.5 1Z",
        ],
    },
    FaUserGraduate: {
        viewBox: "0 0 24 24",
        paths: [
            "M12 3 3 6l9 4 9-4-9-3Z",
            "M4.5 6.75v4.5c0 4.75 3.5 7.5 7.5 7.5s7.5-2.75 7.5-7.5v-4.5",
            "M12 11.5v8",
        ],
    },
    FaUserTie: {
        viewBox: "0 0 24 24",
        paths: [
            "M12 12c2.8 0 5-2.2 5-5S14.8 2 12 2 7 4.2 7 7s2.2 5 5 5Z",
            "M6 22c0-3.3 2.7-6 6-6s6 2.7 6 6",
            "M12 12v6",
            "M10 16h4",
        ],
    },
    FaSun: {
        viewBox: "0 0 24 24",
        paths: [
            "M12 6a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z",
            "M9 11c-1 2-1 4 3 4s4-2 3-4c-.3-.7-1-1-2-1h-2c-1 0-1.7.3-2 1Z",
        ],
    },
    FaStar: {
        viewBox: "0 0 24 24",
        paths: [
            "M12 2.5 15.1 8.9 22 9.7 17 14.2 18.2 21 12 17.7 5.8 21 7 14.2 2 9.7 8.9 8.9 12 2.5Z",
        ],
    },
    FaUserTie: {
        viewBox: "0 0 24 24",
        paths: [
            "M12 12c2.8 0 5-2.2 5-5S14.8 2 12 2 7 4.2 7 7s2.2 5 5 5Z",
            "M6 22c0-3.3 2.7-6 6-6s6 2.7 6 6",
            "M12 12v6",
            "M10 16h4",
        ],
    },
    FaSun: {
        viewBox: "0 0 24 24",
        paths: [
            "M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z",
            "M12 2v2",
            "M12 20v2",
            "M4.9 4.9l1.4 1.4",
            "M17.7 17.7l1.4 1.4",
            "M2 12h2",
            "M20 12h2",
            "M4.9 19.1l1.4-1.4",
            "M17.7 6.3l1.4-1.4",
        ],
    },
    FaMoon: {
        viewBox: "0 0 24 24",
        paths: ["M21 12.79A9 9 0 0 1 11.21 3 7 7 0 1 0 21 12.79Z"],
    },
    FaEye: {
        viewBox: "0 0 24 24",
        paths: [
            "M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z",
            "M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z",
        ],
    },
    FaAdjust: {
        viewBox: "0 0 24 24",
        paths: ["M12 2a10 10 0 0 0 0 20V2Z"],
    },
};

const iconDefinition = (name) => iconLibrary[name] || iconLibrary.FaPalette;
</script>

<template>
    <Head title="Configuración de Cuenta" />

    <component :is="currentLayout" v-bind="layoutProps">
        <div>
            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <FlashNotification />

                <div
                    class="mb-6 flex flex-wrap items-start justify-between gap-4"
                >
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">
                            Configuración de Cuenta
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm text-slate-600">
                            Ajusta tus datos personales, cambia tu contraseña y
                            elige el estilo visual que prefieras para la página.
                        </p>
                    </div>

                    <Link
                        :href="route('perfil.show')"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                        Volver al perfil
                    </Link>
                </div>

                <div class="grid gap-6 lg:grid-cols-12">
                    <div class="space-y-6 lg:col-span-5">
                        <section
                            class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"
                        >
                            <div
                                class="mb-4 flex flex-wrap items-center justify-between gap-3"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="rounded-2xl bg-emerald-50 p-3 text-emerald-700"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M12 6v6l4 2"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2
                                            class="text-lg font-semibold text-slate-800"
                                        >
                                            Apariencia
                                        </h2>
                                        <p class="text-sm text-slate-500">
                                            Tema, modo, tamaño y contraste.
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="resetPreferences"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M4 4v6h6" />
                                        <path d="M20 20v-6h-6" />
                                        <path d="M5 19a9 9 0 0 0 14-7.9" />
                                        <path d="M19 5a9 9 0 0 0-14 7.9" />
                                    </svg>
                                    Restablecer estilo
                                </button>
                            </div>

                            <div class="grid gap-5">
                                <div
                                    class="rounded-3xl border border-slate-200 bg-slate-50 p-4"
                                >
                                    <p
                                        class="mb-3 text-sm font-semibold text-slate-700"
                                    >
                                        Tema de color
                                    </p>
                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <button
                                            v-for="option in themeOptions"
                                            :key="option.value"
                                            type="button"
                                            @click="setTheme(option.value)"
                                            class="group rounded-3xl border p-3 text-left transition"
                                            :class="
                                                theme === option.value
                                                    ? 'border-emerald-500 ring-4 ring-emerald-100 bg-white'
                                                    : 'border-slate-200 bg-white hover:border-emerald-200 hover:bg-slate-50'
                                            "
                                        >
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <svg
                                                    class="h-8 w-8 text-slate-700"
                                                    :viewBox="
                                                        iconDefinition(
                                                            option.icon,
                                                        ).viewBox
                                                    "
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        v-for="(
                                                            strokePath, index
                                                        ) in iconDefinition(
                                                            option.icon,
                                                        ).paths"
                                                        :key="`theme-icon-${option.value}-${index}`"
                                                        :d="strokePath"
                                                    />
                                                </svg>
                                                <div class="flex-1">
                                                    <div
                                                        class="mt-1 text-sm font-semibold text-slate-800"
                                                    >
                                                        {{ option.label }}
                                                    </div>
                                                    <div
                                                        class="text-xs text-slate-500"
                                                    >
                                                        {{ option.description }}
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <div
                                    class="rounded-3xl border border-slate-200 bg-slate-50 p-4"
                                >
                                    <p
                                        class="mb-3 text-sm font-semibold text-slate-700"
                                    >
                                        Modo
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            v-for="option in modeOptions"
                                            :key="option.value"
                                            type="button"
                                            @click="setMode(option.value)"
                                            class="inline-flex items-center rounded-2xl px-4 py-3 text-sm font-semibold transition"
                                            :class="
                                                mode === option.value
                                                    ? 'bg-emerald-600 text-white shadow-sm'
                                                    : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                            "
                                        >
                                            <svg
                                                class="mr-2 h-5 w-5"
                                                :viewBox="
                                                    iconDefinition(option.icon)
                                                        .viewBox
                                                "
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    v-for="(
                                                        strokePath, index
                                                    ) in iconDefinition(
                                                        option.icon,
                                                    ).paths"
                                                    :key="`mode-icon-${option.value}-${index}`"
                                                    :d="strokePath"
                                                />
                                            </svg>
                                            {{ option.label }}
                                        </button>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div
                                        class="rounded-3xl border border-slate-200 bg-slate-50 p-4"
                                    >
                                        <p
                                            class="mb-3 text-sm font-semibold text-slate-700"
                                        >
                                            Tamaño de fuente
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                v-for="option in fontSizeOptions"
                                                :key="option.value"
                                                type="button"
                                                @click="
                                                    setFontSize(option.value)
                                                "
                                                class="rounded-2xl px-3 py-2 text-sm font-semibold transition"
                                                :class="
                                                    fontSize === option.value
                                                        ? 'bg-sky-600 text-white shadow-sm'
                                                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                                "
                                            >
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-3xl border border-slate-200 bg-slate-50 p-4"
                                    >
                                        <p
                                            class="mb-3 text-sm font-semibold text-slate-700"
                                        >
                                            Contraste
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                v-for="option in contrastOptions"
                                                :key="option.value"
                                                type="button"
                                                @click="
                                                    setContrast(option.value)
                                                "
                                                class="inline-flex items-center rounded-2xl px-3 py-2 text-sm font-semibold transition"
                                                :class="
                                                    contrast === option.value
                                                        ? 'bg-slate-800 text-white shadow-sm'
                                                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                                "
                                            >
                                                <svg
                                                    class="mr-2 h-5 w-5"
                                                    :viewBox="
                                                        iconDefinition(
                                                            option.icon,
                                                        ).viewBox
                                                    "
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        v-for="(
                                                            strokePath, index
                                                        ) in iconDefinition(
                                                            option.icon,
                                                        ).paths"
                                                        :key="`contrast-icon-${option.value}-${index}`"
                                                        :d="strokePath"
                                                    />
                                                </svg>
                                                {{ option.label }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="space-y-6 lg:col-span-7">
                        <UpdatePasswordForm
                            v-if="$page.props.jetstream.canUpdatePassword"
                        />
                    </div>
                </div>
            </main>
        </div>
    </component>
</template>
