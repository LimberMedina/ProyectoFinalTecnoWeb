<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    compra: Object,
    proveedores: Array,
    variantes: Array,
});

const form = useForm({
    id_proveedor: props.compra.id_proveedor,
    num_compra: props.compra.num_compra || "",
    fecha_compra: props.compra.fecha_compra,
    detalles: (props.compra.detalles || []).map((detalle) => ({
        id_producto_variante:
            detalle.id_producto_variante ||
            detalle.id_producto_variante_id ||
            detalle.id_producto_varianteId ||
            detalle.id_producto_variante,
        cantidad: Number(detalle.cantidad || 1),
        precio: Number(detalle.precio || 0),
        descuento: Number(detalle.descuento || 0),
        subtotal: Number(detalle.subtotal || 0),
    })),
});

if (form.detalles.length === 0) {
    form.detalles.push({
        id_producto_variante: "",
        cantidad: 1,
        precio: 0,
        descuento: 0,
        subtotal: 0,
    });
}

const addRow = () => {
    form.detalles.push({
        id_producto_variante: "",
        cantidad: 1,
        precio: 0,
        descuento: 0,
        subtotal: 0,
    });
};

const removeRow = (index) => {
    if (form.detalles.length === 1) return;
    form.detalles.splice(index, 1);
};

const setDefaultPrice = (index) => {
    const id = Number(form.detalles[index].id_producto_variante);
    const variante = props.variantes.find((item) => Number(item.id) === id);

    if (!variante) return;
    form.detalles[index].precio = Number(variante.precio_compra || 0);
    form.detalles[index].descuento = 0;
};

const getVariantLabel = (variante) => {
    if (!variante) return "Seleccione una variante";

    const producto = variante.producto?.nombre || "Producto";
    const codigo = variante.producto?.codigo || variante.sku || "Sin código";
    const talla = variante.talla || "Sin talla";
    const color = variante.color || "Sin color";

    return `${codigo} · ${producto} · ${talla} / ${color}`;
};

const lineTotal = (item) => {
    const cantidad = Number(item.cantidad || 0);
    const precio = Number(item.precio || 0);
    const descuento = Number(item.descuento || 0);
    return Math.max(0, (precio - descuento) * cantidad);
};

const total = computed(() =>
    form.detalles.reduce((acc, item) => acc + lineTotal(item), 0),
);

const submitForm = () => {
    form.put(route("compras.update", props.compra.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Editar compra #${compra.id}`" />

    <AppLayout :title="`Editar compra #${compra.id}`">
        <FlashNotification />

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div
                class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-cyan-200 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-cyan-700 shadow-sm"
                    >
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Compras
                    </div>
                    <h1
                        class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Editar compra
                    </h1>
                </div>

                <Link
                    :href="route('compras.index')"
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
                    <h2 class="mb-5 text-xl font-black text-slate-900">
                        Cabecera de compra
                    </h2>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Proveedor
                                <span class="text-rose-500">*</span></label
                            >
                            <select
                                v-model="form.id_proveedor"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-100"
                            >
                                <option value="">
                                    Seleccione un proveedor
                                </option>
                                <option
                                    v-for="proveedor in proveedores"
                                    :key="proveedor.id"
                                    :value="proveedor.id"
                                >
                                    {{ proveedor.nombre }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.id_proveedor"
                                class="mt-2 text-sm text-rose-600"
                            >
                                {{ form.errors.id_proveedor }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >N° compra</label
                            >
                            <input
                                v-model="form.num_compra"
                                type="text"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-100"
                            />
                            <p
                                v-if="form.errors.num_compra"
                                class="mt-2 text-sm text-rose-600"
                            >
                                {{ form.errors.num_compra }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Fecha compra
                                <span class="text-rose-500">*</span></label
                            >
                            <input
                                v-model="form.fecha_compra"
                                type="date"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:bg-white focus:ring-4 focus:ring-cyan-100"
                            />
                            <p
                                v-if="form.errors.fecha_compra"
                                class="mt-2 text-sm text-rose-600"
                            >
                                {{ form.errors.fecha_compra }}
                            </p>
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <h2 class="text-xl font-black text-slate-900">
                            Detalle de compra
                        </h2>
                        <button
                            type="button"
                            @click="addRow"
                            class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700"
                        >
                            <i class="bi bi-plus-circle"></i>
                            Agregar variante
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(detalle, index) in form.detalles"
                            :key="index"
                            class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-12"
                        >
                            <div class="md:col-span-5">
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500"
                                    >Variante</label
                                >
                                <select
                                    v-model="detalle.id_producto_variante"
                                    @change="setDefaultPrice(index)"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                                >
                                    <option value="">
                                        Seleccione una variante
                                    </option>
                                    <option
                                        v-for="variante in variantes"
                                        :key="variante.id"
                                        :value="variante.id"
                                    >
                                        {{ getVariantLabel(variante) }}
                                    </option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500"
                                    >Cantidad</label
                                >
                                <input
                                    v-model.number="detalle.cantidad"
                                    type="number"
                                    min="1"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500"
                                    >Precio</label
                                >
                                <input
                                    v-model.number="detalle.precio"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500"
                                    >Descuento</label
                                >
                                <input
                                    v-model.number="detalle.descuento"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100"
                                />
                            </div>

                            <div class="md:col-span-1">
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500"
                                    >Total</label
                                >
                                <div
                                    class="rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-2 text-sm font-bold text-cyan-700"
                                >
                                    Bs. {{ lineTotal(detalle).toFixed(2) }}
                                </div>
                            </div>

                            <div class="md:col-span-1">
                                <label
                                    class="mb-1 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500"
                                    >Subtotal</label
                                >
                                <div
                                    class="rounded-xl border border-cyan-200 bg-cyan-50 px-3 py-2 text-sm font-bold text-cyan-700"
                                >
                                    Bs. {{ lineTotal(detalle).toFixed(2) }}
                                </div>
                            </div>

                            <div class="md:col-span-1 md:self-end">
                                <button
                                    type="button"
                                    @click="removeRow(index)"
                                    class="inline-flex w-full items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-rose-600 transition hover:bg-rose-50"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <p
                        v-if="form.errors.detalles"
                        class="mt-3 text-sm text-rose-600"
                    >
                        {{ form.errors.detalles }}
                    </p>
                    <div class="mt-5 flex justify-end">
                        <div
                            class="rounded-2xl border border-cyan-200 bg-cyan-50 px-5 py-3 text-right"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-[0.16em] text-cyan-700"
                            >
                                Total compra
                            </p>
                            <p class="mt-1 text-2xl font-black text-cyan-700">
                                Bs. {{ total.toFixed(2) }}
                            </p>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <Link
                        :href="route('compras.index')"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >Cancelar</Link
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-200 transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span v-if="form.processing"
                            ><i class="bi bi-hourglass-split"></i>
                            Guardando...</span
                        >
                        <span v-else
                            ><i class="bi bi-save"></i> Actualizar compra</span
                        >
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
