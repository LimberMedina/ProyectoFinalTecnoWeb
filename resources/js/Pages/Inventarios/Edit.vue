<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    movimiento: Object,
    variantes: Array,
    tecnicasInventario: Array,
    tecnicasCosto: Array,
});

const form = useForm({
    id_producto_variante: props.movimiento.id_producto_variante,
    id_tecnica_inventario: props.movimiento.id_tecnica_inventario,
    id_tecnica_costo: props.movimiento.id_tecnica_costo,
    tipo_movimiento: props.movimiento.tipo_movimiento,
    motivo: props.movimiento.motivo || "",
    cantidad: props.movimiento.cantidad,
    costo_unitario: props.movimiento.costo_unitario,
    fecha: props.movimiento.fecha,
    observacion: props.movimiento.observacion || "",
});

const submit = () => form.put(route("inventarios.update", props.movimiento.id));
</script>

<template>
    <Head title="Editar movimiento" />

    <AppLayout title="Editar movimiento">
        <div class="p-6 max-w-4xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-slate-900">
                    Editar movimiento
                </h1>
                <Link
                    :href="route('inventarios.index')"
                    class="text-sm font-semibold text-cyan-700 hover:underline"
                    >Volver</Link
                >
            </div>

            <form
                class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:grid-cols-2"
                @submit.prevent="submit"
            >
                <label class="space-y-1">
                    <span class="text-sm font-medium text-slate-700"
                        >Variante</span
                    >
                    <select
                        v-model="form.id_producto_variante"
                        class="w-full rounded-xl border-slate-300"
                    >
                        <option value="">Selecciona</option>
                        <option
                            v-for="variante in variantes"
                            :key="variante.id"
                            :value="variante.id"
                        >
                            {{ variante.producto?.nombre }} -
                            {{ variante.talla }} / {{ variante.color }}
                        </option>
                    </select>
                </label>

                <label class="space-y-1">
                    <span class="text-sm font-medium text-slate-700"
                        >Técnica inventario</span
                    >
                    <select
                        v-model="form.id_tecnica_inventario"
                        class="w-full rounded-xl border-slate-300"
                    >
                        <option
                            v-for="item in tecnicasInventario"
                            :key="item.id"
                            :value="item.id"
                        >
                            {{ item.nombre }}
                        </option>
                    </select>
                </label>

                <label class="space-y-1">
                    <span class="text-sm font-medium text-slate-700"
                        >Técnica costo</span
                    >
                    <select
                        v-model="form.id_tecnica_costo"
                        class="w-full rounded-xl border-slate-300"
                    >
                        <option
                            v-for="item in tecnicasCosto"
                            :key="item.id"
                            :value="item.id"
                        >
                            {{ item.nombre }}
                        </option>
                    </select>
                </label>

                <label class="space-y-1">
                    <span class="text-sm font-medium text-slate-700">Tipo</span>
                    <select
                        v-model="form.tipo_movimiento"
                        class="w-full rounded-xl border-slate-300"
                    >
                        <option value="ingreso">Ingreso</option>
                        <option value="salida">Salida</option>
                    </select>
                </label>

                <label class="space-y-1">
                    <span class="text-sm font-medium text-slate-700"
                        >Cantidad</span
                    >
                    <input
                        v-model="form.cantidad"
                        type="number"
                        min="1"
                        class="w-full rounded-xl border-slate-300"
                    />
                </label>

                <label class="space-y-1">
                    <span class="text-sm font-medium text-slate-700"
                        >Costo unitario</span
                    >
                    <input
                        v-model="form.costo_unitario"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-full rounded-xl border-slate-300"
                    />
                </label>

                <label class="space-y-1">
                    <span class="text-sm font-medium text-slate-700"
                        >Fecha</span
                    >
                    <input
                        v-model="form.fecha"
                        type="date"
                        class="w-full rounded-xl border-slate-300"
                    />
                </label>

                <label class="space-y-1 md:col-span-2">
                    <span class="text-sm font-medium text-slate-700"
                        >Motivo</span
                    >
                    <textarea
                        v-model="form.motivo"
                        rows="3"
                        class="w-full rounded-xl border-slate-300"
                    ></textarea>
                </label>

                <label class="space-y-1 md:col-span-2">
                    <span class="text-sm font-medium text-slate-700"
                        >Observación</span
                    >
                    <textarea
                        v-model="form.observacion"
                        rows="3"
                        class="w-full rounded-xl border-slate-300"
                    ></textarea>
                </label>

                <div class="md:col-span-2 flex justify-end gap-3">
                    <Link
                        :href="route('inventarios.index')"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700"
                        >Cancelar</Link
                    >
                    <button
                        type="submit"
                        class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Actualizar
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
