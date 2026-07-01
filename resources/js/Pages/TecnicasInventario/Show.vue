<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    tecnica: Object,
});
</script>

<template>
    <Head title="Técnica" />

    <AppLayout title="Técnica">
        <div class="p-6 max-w-3xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-slate-900">
                    {{ tecnica.nombre }}
                </h1>
                <Link
                    :href="route('tecnicas-inventario.index')"
                    class="text-sm font-semibold text-cyan-700 hover:underline"
                    >Volver</Link
                >
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3"
            >
                <p>
                    <span class="font-semibold">Descripción:</span>
                    {{ tecnica.descripcion || "-" }}
                </p>
                <p>
                    <span class="font-semibold">Movimientos asociados:</span>
                    {{ tecnica.movimientos_count ?? 0 }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
