<script setup>
import { computed, ref, watch } from "vue";

const props = defineProps({
    show: Boolean,
    producto: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "agregar", "vender"]);

const cantidades = ref({});

watch(
    () => [props.show, props.producto],
    () => {
        if (!props.show) return;

        const initialQuantities = {};
        for (const variant of props.producto?.variantes || []) {
            initialQuantities[variant.id] = 0;
        }
        cantidades.value = initialQuantities;
    },
    { immediate: true },
);

const formatPrice = (price) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(Number(price) || 0);

const getImageUrl = (producto) =>
    producto?.imagen ? `/storage/${producto.imagen}` : "/images/no-image.png";

const updateCantidad = (variant, value) => {
    const parsed = Number(value);
    const stock = Number(variant.stock_actual) || 0;

    if (!Number.isFinite(parsed) || parsed < 0) {
        cantidades.value[variant.id] = 0;
        return;
    }

    cantidades.value[variant.id] = Math.min(Math.floor(parsed), stock);
};

const getCantidad = (variantId) => Number(cantidades.value[variantId]) || 0;

const buildPayload = (variant) => ({
    key: `${props.producto.id}-${variant.id}`,
    producto_variante_id: variant.id,
    producto_id: props.producto.id,
    producto_nombre: props.producto?.nombre || "Producto",
    categoria_nombre: props.producto?.categoria?.nombre || "Sin categoria",
    imagen: props.producto?.imagen || null,
    talla: variant.talla || "-",
    color: variant.color || "-",
    sku: variant.sku || "-",
    estado: (variant.estado || "").toUpperCase(),
    stock_actual: Number(variant.stock_actual) || 0,
    precio_venta: Number(variant.precio_venta) || 0,
    cantidad: getCantidad(variant.id),
});

const agregarVariante = (variant) => {
    const cantidad = getCantidad(variant.id);
    if (cantidad <= 0) {
        return;
    }

    emit("agregar", buildPayload(variant));
};

const venderVariante = (variant) => {
    const cantidad = getCantidad(variant.id);
    if (cantidad <= 0) {
        return;
    }

    emit("vender", buildPayload(variant));
};
</script>

<template>
    <teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[94] flex items-center justify-center bg-slate-950/55 px-4 py-6"
            @click.self="$emit('close')"
        >
            <div
                class="flex w-full max-w-5xl flex-col rounded-[2rem] bg-white shadow-2xl"
                style="max-height: 90vh"
            >
                <div
                    class="flex flex-shrink-0 items-start justify-between gap-4 border-b px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                        >
                            Variantes
                        </p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">
                            {{ producto?.nombre || "Producto" }}
                        </h3>
                        <p class="mt-0.5 text-sm text-slate-500">
                            Selecciona talla, color y cantidad para cargar la
                            venta.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="$emit('close')"
                        aria-label="Cerrar"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    <div
                        class="grid grid-cols-1 gap-4 md:grid-cols-[160px_1fr]"
                    >
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-100 bg-slate-50"
                        >
                            <img
                                :src="getImageUrl(producto)"
                                :alt="producto?.nombre || 'Producto'"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3"
                            >
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Código
                                </p>
                                <p
                                    class="mt-1 text-sm font-black text-slate-800"
                                >
                                    {{ producto?.codigo || "—" }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3"
                            >
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Categoría
                                </p>
                                <p
                                    class="mt-1 text-sm font-black text-slate-800"
                                >
                                    {{
                                        producto?.categoria?.nombre ||
                                        "Sin categoría"
                                    }}
                                </p>
                            </div>
                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3"
                            >
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Marca
                                </p>
                                <p
                                    class="mt-1 text-sm font-black text-slate-800"
                                >
                                    {{ producto?.marca || "—" }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-2xl border border-slate-100"
                    >
                        <div
                            class="border-b border-slate-100 bg-slate-50 px-4 py-3"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                            >
                                Variantes disponibles
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Talla
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Color
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            SKU
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Precio
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Stock
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Cantidad
                                        </th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-bold uppercase tracking-[0.15em] text-slate-400"
                                        >
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-if="
                                            !(producto?.variantes || []).length
                                        "
                                    >
                                        <td
                                            colspan="7"
                                            class="px-4 py-12 text-center text-slate-500"
                                        >
                                            Este producto no tiene variantes
                                            registradas.
                                        </td>
                                    </tr>

                                    <tr
                                        v-for="variant in producto?.variantes ||
                                        []"
                                        :key="variant.id"
                                        class="border-b border-slate-50 transition hover:bg-slate-50/60"
                                    >
                                        <td
                                            class="px-4 py-3.5 font-black text-slate-800"
                                        >
                                            {{ variant.talla || "—" }}
                                        </td>
                                        <td class="px-4 py-3.5 text-slate-600">
                                            {{ variant.color || "—" }}
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <code
                                                class="rounded-md bg-slate-100 px-2 py-0.5 text-xs text-slate-500"
                                                >{{ variant.sku || "—" }}</code
                                            >
                                        </td>
                                        <td
                                            class="px-4 py-3.5 text-right font-black text-emerald-700"
                                        >
                                            {{
                                                formatPrice(
                                                    variant.precio_venta,
                                                )
                                            }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                                :class="
                                                    Number(
                                                        variant.stock_actual,
                                                    ) > 0
                                                        ? 'bg-sky-100 text-sky-800'
                                                        : 'bg-rose-100 text-rose-700'
                                                "
                                            >
                                                {{
                                                    Number(
                                                        variant.stock_actual,
                                                    ) || 0
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <input
                                                type="number"
                                                min="0"
                                                :max="
                                                    Number(
                                                        variant.stock_actual,
                                                    ) || 0
                                                "
                                                :value="getCantidad(variant.id)"
                                                class="w-20 rounded-xl border border-slate-200 bg-white px-3 py-2 text-center text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                                :disabled="
                                                    Number(
                                                        variant.stock_actual,
                                                    ) === 0 ||
                                                    (
                                                        variant.estado || ''
                                                    ).toUpperCase() !== 'ACTIVO'
                                                "
                                                @input="
                                                    updateCantidad(
                                                        variant,
                                                        $event.target.value,
                                                    )
                                                "
                                            />
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <div
                                                class="flex items-center justify-center gap-2"
                                            >
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400"
                                                    :disabled="
                                                        getCantidad(
                                                            variant.id,
                                                        ) <= 0
                                                    "
                                                    @click="
                                                        agregarVariante(variant)
                                                    "
                                                >
                                                    <svg
                                                        class="h-4 w-4"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        aria-hidden="true"
                                                    >
                                                        <path
                                                            d="M12 5v14M5 12h14"
                                                        />
                                                    </svg>
                                                    Agregar
                                                </button>
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                                    :disabled="
                                                        getCantidad(
                                                            variant.id,
                                                        ) <= 0
                                                    "
                                                    @click="
                                                        venderVariante(variant)
                                                    "
                                                >
                                                    <svg
                                                        class="h-4 w-4"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        aria-hidden="true"
                                                    >
                                                        <circle
                                                            cx="9"
                                                            cy="21"
                                                            r="1"
                                                        />
                                                        <circle
                                                            cx="20"
                                                            cy="21"
                                                            r="1"
                                                        />
                                                        <path
                                                            d="M1 1h4l2.7 12.7a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.7L23 6H6"
                                                        />
                                                    </svg>
                                                    Vender
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
        </div>
    </teleport>
</template>
