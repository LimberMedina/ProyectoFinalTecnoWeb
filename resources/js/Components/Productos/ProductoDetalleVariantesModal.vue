<script setup>
const props = defineProps({
    show: Boolean,
    producto: {
        type: Object,
        default: null,
    },
});

defineEmits(["close"]);

const formatPrice = (price) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(Number(price) || 0);
</script>

<template>
    <teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/50 px-4 py-6"
            @click.self="$emit('close')"
        >
            <div
                class="w-full max-w-3xl rounded-[2rem] border border-white bg-white/95 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.4)]"
            >
                <!-- Cabecera -->
                <div
                    class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5"
                >
                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                        >
                            Producto
                        </p>
                        <h3 class="mt-1 text-xl font-black text-slate-900">
                            {{ producto?.nombre || "Detalle de producto" }}
                        </h3>
                        <p class="mt-0.5 text-sm text-slate-500">
                            Variantes disponibles con talla y stock.
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

                <div class="p-6">
                    <!-- Info general -->
                    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div
                            class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400"
                            >
                                Código
                            </p>
                            <p class="mt-1 text-sm font-black text-slate-800">
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
                            <p class="mt-1 text-sm font-black text-slate-800">
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
                            <p class="mt-1 text-sm font-black text-slate-800">
                                {{ producto?.marca || "—" }}
                            </p>
                        </div>
                    </div>

                    <!-- Tabla de variantes -->
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
                                            Estado
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Vacío -->
                                    <tr
                                        v-if="
                                            !(producto?.variantes || []).length
                                        "
                                    >
                                        <td
                                            colspan="6"
                                            class="px-4 py-12 text-center"
                                        >
                                            <div
                                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-6 w-6 text-slate-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"
                                                    />
                                                </svg>
                                            </div>
                                            <p
                                                class="text-sm font-black text-slate-700"
                                            >
                                                Sin variantes registradas
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-400"
                                            >
                                                Este producto no tiene variantes
                                                todavía.
                                            </p>
                                        </td>
                                    </tr>

                                    <!-- Filas -->
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
                                            >
                                                {{ variant.sku || "—" }}
                                            </code>
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
                                            <span
                                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                                :class="
                                                    (
                                                        variant.estado || ''
                                                    ).toUpperCase() === 'ACTIVO'
                                                        ? 'bg-emerald-100 text-emerald-700'
                                                        : 'bg-rose-100 text-rose-700'
                                                "
                                            >
                                                {{ variant.estado || "—" }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pie del modal -->
                <div
                    class="flex justify-end border-t border-slate-100 px-6 py-4"
                >
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        @click="$emit('close')"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </teleport>
</template>
