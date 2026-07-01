<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    evento: Object,
});

const formatoFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

const getClaseAccion = (accion) => {
    const clases = {
        login: "bg-green-100 text-green-800",
        logout: "bg-yellow-100 text-yellow-800",
        create: "bg-blue-100 text-blue-800",
        update: "bg-indigo-100 text-indigo-800",
        delete: "bg-red-100 text-red-800",
        view: "bg-slate-100 text-slate-800",
        download: "bg-blue-100 text-blue-800",
    };
    return clases[accion] || "bg-slate-100 text-slate-800";
};
</script>

<template>
    <Head :title="`Evento #${evento.id}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('bitacora.index')"
                    class="text-cyan-600 hover:text-cyan-800 transition-colors"
                >
                    ← Volver
                </Link>
                <h2 class="text-2xl font-bold text-slate-800">
                    Detalles del Evento
                </h2>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Panel Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Información General -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-6 text-slate-700">
                        Información General
                    </h3>

                    <div class="space-y-4">
                        <!-- Evento ID -->
                        <div class="border-b border-slate-200 pb-4">
                            <label class="text-sm font-medium text-slate-600">
                                ID del Evento
                            </label>
                            <p
                                class="mt-1 text-lg font-semibold text-slate-900"
                            >
                                #{{ evento.id }}
                            </p>
                        </div>

                        <!-- Acción -->
                        <div class="border-b border-slate-200 pb-4">
                            <label class="text-sm font-medium text-slate-600">
                                Acción
                            </label>
                            <div class="mt-2">
                                <span
                                    :class="[
                                        'inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold',
                                        getClaseAccion(evento.accion),
                                    ]"
                                >
                                    {{ evento.accion.toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <!-- Entidad -->
                        <div class="border-b border-slate-200 pb-4">
                            <label class="text-sm font-medium text-slate-600">
                                Tipo de Entidad
                            </label>
                            <p class="mt-1 text-slate-900">
                                {{ evento.entidad || "N/A" }}
                            </p>
                        </div>

                        <!-- Entidad ID -->
                        <div class="border-b border-slate-200 pb-4">
                            <label class="text-sm font-medium text-slate-600">
                                ID de Entidad
                            </label>
                            <p class="mt-1 text-slate-900">
                                {{ evento.entidad_id || "N/A" }}
                            </p>
                        </div>

                        <!-- Entidad Nombre -->
                        <div class="border-b border-slate-200 pb-4">
                            <label class="text-sm font-medium text-slate-600">
                                Nombre de Entidad
                            </label>
                            <p class="mt-1 text-slate-900">
                                {{ evento.entidad_nombre || "N/A" }}
                            </p>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label class="text-sm font-medium text-slate-600">
                                Descripción
                            </label>
                            <p
                                class="mt-2 p-4 bg-slate-50 rounded-lg text-slate-900"
                            >
                                {{ evento.descripcion || "Sin descripción" }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cambios Realizados -->
                <div
                    v-if="evento.cambios"
                    class="bg-white rounded-lg shadow-sm p-6"
                >
                    <h3 class="text-lg font-semibold mb-6 text-slate-700">
                        Cambios Realizados
                    </h3>

                    <div
                        class="bg-slate-50 rounded-lg p-4 font-mono text-sm overflow-x-auto"
                    >
                        <pre>{{ JSON.stringify(evento.cambios, null, 2) }}</pre>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="space-y-6">
                <!-- Usuario -->
                <div
                    class="bg-gradient-to-br from-cyan-50 to-teal-50 rounded-lg shadow-sm p-6 border border-cyan-200/70"
                >
                    <h3 class="text-lg font-semibold mb-4 text-slate-700">
                        Usuario
                    </h3>

                    <div class="space-y-3">
                        <div v-if="evento.user">
                            <p class="text-sm text-slate-600">Nombre</p>
                            <p class="font-semibold text-slate-900">
                                {{ evento.user.name }}
                            </p>
                        </div>

                        <div v-if="evento.user">
                            <p class="text-sm text-slate-600">Email</p>
                            <p class="font-semibold text-slate-900">
                                {{ evento.user.email }}
                            </p>
                        </div>

                        <div
                            v-if="evento.user"
                            class="pt-3 border-t border-cyan-200"
                        >
                            <p class="text-sm text-slate-600">Rol</p>
                            <p class="font-semibold text-slate-900">
                                {{ evento.user.role?.nombre || "N/A" }}
                            </p>
                        </div>

                        <div v-else class="text-slate-600 italic">
                            Evento de sistema
                        </div>
                    </div>
                </div>

                <!-- Información Técnica -->
                <div
                    class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-lg shadow-sm p-6 border border-slate-200"
                >
                    <h3 class="text-lg font-semibold mb-4 text-slate-700">
                        Información Técnica
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-slate-600 font-medium">
                                Fecha y Hora
                            </p>
                            <p class="text-slate-900 mt-1">
                                {{ formatoFecha(evento.created_at) }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-300">
                            <p class="text-slate-600 font-medium">
                                Dirección IP
                            </p>
                            <p class="text-slate-900 mt-1 break-all">
                                {{ evento.ip_address || "N/A" }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-300">
                            <p class="text-slate-600 font-medium">User Agent</p>
                            <p
                                class="text-slate-900 mt-1 text-xs break-all max-h-20 overflow-y-auto"
                            >
                                {{ evento.user_agent || "N/A" }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botón de Acción -->
                <Link
                    :href="route('bitacora.index')"
                    class="block w-full px-4 py-3 bg-cyan-600 text-white text-center rounded-lg hover:bg-cyan-700 transition-colors font-medium"
                >
                    Volver a la Bitácora
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
