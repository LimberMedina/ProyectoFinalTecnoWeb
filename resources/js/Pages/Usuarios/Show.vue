<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    usuario: Object,
    estadisticas: Object,
});

const eliminarUsuario = () => {
    if (
        confirm(
            "¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.",
        )
    ) {
        router.delete(route("usuarios.destroy", props.usuario.id));
    }
};

const toggleEstado = () => {
    router.post(
        route("usuarios.toggle-estado", props.usuario.id),
        {},
        { preserveScroll: true },
    );
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(value || 0);
};

const formatDate = (date) => {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("es-BO", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<template>
    <AppLayout :title="`Usuario: ${usuario.name}`">
        <Head :title="`Usuario: ${usuario.name}`" />

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
                        Usuarios
                    </div>
                    <h1
                        class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                    >
                        Detalle de usuario
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        Información completa del usuario.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('usuarios.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </Link>
                    <Link
                        :href="route('usuarios.edit', usuario.id)"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                    >
                        <i class="bi bi-pencil"></i>
                        Editar
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <aside class="lg:col-span-4">
                    <section
                        class="rounded-[2rem] border border-white bg-white/90 p-6 text-center shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <img
                            :src="usuario.profile_photo_url"
                            :alt="usuario.name"
                            class="mx-auto h-28 w-28 rounded-full object-cover ring-4 ring-emerald-100"
                        />
                        <h2 class="mt-4 text-2xl font-black text-slate-900">
                            {{ usuario.name }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ usuario.email }}
                        </p>

                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <span
                                class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                :class="
                                    usuario.estado
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-slate-100 text-slate-600'
                                "
                                >{{
                                    usuario.estado ? "Activo" : "Inactivo"
                                }}</span
                            >
                            <span
                                class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700"
                                >{{ usuario.role?.nombre || "Sin rol" }}</span
                            >
                        </div>

                        <div class="mt-6 grid gap-3">
                            <button
                                @click="toggleEstado"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-100"
                            >
                                <i
                                    :class="
                                        usuario.estado
                                            ? 'bi bi-pause-circle'
                                            : 'bi bi-play-circle'
                                    "
                                ></i>
                                {{ usuario.estado ? "Desactivar" : "Activar" }}
                            </button>
                            <button
                                @click="eliminarUsuario"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
                            >
                                <i class="bi bi-trash"></i>
                                Eliminar usuario
                            </button>
                        </div>
                    </section>

                    <section
                        class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center gap-2 text-slate-900"
                        >
                            <i class="bi bi-telephone text-emerald-600"></i>
                            <h3 class="text-lg font-black">Contacto</h3>
                        </div>
                        <div class="space-y-4 text-sm text-slate-700">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    CI
                                </p>
                                <p class="mt-1 font-medium text-slate-900">
                                    {{ usuario.ci }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Teléfono
                                </p>
                                <p class="mt-1 font-medium text-slate-900">
                                    {{ usuario.telefono }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Email
                                </p>
                                <p class="mt-1 font-medium text-slate-900">
                                    {{ usuario.email }}
                                </p>
                            </div>
                            <div v-if="usuario.fecha_nacimiento">
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Fecha de nacimiento
                                </p>
                                <p class="mt-1 font-medium text-slate-900">
                                    {{ formatDate(usuario.fecha_nacimiento) }}
                                </p>
                            </div>
                        </div>
                    </section>
                </aside>

                <main class="lg:col-span-8">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <section
                            class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="rounded-2xl bg-emerald-50 p-3 text-emerald-600"
                                >
                                    <i class="bi bi-cart-check text-3xl"></i>
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-slate-500"
                                    >
                                        Total ventas
                                    </p>
                                    <p
                                        class="text-3xl font-black text-slate-900"
                                    >
                                        {{ estadisticas.total_ventas }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section
                            class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="rounded-2xl bg-sky-50 p-3 text-sky-600"
                                >
                                    <i class="bi bi-cash-stack text-3xl"></i>
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-slate-500"
                                    >
                                        Total gastado
                                    </p>
                                    <p
                                        class="text-3xl font-black text-slate-900"
                                    >
                                        {{
                                            formatCurrency(
                                                estadisticas.total_gastado,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section
                            class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="rounded-2xl bg-amber-50 p-3 text-amber-600"
                                >
                                    <i class="bi bi-credit-card text-3xl"></i>
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-slate-500"
                                    >
                                        Créditos activos
                                    </p>
                                    <p
                                        class="text-3xl font-black text-slate-900"
                                    >
                                        {{ estadisticas.creditos_activos }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <section
                            class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="rounded-2xl bg-rose-50 p-3 text-rose-600"
                                >
                                    <i
                                        class="bi bi-exclamation-triangle text-3xl"
                                    ></i>
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-slate-500"
                                    >
                                        Deuda total
                                    </p>
                                    <p
                                        class="text-3xl font-black text-slate-900"
                                    >
                                        {{
                                            formatCurrency(
                                                estadisticas.total_credito,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <section
                        class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center gap-2 text-slate-900"
                        >
                            <i class="bi bi-clock-history text-emerald-600"></i>
                            <h3 class="text-lg font-black">Últimas ventas</h3>
                        </div>
                        <div
                            v-if="usuario.ventas && usuario.ventas.length > 0"
                            class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                        >
                            <div class="overflow-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead
                                        class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                    >
                                        <tr>
                                            <th class="px-4 py-4">ID</th>
                                            <th class="px-4 py-4">Fecha</th>
                                            <th class="px-4 py-4">Tipo</th>
                                            <th class="px-4 py-4 text-end">
                                                Total
                                            </th>
                                            <th class="px-4 py-4">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="venta in usuario.ventas"
                                            :key="venta.id"
                                            class="border-t border-slate-100"
                                        >
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                #{{ venta.id }}
                                            </td>
                                            <td
                                                class="px-4 py-4 text-slate-700"
                                            >
                                                {{
                                                    formatDate(venta.created_at)
                                                }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                                    :class="
                                                        venta.tipo_venta ===
                                                        'contado'
                                                            ? 'bg-emerald-100 text-emerald-700'
                                                            : 'bg-amber-100 text-amber-700'
                                                    "
                                                    >{{
                                                        venta.tipo_venta
                                                    }}</span
                                                >
                                            </td>
                                            <td
                                                class="px-4 py-4 text-end text-slate-700"
                                            >
                                                {{
                                                    formatCurrency(venta.total)
                                                }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <span
                                                    class="inline-flex rounded-full px-3 py-1 text-xs font-bold"
                                                    :class="
                                                        venta.estado ===
                                                        'completado'
                                                            ? 'bg-emerald-100 text-emerald-700'
                                                            : 'bg-amber-100 text-amber-700'
                                                    "
                                                    >{{ venta.estado }}</span
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div
                            v-else
                            class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-slate-500"
                        >
                            <i
                                class="bi bi-inbox mb-3 block text-4xl text-slate-300"
                            ></i
                            >No hay ventas registradas.
                        </div>
                    </section>

                    <section
                        class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div
                            class="mb-5 flex items-center gap-2 text-slate-900"
                        >
                            <i class="bi bi-info-circle text-emerald-600"></i>
                            <h3 class="text-lg font-black">
                                Información del sistema
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Fecha de registro
                                </p>
                                <p class="mt-1 font-medium text-slate-900">
                                    {{ formatDate(usuario.created_at) }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400"
                                >
                                    Última actualización
                                </p>
                                <p class="mt-1 font-medium text-slate-900">
                                    {{ formatDate(usuario.updated_at) }}
                                </p>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </AppLayout>
</template>
