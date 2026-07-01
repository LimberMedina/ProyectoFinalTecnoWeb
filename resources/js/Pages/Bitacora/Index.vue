<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    bitacora: Object,
    acciones: Array,
    entidades: Array,
    filtros: Object,
});

const filtrosLocales = ref({
    fecha_inicio: "",
    fecha_fin: "",
    accion: "todas",
    entidad: "todas",
    usuario_id: "",
    buscar: "",
});

const aplicarFiltros = () => {
    router.get(route("bitacora.index"), filtrosLocales.value, {
        preserveState: true,
    });
};

const limpiarFiltros = () => {
    filtrosLocales.value = {
        fecha_inicio: "",
        fecha_fin: "",
        accion: "todas",
        entidad: "todas",
        usuario_id: "",
        buscar: "",
    };
    router.get(route("bitacora.index"));
};

const exportarCSV = () => {
    router.get(route("bitacora.exportar"), filtrosLocales.value);
};

const formatoFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
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

const getClaseEntidad = (entidad) => {
    const clases = {
        Usuario: "bg-indigo-100 text-indigo-800",
        Producto: "bg-blue-100 text-blue-800",
        Venta: "bg-green-100 text-green-800",
        Credito: "bg-yellow-100 text-yellow-800",
        Pago: "bg-green-100 text-green-800",
        Categoria: "bg-slate-100 text-slate-800",
        Promocion: "bg-blue-100 text-blue-800",
    };
    return clases[entidad] || "bg-slate-100 text-slate-800";
};

const hasFilters = computed(() => {
    return (
        filtrosLocales.value.fecha_inicio ||
        filtrosLocales.value.fecha_fin ||
        filtrosLocales.value.accion !== "todas" ||
        filtrosLocales.value.entidad !== "todas" ||
        filtrosLocales.value.usuario_id ||
        filtrosLocales.value.buscar
    );
});
</script>

<template>
    <Head title="Bitácora de Auditoría" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-slate-800">
                    📋 Bitácora de Auditoría
                </h2>
                <button
                    @click="exportarCSV"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
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
                            d="M12 19l9 2-9-18-9 18 9-2m0 0v-8m0 8H3m15-8h3"
                        />
                    </svg>
                    Exportar CSV
                </button>
            </div>
        </template>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4 text-slate-700">Filtros</h3>

            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-4"
            >
                <!-- Fecha Inicio -->
                <div>
                    <label
                        class="block text-sm font-medium text-slate-600 mb-2"
                    >
                        Desde
                    </label>
                    <input
                        v-model="filtrosLocales.fecha_inicio"
                        type="date"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    />
                </div>

                <!-- Fecha Fin -->
                <div>
                    <label
                        class="block text-sm font-medium text-slate-600 mb-2"
                    >
                        Hasta
                    </label>
                    <input
                        v-model="filtrosLocales.fecha_fin"
                        type="date"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    />
                </div>

                <!-- Acción -->
                <div>
                    <label
                        class="block text-sm font-medium text-slate-600 mb-2"
                    >
                        Acción
                    </label>
                    <select
                        v-model="filtrosLocales.accion"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    >
                        <option value="todas">Todas</option>
                        <option
                            v-for="accion in acciones"
                            :key="accion"
                            :value="accion"
                        >
                            {{ accion }}
                        </option>
                    </select>
                </div>

                <!-- Entidad -->
                <div>
                    <label
                        class="block text-sm font-medium text-slate-600 mb-2"
                    >
                        Entidad
                    </label>
                    <select
                        v-model="filtrosLocales.entidad"
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    >
                        <option value="todas">Todas</option>
                        <option
                            v-for="entidad in entidades"
                            :key="entidad"
                            :value="entidad"
                        >
                            {{ entidad }}
                        </option>
                    </select>
                </div>

                <!-- Búsqueda -->
                <div>
                    <label
                        class="block text-sm font-medium text-slate-600 mb-2"
                    >
                        Buscar
                    </label>
                    <input
                        v-model="filtrosLocales.buscar"
                        type="text"
                        placeholder="Usuario, descripción..."
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    />
                </div>

                <!-- Botones -->
                <div class="flex items-end gap-2">
                    <button
                        @click="aplicarFiltros"
                        class="flex-1 px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors font-medium"
                    >
                        Filtrar
                    </button>
                    <button
                        v-if="hasFilters"
                        @click="limpiarFiltros"
                        class="flex-1 px-4 py-2 bg-slate-300 text-slate-700 rounded-lg hover:bg-slate-400 transition-colors font-medium"
                    >
                        Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de Bitácora -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead
                        class="bg-gradient-to-r from-cyan-50 to-teal-50 border-b border-slate-200"
                    >
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-slate-700"
                            >
                                Fecha
                            </th>
                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-slate-700"
                            >
                                Usuario
                            </th>
                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-slate-700"
                            >
                                Acción
                            </th>
                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-slate-700"
                            >
                                Entidad
                            </th>
                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-slate-700"
                            >
                                Descripción
                            </th>
                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-slate-700"
                            >
                                IP
                            </th>
                            <th
                                class="px-6 py-4 text-left text-sm font-semibold text-slate-700"
                            >
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr
                            v-for="evento in bitacora.data"
                            :key="evento.id"
                            class="hover:bg-slate-50 transition-colors"
                        >
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-slate-600"
                            >
                                {{ formatoFecha(evento.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    v-if="evento.user"
                                    class="font-medium text-slate-800"
                                >
                                    {{ evento.user.name }}
                                </span>
                                <span v-else class="text-slate-500 italic">
                                    Sistema
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                                        getClaseAccion(evento.accion),
                                    ]"
                                >
                                    {{ evento.accion }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="evento.entidad"
                                    :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                                        getClaseEntidad(evento.entidad),
                                    ]"
                                >
                                    {{ evento.entidad }}
                                </span>
                                <span v-else class="text-slate-400">—</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <div class="max-w-xs truncate">
                                    {{
                                        evento.descripcion ||
                                        evento.entidad_nombre ||
                                        "—"
                                    }}
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-slate-500"
                            >
                                {{ evento.ip_address }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <Link
                                    :href="route('bitacora.show', evento.id)"
                                    class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-cyan-600 hover:text-cyan-800 hover:bg-cyan-50 rounded-lg transition-colors"
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
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                    Ver
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Sin resultados -->
                <div
                    v-if="bitacora.data.length === 0"
                    class="text-center py-12 px-6"
                >
                    <svg
                        class="mx-auto h-12 w-12 text-slate-400 mb-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">
                        No hay eventos registrados
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Ajusta los filtros para ver más resultados
                    </p>
                </div>
            </div>
        </div>

        <!-- Paginación -->
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-slate-600">
                Mostrando {{ bitacora.from }} a {{ bitacora.to }} de
                {{ bitacora.total }} registros
            </div>
            <div class="flex gap-2">
                <Link
                    v-if="bitacora.prev_page_url"
                    :href="bitacora.prev_page_url"
                    class="px-4 py-2 bg-slate-200 text-slate-800 rounded-lg hover:bg-slate-300 transition-colors"
                >
                    Anterior
                </Link>
                <div class="flex items-center gap-2">
                    <span
                        v-for="page in bitacora.last_page"
                        :key="page"
                        class="text-sm"
                    >
                        <Link
                            :href="`${route('bitacora.index')}?page=${page}`"
                            :class="[
                                'px-2 py-1 rounded transition-colors',
                                page === bitacora.current_page
                                    ? 'bg-cyan-600 text-white font-semibold'
                                    : 'bg-slate-200 text-slate-800 hover:bg-slate-300',
                            ]"
                        >
                            {{ page }}
                        </Link>
                    </span>
                </div>
                <Link
                    v-if="bitacora.next_page_url"
                    :href="bitacora.next_page_url"
                    class="px-4 py-2 bg-slate-200 text-slate-800 rounded-lg hover:bg-slate-300 transition-colors"
                >
                    Siguiente
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
