<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    nombre: "",
    descripcion: "",
});

const submit = () => form.post(route("tecnicas-inventario.store"));
</script>

<template>
    <Head title="Nueva técnica" />

    <AppLayout title="Nueva técnica">
        <div class="p-6 max-w-3xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-slate-900">Nueva técnica</h1>
                <Link
                    :href="route('tecnicas-inventario.index')"
                    class="text-sm font-semibold text-cyan-700 hover:underline"
                    >Volver</Link
                >
            </div>

            <form
                class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                @submit.prevent="submit"
            >
                <label class="space-y-1 block">
                    <span class="text-sm font-medium text-slate-700"
                        >Nombre</span
                    >
                    <input
                        v-model="form.nombre"
                        type="text"
                        class="w-full rounded-xl border-slate-300"
                    />
                </label>
                <label class="space-y-1 block">
                    <span class="text-sm font-medium text-slate-700"
                        >Descripción</span
                    >
                    <textarea
                        v-model="form.descripcion"
                        rows="4"
                        class="w-full rounded-xl border-slate-300"
                    ></textarea>
                </label>
                <div class="flex justify-end gap-3">
                    <Link
                        :href="route('tecnicas-inventario.index')"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700"
                        >Cancelar</Link
                    >
                    <button
                        type="submit"
                        class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
