<script setup>
import { ref, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import CreditoModal from "@/Components/CreditoModal.vue";

const props = defineProps({
    pedidos: Object,
    filtro_origen: String,
    filtro_estado: String,
});

// Filtros por defecto si vienen vacíos
const filtroOrigen = computed(() => props.filtro_origen || "tienda");
const filtroEstado = computed(() => props.filtro_estado || "pendiente");

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);

const getBadgeClass = (estado) => {
    const badges = {
        pendiente: "warning",
        pagado: "success",
        enviado: "info",
        entregado: "success",
        anulado: "danger",
    };
    return `bg-${badges[estado] || "secondary"}`;
};

// Modales de confirmación
const showConfirmModal = ref(false);
const showCancelModal = ref(false);
const mostrarModalCredito = ref(false);
const pedidoSeleccionado = ref(null);

const confirmarPedido = (pedido) => {
    pedidoSeleccionado.value = pedido;
    showConfirmModal.value = true;
};

const ejecutarConfirmacion = () => {
    if (!pedidoSeleccionado.value) return;

    const esCredito = pedidoSeleccionado.value.tipo_pago === "credito";

    // Si es crédito, mostrar modal para configurar cuotas
    if (esCredito) {
        mostrarModalCredito.value = true;
        return;
    }

    // Si es al contado, confirmar directamente
    router.patch(
        route("pedidos.accion", pedidoSeleccionado.value.id),
        {
            accion: "confirmar",
        },
        {
            onFinish: () => {
                cerrarModales();
            },
        },
    );
};

const confirmarConCuotas = (numeroCuotas) => {
    router.patch(
        route("pedidos.accion", pedidoSeleccionado.value.id),
        {
            accion: "confirmar",
            numero_cuotas: numeroCuotas,
        },
        {
            onFinish: () => {
                cerrarModales();
                mostrarModalCredito.value = false;
            },
        },
    );
};

const cancelarPedido = (pedido) => {
    pedidoSeleccionado.value = pedido;
    showCancelModal.value = true;
};

const ejecutarCancelacion = () => {
    if (!pedidoSeleccionado.value) return;

    router.patch(
        route("pedidos.accion", pedidoSeleccionado.value.id),
        {
            accion: "cancelar",
        },
        {
            onFinish: () => {
                cerrarModales();
            },
        },
    );
};

const cerrarModales = () => {
    showConfirmModal.value = false;
    showCancelModal.value = false;
    pedidoSeleccionado.value = null;
};

const marcarEnviado = (pedido) => {
    if (
        !confirm(`¿Está seguro de marcar el pedido #${pedido.id} como enviado?`)
    )
        return;

    router.patch(
        route("pedidos.marcar-enviado", pedido.id),
        {},
        {
            preserveState: false,
            preserveScroll: false,
        },
    );
};

// Cambiar filtro de estado
const cambiarFiltroEstado = (estado) => {
    router.get(
        route("pedidos.index"),
        { origen: filtroOrigen.value, estado },
        {
            preserveState: false,
            preserveScroll: false,
        },
    );
};

// Cambiar filtro de origen
const cambiarFiltroOrigen = (origen) => {
    router.get(
        route("pedidos.index"),
        { origen, estado: filtroEstado.value },
        {
            preserveState: false,
            preserveScroll: false,
        },
    );
};

// Calcular páginas visibles
const visiblePages = computed(() => {
    const current = props.pedidos.current_page;
    const last = props.pedidos.last_page;
    const delta = 2;
    const range = [];
    const rangeWithDots = [];

    for (
        let i = Math.max(2, current - delta);
        i <= Math.min(last - 1, current + delta);
        i++
    ) {
        range.push(i);
    }

    if (current - delta > 2) {
        rangeWithDots.push(1, "...");
    } else {
        rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (current + delta < last - 1) {
        rangeWithDots.push("...", last);
    } else if (last > 1) {
        rangeWithDots.push(last);
    }

    return rangeWithDots;
});
</script>

<template>
    <AppLayout title="Gestión de Pedidos">
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
                            Gestión de pedidos
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Administra los pedidos realizados por clientes.
                        </p>
                    </div>

                    <Link
                        :href="route('pedidos.create')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Nuevo pedido
                    </Link>
                </div>

                <section
                    class="mb-6 rounded-[2rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div class="flex flex-wrap gap-2">
                            <button
                                class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                                :class="
                                    filtroOrigen === 'tienda'
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                "
                                @click="cambiarFiltroOrigen('tienda')"
                            >
                                <i class="bi bi-shop"></i>
                                Tienda
                            </button>
                            <button
                                class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                                :class="
                                    filtroOrigen === 'online'
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                "
                                @click="cambiarFiltroOrigen('online')"
                            >
                                <i class="bi bi-globe2"></i>
                                Online
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="estado in [
                                    'pendiente',
                                    'pagado',
                                    'enviado',
                                    'entregado',
                                    'anulado',
                                ]"
                                :key="estado"
                                class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition"
                                :class="
                                    filtroEstado === estado
                                        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                        : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                "
                                @click="cambiarFiltroEstado(estado)"
                            >
                                <i
                                    class="bi"
                                    :class="{
                                        'bi-clock-history':
                                            estado === 'pendiente',
                                        'bi-cash-coin': estado === 'pagado',
                                        'bi-truck': estado === 'enviado',
                                        'bi-check-square':
                                            estado === 'entregado',
                                        'bi-x-circle': estado === 'anulado',
                                    }"
                                ></i>
                                {{
                                    estado.charAt(0).toUpperCase() +
                                    estado.slice(1)
                                }}
                            </button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="pedidos.data.length > 0"
                    class="rounded-[2rem] border border-white bg-white/90 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)] overflow-hidden"
                >
                    <div class="overflow-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead
                                class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                            >
                                <tr>
                                    <th class="px-6 py-4">N° Pedido</th>
                                    <th class="px-6 py-4">Fecha</th>
                                    <th class="px-6 py-4">Cliente</th>
                                    <th class="px-6 py-4">Método de pago</th>
                                    <th class="px-6 py-4">Tipo de pago</th>
                                    <th
                                        v-if="filtroOrigen === 'online'"
                                        class="px-6 py-4"
                                    >
                                        Dirección de entrega
                                    </th>
                                    <th class="px-6 py-4">Total</th>
                                    <th class="px-6 py-4">Estado</th>
                                    <th class="px-6 py-4 text-center">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="pedido in pedidos.data"
                                    :key="pedido.id"
                                    class="border-t border-slate-100 transition hover:bg-slate-50/70"
                                >
                                    <td
                                        class="px-6 py-4 font-semibold text-slate-900"
                                    >
                                        #{{ pedido.id }}
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">
                                        {{
                                            new Date(
                                                pedido.created_at,
                                            ).toLocaleDateString("es-ES", {
                                                day: "2-digit",
                                                month: "short",
                                                year: "numeric",
                                                hour: "2-digit",
                                                minute: "2-digit",
                                            })
                                        }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-900">
                                            {{ pedido.user?.nombre }}
                                            {{ pedido.user?.apellidos }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ pedido.user?.email }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                                            :class="
                                                (
                                                    pedido.metodo_pago
                                                        ?.nombre ||
                                                    pedido.metodoPago?.nombre ||
                                                    ''
                                                )?.toLowerCase() === 'crédito'
                                                    ? 'bg-sky-100 text-sky-700'
                                                    : 'bg-emerald-100 text-emerald-700'
                                            "
                                        >
                                            {{
                                                pedido.metodo_pago?.nombre ||
                                                pedido.metodoPago?.nombre ||
                                                "N/A"
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                                            :class="
                                                pedido.tipo_pago === 'credito'
                                                    ? 'bg-amber-100 text-amber-700'
                                                    : 'bg-emerald-100 text-emerald-700'
                                            "
                                        >
                                            {{
                                                pedido.tipo_pago === "credito"
                                                    ? "A crédito"
                                                    : "Al contado"
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        v-if="filtroOrigen === 'online'"
                                        class="px-6 py-4 text-slate-600"
                                    >
                                        {{ pedido.direccion_entrega || "-" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 font-bold text-slate-900"
                                    >
                                        Bs. {{ formatMoney(pedido.total) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold"
                                            :class="
                                                pedido.tipo_pago ===
                                                    'credito' &&
                                                pedido.credito &&
                                                pedido.credito.estado ===
                                                    'pendiente'
                                                    ? 'bg-amber-100 text-amber-700'
                                                    : getBadgeClass(
                                                          pedido.estado,
                                                      )
                                            "
                                        >
                                            {{
                                                pedido.tipo_pago ===
                                                    "credito" &&
                                                pedido.credito &&
                                                pedido.credito.estado ===
                                                    "pendiente"
                                                    ? `Crédito (${pedido.credito.cuotas.filter((c) => c.estado === "pagada").length}/${pedido.credito.cuotas.length})`
                                                    : pedido.estado
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div
                                            class="inline-flex items-center gap-2"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'pedidos.show',
                                                        pedido.id,
                                                    )
                                                "
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                                                title="Ver detalles"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </Link>

                                            <Link
                                                v-if="
                                                    filtroOrigen === 'tienda' &&
                                                    pedido.estado ===
                                                        'pendiente'
                                                "
                                                :href="
                                                    route(
                                                        'pedidos.edit',
                                                        pedido.id,
                                                    )
                                                "
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700"
                                                title="Editar pedido"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </Link>

                                            <button
                                                v-if="
                                                    filtroOrigen === 'tienda' &&
                                                    pedido.estado ===
                                                        'pendiente'
                                                "
                                                type="button"
                                                @click="confirmarPedido(pedido)"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                                                title="Confirmar pedido"
                                            >
                                                <i
                                                    class="bi bi-check-circle"
                                                ></i>
                                            </button>
                                            <button
                                                v-if="
                                                    filtroOrigen === 'tienda' &&
                                                    pedido.estado ===
                                                        'pendiente'
                                                "
                                                type="button"
                                                @click="cancelarPedido(pedido)"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-700 transition hover:bg-rose-100"
                                                title="Cancelar pedido"
                                            >
                                                <i class="bi bi-x-circle"></i>
                                            </button>

                                            <button
                                                v-if="
                                                    filtroOrigen === 'online' &&
                                                    pedido.estado ===
                                                        'pendiente'
                                                "
                                                type="button"
                                                @click="cancelarPedido(pedido)"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-700 transition hover:bg-rose-100"
                                                title="Cancelar pedido"
                                            >
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                            <button
                                                v-if="
                                                    filtroOrigen === 'online' &&
                                                    pedido.estado === 'pagado'
                                                "
                                                type="button"
                                                @click="marcarEnviado(pedido)"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100"
                                                title="Marcar como enviado"
                                            >
                                                <i class="bi bi-truck"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <nav
                        v-if="pedidos.last_page > 1"
                        class="border-t border-slate-100 px-6 py-5"
                    >
                        <ul
                            class="flex flex-wrap items-center justify-center gap-2"
                        >
                            <li>
                                <Link
                                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                    :href="pedidos.prev_page_url || '#'"
                                    :disabled="!pedidos.prev_page_url"
                                >
                                    Anterior
                                </Link>
                            </li>
                            <li v-for="page in visiblePages" :key="page">
                                <Link
                                    v-if="page !== '...'"
                                    class="inline-flex min-w-10 items-center justify-center rounded-xl border px-3 py-2 text-sm font-semibold transition"
                                    :class="
                                        page === pedidos.current_page
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                    "
                                    :href="pedidos.links[page]?.url || '#'"
                                >
                                    {{ page }}
                                </Link>
                                <span
                                    v-else
                                    class="inline-flex min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500"
                                    >...</span
                                >
                            </li>
                            <li>
                                <Link
                                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                    :href="pedidos.next_page_url || '#'"
                                    :disabled="!pedidos.next_page_url"
                                >
                                    Siguiente
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </section>

                <div
                    v-else
                    class="rounded-[2rem] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-slate-500"
                >
                    <i
                        class="bi bi-info-circle mb-2 block text-2xl text-emerald-600"
                    ></i>
                    No hay pedidos
                    {{
                        filtroEstado === "pendiente"
                            ? "pendientes"
                            : filtroEstado === "pagado"
                              ? "pagados"
                              : filtroEstado === "enviado"
                                ? "enviados"
                                : "anulados"
                    }}.
                </div>
            </div>
        </div>

        <!-- Modal Confirmar -->
        <ConfirmationModal
            :show="showConfirmModal"
            @close="cerrarModales"
            max-width="sm"
        >
            <template #title> Confirmar Pedido </template>

            <template #content>
                <p class="mb-0">
                    ¿Está seguro de que desea confirmar el pedido
                    <strong>#{{ pedidoSeleccionado?.id }}</strong
                    >?
                </p>
                <p class="text-muted small mb-0 mt-2">
                    Esta acción convertirá el pedido en una venta.
                </p>
            </template>

            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="cerrarModales"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-success"
                    @click="ejecutarConfirmacion"
                >
                    <i class="bi bi-check-circle me-2"></i>
                    Confirmar
                </button>
            </template>
        </ConfirmationModal>

        <!-- Modal Cancelar -->
        <ConfirmationModal
            :show="showCancelModal"
            @close="cerrarModales"
            max-width="sm"
        >
            <template #title> Cancelar Pedido </template>

            <template #content>
                <p class="mb-0">
                    ¿Está seguro de que desea cancelar el pedido
                    <strong>#{{ pedidoSeleccionado?.id }}</strong
                    >?
                </p>
                <p class="text-muted small mb-0 mt-2">
                    El stock de los productos será devuelto automáticamente.
                </p>
            </template>

            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="cerrarModales"
                >
                    No, volver
                </button>
                <button
                    type="button"
                    class="btn btn-danger"
                    @click="ejecutarCancelacion"
                >
                    <i class="bi bi-x-circle me-2"></i>
                    Sí, cancelar pedido
                </button>
            </template>
        </ConfirmationModal>

        <!-- Modal de configuración de crédito -->
        <CreditoModal
            :show="mostrarModalCredito"
            :total="pedidoSeleccionado?.total || 0"
            :cuotas-iniciales="3"
            @close="mostrarModalCredito = false"
            @confirmar="confirmarConCuotas"
        />
    </AppLayout>
</template>
