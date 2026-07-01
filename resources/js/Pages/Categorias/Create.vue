<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const form = useForm({
    nombre: "",
    descripcion: "",
    imagen: null,
});

const imagenPreview = ref(null);

const handleImagen = (event) => {
    const [file] = event.target.files;
    form.imagen = file || null;
    imagenPreview.value = file ? URL.createObjectURL(file) : null;
};

const submitForm = () => {
    form.post(route("categorias.store"), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Crear Categoría" />

    <AppLayout title="Crear Categoría">
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
                            Categorías
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Crear categoría
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Registra una nueva categoría para organizar tu
                            catálogo.
                        </p>
                    </div>

                    <Link
                        :href="route('categorias.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </Link>
                </div>

                <form
                    @submit.prevent="submitForm"
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label
                                for="nombre"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Nombre
                                <span class="text-rose-500">*</span></label
                            >
                            <input
                                id="nombre"
                                v-model="form.nombre"
                                type="text"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                :class="{
                                    'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                        form.errors.nombre,
                                }"
                                placeholder="Ej: Electrónica"
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
                                for="descripcion"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Descripción</label
                            >
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                rows="4"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                :class="{
                                    'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                        form.errors.descripcion,
                                }"
                                placeholder="Descripción de la categoría..."
                            ></textarea>
                            <p
                                v-if="form.errors.descripcion"
                                class="mt-2 text-sm text-rose-600"
                            >
                                {{ form.errors.descripcion }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="imagen"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Imagen de categoría</label
                            >
                            <div
                                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4"
                            >
                                <div
                                    v-if="imagenPreview"
                                    class="mb-3 flex items-center gap-3"
                                >
                                    <img
                                        :src="imagenPreview"
                                        alt="Vista previa"
                                        class="h-16 w-16 rounded-xl border border-slate-200 object-cover"
                                    />
                                    <span class="text-sm text-slate-600"
                                        >Vista previa de la imagen
                                        seleccionada.</span
                                    >
                                </div>

                                <input
                                    id="imagen"
                                    type="file"
                                    accept="image/png,image/jpeg,image/jpg,image/webp"
                                    class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-emerald-700"
                                    @change="handleImagen"
                                />
                                <p class="mt-2 text-xs text-slate-500">
                                    Formatos permitidos: JPG, JPEG, PNG o WEBP.
                                    Máximo 2MB.
                                </p>
                                <p
                                    v-if="form.errors.imagen"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.imagen }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end"
                    >
                        <Link
                            :href="route('categorias.index')"
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
                                ><i class="bi bi-save"></i> Guardar
                                categoría</span
                            >
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
