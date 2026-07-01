<template>
    <AppLayout title="Registrar Pago">
        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
            <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
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
                            Pagos
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Registrar pago manual
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Busca una cuota, revisa el saldo y registra el pago.
                        </p>
                    </div>
                </div>

                <section
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div v-if="!cuota" class="mb-6">
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700"
                            >Buscar crédito</label
                        >
                        <input
                            v-model="creditoId"
                            type="number"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            placeholder="Ingrese ID del crédito..."
                            @change="buscarCuotas"
                        />
                    </div>

                    <div
                        v-if="
                            cuotasDisponibles.length > 0 && !cuotaSeleccionada
                        "
                        class="mb-6 overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                    >
                        <div
                            class="border-b border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700"
                        >
                            Seleccionar cuota
                        </div>
                        <div class="divide-y divide-slate-100">
                            <button
                                v-for="c in cuotasDisponibles"
                                :key="c.id"
                                class="flex w-full flex-col gap-4 px-4 py-4 text-left transition hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between"
                                @click="seleccionarCuota(c)"
                            >
                                <div>
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <strong class="text-slate-900"
                                            >Cuota {{ c.numero_cuota }}</strong
                                        >
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                            :class="
                                                c.estado === 'vencida'
                                                    ? 'bg-rose-100 text-rose-700'
                                                    : 'bg-amber-100 text-amber-700'
                                            "
                                            >{{ c.estado }}</span
                                        >
                                    </div>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Vence:
                                        {{ formatDate(c.fecha_vencimiento) }}
                                    </p>
                                </div>
                                <div
                                    class="text-sm text-slate-700 sm:text-right"
                                >
                                    <p>
                                        Saldo: Bs.
                                        {{ formatMoney(c.monto_pendiente) }}
                                    </p>
                                    <p
                                        v-if="c.mora_calculada > 0"
                                        class="text-rose-600"
                                    >
                                        Mora: Bs.
                                        {{ formatMoney(c.mora_calculada) }}
                                    </p>
                                    <p class="font-black text-slate-900">
                                        Total: Bs.
                                        {{ formatMoney(c.total_pagar) }}
                                    </p>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="cuotaSeleccionada || cuota"
                        class="grid grid-cols-1 gap-6 lg:grid-cols-2"
                    >
                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-5"
                        >
                            <h2 class="text-lg font-black text-slate-900">
                                Resumen de cuota
                            </h2>
                            <div class="mt-4 space-y-3 text-sm text-slate-700">
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Cuota:</span
                                    >
                                    {{
                                        (cuotaSeleccionada || cuota)
                                            .numero_cuota
                                    }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Cliente:</span
                                    >
                                    {{
                                        (cuotaSeleccionada || cuota).credito
                                            ?.venta?.cliente?.name
                                    }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Saldo pendiente:</span
                                    >
                                    Bs.
                                    {{
                                        formatMoney(
                                            (cuotaSeleccionada || cuota)
                                                .monto_pendiente,
                                        )
                                    }}
                                </p>
                                <p v-if="moraCalculada > 0">
                                    <span class="font-semibold text-rose-700"
                                        >Mora:</span
                                    >
                                    Bs. {{ formatMoney(moraCalculada) }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-900"
                                        >Total a pagar:</span
                                    >
                                    Bs. {{ formatMoney(totalDeuda) }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-[1.5rem] border border-slate-100 bg-white p-5"
                        >
                            <h2 class="text-lg font-black text-slate-900">
                                Formulario de pago
                            </h2>
                            <form
                                @submit.prevent="registrarPago"
                                class="mt-4 space-y-4"
                            >
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                        >Monto del pago *</label
                                    >
                                    <input
                                        v-model="form.monto"
                                        type="number"
                                        step="0.01"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                        :max="totalDeuda"
                                        required
                                    />
                                    <p class="mt-2 text-xs text-slate-500">
                                        Máximo: Bs.
                                        {{ formatMoney(totalDeuda) }}
                                    </p>
                                    <p
                                        v-if="errors.monto"
                                        class="mt-2 text-sm text-rose-600"
                                    >
                                        {{ errors.monto }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                        >Método de pago *</label
                                    >
                                    <select
                                        v-model="form.metodo_pago_id"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                        required
                                    >
                                        <option value="">Seleccione...</option>
                                        <option
                                            v-for="metodo in metodosPago"
                                            :key="metodo.id"
                                            :value="metodo.id"
                                        >
                                            {{ metodo.nombre }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="errors.metodo_pago_id"
                                        class="mt-2 text-sm text-rose-600"
                                    >
                                        {{ errors.metodo_pago_id }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                        >Fecha de pago</label
                                    >
                                    <input
                                        v-model="form.fecha"
                                        type="date"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                        >Número de comprobante</label
                                    >
                                    <input
                                        v-model="form.comprobante"
                                        type="text"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                        >Notas</label
                                    >
                                    <textarea
                                        v-model="form.notas"
                                        rows="3"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                    ></textarea>
                                </div>

                                <div
                                    class="flex flex-wrap justify-between gap-3 pt-2"
                                >
                                    <Link
                                        :href="route('pagos.index')"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                        >Cancelar</Link
                                    >
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70"
                                        :disabled="processing"
                                    >
                                        <span
                                            v-if="processing"
                                            class="inline-flex items-center gap-2"
                                            ><span
                                                class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                                            ></span
                                            >Registrando...</span
                                        >
                                        <span v-else>Registrar pago</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <QRModal
            v-if="showQRModal"
            :cuota="cuotaParaPago"
            @close="showQRModal = false"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import QRModal from "@/Components/QRModal.vue";
import axios from "axios";

const props = defineProps({
    cuota: Object,
    metodosPago: Array,
});

const creditoId = ref("");
const cuotasDisponibles = ref([]);
const cuotaSeleccionada = ref(null);
const processing = ref(false);
const errors = ref({});

const form = ref({
    cuota_id: props.cuota?.id || "",
    monto: "",
    metodo_pago_id: "",
    fecha: new Date().toISOString().split("T")[0],
    comprobante: "",
    notas: "",
});

const showQRModal = ref(false);
const cuotaParaPago = ref(null);

const moraCalculada = computed(() => {
    const c = cuotaSeleccionada.value || props.cuota;
    return c?.mora_calculada || c?.mora || 0;
});

const totalDeuda = computed(() => {
    const c = cuotaSeleccionada.value || props.cuota;
    return (c?.monto_pendiente || 0) + moraCalculada.value;
});

const buscarCuotas = async () => {
    if (!creditoId.value) return;

    try {
        const response = await axios.get(route("pagos.buscar-cuotas"), {
            params: { credito_id: creditoId.value },
        });
        cuotasDisponibles.value = response.data;
    } catch (error) {
        console.error("Error buscando cuotas:", error);
    }
};

const seleccionarCuota = (cuota) => {
    cuotaSeleccionada.value = cuota;
    form.value.cuota_id = cuota.id;
    form.value.monto = cuota.total_pagar;
};

const registrarPago = () => {
    processing.value = true;
    errors.value = {};

    router.post(route("pagos.store"), form.value, {
        onError: (err) => {
            errors.value = err;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
const formatDate = (date) => new Date(date).toLocaleDateString("es-ES");
</script>
