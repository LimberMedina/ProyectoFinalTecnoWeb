<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
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
    isOwnerOrSeller.value ? { title: "Mi perfil" } : {},
);
const displayName = computed(() => {
    const rawName = user.value?.nombre || user.value?.name || "";
    const name = String(rawName).trim();

    if (!name) {
        return "Usuario";
    }

    return name.split(" ")[0];
});
const fallbackPhotoUrl = computed(
    () =>
        `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName.value)}&background=10b981&color=ffffff`,
);
const profilePhotoUrl = computed(() => {
    if (user.value?.profile_photo_path) {
        return `/storage/${user.value.profile_photo_path}`;
    }

    if (user.value?.profile_photo_url) {
        return user.value.profile_photo_url;
    }

    return fallbackPhotoUrl.value;
});

const handlePhotoError = (event) => {
    event.target.src = fallbackPhotoUrl.value;
};

const formatDate = (value) => {
    if (!value) return "No registrada";
    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime())
        ? String(value)
        : parsed.toLocaleDateString("es-ES", {
              day: "2-digit",
              month: "long",
              year: "numeric",
          });
};

const profileFields = computed(() => [
    { label: "Nombre", value: displayName.value || "N/A" },
    { label: "Apellidos", value: user.value.apellidos || "N/A" },
    { label: "CI", value: user.value.ci || "N/A" },
    { label: "Teléfono", value: user.value.telefono || "N/A" },
    { label: "Dirección", value: user.value.direccion || "No registrada" },
    {
        label: "Fecha de nacimiento",
        value: formatDate(user.value.fecha_nacimiento),
    },
]);

const accountFields = computed(() => [
    { label: "Correo electrónico", value: user.value.email || "N/A" },
    {
        label: "Correo verificado",
        value: user.value.email_verified_at
            ? "Verificado"
            : "Pendiente de verificación",
    },
    { label: "Rol", value: user.value.role?.nombre || "Cliente" },
    {
        label: "Estado de la cuenta",
        value: user.value.estado === false ? "Inactiva" : "Activa",
    },
]);
</script>

<template>
    <Head title="Mi Perfil" />

    <component :is="currentLayout" v-bind="layoutProps">
        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <!-- Encabezado -->
                <div
                    class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <div
                            class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-emerald-700 shadow-sm"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-emerald-500"
                            ></span>
                            Mi cuenta
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Mi perfil
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Consulta todos los datos de tu cuenta. La edición de
                            información, contraseña y seguridad está en
                            Configuración.
                        </p>
                    </div>

                    <Link
                        :href="route('perfil.edit')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
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
                                d="M12 20h9"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"
                            />
                        </svg>
                        Editar información
                    </Link>
                </div>

                <!-- Grid principal -->
                <div class="grid gap-6 lg:grid-cols-12">
                    <!-- Columna izquierda: Avatar + acciones -->
                    <aside class="space-y-6 lg:col-span-4">
                        <!-- Tarjeta de perfil -->
                        <div
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div class="flex flex-col items-center text-center">
                                <div
                                    class="rounded-full bg-emerald-50 p-2 shadow-inner"
                                >
                                    <img
                                        :src="profilePhotoUrl"
                                        :alt="`Foto de perfil de ${displayName}`"
                                        class="h-28 w-28 rounded-full object-cover ring-4 ring-white"
                                        @error="handlePhotoError"
                                    />
                                </div>
                                <h2
                                    class="mt-4 text-2xl font-black text-slate-900"
                                >
                                    {{ displayName }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ user.role?.nombre || "Cliente" }}
                                </p>
                            </div>

                            <div class="mt-6 grid grid-cols-2 gap-3">
                                <div
                                    class="rounded-2xl border border-slate-100 bg-slate-50 p-3 text-center"
                                >
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                    >
                                        Estado
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-black text-slate-800"
                                    >
                                        {{
                                            user.estado === false
                                                ? "Inactiva"
                                                : "Activa"
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-slate-100 bg-slate-50 p-3 text-center"
                                >
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                    >
                                        Email
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-black text-slate-800"
                                    >
                                        {{
                                            user.email_verified_at
                                                ? "Verificado"
                                                : "Pendiente"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones rápidas -->
                        <div
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div class="mb-5 border-b border-slate-100 pb-5">
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                >
                                    Accesos
                                </p>
                                <h2
                                    class="mt-1 text-xl font-black text-slate-900"
                                >
                                    Acciones rápidas
                                </h2>
                            </div>

                            <div class="space-y-3">
                                <Link
                                    :href="route('profile.settings')"
                                    class="flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50/50 px-4 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50"
                                >
                                    <span class="flex items-center gap-2">
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
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                        </svg>
                                        Ir a configuración
                                    </span>
                                    <span aria-hidden="true">→</span>
                                </Link>

                                <Link
                                    :href="route('mis-pedidos.index')"
                                    class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                                >
                                    <span class="flex items-center gap-2">
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
                                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                                            />
                                        </svg>
                                        Ver mis pedidos
                                    </span>
                                    <span aria-hidden="true">→</span>
                                </Link>
                            </div>
                        </div>
                    </aside>

                    <!-- Columna derecha: Datos personales + cuenta -->
                    <section class="space-y-6 lg:col-span-8">
                        <!-- Datos personales -->
                        <div
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div
                                class="mb-6 flex items-center justify-between gap-3 border-b border-slate-100 pb-5"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                    >
                                        Perfil
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-black text-slate-900"
                                    >
                                        Datos personales
                                    </h2>
                                    <p class="mt-0.5 text-sm text-slate-500">
                                        Información básica y de contacto.
                                    </p>
                                </div>
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-emerald-600"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <dl class="grid gap-3 sm:grid-cols-2">
                                <div
                                    v-for="field in profileFields"
                                    :key="field.label"
                                    class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3"
                                >
                                    <dt
                                        class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                    >
                                        {{ field.label }}
                                    </dt>
                                    <dd
                                        class="mt-1 text-sm font-semibold text-slate-800"
                                    >
                                        {{ field.value }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Cuenta -->
                        <div
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div
                                class="mb-6 flex items-center justify-between gap-3 border-b border-slate-100 pb-5"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.22em] text-sky-700"
                                    >
                                        Cuenta
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-black text-slate-900"
                                    >
                                        Información de cuenta
                                    </h2>
                                    <p class="mt-0.5 text-sm text-slate-500">
                                        Estado de acceso y verificación.
                                    </p>
                                </div>
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-sky-600"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <dl class="grid gap-3 sm:grid-cols-2">
                                <div
                                    v-for="field in accountFields"
                                    :key="field.label"
                                    class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3"
                                >
                                    <dt
                                        class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                    >
                                        {{ field.label }}
                                    </dt>
                                    <dd
                                        class="mt-1 text-sm font-semibold"
                                        :class="{
                                            'text-emerald-700':
                                                field.value === 'Activa' ||
                                                field.value === 'Verificado',
                                            'text-rose-600':
                                                field.value === 'Inactiva' ||
                                                field.value ===
                                                    'Pendiente de verificación',
                                            'text-slate-800':
                                                field.value !== 'Activa' &&
                                                field.value !== 'Verificado' &&
                                                field.value !== 'Inactiva' &&
                                                field.value !==
                                                    'Pendiente de verificación',
                                        }"
                                    >
                                        {{ field.value }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </component>
</template>
