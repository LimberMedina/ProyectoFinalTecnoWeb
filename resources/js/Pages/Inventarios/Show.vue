<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    movimiento: Object,
});
</script>

<template>
    <Head title="Movimiento" />

    <AppLayout title="Movimiento">
        <div class="p-6 max-w-4xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-slate-900">
                    Movimiento #{{ movimiento.id }}
                </h1>
                <Link
                    :href="route('inventarios.index')"
                    class="text-sm font-semibold text-cyan-700 hover:underline"
                    >Volver</Link
                >
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-3"
            >
                <p>
                    <span class="font-semibold">Variante:</span>
                    {{ movimiento.variante?.producto?.nombre }} -
                    {{ movimiento.variante?.talla }} /
                    {{ movimiento.variante?.color }}
                </p>
                <p>
                    <span class="font-semibold">Tipo:</span>
                    {{ movimiento.tipo_movimiento }}
                </p>
                <p>
                    <span class="font-semibold">Cantidad:</span>
                    {{ movimiento.cantidad }}
                </p>
                <p>
                    <span class="font-semibold">Stock final:</span>
                    {{ movimiento.stock_resultante }}
                </p>
                <p>
                    <span class="font-semibold">Motivo:</span>
                    {{ movimiento.motivo || "-" }}
                </p>
                <p>
                    <span class="font-semibold">Observación:</span>
                    {{ movimiento.observacion || "-" }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
