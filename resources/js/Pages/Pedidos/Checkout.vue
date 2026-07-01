<template>
    <PublicStoreLayout>
        <FlashNotification />

        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <!-- Encabezado -->
                <div class="mb-8">
                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em] text-emerald-700 shadow-sm"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-500"
                        ></span>
                        Pedidos
                    </div>

                    <h1
                        class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Finalizar compra
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        Revisa el resumen del pedido y selecciona tu método de
                        pago.
                    </p>
                </div>

                <!-- Grid principal -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <!-- Columna izquierda: Pago -->
                    <aside class="lg:col-span-7">
                        <div
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <!-- Cabecera de la tarjeta -->
                            <div
                                class="mb-6 flex items-center justify-between gap-3 border-b border-slate-100 pb-5"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                    >
                                        Pago
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-black text-slate-900"
                                    >
                                        Pago al contado
                                    </h2>
                                </div>
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50"
                                >
                                    <i
                                        class="bi bi-cash-coin text-xl text-emerald-600"
                                    ></i>
                                </div>
                            </div>

                            <form
                                class="space-y-5"
                                @submit.prevent="procesarVenta"
                            >
                                <input
                                    type="hidden"
                                    v-model="form.tipo_venta"
                                />

                                <!-- Aviso de método -->
                                <div
                                    class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                                >
                                    <p class="font-semibold">
                                        {{
                                            esMetodoQR
                                                ? "Pago con QR"
                                                : "Venta al contado"
                                        }}
                                    </p>
                                    <p class="mt-1 leading-5 text-emerald-700">
                                        {{
                                            esMetodoQR
                                                ? "Al confirmar la compra se generará el QR real de PagoFácil para completar el pago."
                                                : "Esta compra se registrará como pago al contado. Puedes elegir el método con el que deseas pagar."
                                        }}
                                    </p>
                                </div>

                                <!-- Método de pago -->
                                <div>
                                    <label
                                        for="metodo_pago_id"
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        Método de pago
                                    </label>
                                    <select
                                        id="metodo_pago_id"
                                        v-model="form.metodo_pago_id"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                        :class="{
                                            'border-rose-300 focus:border-rose-500 focus:ring-rose-100':
                                                form.errors.metodo_pago_id,
                                        }"
                                    >
                                        <option value="" disabled>
                                            Selecciona un método de pago
                                        </option>
                                        <option
                                            v-for="metodo in metodosPago"
                                            :key="metodo.id"
                                            :value="metodo.id"
                                        >
                                            {{ metodo.nombre }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.metodo_pago_id"
                                        class="mt-2 text-xs font-semibold text-rose-600"
                                    >
                                        {{ form.errors.metodo_pago_id }}
                                    </p>
                                </div>

                                <!-- Método seleccionado (info adicional) -->
                                <div
                                    v-if="metodoSeleccionado"
                                    class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600"
                                >
                                    <p class="font-semibold text-slate-800">
                                        Método seleccionado:
                                        {{ metodoSeleccionado.nombre }}
                                    </p>
                                    <p class="mt-1">
                                        {{
                                            esMetodoQR
                                                ? "Se generará un QR real de PagoFácil y el pedido quedará pendiente hasta confirmar el pago."
                                                : "El pedido se registrará como contado, no como crédito."
                                        }}
                                    </p>
                                </div>

                                <!-- Dirección de entrega -->
                                <div>
                                    <label
                                        for="direccion_entrega"
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        Dirección de entrega
                                    </label>
                                    <textarea
                                        id="direccion_entrega"
                                        v-model="form.direccion_entrega"
                                        rows="3"
                                        placeholder="Escribe aquí la dirección completa donde quieres recibir tu pedido"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                        :class="{
                                            'border-rose-300 focus:border-rose-500 focus:ring-rose-100':
                                                form.errors.direccion_entrega,
                                        }"
                                    />
                                    <p class="mt-2 text-xs text-slate-500">
                                        Esta dirección se usará para enviar tu
                                        pedido antes de confirmar el pago.
                                    </p>
                                    <p
                                        v-if="form.errors.direccion_entrega"
                                        class="mt-2 text-xs font-semibold text-rose-600"
                                    >
                                        {{ form.errors.direccion_entrega }}
                                    </p>
                                </div>

                                <!-- Totales -->
                                <div
                                    class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-4"
                                >
                                    <div
                                        class="flex items-center justify-between text-sm text-slate-500"
                                    >
                                        <span>Subtotal</span>
                                        <span>{{
                                            formatearMoneda(total)
                                        }}</span>
                                    </div>
                                    <div
                                        class="mt-4 flex items-center justify-between border-t border-slate-200 pt-4"
                                    >
                                        <span
                                            class="text-sm font-semibold text-slate-700"
                                            >Total a pagar</span
                                        >
                                        <span
                                            class="text-xl font-black text-emerald-700"
                                        >
                                            {{ formatearMoneda(total) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div class="space-y-3 pt-1">
                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="
                                            form.processing ||
                                            !form.metodo_pago_id
                                        "
                                    >
                                        <span
                                            v-if="form.processing"
                                            class="inline-flex items-center gap-2"
                                        >
                                            <span
                                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                                            ></span>
                                            Procesando...
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-2"
                                        >
                                            <i class="bi bi-check-circle"></i>
                                            {{
                                                esMetodoQR
                                                    ? "Generar QR y confirmar"
                                                    : "Confirmar compra"
                                            }}
                                        </span>
                                    </button>

                                    <Link
                                        :href="route('carritos.index')"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        <i class="bi bi-arrow-left"></i>
                                        Volver al carrito
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </aside>

                    <!-- Columna derecha: Resumen de productos -->
                    <section class="lg:col-span-5">
                        <div
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <!-- Cabecera de la tarjeta -->
                            <div
                                class="mb-6 flex items-center justify-between gap-3 border-b border-slate-100 pb-5"
                            >
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                    >
                                        Resumen
                                    </p>
                                    <h2
                                        class="mt-1 text-xl font-black text-slate-900"
                                    >
                                        Productos del carrito
                                    </h2>
                                </div>
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50"
                                >
                                    <i
                                        class="bi bi-box-seam text-xl text-emerald-600"
                                    ></i>
                                </div>
                            </div>

                            <!-- Badge de método -->
                            <div class="mb-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"
                                >
                                    <i class="bi bi-credit-card text-xs"></i>
                                    {{
                                        esMetodoQR
                                            ? "Pago con QR"
                                            : "Pago al contado"
                                    }}
                                </span>
                            </div>

                            <!-- Lista de productos -->
                            <div class="space-y-3">
                                <article
                                    v-for="item in detalles"
                                    :key="
                                        item.variante?.id ||
                                        item.producto?.id ||
                                        item.cantidad
                                    "
                                    class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                                >
                                    <div
                                        class="flex items-start justify-between gap-4"
                                    >
                                        <div class="min-w-0 flex-1">
                                            <h3
                                                class="truncate text-sm font-semibold text-slate-900"
                                            >
                                                {{ item.producto.nombre }}
                                            </h3>
                                            <p
                                                class="mt-1 text-xs text-slate-400"
                                            >
                                                Talla:
                                                {{
                                                    item.variante?.talla || "—"
                                                }}
                                                &nbsp;·&nbsp; Color:
                                                {{
                                                    item.variante?.color || "—"
                                                }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                {{ item.cantidad }} ×
                                                {{
                                                    formatearMoneda(
                                                        item.precio_unitario,
                                                    )
                                                }}
                                            </p>
                                            <span
                                                v-if="item.descuento > 0"
                                                class="mt-2 inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700"
                                            >
                                                −{{
                                                    formatearMoneda(
                                                        item.descuento,
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div class="flex-shrink-0 text-right">
                                            <p class="text-xs text-slate-400">
                                                Subtotal
                                            </p>
                                            <p
                                                class="mt-0.5 text-base font-black text-emerald-700"
                                            >
                                                {{
                                                    formatearMoneda(
                                                        item.subtotal,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </article>
                            </div>

                            <!-- Total del resumen -->
                            <div
                                class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-semibold text-emerald-800"
                                        >Total del pedido</span
                                    >
                                    <span
                                        class="text-lg font-black text-emerald-700"
                                        >{{ formatearMoneda(total) }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </PublicStoreLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    detalles: {
        type: Array,
        default: () => [],
    },
    total: {
        type: Number,
        default: 0,
    },
    metodosPago: {
        type: Array,
        default: () => [],
    },
    direccionEntrega: {
        type: String,
        default: "",
    },
});

const form = useForm({
    metodo_pago_id: props.metodosPago.length > 0 ? props.metodosPago[0].id : "",
    tipo_venta: "contado",
    meses_plazo: 0,
    tasa_interes: 0,
    fecha_primer_pago: "",
    direccion_entrega: props.direccionEntrega || "",
});

const metodoSeleccionado = computed(() => {
    return props.metodosPago.find(
        (metodo) => Number(metodo.id) === Number(form.metodo_pago_id),
    );
});

const esMetodoQR = computed(() => {
    return (
        (metodoSeleccionado.value?.nombre || "")
            .toString()
            .trim()
            .toUpperCase() === "QR"
    );
});

const formatearMoneda = (amount) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(Number(amount || 0));
};

const procesarVenta = () => {
    form.tipo_venta = "contado";
    form.meses_plazo = 0;
    form.tasa_interes = 0;
    form.fecha_primer_pago = "";

    form.post(route("pedidos.store"), {
        preserveScroll: true,
    });
};
</script>
