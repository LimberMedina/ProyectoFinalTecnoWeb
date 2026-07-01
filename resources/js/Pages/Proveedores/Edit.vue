<script setup>
import { computed, ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    proveedor: Object,
});

const form = useForm({
    nombre: props.proveedor.nombre,
    nit: props.proveedor.nit,
    telefono: props.proveedor.telefono,
    email: props.proveedor.email,
    direccion: props.proveedor.direccion,
    estado: props.proveedor.estado || "Activo",
});

const estadoActual = computed(() => props.proveedor.estado || "Activo");
const direccionActual = computed(() => props.proveedor.direccion || "");
const mostrarDireccionActual = computed(() => direccionActual.value.length > 0);

const submitForm = () => {
    form.put(route("proveedores.update", props.proveedor.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Editar: ${proveedor.nombre}`" />

    <AppLayout :title="`Editar: ${proveedor.nombre}`">
        <FlashNotification />

        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
            <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
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
                            Proveedores
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Editar proveedor
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Actualiza los datos del proveedor manteniendo un
                            formulario limpio y ordenado.
                        </p>
                    </div>

                    <Link
                        :href="route('proveedores.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </Link>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center justify-between gap-3"
                        >
                            <h2 class="text-xl font-black text-slate-900">
                                Información general
                            </h2>
                            <i
                                class="bi bi-truck text-2xl text-emerald-600"
                            ></i>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Nombre
                                    <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.nombre,
                                    }"
                                />
                                <p
                                    v-if="form.errors.nombre"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.nombre }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >NIT</label
                                >
                                <input
                                    v-model="form.nit"
                                    type="text"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.nit,
                                    }"
                                />
                                <p
                                    v-if="form.errors.nit"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.nit }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Teléfono</label
                                >
                                <input
                                    v-model="form.telefono"
                                    type="text"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.telefono,
                                    }"
                                />
                                <p
                                    v-if="form.errors.telefono"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.telefono }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Correo electrónico</label
                                >
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.email,
                                    }"
                                />
                                <p
                                    v-if="form.errors.email"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Estado</label
                                >
                                <select
                                    v-model="form.estado"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.estado,
                                    }"
                                >
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                                <p
                                    v-if="form.errors.estado"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.estado }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Dirección</label
                                >
                                <textarea
                                    v-model="form.direccion"
                                    rows="4"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.direccion,
                                    }"
                                ></textarea>
                                <p
                                    v-if="form.errors.direccion"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.direccion }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-[2rem] border border-slate-200 bg-slate-50 p-6"
                    >
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Referencia actual
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    Estado: {{ estadoActual }}
                                </p>
                            </div>
                            <p
                                v-if="mostrarDireccionActual"
                                class="max-w-xl text-sm text-slate-500"
                            >
                                {{ direccionActual }}
                            </p>
                        </div>
                    </section>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <Link
                            :href="route('proveedores.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >Cancelar</Link
                        >
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing"
                                ><i class="bi bi-hourglass-split"></i>
                                Guardando...</span
                            >
                            <span v-else
                                ><i class="bi bi-save"></i> Actualizar
                                proveedor</span
                            >
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
