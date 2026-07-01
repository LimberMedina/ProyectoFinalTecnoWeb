<script setup>
import { computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    proveedor: Object,
});

const isActive = computed(
    () => (props.proveedor.estado || "").toLowerCase() === "activo",
);

const formatDate = (date) => {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("es-BO");
};

const confirmDelete = () => {
    if (!window.confirm("¿Seguro que deseas eliminar este proveedor?")) {
        return;
    }

    router.delete(route("proveedores.destroy", props.proveedor.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="proveedor.nombre" />

    <AppLayout :title="proveedor.nombre">
        <FlashNotification />

        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
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
                            Detalle de proveedor
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            {{ proveedor.nombre }}
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            {{
                                proveedor.direccion ||
                                "Sin dirección registrada"
                            }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="route('proveedores.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <i class="bi bi-arrow-left"></i>
                            Volver
                        </Link>
                        <Link
                            :href="route('proveedores.edit', proveedor.id)"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                        >
                            <i class="bi bi-pencil-square"></i>
                            Editar
                        </Link>
                        <button
                            v-if="Number(proveedor.compras_count || 0) === 0"
                            type="button"
                            @click="confirmDelete"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-700"
                        >
                            <i class="bi bi-trash"></i>
                            Eliminar
                        </button>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                        >
                            Estado
                        </p>
                        <span
                            class="mt-3 inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                            :class="
                                isActive
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-rose-100 text-rose-700'
                            "
                            >{{ proveedor.estado || "Sin estado" }}</span
                        >
                    </div>
                    <div
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                        >
                            Compras
                        </p>
                        <p class="mt-3 text-3xl font-black text-slate-900">
                            {{ proveedor.compras_count || 0 }}
                        </p>
                    </div>
                    <div
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                        >
                            NIT
                        </p>
                        <p class="mt-3 text-lg font-bold text-slate-900">
                            {{ proveedor.nit || "No registrado" }}
                        </p>
                    </div>
                </div>

                <section
                    class="mt-8 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                            >
                                Contacto
                            </p>
                            <div class="mt-3 space-y-3 text-sm text-slate-700">
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Teléfono:</span
                                    >
                                    {{ proveedor.telefono || "No registrado" }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Correo:</span
                                    >
                                    {{ proveedor.email || "No registrado" }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                            >
                                Fechas
                            </p>
                            <div class="mt-3 space-y-3 text-sm text-slate-700">
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Creación:</span
                                    >
                                    {{ formatDate(proveedor.fecha_creacion) }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Modificación:</span
                                    >
                                    {{
                                        formatDate(proveedor.fecha_modificacion)
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p
                            class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                        >
                            Dirección
                        </p>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{
                                proveedor.direccion ||
                                "Sin dirección registrada"
                            }}
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
