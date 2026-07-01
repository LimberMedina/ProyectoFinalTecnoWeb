<template>
    <div
        class="search-results absolute left-0 top-full z-[1050] mt-2 w-full max-h-[360px] overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-2xl sm:max-h-[440px]"
        role="dialog"
        aria-label="Resultados de búsqueda"
    >
        <div class="divide-y divide-slate-200">
            <div class="bg-slate-50 px-3 py-2">
                <small class="flex items-center text-xs text-slate-500">
                    <i class="bi bi-search mr-1"></i>
                    Se encontraron
                    <strong class="mx-1 text-slate-700">
                        {{
                            productos.length + promociones.length + menus.length
                        }}
                    </strong>
                    resultado(s)
                </small>
            </div>

            <div v-if="menus.length > 0" class="border-b border-slate-200">
                <h6
                    class="mb-0 flex items-center gap-2 px-3 pb-1.5 pt-2.5 text-[11px] font-bold uppercase tracking-wide text-slate-500"
                >
                    <i class="bi bi-compass text-sm"></i>Navegación ({{
                        menus.length
                    }})
                </h6>
                <div class="divide-y divide-slate-100">
                    <button
                        v-for="menu in menus"
                        :key="menu.id"
                        class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none active:bg-slate-100"
                        @click="navigateTo('menus', menu.route)"
                        :title="`Ir a ${menu.label}`"
                    >
                        <div class="shrink-0">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                            >
                                <i :class="['bi', menu.icon, 'text-base']"></i>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h6
                                class="truncate text-sm font-semibold text-slate-800"
                            >
                                {{ menu.label }}
                            </h6>
                            <small
                                v-if="menu.parent"
                                class="mt-0.5 flex items-center text-[11px] text-slate-500"
                            >
                                <i class="bi bi-arrow-return-right mr-1"></i>
                                {{ menu.parent }}
                            </small>
                            <small
                                v-else-if="
                                    menu.submenus && menu.submenus.length > 0
                                "
                                class="mt-0.5 text-[11px] text-slate-500"
                            >
                                {{ menu.submenus.length }}
                                {{
                                    menu.submenus.length === 1
                                        ? "opción"
                                        : "opciones"
                                }}
                            </small>
                        </div>
                        <div class="shrink-0 text-slate-400">
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </button>
                </div>
            </div>

            <div v-if="productos.length > 0" class="border-b border-slate-200">
                <h6
                    class="mb-0 flex items-center gap-2 px-3 pb-1.5 pt-2.5 text-[11px] font-bold uppercase tracking-wide text-slate-500"
                >
                    <i class="bi bi-box-seam text-sm"></i>Productos ({{
                        productos.length
                    }})
                </h6>
                <div class="divide-y divide-slate-100">
                    <button
                        v-for="producto in productos"
                        :key="producto.id"
                        class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none active:bg-slate-100"
                        @click="navigateToProducto(producto)"
                        :title="`Ver detalles de ${producto.nombre}`"
                    >
                        <div class="shrink-0">
                            <div
                                class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-slate-100"
                            >
                                <i
                                    v-if="!getImageUrl(producto)"
                                    class="bi bi-image text-lg text-slate-400"
                                ></i>
                                <img
                                    v-else
                                    :src="getImageUrl(producto)"
                                    :alt="producto.nombre"
                                    class="h-10 w-10 rounded-lg object-cover"
                                    loading="lazy"
                                />
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h6
                                        class="truncate text-sm font-semibold text-slate-800"
                                    >
                                        {{ producto.nombre }}
                                    </h6>
                                    <div
                                        class="mt-0.5 flex flex-wrap items-center gap-x-1 gap-y-0.5 text-[11px] text-slate-500"
                                    >
                                        <i class="bi bi-tag"></i>
                                        {{ producto.categoria }}
                                        <span class="mx-1 text-slate-300"
                                            >•</span
                                        >
                                        <i class="bi bi-upc"></i>
                                        {{ producto.codigo }}
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <strong
                                        class="block text-xs font-bold text-emerald-600"
                                    >
                                        Bs. {{ formatMoney(producto.precio) }}
                                    </strong>
                                    <small
                                        class="mt-0.5 block text-[11px] text-slate-500"
                                        :class="{
                                            'text-rose-600':
                                                producto.stock <= 5,
                                        }"
                                    >
                                        <i class="bi bi-box mr-1"></i>
                                        {{ producto.stock }}
                                        {{
                                            producto.stock === 1
                                                ? "unidad"
                                                : "unidades"
                                        }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div v-if="usuarios.length > 0" class="border-b border-slate-200">
                <h6
                    class="mb-0 flex items-center gap-2 px-3 pb-1.5 pt-2.5 text-[11px] font-bold uppercase tracking-wide text-slate-500"
                >
                    <i class="bi bi-people text-sm"></i>Usuarios ({{
                        usuarios.length
                    }})
                </h6>
                <div class="divide-y divide-slate-100">
                    <button
                        v-for="usuario in usuarios"
                        :key="usuario.id"
                        class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none active:bg-slate-100"
                        @click="navigateToUsuario(usuario)"
                        :title="`Ver detalles de ${usuario.nombre} ${usuario.apellidos || ''}`"
                    >
                        <div class="shrink-0">
                            <div
                                class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-xs font-bold text-slate-500"
                            >
                                <img
                                    v-if="getUserImageUrl(usuario)"
                                    :src="getUserImageUrl(usuario)"
                                    :alt="usuario.nombre"
                                    class="h-10 w-10 rounded-full object-cover"
                                    loading="lazy"
                                />
                                <span v-else>
                                    {{ getUserInitials(usuario) }}
                                </span>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h6
                                        class="truncate text-sm font-semibold text-slate-800"
                                    >
                                        {{ usuario.nombre }}
                                        {{ usuario.apellidos }}
                                    </h6>
                                    <div
                                        class="mt-0.5 flex flex-wrap items-center gap-x-1 gap-y-0.5 text-[11px] text-slate-500"
                                    >
                                        <i class="bi bi-credit-card"></i>
                                        {{ usuario.ci || "Sin CI" }}
                                        <span class="mx-1 text-slate-300"
                                            >•</span
                                        >
                                        <i class="bi bi-envelope"></i>
                                        {{ usuario.email }}
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                        :class="
                                            usuario.estado
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-slate-100 text-slate-600'
                                        "
                                    >
                                        {{ usuario.role || "Usuario" }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div v-if="categorias.length > 0" class="border-b border-slate-200">
                <h6
                    class="mb-0 flex items-center gap-2 px-3 pb-1.5 pt-2.5 text-[11px] font-bold uppercase tracking-wide text-slate-500"
                >
                    <i class="bi bi-grid text-sm"></i>Categorías ({
                    categorias.length })
                </h6>
                <div class="divide-y divide-slate-100">
                    <button
                        v-for="categoria in categorias"
                        :key="categoria.id"
                        class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none active:bg-slate-100"
                        @click="navigateToCategoria(categoria)"
                        :title="`Ver productos de ${categoria.nombre}`"
                    >
                        <div class="shrink-0">
                            <div
                                class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-slate-100"
                            >
                                <img
                                    v-if="getCategoryImageUrl(categoria)"
                                    :src="getCategoryImageUrl(categoria)"
                                    :alt="categoria.nombre"
                                    class="h-10 w-10 object-cover"
                                    loading="lazy"
                                />
                                <i
                                    v-else
                                    class="bi bi-grid text-lg text-slate-400"
                                ></i>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h6
                                        class="truncate text-sm font-semibold text-slate-800"
                                    >
                                        {{ categoria.nombre }}
                                    </h6>
                                    <p
                                        class="mt-0.5 line-clamp-1 text-[11px] text-slate-500"
                                    >
                                        {{
                                            categoria.descripcion ||
                                            "Sin descripción"
                                        }}
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span
                                        class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700"
                                    >
                                        {{ categoria.productos_count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div
                v-if="proveedores.length > 0"
                class="border-b border-slate-200"
            >
                <h6
                    class="mb-0 flex items-center gap-2 px-3 pb-1.5 pt-2.5 text-[11px] font-bold uppercase tracking-wide text-slate-500"
                >
                    <i class="bi bi-truck text-sm"></i>Proveedores ({
                    proveedores.length })
                </h6>
                <div class="divide-y divide-slate-100">
                    <button
                        v-for="proveedor in proveedores"
                        :key="proveedor.id"
                        class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none active:bg-slate-100"
                        @click="navigateToProveedor(proveedor)"
                        :title="`Ver proveedor ${proveedor.nombre}`"
                    >
                        <div class="shrink-0">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                            >
                                <i class="bi bi-truck text-base"></i>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h6
                                        class="truncate text-sm font-semibold text-slate-800"
                                    >
                                        {{ proveedor.nombre }}
                                    </h6>
                                    <div
                                        class="mt-0.5 flex flex-wrap items-center gap-x-1 gap-y-0.5 text-[11px] text-slate-500"
                                    >
                                        <i class="bi bi-credit-card"></i>
                                        {{ proveedor.nit || "Sin NIT" }}
                                        <span class="mx-1 text-slate-300"
                                            >•</span
                                        >
                                        <i class="bi bi-telephone"></i>
                                        {{
                                            proveedor.telefono || "Sin teléfono"
                                        }}
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                        :class="
                                            String(
                                                proveedor.estado || '',
                                            ).toLowerCase() === 'activo'
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-slate-100 text-slate-600'
                                        "
                                    >
                                        {{ proveedor.estado || "Sin estado" }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div v-if="promociones.length > 0">
                <h6
                    class="mb-0 flex items-center gap-2 px-3 pb-1.5 pt-2.5 text-[11px] font-bold uppercase tracking-wide text-slate-500"
                >
                    <i class="bi bi-tags text-sm"></i>Promociones ({{
                        promociones.length
                    }})
                </h6>
                <div class="divide-y divide-slate-100">
                    <button
                        v-for="promocion in promociones"
                        :key="promocion.id"
                        class="flex w-full items-start gap-2.5 px-3 py-2.5 text-left transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none active:bg-slate-100"
                        @click="navigateTo('promociones', promocion.id)"
                        :title="`Ver detalles de ${promocion.nombre}`"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h6
                                        class="flex items-center gap-2 text-sm font-semibold text-slate-800"
                                    >
                                        <i
                                            class="bi bi-gift text-amber-500"
                                        ></i>
                                        {{ promocion.nombre }}
                                    </h6>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ promocion.descripcion }}
                                    </p>
                                    <small
                                        class="mt-2 flex items-center text-xs text-slate-500"
                                    >
                                        <i
                                            class="bi bi-calendar-event mr-1"
                                        ></i>
                                        Válido hasta:
                                        {{ formatDate(promocion.fecha_fin) }}
                                    </small>
                                </div>
                                <div class="shrink-0">
                                    <span
                                        class="inline-flex rounded-full bg-rose-600 px-2.5 py-0.5 text-xs font-semibold text-white"
                                    >
                                        <span
                                            v-if="
                                                promocion.descuento_porcentaje
                                            "
                                        >
                                            -{{
                                                promocion.descuento_porcentaje
                                            }}%
                                        </span>
                                        <span v-else>
                                            -Bs.
                                            {{
                                                formatMoney(
                                                    promocion.descuento_monto,
                                                )
                                            }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div
                class="border-t border-slate-200 bg-slate-50 px-3 py-2 text-center"
            >
                <small
                    class="flex items-center justify-center text-[11px] text-slate-500"
                >
                    <i class="bi bi-info-circle mr-1"></i>
                    Haz clic en un resultado para ver más detalles
                </small>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3";

defineProps({
    productos: Array,
    promociones: Array,
    categorias: Array,
    proveedores: Array,
    menus: Array,
    usuarios: Array,
});

const emit = defineEmits(["close"]);

const normalizeImageUrl = (value) => {
    const url = String(value || "")
        .trim()
        .replace(/\\/g, "/");

    if (!url) {
        return "";
    }

    if (url.startsWith("http://") || url.startsWith("https://")) {
        return url;
    }

    if (url.startsWith("/")) {
        return url;
    }

    if (url.startsWith("storage/")) {
        return `/${url}`;
    }

    return `/storage/${url}`;
};

const getImageUrl = (producto) => {
    const candidate =
        producto?.imagen ??
        producto?.imagen_url ??
        producto?.imagenes?.[0]?.ruta ??
        producto?.imagenes?.[0]?.url ??
        producto?.imagenes?.[0] ??
        "";

    return candidate ? normalizeImageUrl(candidate) : "";
};

const getCategoryImageUrl = (categoria) => {
    const candidate = categoria?.imagen || "";
    return candidate ? normalizeImageUrl(candidate) : "";
};

const getUserImageUrl = (usuario) => {
    const candidate = usuario?.profile_photo_url || "";

    return candidate ? normalizeImageUrl(candidate) : "";
};

const getUserInitials = (usuario) => {
    const first = String(usuario?.nombre || "")
        .trim()
        .charAt(0);
    const last = String(usuario?.apellidos || "")
        .trim()
        .charAt(0);
    return `${first}${last}`.trim() || "U";
};

const getSearchLabel = (producto) => producto?.nombre || producto?.codigo || "";

const navigateToProducto = (producto) => {
    const searchTerm = getSearchLabel(producto);

    emit("close");

    router.visit(
        route("productos.index", {
            search: searchTerm,
            highlight: producto.id,
        }),
        {
            preserveScroll: true,
            replace: true,
        },
    );
};

const navigateToUsuario = (usuario) => {
    emit("close");

    router.visit(
        route("usuarios.index", {
            buscar: `${usuario.nombre} ${usuario.apellidos || ""}`.trim(),
            highlight: usuario.id,
        }),
        {
            preserveScroll: true,
            replace: true,
        },
    );
};

const navigateToCategoria = (categoria) => {
    emit("close");

    router.visit(
        route("categorias.index", {
            search: categoria.nombre,
            highlight: categoria.id,
        }),
        {
            preserveScroll: true,
            replace: true,
        },
    );
};

const navigateToProveedor = (proveedor) => {
    emit("close");

    router.visit(
        route("proveedores.index", {
            search: proveedor.nombre,
            highlight: proveedor.id,
        }),
        {
            preserveScroll: true,
            replace: true,
        },
    );
};

const navigateTo = (type, id) => {
    emit("close");

    if (type === "promociones") {
        router.visit(route("promociones.show", id));
    } else if (type === "menus") {
        router.visit(route(id));
    }
};

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
const formatDate = (date) => new Date(date).toLocaleDateString("es-ES");
</script>
