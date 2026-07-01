<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    categorias: Array,
    categoriaId: [String, Number],
});

const form = useForm({
    codigo: "",
    nombre: "",
    categoria_id: props.categoriaId ? String(props.categoriaId) : "",
    descripcion: "",
    imagen: null,
    qr: null,
    marca: "",
    genero: "",
    precio_venta_base: "",
    precio_venta_mayorista_base: "",
    estado: true,
});

const categoriasLocal = ref([...props.categorias]);
const imagenPreview = ref(null);
const qrPreview = ref(null);

const handleImageChange = (event) => {
    const file = event.target.files?.[0] || null;
    form.imagen = file;
    imagenPreview.value = file ? URL.createObjectURL(file) : null;
};

const handleQrChange = (event) => {
    const file = event.target.files?.[0] || null;
    form.qr = file;
    qrPreview.value = file ? URL.createObjectURL(file) : null;
};

const submitForm = () => {
    form.post(route("productos.store"), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Crear Producto" />

    <AppLayout title="Crear Producto">
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
                            Productos
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Crear producto
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Completa solo los atributos del producto principal.
                        </p>
                    </div>

                    <Link
                        :href="route('productos.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </Link>
                </div>

                <form
                    @submit.prevent="submitForm"
                    class="space-y-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <section class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label
                                for="codigo"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Código
                                <span class="text-rose-500">*</span></label
                            >
                            <input
                                id="codigo"
                                v-model="form.codigo"
                                type="text"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                :class="{
                                    'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                        form.errors.codigo,
                                }"
                            />
                            <p
                                v-if="form.errors.codigo"
                                class="mt-1 text-sm text-rose-600"
                            >
                                {{ form.errors.codigo }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="categoria_id"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Categoría
                                <span class="text-rose-500">*</span></label
                            >
                            <div class="flex items-center gap-2">
                                <select
                                    id="categoria_id"
                                    v-model="form.categoria_id"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    :class="{
                                        'border-rose-300 bg-rose-50 focus:border-rose-500 focus:ring-rose-100':
                                            form.errors.categoria_id,
                                    }"
                                >
                                    <option value="" disabled>
                                        Selecciona una categoría
                                    </option>
                                    <option
                                        v-for="cat in categoriasLocal"
                                        :key="cat.id"
                                        :value="cat.id"
                                    >
                                        {{ cat.nombre }}
                                    </option>
                                </select>
                                <Link
                                    :href="route('categorias.create')"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                    title="Crear nueva categoría"
                                    ><i class="bi bi-folder-plus"></i
                                ></Link>
                            </div>
                            <p
                                v-if="form.errors.categoria_id"
                                class="mt-1 text-sm text-rose-600"
                            >
                                {{ form.errors.categoria_id }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
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
                            />
                            <p
                                v-if="form.errors.nombre"
                                class="mt-1 text-sm text-rose-600"
                            >
                                {{ form.errors.nombre }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                for="descripcion"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Descripción</label
                            >
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                rows="3"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                            ></textarea>
                        </div>

                        <div>
                            <label
                                for="marca"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Marca</label
                            >
                            <input
                                id="marca"
                                v-model="form.marca"
                                type="text"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                            />
                        </div>

                        <div>
                            <label
                                for="genero"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Género</label
                            >
                            <input
                                id="genero"
                                v-model="form.genero"
                                type="text"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                            />
                        </div>

                        <div>
                            <label
                                for="precio_venta_base"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Precio venta base</label
                            >
                            <input
                                id="precio_venta_base"
                                v-model="form.precio_venta_base"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                            />
                        </div>

                        <div>
                            <label
                                for="precio_venta_mayorista_base"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Precio mayorista base</label
                            >
                            <input
                                id="precio_venta_mayorista_base"
                                v-model="form.precio_venta_mayorista_base"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                            />
                        </div>

                        <div>
                            <label
                                for="imagen"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Imagen</label
                            >
                            <input
                                id="imagen"
                                type="file"
                                accept="image/*"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none"
                                @change="handleImageChange"
                            />
                            <div
                                v-if="imagenPreview"
                                class="mt-3 flex flex-wrap gap-2"
                            >
                                <img
                                    :src="imagenPreview"
                                    class="h-28 w-28 rounded-xl border border-slate-200 object-cover"
                                    alt="Vista previa de la imagen"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                for="qr"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >QR del producto</label
                            >
                            <input
                                id="qr"
                                type="file"
                                accept="image/*"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none"
                                @change="handleQrChange"
                            />
                            <div v-if="qrPreview" class="mt-3">
                                <img
                                    :src="qrPreview"
                                    alt="Vista previa del QR"
                                    class="h-36 w-36 rounded-xl border border-slate-200 object-contain bg-white p-2"
                                />
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700"
                            >
                                <input
                                    id="estado"
                                    v-model="form.estado"
                                    type="checkbox"
                                    class="h-5 w-5 text-emerald-600"
                                />
                                <span>{{
                                    form.estado ? "Activo" : "Inactivo"
                                }}</span>
                            </label>
                        </div>
                    </section>

                    <div
                        class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                    >
                        <Link
                            :href="route('productos.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <i class="bi bi-x-lg"></i>Cancelar
                        </Link>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing"
                                ><i class="bi bi-hourglass-split"></i>
                                Guardando...</span
                            >
                            <span v-else
                                ><i class="bi bi-save"></i> Guardar
                                producto</span
                            >
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
