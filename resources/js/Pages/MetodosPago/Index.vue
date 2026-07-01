<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    metodosPago: Object,
    filters: Object,
    estadisticas: Object,
});

const search = ref(props.filters.search || "");
let searchTimeout = null;

const formatDescription = (descripcion) => descripcion || "Sin descripción";

const formatCount = (value) => Number(value || 0).toLocaleString("es-BO");

const deleteMetodoPago = (metodoPago) => {
    if (
        !window.confirm(`¿Eliminar el método de pago "${metodoPago.nombre}"?`)
    ) {
        return;
    }

    router.delete(route("metodos-pago.destroy", metodoPago.id), {
        preserveScroll: true,
    });
};

const performSearch = () => {
    router.get(
        route("metodos-pago.index"),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const searchWithDelay = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(performSearch, 300);
};
</script>

<template>
    <Head title="Métodos de Pago" />

    <AppLayout title="Métodos de Pago">
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
                            Pagos
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Métodos de pago
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Administra los métodos de pago disponibles para el
                            propietario.
                        </p>
                    </div>

                    <Link
                        :href="route('metodos-pago.create')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                    >
                        <i class="bi bi-plus-lg"></i>
                        Nuevo método
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-emerald-50 p-3 text-emerald-600"
                            >
                                <i class="bi bi-wallet2 text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Total métodos
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    {{
                                        formatCount(estadisticas.total_metodos)
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div class="rounded-2xl bg-sky-50 p-3 text-sky-600">
                                <i class="bi bi-card-text text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Con descripción
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    {{
                                        formatCount(
                                            estadisticas.con_descripcion,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end"
                    >
                        <div class="lg:col-span-10">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Buscar</label
                            >
                            <input
                                v-model="search"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                placeholder="Buscar por nombre o descripción..."
                                @input="searchWithDelay"
                            />
                        </div>
                        <div class="lg:col-span-2">
                            <button
                                @click="performSearch"
                                class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                <i class="bi bi-search"></i>
                                Buscar
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <h2 class="text-xl font-black text-slate-900">
                            Listado de métodos de pago
                        </h2>
                    </div>

                    <div
                        v-if="metodosPago.data.length === 0"
                        class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center text-slate-500"
                    >
                        <i
                            class="bi bi-inbox mb-3 block text-4xl text-slate-300"
                        ></i>
                        No hay métodos de pago registrados.
                    </div>

                    <div
                        v-else
                        class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                    >
                        <div class="overflow-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead
                                    class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                >
                                    <tr>
                                        <th class="px-4 py-4">#</th>
                                        <th class="px-4 py-4">Nombre</th>
                                        <th class="px-4 py-4">Descripción</th>
                                        <th class="px-4 py-4 text-center">
                                            Ventas
                                        </th>
                                        <th class="px-4 py-4 text-center">
                                            Pagos
                                        </th>
                                        <th class="px-4 py-4 text-center">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="metodo in metodosPago.data"
                                        :key="metodo.id"
                                        class="border-t border-slate-100"
                                    >
                                        <td class="px-4 py-4">
                                            <span
                                                class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700"
                                                >{{ metodo.id }}</span
                                            >
                                        </td>
                                        <td class="px-4 py-4">
                                            <p
                                                class="font-semibold text-slate-900"
                                            >
                                                {{ metodo.nombre }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-4 text-slate-600">
                                            {{
                                                formatDescription(
                                                    metodo.descripcion,
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span
                                                class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"
                                            >
                                                {{
                                                    formatCount(
                                                        metodo.ventas_count,
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span
                                                class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700"
                                            >
                                                {{
                                                    formatCount(
                                                        metodo.pagos_count,
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <div
                                                class="inline-flex items-center gap-2"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'metodos-pago.edit',
                                                            metodo.id,
                                                        )
                                                    "
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                                                    title="Editar"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </Link>
                                                <button
                                                    type="button"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-rose-200 bg-white text-rose-600 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700"
                                                    title="Eliminar"
                                                    @click="
                                                        deleteMetodoPago(metodo)
                                                    "
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        v-if="metodosPago.links && metodosPago.links.length > 3"
                        class="mt-6 flex flex-wrap items-center justify-center gap-2"
                    >
                        <template
                            v-for="(link, index) in metodosPago.links"
                            :key="`${link.label}-${index}`"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                class="rounded-xl border px-4 py-2 text-sm transition"
                                :class="
                                    link.active
                                        ? 'border-emerald-600 bg-emerald-600 text-white'
                                        : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                "
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-400"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
