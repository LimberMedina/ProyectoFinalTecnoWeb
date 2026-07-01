<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    usuarios: Object,
    roles: Array,
    filters: Object,
    estadisticas: Object,
});

const highlightedUserId = computed(() =>
    String(props.filters?.highlight || ""),
);

const filtros = ref({
    buscar: props.filters.buscar || "",
    role_id: props.filters.role_id || "",
    estado: props.filters.estado || "",
});

const aplicarFiltros = () => {
    router.get(route("usuarios.index"), filtros.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    filtros.value = {
        buscar: "",
        role_id: "",
        estado: "",
    };
    aplicarFiltros();
};

const eliminarUsuario = (id) => {
    if (
        confirm(
            "¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.",
        )
    ) {
        router.delete(route("usuarios.destroy", id), {
            preserveScroll: true,
        });
    }
};

const toggleEstado = (id) => {
    router.post(
        route("usuarios.toggle-estado", id),
        {},
        {
            preserveScroll: true,
        },
    );
};

const getBadgeClass = (estado) => {
    return estado
        ? "bg-emerald-100 text-emerald-700"
        : "bg-slate-100 text-slate-600";
};

const getEstadoTexto = (estado) => {
    return estado ? "Activo" : "Inactivo";
};

const getUserDomId = (userId) => `usuario-${userId}`;
const isHighlightedUser = (userId) =>
    highlightedUserId.value && String(userId) === highlightedUserId.value;

const scrollToHighlightedUser = async () => {
    if (!highlightedUserId.value) {
        return;
    }

    await nextTick();
    const target = document.getElementById(
        getUserDomId(highlightedUserId.value),
    );

    if (target) {
        target.scrollIntoView({ behavior: "smooth", block: "center" });
    }
};

onMounted(scrollToHighlightedUser);

watch(highlightedUserId, scrollToHighlightedUser);
</script>

<template>
    <AppLayout title="Gestión de Usuarios">
        <Head title="Usuarios" />

        <div
            class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_42%),linear-gradient(180deg,#f8fafc_0%,#ffffff_100%)]"
        >
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <FlashNotification />

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
                            Gestión de usuarios
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Administra los usuarios del sistema.
                        </p>
                    </div>

                    <Link
                        :href="route('usuarios.create')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                    >
                        <i class="bi bi-plus-circle"></i>
                        Nuevo usuario
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-emerald-50 p-3 text-emerald-600"
                            >
                                <i class="bi bi-people text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Total usuarios
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    {{ estadisticas.total }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div class="rounded-2xl bg-sky-50 p-3 text-sky-600">
                                <i class="bi bi-person-check text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Activos
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    {{ estadisticas.activos }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-[1.5rem] border border-white bg-white/90 p-5 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-slate-100 p-3 text-slate-600"
                            >
                                <i class="bi bi-person-x text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Inactivos
                                </p>
                                <p class="text-3xl font-black text-slate-900">
                                    {{ estadisticas.inactivos }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <form
                        @submit.prevent="aplicarFiltros"
                        class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end"
                    >
                        <div class="lg:col-span-5">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Buscar</label
                            >
                            <input
                                v-model="filtros.buscar"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                placeholder="Nombre, CI, email..."
                            />
                        </div>
                        <div class="lg:col-span-3">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Rol</label
                            >
                            <select
                                v-model="filtros.role_id"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            >
                                <option value="">Todos los roles</option>
                                <option
                                    v-for="rol in roles"
                                    :key="rol.id"
                                    :value="rol.id"
                                >
                                    {{ rol.nombre }}
                                </option>
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Estado</label
                            >
                            <select
                                v-model="filtros.estado"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            >
                                <option value="">Todos</option>
                                <option value="activo">Activos</option>
                                <option value="inactivo">Inactivos</option>
                            </select>
                        </div>
                        <div class="flex gap-3 lg:col-span-2">
                            <button
                                type="submit"
                                class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                            >
                                <i class="bi bi-search"></i>
                            </button>
                            <button
                                type="button"
                                @click="limpiarFiltros"
                                class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </form>
                </section>

                <section
                    class="mt-6 rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white"
                    >
                        <div class="overflow-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead
                                    class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-600"
                                >
                                    <tr>
                                        <th class="px-4 py-4">ID</th>
                                        <th class="px-4 py-4">
                                            Nombre completo
                                        </th>
                                        <th class="px-4 py-4">CI</th>
                                        <th class="px-4 py-4">Email</th>
                                        <th class="px-4 py-4">Teléfono</th>
                                        <th class="px-4 py-4">Rol</th>
                                        <th class="px-4 py-4">Estado</th>
                                        <th class="px-4 py-4">
                                            Fecha registro
                                        </th>
                                        <th class="px-4 py-4 text-end">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="usuarios.data.length === 0">
                                        <td
                                            colspan="9"
                                            class="px-4 py-10 text-center text-slate-500"
                                        >
                                            <i
                                                class="bi bi-inbox mb-3 block text-4xl text-slate-300"
                                            ></i>
                                            No se encontraron usuarios.
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="usuario in usuarios.data"
                                        :key="usuario.id"
                                        :id="getUserDomId(usuario.id)"
                                        class="border-t border-slate-100 transition"
                                        :class="
                                            isHighlightedUser(usuario.id)
                                                ? 'bg-emerald-50 ring-2 ring-inset ring-emerald-300'
                                                : 'hover:bg-slate-50'
                                        "
                                    >
                                        <td class="px-4 py-4 text-slate-700">
                                            {{ usuario.id }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <img
                                                    :src="
                                                        usuario.profile_photo_url
                                                    "
                                                    :alt="usuario.name"
                                                    class="h-10 w-10 rounded-full object-cover ring-2 ring-emerald-100"
                                                />
                                                <span
                                                    class="font-semibold text-slate-900"
                                                    >{{ usuario.name }}</span
                                                >
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-slate-700">
                                            {{ usuario.ci }}
                                        </td>
                                        <td class="px-4 py-4 text-slate-700">
                                            {{ usuario.email }}
                                        </td>
                                        <td class="px-4 py-4 text-slate-700">
                                            {{ usuario.telefono }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <span
                                                class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700"
                                                >{{
                                                    usuario.role?.nombre ||
                                                    "Sin rol"
                                                }}</span
                                            >
                                        </td>
                                        <td class="px-4 py-4">
                                            <span
                                                :class="[
                                                    'inline-flex rounded-full px-3 py-1 text-xs font-bold',
                                                    getBadgeClass(
                                                        usuario.estado,
                                                    ),
                                                ]"
                                                >{{
                                                    getEstadoTexto(
                                                        usuario.estado,
                                                    )
                                                }}</span
                                            >
                                        </td>
                                        <td class="px-4 py-4 text-slate-700">
                                            {{
                                                new Date(
                                                    usuario.created_at,
                                                ).toLocaleDateString("es-BO")
                                            }}
                                        </td>
                                        <td class="px-4 py-4 text-end">
                                            <div
                                                class="inline-flex flex-wrap justify-end gap-2"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'usuarios.show',
                                                            usuario.id,
                                                        )
                                                    "
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:bg-sky-100"
                                                    title="Ver detalles"
                                                    ><i class="bi bi-eye"></i
                                                ></Link>
                                                <Link
                                                    :href="
                                                        route(
                                                            'usuarios.edit',
                                                            usuario.id,
                                                        )
                                                    "
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                                                    title="Editar"
                                                    ><i class="bi bi-pencil"></i
                                                ></Link>
                                                <button
                                                    @click="
                                                        toggleEstado(usuario.id)
                                                    "
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-amber-200 bg-amber-50 text-amber-700 transition hover:bg-amber-100"
                                                    :title="
                                                        usuario.estado
                                                            ? 'Desactivar'
                                                            : 'Activar'
                                                    "
                                                >
                                                    <i
                                                        :class="
                                                            usuario.estado
                                                                ? 'bi bi-pause-circle'
                                                                : 'bi bi-play-circle'
                                                        "
                                                    ></i>
                                                </button>
                                                <button
                                                    @click="
                                                        eliminarUsuario(
                                                            usuario.id,
                                                        )
                                                    "
                                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-700 transition hover:bg-rose-100"
                                                    title="Eliminar"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div
                            v-if="usuarios.data.length > 0"
                            class="flex flex-col gap-4 border-t border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="text-sm text-slate-600">
                                Mostrando {{ usuarios.from }} a
                                {{ usuarios.to }} de
                                {{ usuarios.total }} usuarios
                            </div>
                            <nav>
                                <ul class="flex flex-wrap items-center gap-2">
                                    <li
                                        v-for="link in usuarios.links"
                                        :key="link.label"
                                    >
                                        <Link
                                            v-if="link.url"
                                            :href="link.url"
                                            preserve-state
                                            v-html="link.label"
                                            class="inline-flex min-w-10 items-center justify-center rounded-xl border px-3 py-2 text-sm font-semibold transition"
                                            :class="
                                                link.active
                                                    ? 'border-emerald-500 bg-emerald-600 text-white'
                                                    : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                                            "
                                        />
                                        <span
                                            v-else
                                            v-html="link.label"
                                            class="inline-flex min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-400"
                                        ></span>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
