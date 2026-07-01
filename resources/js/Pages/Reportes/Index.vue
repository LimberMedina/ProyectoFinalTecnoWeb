<script setup>
import { ref, watch, onMounted, nextTick, computed } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import Chart from "chart.js/auto";
import AppLayout from "@/Layouts/AppLayout.vue";

const tipoReporte = ref("ventas-fecha");
const fechaInicio = ref("");
const fechaFin = ref("");
const limite = ref(20);
const stockMinimo = ref(10);

const reportes = [
    {
        value: "ventas-fecha",
        label: "Ventas por Fecha",
        icon: "bi-calendar-check",
        requireFechas: true,
    },
    {
        value: "ventas-metodo",
        label: "Ventas por Método de Pago",
        icon: "bi-credit-card",
        requireFechas: true,
    },
    {
        value: "creditos-estado",
        label: "Créditos por Estado",
        icon: "bi-cash-stack",
        requireFechas: true,
    },
    {
        value: "productos-vendidos",
        label: "Productos Más Vendidos",
        icon: "bi-bar-chart",
        requireFechas: true,
        hasLimite: true,
    },
    {
        value: "clientes-top",
        label: "Clientes Top",
        icon: "bi-people",
        requireFechas: true,
        hasLimite: true,
    },
    {
        value: "inventario-critico",
        label: "Inventario Crítico",
        icon: "bi-exclamation-triangle",
        requireFechas: false,
        hasStock: true,
    },
];

const reporteSeleccionado = ref(reportes[0]);

const seleccionarReporte = (reporte) => {
    reporteSeleccionado.value = reporte;
    tipoReporte.value = reporte.value;
};

const generarReporte = () => {
    if (!fechaInicio.value || !fechaFin.value) {
        alert("Por favor selecciona las fechas");
        return;
    }

    const params = {
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value,
    };

    if (reporteSeleccionado.value.hasLimite) {
        params.limite = limite.value;
    }

    if (reporteSeleccionado.value.hasStock) {
        params.stock_minimo = stockMinimo.value;
    }

    router.get(route("reportes.show", tipoReporte.value), params);
};

const hoy = new Date();
const haceUnMes = new Date();
haceUnMes.setMonth(haceUnMes.getMonth() - 1);

fechaFin.value = hoy.toISOString().split("T")[0];
fechaInicio.value = haceUnMes.toISOString().split("T")[0];

const estadisticas = ref({
    ventas_totales: 0,
    ingresos_totales: 0,
    ventas_hoy: 0,
    ingresos_mes: 0,
    creditos_pendientes: 0,
    stock_critico: 0,
    productos_activos: 0,
});

const barMetric = ref("productos_vendidos");
const donutMetric = ref("categorias");
const lineMetric = ref("evolucion");

const chartData = ref({
    bar: {
        labels: [],
        datasets: [],
    },
    donut: {
        labels: [],
        datasets: [],
    },
    line: {
        labels: [],
        datasets: [],
    },
});

const chartLoading = ref(false);
const chartError = ref("");

const barCanvas = ref(null);
const donutCanvas = ref(null);
const lineCanvas = ref(null);

const barChart = ref(null);
const donutChart = ref(null);
const lineChart = ref(null);

const metricOptions = {
    bar: [
        { value: "productos_vendidos", label: "Productos más vendidos" },
        { value: "ventas_metodo", label: "Ventas por método de pago" },
        { value: "ventas_canal", label: "Ventas por canal" },
    ],
    donut: [
        { value: "categorias", label: "Categorías más vendidas" },
        { value: "pagos_contado_credito", label: "Pago contado vs crédito" },
        { value: "estado_pedidos", label: "Estado logístico de pedidos" },
    ],
    line: [
        { value: "evolucion", label: "Evolución temporal de ventas" },
        { value: "ventas_hora", label: "Ventas por hora (omnicanal)" },
        { value: "ticket_promedio", label: "Ticket promedio mensual" },
    ],
};

const statsCards = computed(() => [
    {
        label: "Ventas totales",
        value: estadisticas.value.ventas_totales,
        description: "Ventas completadas en el período seleccionado",
        icon: "bi-cart-check",
        theme: "bg-emerald-50 text-emerald-600",
        format: "number",
    },
    {
        label: "Ingresos totales",
        value: estadisticas.value.ingresos_totales,
        description: "Monto facturado en el período seleccionado",
        icon: "bi-cash-stack",
        theme: "bg-sky-50 text-sky-600",
        format: "currency",
    },
    {
        label: "Ventas hoy",
        value: estadisticas.value.ventas_hoy,
        description: "Ventas completadas hoy",
        icon: "bi-calendar-day",
        theme: "bg-violet-50 text-violet-600",
        format: "number",
    },
    {
        label: "Ingresos mes",
        value: estadisticas.value.ingresos_mes,
        description: "Ingresos del mes actual",
        icon: "bi-bar-chart-line",
        theme: "bg-orange-50 text-orange-600",
        format: "currency",
    },
    {
        label: "Créditos pendientes",
        value: estadisticas.value.creditos_pendientes,
        description: "Créditos aún por cobrar",
        icon: "bi-wallet2",
        theme: "bg-rose-50 text-rose-600",
        format: "number",
    },
    {
        label: "Stock crítico",
        value: estadisticas.value.stock_critico,
        description: "Productos con inventario bajo",
        icon: "bi-exclamation-triangle",
        theme: "bg-amber-50 text-amber-700",
        format: "number",
    },
]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(Number(value || 0));
};

const formatCount = (value) => {
    return new Intl.NumberFormat("es-ES").format(Number(value || 0));
};

const formatCardValue = (card) => {
    return card.format === "currency"
        ? formatCurrency(card.value)
        : formatCount(card.value);
};

const buildChartOptions = (type) => {
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom",
                labels: {
                    color: "#475569",
                    boxWidth: 10,
                    padding: 16,
                },
            },
            tooltip: {
                callbacks: {
                    label: (context) => {
                        const value = context.parsed.y ?? context.parsed;
                        return `${context.dataset.label}: ${Number(
                            value,
                        ).toLocaleString("es-ES", {
                            style: type === "donut" ? "decimal" : "currency",
                            currency: "BOB",
                        })}`;
                    },
                },
            },
        },
    };

    if (type !== "donut") {
        baseOptions.scales = {
            x: {
                ticks: {
                    color: "#475569",
                },
                grid: {
                    display: false,
                },
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: "#475569",
                },
                grid: {
                    color: "rgba(148,163,184,0.18)",
                },
            },
        };
    }

    return baseOptions;
};

const renderChart = (type) => {
    const canvas =
        type === "bar"
            ? barCanvas.value
            : type === "donut"
              ? donutCanvas.value
              : lineCanvas.value;
    if (!canvas) {
        return;
    }

    const instance =
        type === "bar" ? barChart : type === "donut" ? donutChart : lineChart;
    if (instance.value) {
        instance.value.destroy();
    }

    const dataset = chartData.value[type];
    const datasets = dataset.datasets.map((serie) => ({
        ...serie,
        fill: type === "line",
        borderWidth: 2,
        tension: type === "line" ? 0.3 : 0.4,
        pointRadius: type === "line" ? 4 : 3,
    }));

    instance.value = new Chart(canvas, {
        type: type === "donut" ? "doughnut" : type,
        data: {
            labels: dataset.labels,
            datasets,
        },
        options: buildChartOptions(type),
    });
};

const fetchChart = async (type) => {
    if (!fechaInicio.value || !fechaFin.value) {
        chartError.value = "Selecciona un rango de fechas válido.";
        return;
    }

    chartLoading.value = true;
    chartError.value = "";

    try {
        const params = {
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
            chart: type,
        };

        if (type === "bar") {
            params.bar_metric = barMetric.value;
        }

        if (type === "donut") {
            params.donut_metric = donutMetric.value;
        }

        if (type === "line") {
            params.line_metric = lineMetric.value;
        }

        const response = await axios.get(route("reportes.stats"), {
            params,
        });

        if (response.data.indicadores) {
            estadisticas.value = response.data.indicadores;
        }

        if (response.data.chart) {
            chartData.value[type] = response.data.chart;
        } else if (response.data.charts && response.data.charts[type]) {
            chartData.value[type] = response.data.charts[type];
        }

        await nextTick();
        renderChart(type);
    } catch (error) {
        chartError.value =
            error.response?.data?.message ||
            "No se pudieron cargar las estadísticas. Intenta de nuevo.";
    } finally {
        chartLoading.value = false;
    }
};

const fetchStats = async () => {
    if (!fechaInicio.value || !fechaFin.value) {
        chartError.value = "Selecciona un rango de fechas válido.";
        return;
    }

    chartLoading.value = true;
    chartError.value = "";

    try {
        const response = await axios.get(route("reportes.stats"), {
            params: {
                fecha_inicio: fechaInicio.value,
                fecha_fin: fechaFin.value,
                bar_metric: barMetric.value,
                donut_metric: donutMetric.value,
                line_metric: lineMetric.value,
            },
        });

        estadisticas.value = response.data.indicadores;
        chartData.value = response.data.charts;

        await nextTick();
        renderChart("bar");
        renderChart("donut");
        renderChart("line");
    } catch (error) {
        chartError.value =
            error.response?.data?.message ||
            "No se pudieron cargar las estadísticas. Intenta de nuevo.";
    } finally {
        chartLoading.value = false;
    }
};

onMounted(() => {
    fetchStats();
});

watch(barMetric, () => {
    fetchChart("bar");
});

watch(donutMetric, () => {
    fetchChart("donut");
});

watch(lineMetric, () => {
    fetchChart("line");
});

watch([fechaInicio, fechaFin], () => {
    if (fechaInicio.value && fechaFin.value) {
        fetchStats();
    }
});
</script>

<template>
    <AppLayout title="Reportes y Estadísticas">
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
                            Reportes
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Reportes y estadísticas
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Genera reportes operativos con los parámetros que
                            necesites.
                        </p>
                    </div>
                </div>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div
                            v-for="card in statsCards"
                            :key="card.label"
                            class="rounded-[1.75rem] border border-slate-100 bg-slate-50 p-5 shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{ card.label }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ card.description }}
                                    </p>
                                </div>
                                <div
                                    class="inline-flex h-12 w-12 items-center justify-center rounded-2xl"
                                    :class="card.theme"
                                >
                                    <i
                                        :class="['bi', card.icon, 'text-xl']"
                                    ></i>
                                </div>
                            </div>
                            <p class="mt-6 text-3xl font-black text-slate-900">
                                {{ formatCardValue(card) }}
                            </p>
                        </div>
                    </div>
                </section>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <p
                                class="text-sm uppercase tracking-[0.22em] text-slate-400"
                            >
                                Estadísticas dinámicas
                            </p>
                            <h2 class="mt-3 text-2xl font-black text-slate-900">
                                Gráficos interactivos de la tienda
                            </h2>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Cambia cada gráfico con el menú desplegable para
                                ver distintos insights.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                @click="fetchStats"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                                :disabled="chartLoading"
                            >
                                <i class="bi bi-arrow-clockwise"></i>
                                Actualizar
                            </button>
                            <span
                                v-if="chartLoading"
                                class="text-sm text-slate-500"
                            >
                                Actualizando gráficos...
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="chartError"
                        class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
                    >
                        {{ chartError }}
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-2">
                        <div
                            class="rounded-[1.75rem] border border-slate-100 bg-white p-5 shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        Gráfico de Barras
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Ranking y comparaciones directas.
                                    </p>
                                </div>
                                <select
                                    v-model="barMetric"
                                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                >
                                    <option
                                        v-for="option in metricOptions.bar"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="mt-6 h-72">
                                <canvas
                                    ref="barCanvas"
                                    class="h-full w-full"
                                ></canvas>
                            </div>
                        </div>

                        <div
                            class="rounded-[1.75rem] border border-slate-100 bg-white p-5 shadow-sm"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        Gráfico de Dona
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Participación del total.
                                    </p>
                                </div>
                                <select
                                    v-model="donutMetric"
                                    class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                >
                                    <option
                                        v-for="option in metricOptions.donut"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="mt-6 h-72">
                                <canvas
                                    ref="donutCanvas"
                                    class="h-full w-full"
                                ></canvas>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-6 rounded-[1.75rem] border border-slate-100 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    Gráfico de Líneas
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Evolución y tendencias en el tiempo.
                                </p>
                            </div>
                            <select
                                v-model="lineMetric"
                                class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            >
                                <option
                                    v-for="option in metricOptions.line"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div class="mt-6 h-72">
                            <canvas
                                ref="lineCanvas"
                                class="h-full w-full"
                            ></canvas>
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <aside class="lg:col-span-4">
                        <section
                            class="rounded-[2rem] border border-white bg-white/90 p-4 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div
                                class="mb-4 flex items-center gap-2 px-2 text-slate-900"
                            >
                                <i
                                    class="bi bi-journal-text text-emerald-600"
                                ></i>
                                <h2 class="text-lg font-black">
                                    Tipos de reporte
                                </h2>
                            </div>

                            <div class="space-y-2">
                                <button
                                    v-for="reporte in reportes"
                                    :key="reporte.value"
                                    @click="seleccionarReporte(reporte)"
                                    class="flex w-full items-center gap-3 rounded-2xl border px-4 py-4 text-left transition"
                                    :class="
                                        tipoReporte === reporte.value
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-800 shadow-sm'
                                            : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                    "
                                >
                                    <span
                                        class="flex h-11 w-11 items-center justify-center rounded-2xl"
                                        :class="
                                            tipoReporte === reporte.value
                                                ? 'bg-emerald-600 text-white'
                                                : 'bg-slate-100 text-slate-600'
                                        "
                                    >
                                        <i :class="['bi', reporte.icon]"></i>
                                    </span>
                                    <span class="font-semibold">{{
                                        reporte.label
                                    }}</span>
                                </button>
                            </div>
                        </section>
                    </aside>

                    <main class="lg:col-span-8">
                        <section
                            class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)] sm:p-8"
                        >
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="rounded-2xl bg-emerald-50 p-3 text-emerald-600"
                                >
                                    <i
                                        :class="[
                                            'bi',
                                            reporteSeleccionado.icon,
                                            'text-2xl',
                                        ]"
                                    ></i>
                                </div>
                                <div>
                                    <h2
                                        class="text-2xl font-black text-slate-900"
                                    >
                                        {{ reporteSeleccionado.label }}
                                    </h2>
                                    <p class="text-sm text-slate-500">
                                        Configura los parámetros y genera el
                                        reporte.
                                    </p>
                                </div>
                            </div>

                            <form
                                @submit.prevent="generarReporte"
                                class="space-y-5"
                            >
                                <div
                                    class="grid grid-cols-1 gap-4 md:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-semibold text-slate-700"
                                            >Fecha inicio</label
                                        >
                                        <input
                                            v-model="fechaInicio"
                                            type="date"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-semibold text-slate-700"
                                            >Fecha fin</label
                                        >
                                        <input
                                            v-model="fechaFin"
                                            type="date"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                            required
                                        />
                                    </div>
                                </div>

                                <div
                                    v-if="reporteSeleccionado.hasLimite"
                                    class="grid grid-cols-1 gap-4 md:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-semibold text-slate-700"
                                            >Límite de registros</label
                                        >
                                        <select
                                            v-model.number="limite"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                        >
                                            <option :value="10">10</option>
                                            <option :value="20">20</option>
                                            <option :value="50">50</option>
                                            <option :value="100">100</option>
                                        </select>
                                    </div>
                                </div>

                                <div
                                    v-if="reporteSeleccionado.hasStock"
                                    class="grid grid-cols-1 gap-4 md:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            class="mb-2 block text-sm font-semibold text-slate-700"
                                            >Stock mínimo</label
                                        >
                                        <input
                                            v-model.number="stockMinimo"
                                            type="number"
                                            min="0"
                                            max="100"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                        />
                                        <p class="mt-2 text-xs text-slate-500">
                                            Productos con stock menor o igual a
                                            este valor.
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800"
                                >
                                    <i class="bi bi-info-circle me-2"></i>
                                    El rango de fechas no puede ser mayor a 1
                                    año.
                                </div>

                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                                >
                                    <i class="bi bi-file-earmark-bar-graph"></i>
                                    Generar reporte
                                </button>
                            </form>
                        </section>

                        <section
                            class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div
                                class="mb-4 flex items-center gap-2 text-slate-900"
                            >
                                <i class="bi bi-card-text text-emerald-600"></i>
                                <h2 class="text-lg font-black">
                                    Descripción del reporte
                                </h2>
                            </div>

                            <p
                                v-if="tipoReporte === 'ventas-fecha'"
                                class="text-sm leading-6 text-slate-600"
                            >
                                Listado detallado de todas las ventas realizadas
                                en el período seleccionado, incluyendo cliente,
                                vendedor, método de pago y total.
                            </p>
                            <p
                                v-else-if="tipoReporte === 'ventas-metodo'"
                                class="text-sm leading-6 text-slate-600"
                            >
                                Resumen de ventas agrupadas por método de pago,
                                mostrando cantidad y monto total por cada uno.
                            </p>
                            <p
                                v-else-if="tipoReporte === 'creditos-estado'"
                                class="text-sm leading-6 text-slate-600"
                            >
                                Estado de los créditos otorgados: activos,
                                pagados y vencidos, con montos totales y saldos
                                pendientes.
                            </p>
                            <p
                                v-else-if="tipoReporte === 'productos-vendidos'"
                                class="text-sm leading-6 text-slate-600"
                            >
                                Ranking de productos más vendidos en el período,
                                mostrando cantidad vendida, stock actual e
                                ingresos generados.
                            </p>
                            <p
                                v-else-if="tipoReporte === 'clientes-top'"
                                class="text-sm leading-6 text-slate-600"
                            >
                                Listado de clientes con mayor volumen de
                                compras, ordenados por monto total gastado en el
                                período.
                            </p>
                            <p
                                v-else-if="tipoReporte === 'inventario-critico'"
                                class="text-sm leading-6 text-slate-600"
                            >
                                Productos con stock bajo o agotado que requieren
                                reposición urgente.
                            </p>
                        </section>
                    </main>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
