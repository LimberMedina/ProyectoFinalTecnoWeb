<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

defineProps({
    tecnicas: Object,
    filters: Object,
});

const destroyTecnica = (id) => {
    if (confirm("¿Eliminar esta técnica de inventario?")) {
        router.delete(route("tecnicas-inventario.destroy", id));
    }
};
</script>

<template>
    <Head title="Técnicas de inventario" />

    <AppLayout title="Técnicas de inventario">
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">
                        Técnicas de inventario
                    </h1>
                    <p class="text-sm text-slate-500">
                        Configuración de movimientos FIFO/manuales.
                    </p>
                </div>
                <Link
                    :href="route('tecnicas-inventario.create')"
                    class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white"
                    >Nueva técnica</Link
                >
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Nombre</th>
                            <th class="px-4 py-3 text-left">Descripción</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="tecnica in tecnicas.data" :key="tecnica.id">
                            <td class="px-4 py-3 font-medium text-slate-900">
                                {{ tecnica.nombre }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ tecnica.descripcion || "-" }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <Link
                                    :href="
                                        route(
                                            'tecnicas-inventario.show',
                                            tecnica.id,
                                        )
                                    "
                                    class="text-cyan-700 hover:underline"
                                    >Ver</Link
                                >
                                <Link
                                    :href="
                                        route(
                                            'tecnicas-inventario.edit',
                                            tecnica.id,
                                        )
                                    "
                                    class="text-amber-700 hover:underline"
                                    >Editar</Link
                                >
                                <button
                                    class="text-red-700 hover:underline"
                                    @click="destroyTecnica(tecnica.id)"
                                >
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
