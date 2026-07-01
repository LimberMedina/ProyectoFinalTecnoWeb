<template>
    <AppLayout title="Editar Pedido">
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
                            Pedidos
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Editar pedido #{{ pedido.id }}
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Modifica los datos del pedido seleccionado.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center justify-between gap-3"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                >
                                    Paso 1
                                </p>
                                <h2
                                    class="mt-2 text-xl font-black text-slate-900"
                                >
                                    Seleccionar cliente
                                </h2>
                            </div>
                            <i
                                class="bi bi-person-lines-fill text-2xl text-emerald-600"
                            ></i>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Cliente
                                    <span class="text-rose-500">*</span></label
                                >
                                <select
                                    v-model="form.user_id"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    required
                                >
                                    <option value="">
                                        -- Seleccione un cliente --
                                    </option>
                                    <option
                                        v-for="cliente in clientes"
                                        :key="cliente.id"
                                        :value="cliente.id"
                                    >
                                        {{ cliente.nombre }}
                                        {{ cliente.apellidos }} - CI:
                                        {{ cliente.ci }}
                                    </option>
                                </select>

                                <div
                                    v-if="form.user_id"
                                    class="mt-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                                >
                                    <i class="bi bi-info-circle me-2"></i>
                                    {{ clienteSeleccionado.email }} |
                                    {{ clienteSeleccionado.telefono }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center justify-between gap-3"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                >
                                    Paso 2
                                </p>
                                <h2
                                    class="mt-2 text-xl font-black text-slate-900"
                                >
                                    Agregar productos
                                </h2>
                            </div>
                            <i
                                class="bi bi-bag-plus text-2xl text-emerald-600"
                            ></i>
                        </div>

                        <div class="mb-4 max-w-xl">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Buscar producto</label
                            >
                            <div class="relative">
                                <i
                                    class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                                ></i>
                                <input
                                    v-model="searchProducto"
                                    type="text"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    placeholder="Buscar producto por nombre o código..."
                                />
                            </div>
                        </div>

                        <div
                            class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                        >
                            <div class="max-h-[24rem] overflow-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead
                                        class="sticky top-0 z-10 bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                    >
                                        <tr>
                                            <th class="px-4 py-4">Código</th>
                                            <th class="px-4 py-4">Producto</th>
                                            <th class="px-4 py-4">Categoría</th>
                                            <th class="px-4 py-4">Precio</th>
                                            <th class="px-4 py-4">Stock</th>
                                            <th class="px-4 py-4">Descuento</th>
                                            <th class="px-4 py-4 text-center">
                                                Acción
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="producto in productosFiltrados"
                                            :key="producto.id"
                                            class="border-t border-slate-100 transition hover:bg-slate-50/70"
                                        >
                                            <td
                                                class="px-4 py-4 text-slate-600"
                                            >
                                                {{ producto.codigo }}
                                            </td>
                                            <td
                                                class="px-4 py-4 font-semibold text-slate-900"
                                            >
                                                {{ producto.nombre }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-600"
                                            >
                                                {{
                                                    producto.categoria
                                                        ?.nombre || "N/A"
                                                }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{
                                                    formatearMoneda(
                                                        producto.precio_venta,
                                                    )
                                                }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <span
                                                    :class="
                                                        producto.stock_actual <
                                                        10
                                                            ? 'inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700'
                                                            : 'inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700'
                                                    "
                                                >
                                                    {{ producto.stock_actual }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span
                                                    v-if="
                                                        producto.promociones
                                                            ?.length
                                                    "
                                                    class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700"
                                                >
                                                    -{{
                                                        producto.promociones[0]
                                                            .valor_descuento_decimal
                                                    }}%
                                                </span>
                                                <span
                                                    v-else
                                                    class="text-slate-400"
                                                    >-</span
                                                >
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                                                    @click="
                                                        agregarProducto(
                                                            producto,
                                                        )
                                                    "
                                                    :disabled="
                                                        producto.stock_actual ===
                                                        0
                                                    "
                                                >
                                                    <i
                                                        class="bi bi-plus-circle"
                                                    ></i>
                                                    Agregar
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div
                                v-if="form.detalles.length > 0"
                                class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                            >
                                <div
                                    class="border-b border-slate-100 bg-slate-50 px-4 py-4"
                                >
                                    <h3
                                        class="flex items-center gap-2 text-base font-black text-slate-900"
                                    >
                                        <i
                                            class="bi bi-box-seam text-emerald-600"
                                        ></i>
                                        Productos seleccionados
                                    </h3>
                                </div>

                                <div class="overflow-auto">
                                    <table class="min-w-full text-left text-sm">
                                        <thead
                                            class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                        >
                                            <tr>
                                                <th class="px-4 py-4">
                                                    Producto
                                                </th>
                                                <th class="px-4 py-4">
                                                    Cantidad
                                                </th>
                                                <th class="px-4 py-4">
                                                    Precio Unit.
                                                </th>
                                                <th class="px-4 py-4">
                                                    Descuento
                                                </th>
                                                <th class="px-4 py-4">
                                                    Subtotal
                                                </th>
                                                <th
                                                    class="px-4 py-4 text-center"
                                                >
                                                    Acción
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    detalle, index
                                                ) in form.detalles"
                                                :key="detalle.producto_id"
                                                class="border-t border-slate-100"
                                            >
                                                <td
                                                    class="px-4 py-4 font-semibold text-slate-900"
                                                >
                                                    {{ detalle.nombre }}
                                                </td>
                                                <td class="px-4 py-4">
                                                    <input
                                                        v-model.number="
                                                            detalle.cantidad
                                                        "
                                                        type="number"
                                                        min="1"
                                                        :max="
                                                            detalle.stock_disponible
                                                        "
                                                        class="w-24 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                                        @input="calcularTotales"
                                                    />
                                                    <p
                                                        class="mt-1 text-xs text-slate-500"
                                                    >
                                                        Máx:
                                                        {{
                                                            detalle.stock_disponible
                                                        }}
                                                    </p>
                                                </td>
                                                <td
                                                    class="px-4 py-4 text-slate-700"
                                                >
                                                    {{
                                                        formatearMoneda(
                                                            detalle.precio_unitario,
                                                        )
                                                    }}
                                                </td>
                                                <td class="px-4 py-4">
                                                    <span
                                                        v-if="
                                                            detalle.descuento_porcentaje >
                                                            0
                                                        "
                                                        class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700"
                                                    >
                                                        -{{
                                                            detalle.descuento_porcentaje
                                                        }}%
                                                    </span>
                                                    <span
                                                        v-else
                                                        class="text-slate-400"
                                                        >-</span
                                                    >
                                                </td>
                                                <td
                                                    class="px-4 py-4 font-bold text-emerald-700"
                                                >
                                                    {{
                                                        formatearMoneda(
                                                            detalle.subtotal,
                                                        )
                                                    }}
                                                </td>
                                                <td
                                                    class="px-4 py-4 text-center"
                                                >
                                                    <button
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-700 transition hover:bg-rose-100"
                                                        type="button"
                                                        @click="
                                                            quitarProducto(
                                                                index,
                                                            )
                                                        "
                                                    >
                                                        <i
                                                            class="bi bi-trash3"
                                                        ></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-slate-50">
                                            <tr>
                                                <td
                                                    colspan="4"
                                                    class="px-4 py-4 text-end font-bold text-slate-700"
                                                >
                                                    TOTAL:
                                                </td>
                                                <td
                                                    colspan="2"
                                                    class="px-4 py-4 font-black text-emerald-700"
                                                >
                                                    {{
                                                        formatearMoneda(
                                                            totalGeneral,
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div
                                v-else
                                class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-slate-500"
                            >
                                <i
                                    class="bi bi-info-circle mb-2 block text-2xl text-emerald-600"
                                ></i>
                                No hay productos seleccionados. Agregue al menos
                                uno.
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center justify-between gap-3"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                                >
                                    Paso 3
                                </p>
                                <h2
                                    class="mt-2 text-xl font-black text-slate-900"
                                >
                                    Método de pago
                                </h2>
                            </div>
                            <i
                                class="bi bi-credit-card text-2xl text-emerald-600"
                            ></i>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Tipo de pago
                                    <span class="text-rose-500">*</span></label
                                >
                                <select
                                    v-model="form.tipo_pago"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    required
                                >
                                    <option value="contado">Al Contado</option>
                                    <option value="credito">A Crédito</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                    >Método de pago
                                    <span class="text-rose-500">*</span></label
                                >
                                <select
                                    v-model="form.metodo_pago_id"
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-100"
                                    required
                                >
                                    <option value="">
                                        -- Seleccione método de pago --
                                    </option>
                                    <option
                                        v-for="metodo in metodosPago"
                                        :key="metodo.id"
                                        :value="metodo.id"
                                    >
                                        {{ metodo.nombre }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div
                            v-if="form.tipo_pago === 'credito'"
                            class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                        >
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Pago a crédito:</strong> El número de cuotas
                            se configurará al confirmar el pedido.
                        </div>
                    </section>

                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:justify-end"
                        >
                            <Link
                                :href="route('pedidos.index')"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                <i class="bi bi-x-lg"></i>Cancelar
                            </Link>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="!formularioValido || processing"
                            >
                                <i class="bi bi-save"></i>
                                Actualizar pedido
                            </button>
                        </div>
                    </section>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    pedido: Object,
    clientes: Array,
    productos: Array,
    metodosPago: Array,
});

const form = ref({
    user_id: "",
    metodo_pago_id: "",
    tipo_pago: "contado",
    detalles: [],
    numero_cuotas: 3,
});

const searchProducto = ref("");
const processing = ref(false);

onMounted(() => {
    form.value.user_id = props.pedido.user_id;
    form.value.metodo_pago_id = props.pedido.metodo_pago_id;
    form.value.tipo_pago = props.pedido.tipo_pago || "contado";

    props.pedido.detalles.forEach((detalle) => {
        const producto = detalle.producto;
        const descuentoPorcentaje =
            detalle.descuento > 0
                ? (detalle.descuento / detalle.precio_unitario) * 100
                : 0;

        form.value.detalles.push({
            producto_id: detalle.producto_id,
            nombre: producto.nombre,
            cantidad: detalle.cantidad,
            precio_unitario: parseFloat(detalle.precio_unitario),
            descuento_porcentaje: descuentoPorcentaje,
            stock_disponible: producto.stock_actual + detalle.cantidad,
            subtotal: parseFloat(detalle.subtotal),
        });
    });
});

const clienteSeleccionado = computed(
    () => props.clientes.find((c) => c.id === form.value.user_id) || {},
);

const productosFiltrados = computed(() => {
    if (!searchProducto.value) return props.productos;
    const s = searchProducto.value.toLowerCase();
    return props.productos.filter(
        (p) =>
            p.nombre.toLowerCase().includes(s) ||
            (p.codigo && p.codigo.toLowerCase().includes(s)),
    );
});

const totalGeneral = computed(() =>
    form.value.detalles.reduce((sum, d) => sum + d.subtotal, 0),
);

const formularioValido = computed(
    () =>
        form.value.user_id &&
        form.value.metodo_pago_id &&
        form.value.detalles.length > 0,
);

const agregarProducto = (producto) => {
    const existe = form.value.detalles.find(
        (d) => d.producto_id === producto.id,
    );
    if (existe) return alert("Este producto ya está agregado");

    let des = 0;
    if (producto.promociones?.length)
        des = parseFloat(producto.promociones[0].valor_descuento_decimal);

    const precioConDescuento = producto.precio_venta * (1 - des / 100);

    form.value.detalles.push({
        producto_id: producto.id,
        nombre: producto.nombre,
        cantidad: 1,
        precio_unitario: parseFloat(producto.precio_venta),
        descuento_porcentaje: des,
        stock_disponible: producto.stock_actual,
        subtotal: precioConDescuento,
    });

    calcularTotales();
};

const quitarProducto = (index) => {
    form.value.detalles.splice(index, 1);
    calcularTotales();
};

const calcularTotales = () => {
    form.value.detalles.forEach((d) => {
        const precioConDescuento =
            d.precio_unitario * (1 - d.descuento_porcentaje / 100);
        d.subtotal = precioConDescuento * d.cantidad;
    });
};

const formatearMoneda = (valor) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(valor);

const submitForm = () => {
    if (!formularioValido.value)
        return alert("Complete los campos obligatorios");
    processing.value = true;

    router.put(route("pedidos.update", props.pedido.id), form.value, {
        onFinish: () => (processing.value = false),
    });
};
</script>

<style scoped>
.table-hover tbody tr:hover {
    background-color: #f7f7f7;
}
</style>
