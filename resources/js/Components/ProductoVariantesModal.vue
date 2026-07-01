<script setup>
import { computed, ref, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { showToast } from "@/utils/toast";

const props = defineProps({
    show: Boolean,
    producto: Object,
});

const emit = defineEmits(["close"]);

const defaultVariant = () => ({
    sku: "",
    talla: "",
    color: "",
    precio_compra: 0,
    precio_venta:
        props.producto?.precio_venta_base ?? props.producto?.precio_venta ?? "",
    precio_venta_mayorista:
        props.producto?.precio_venta_mayorista_base ??
        props.producto?.precio_venta_mayorista ??
        props.producto?.precio_venta_base ??
        "",
    stock_actual: 0,
    stock_minimo: 0,
    punto_reorden: 0,
    estado: "ACTIVO",
});

const form = useForm(defaultVariant());
const editingId = ref(null);
const showFormModal = ref(false);

const variants = computed(() => props.producto?.variantes || []);

const resetForm = () => {
    form.clearErrors();
    form.reset();
    Object.assign(form, defaultVariant());
    editingId.value = null;
};

const openCreateForm = () => {
    editingId.value = null;
    Object.assign(form, {
        ...defaultVariant(),
        precio_venta:
            props.producto?.precio_venta_base ??
            props.producto?.precio_venta ??
            "",
        precio_venta_mayorista:
            props.producto?.precio_venta_mayorista_base ??
            props.producto?.precio_venta_mayorista ??
            props.producto?.precio_venta_base ??
            "",
        precio_compra: 0,
        stock_actual: 0,
        stock_minimo: 0,
        punto_reorden: 0,
    });
    showFormModal.value = true;
};

const openEditForm = (variant) => {
    editingId.value = variant.id;
    Object.assign(form, {
        sku: variant.sku || "",
        talla: variant.talla || "",
        color: variant.color || "",
        precio_compra: variant.precio_compra || "",
        precio_venta: variant.precio_venta || "",
        precio_venta_mayorista: variant.precio_venta_mayorista || "",
        stock_actual: variant.stock_actual ?? 0,
        stock_minimo: variant.stock_minimo ?? 0,
        punto_reorden: variant.punto_reorden ?? 0,
        estado: variant.estado || "ACTIVO",
    });
    showFormModal.value = true;
};

const closeFormModal = () => {
    showFormModal.value = false;
    resetForm();
};

const closeModal = () => {
    showFormModal.value = false;
    emit("close");
    resetForm();
};

const submitVariant = () => {
    if (!props.producto) return;

    const payload = {
        sku: form.sku,
        talla: form.talla,
        color: form.color,
        precio_compra: form.precio_compra,
        precio_venta: form.precio_venta,
        precio_venta_mayorista: form.precio_venta_mayorista,
        stock_actual: form.stock_actual,
        stock_minimo: form.stock_minimo,
        punto_reorden: form.punto_reorden,
        estado: form.estado,
    };

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showToast(
                editingId.value
                    ? "Variante actualizada correctamente."
                    : "Variante registrada correctamente.",
                "success",
            );
            router.reload({ preserveScroll: true });
            editingId.value = null;
            showFormModal.value = false;
            form.clearErrors();
        },
        onError: (errors) => {
            const firstErrorValue = Object.values(errors)[0];
            const firstError = Array.isArray(firstErrorValue)
                ? firstErrorValue[0]
                : firstErrorValue;
            showToast(
                firstError ||
                    "Error al guardar la variante. Verifica los campos e intenta nuevamente.",
                "error",
            );
        },
    };

    if (editingId.value) {
        router.put(
            route("productos.variantes.update", {
                producto: props.producto.id,
                variante: editingId.value,
            }),
            payload,
            options,
        );
        return;
    }

    router.post(
        route("productos.variantes.store", { producto: props.producto.id }),
        payload,
        options,
    );
};

const deleteVariant = (variant) => {
    if (
        !confirm(`¿Eliminar la variante ${variant.talla} / ${variant.color}?`)
    ) {
        return;
    }

    router.delete(
        route("productos.variantes.destroy", {
            producto: props.producto.id,
            variante: variant.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ preserveScroll: true });
                if (editingId.value === variant.id) {
                    closeModal();
                }
            },
        },
    );
};

watch(
    () => props.show,
    (visible) => {
        if (visible) {
            resetForm();
            showFormModal.value = false;
        }
    },
);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        >
            <div
                class="absolute inset-0 bg-slate-950/60"
                @click="closeModal"
            ></div>

            <div
                class="relative z-10 w-full max-w-5xl overflow-hidden rounded-[2rem] border border-white bg-white shadow-[0_30px_90px_-30px_rgba(15,23,42,0.5)]"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-cyan-700"
                        >
                            Producto variantes
                        </p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">
                            {{ producto?.nombre || "Variantes del producto" }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50"
                        @click="closeModal"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="max-h-[80vh] overflow-y-auto px-6 py-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div class="text-sm text-slate-600">
                            <span class="font-semibold text-slate-900">{{
                                variants.length
                            }}</span>
                            variantes registradas
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-200 transition hover:bg-cyan-700"
                            @click="openCreateForm"
                        >
                            <i class="bi bi-plus-circle"></i>
                            Agregar nueva variante
                        </button>
                    </div>

                    <div
                        v-if="variants.length === 0"
                        class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-14 text-center"
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-600"
                        >
                            <i class="bi bi-layers-half text-3xl"></i>
                        </div>
                        <h4 class="mt-4 text-lg font-black text-slate-900">
                            No hay variantes registradas
                        </h4>
                        <p class="mt-2 text-sm text-slate-600">
                            Aún no se ha creado ninguna variante para este
                            producto.
                        </p>
                        <button
                            type="button"
                            class="mt-6 inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-200 transition hover:bg-cyan-700"
                            @click="openCreateForm"
                        >
                            <i class="bi bi-plus-circle"></i>
                            Agregar nueva variante
                        </button>
                    </div>

                    <div
                        v-else
                        class="overflow-x-auto rounded-2xl border border-slate-200"
                    >
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="px-4 py-3 text-left">Talla</th>
                                    <th class="px-4 py-3 text-left">Color</th>
                                    <th class="px-4 py-3 text-left">SKU</th>
                                    <th class="px-4 py-3 text-left">
                                        Precio venta
                                    </th>
                                    <th class="px-4 py-3 text-left">Stock</th>
                                    <th class="px-4 py-3 text-left">Estado</th>
                                    <th class="px-4 py-3 text-right">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="variant in variants"
                                    :key="variant.id"
                                    class="border-t border-slate-200"
                                >
                                    <td
                                        class="px-4 py-3 font-semibold text-slate-900"
                                    >
                                        {{ variant.talla }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ variant.color }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ variant.sku || "-" }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ variant.precio_venta }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ variant.stock_actual }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                            :class="
                                                variant.estado === 'ACTIVO'
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : 'bg-slate-100 text-slate-600'
                                            "
                                        >
                                            {{ variant.estado }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div
                                            class="flex items-center justify-end gap-2"
                                        >
                                            <button
                                                type="button"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50"
                                                title="Editar variante"
                                                @click="openEditForm(variant)"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button
                                                type="button"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50"
                                                title="Eliminar variante"
                                                @click="deleteVariant(variant)"
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
            </div>
        </div>
    </Teleport>

    <Teleport to="body">
        <div
            v-if="showFormModal"
            class="fixed inset-0 z-[60] flex items-center justify-center px-4 py-6"
        >
            <div
                class="absolute inset-0 bg-slate-950/60"
                @click="closeFormModal"
            ></div>

            <div
                class="relative z-10 w-full max-w-3xl overflow-hidden rounded-[2rem] border border-white bg-white shadow-[0_30px_90px_-30px_rgba(15,23,42,0.5)]"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-cyan-700"
                        >
                            Producto variantes
                        </p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">
                            {{
                                editingId ? "Editar variante" : "Nueva variante"
                            }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50"
                        @click="closeFormModal"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="max-h-[80vh] overflow-y-auto px-6 py-5">
                    <p class="mb-5 text-sm text-slate-600">
                        Completa los datos de la variante seleccionada.
                    </p>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Talla</label
                            >
                            <input
                                v-model="form.talla"
                                type="text"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Color</label
                            >
                            <input
                                v-model="form.color"
                                type="text"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >SKU</label
                            >
                            <input
                                v-model="form.sku"
                                type="text"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Estado</label
                            >
                            <select
                                v-model="form.estado"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                            >
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Precio compra</label
                            >
                            <input
                                v-model="form.precio_compra"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Precio venta</label
                            >
                            <input
                                v-model="form.precio_venta"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Precio mayorista</label
                            >
                            <input
                                v-model="form.precio_venta_mayorista"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Stock actual</label
                            >
                            <input
                                v-model="form.stock_actual"
                                type="number"
                                min="0"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Stock mínimo</label
                            >
                            <input
                                v-model="form.stock_minimo"
                                type="number"
                                min="0"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                            />
                        </div>
                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Punto de reorden</label
                            >
                            <input
                                v-model="form.punto_reorden"
                                type="number"
                                min="0"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                            />
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            @click="closeFormModal"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-700"
                            @click="submitVariant"
                        >
                            <i class="bi bi-save"></i>
                            {{
                                editingId
                                    ? "Actualizar variante"
                                    : "Guardar variante"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
