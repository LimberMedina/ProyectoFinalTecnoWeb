<script setup>
import { ref, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    productos: Object,
    categorias: Array,
    filters: Object,
    rol: String,
});

const search = ref(props.filters.search || "");
const categoriaSeleccionada = ref(props.filters.categoria || "");
const showDeleteModal = ref(false);
const productToDelete = ref(null);

// Modal para cantidad (solo clientes)
const showCantidadModal = ref(false);
const productoSeleccionado = ref(null);
const cantidadAgregar = ref(1);

const isCliente = computed(() => (props.rol || "").toLowerCase() === "cliente");

// Búsqueda y filtrado reactivo
const performSearch = () => {
    router.get(
        route("productos.index"),
        {
            search: search.value,
            categoria: categoriaSeleccionada.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Filtrar por categoría
const filtrarPorCategoria = (categoriaId) => {
    categoriaSeleccionada.value = categoriaId;
    performSearch();
};

// Limpiar filtros
const limpiarFiltros = () => {
    search.value = "";
    categoriaSeleccionada.value = "";
    performSearch();
};

// Mostrar modal de confirmaciÃ³n
const confirmDelete = (producto) => {
    productToDelete.value = producto;
    showDeleteModal.value = true;
};

// Eliminar producto
const deleteProducto = () => {
    if (productToDelete.value) {
        router.delete(route("productos.destroy", productToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                productToDelete.value = null;
            },
        });
    }
};

// Cancelar eliminaciÃ³n
const cancelDelete = () => {
    showDeleteModal.value = false;
    productToDelete.value = null;
};

// Formatear precio
const formatPrice = (price) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(price);
};

// URL de imagen (usar primera de product_images)
const getImageUrl = (producto) => {
    if (producto.imagenes && producto.imagenes.length > 0) {
        return `/storage/${producto.imagenes[0].url}`;
    }
    return "/images/no-image.png";
};

// Agregar al carrito (solo clientes)
const abrirModalCantidad = (producto) => {
    productoSeleccionado.value = producto;
    cantidadAgregar.value = 1;
    showCantidadModal.value = true;
};

const agregarAlCarrito = () => {
    if (!productoSeleccionado.value) return;
    router.post(
        route("carritos.store"),
        {
            producto_id: productoSeleccionado.value.id,
            cantidad: cantidadAgregar.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showCantidadModal.value = false;
                productoSeleccionado.value = null;
            },
        },
    );
};
</script>

<template>
    <Head title="Productos" />

    <AppLayout
        :title="isCliente ? 'Catálogo de Productos' : 'Gestión de Productos'"
    >
        <FlashNotification />
        <div class="container py-4">
            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h2 class="mb-2">
                        <i class="bi bi-box-seam me-2"></i>
                        {{
                            isCliente
                                ? "Catálogo de Productos"
                                : "Gestión de Productos"
                        }}
                    </h2>
                    <p class="text-muted mb-0">
                        {{
                            isCliente
                                ? "Explora nuestro catálogo y encuentra lo que necesitas"
                                : "Administra tu catálogo de productos"
                        }}
                    </p>
                </div>
                <div class="col-lg-4 text-end">
                    <Link
                        v-if="$page.props.auth.permissions?.productos?.create"
                        :href="route('productos.create')"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Producto
                    </Link>
                </div>
            </div>

            <!-- Barra de búsqueda y filtros -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Búsqueda -->
                        <div class="col-md-5">
                            <label class="form-label fw-bold small">
                                <i class="bi bi-search me-1"></i>Buscar
                            </label>
                            <div class="input-group">
                                <input
                                    v-model="search"
                                    type="text"
                                    class="form-control"
                                    placeholder="Buscar por nombre o código..."
                                    @keyup.enter="performSearch"
                                />
                                <button
                                    class="btn btn-outline-primary"
                                    type="button"
                                    @click="performSearch"
                                >
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Filtro por categoría -->
                        <div class="col-md-5">
                            <label class="form-label fw-bold small">
                                <i class="bi bi-funnel me-1"></i>Categoría
                            </label>
                            <select
                                v-model="categoriaSeleccionada"
                                class="form-select"
                                @change="performSearch"
                            >
                                <option value="">Todas las categorías</option>
                                <option
                                    v-for="categoria in categorias"
                                    :key="categoria.id"
                                    :value="categoria.id"
                                >
                                    {{ categoria.nombre }}
                                </option>
                            </select>
                        </div>

                        <!-- Botón limpiar -->
                        <div class="col-md-2 d-flex align-items-end">
                            <button
                                class="btn btn-outline-secondary w-100"
                                type="button"
                                @click="limpiarFiltros"
                            >
                                <i class="bi bi-x-circle me-1"></i>Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista de tarjetas para clientes, tabla para administradores -->
            <div v-if="isCliente">
                <!-- Vista de tarjetas moderna -->
                <div
                    v-if="productos.data.length === 0"
                    class="text-center py-5"
                >
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No se encontraron productos</h4>
                    <p class="text-muted">
                        Intenta con otros filtros de bÃºsqueda
                    </p>
                </div>

                <div v-else class="row g-4">
                    <div
                        v-for="producto in productos.data"
                        :key="producto.id"
                        class="col-sm-6 col-md-4 col-lg-3"
                    >
                        <div class="card h-100 shadow-sm product-card">
                            <!-- Imagen del producto -->
                            <div
                                class="position-relative overflow-hidden"
                                style="height: 200px"
                            >
                                <img
                                    :src="getImageUrl(producto)"
                                    :alt="producto.nombre"
                                    class="card-img-top h-100 w-100"
                                    style="object-fit: cover"
                                />
                                <!-- Badge de stock -->
                                <div class="position-absolute top-0 end-0 m-2">
                                    <span
                                        class="badge"
                                        :class="{
                                            'bg-success':
                                                producto.stock_actual >
                                                producto.stock_minimo,
                                            'bg-warning text-dark':
                                                producto.stock_actual > 0 &&
                                                producto.stock_actual <=
                                                    producto.stock_minimo,
                                            'bg-danger':
                                                producto.stock_actual === 0,
                                        }"
                                    >
                                        {{
                                            producto.stock_actual === 0
                                                ? "Sin stock"
                                                : `${producto.stock_actual} disponibles`
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body d-flex flex-column">
                                <!-- Categoría -->
                                <div class="mb-2">
                                    <span class="badge bg-secondary">
                                        {{
                                            producto.categoria?.nombre ||
                                            "Sin categoría"
                                        }}
                                    </span>
                                </div>

                                <!-- Nombre -->
                                <h6 class="card-title fw-bold mb-2">
                                    {{ producto.nombre }}
                                </h6>

                                <!-- Precio -->
                                <div class="mb-3">
                                    <h5 class="text-primary mb-0">
                                        {{ formatPrice(producto.precio_venta) }}
                                    </h5>
                                </div>

                                <!-- Botones -->
                                <div class="mt-auto d-grid gap-2">
                                    <Link
                                        :href="
                                            route('productos.show', producto.id)
                                        "
                                        class="btn btn-outline-primary btn-sm"
                                    >
                                        <i class="bi bi-eye me-1"></i>Ver
                                        detalles
                                    </Link>
                                    <button
                                        @click="abrirModalCantidad(producto)"
                                        class="btn btn-primary btn-sm"
                                        :disabled="producto.stock_actual === 0"
                                    >
                                        <i class="bi bi-cart-plus me-1"></i>
                                        {{
                                            producto.stock_actual === 0
                                                ? "Sin stock"
                                                : "Agregar al carrito"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista de tabla para administradores -->
            <div v-else class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 80px">Imagen</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-center">Stock</th>
                                    <th
                                        class="text-center"
                                        style="width: 180px"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="productos.data.length === 0">
                                    <td
                                        colspan="7"
                                        class="text-center text-muted py-4"
                                    >
                                        No se encontraron productos
                                    </td>
                                </tr>
                                <tr
                                    v-for="producto in productos.data"
                                    :key="producto.id"
                                >
                                    <td>
                                        <img
                                            :src="getImageUrl(producto)"
                                            :alt="producto.nombre"
                                            class="img-thumbnail"
                                            style="
                                                width: 60px;
                                                height: 60px;
                                                object-fit: cover;
                                            "
                                            loading="lazy"
                                        />
                                    </td>
                                    <td>
                                        <code>{{ producto.codigo }}</code>
                                    </td>
                                    <td>
                                        <strong>{{ producto.nombre }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{
                                                producto.categoria?.nombre ||
                                                "Sin categoría"
                                            }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{
                                            formatPrice(producto.precio_venta)
                                        }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge"
                                            :class="
                                                producto.stock_actual >
                                                producto.stock_minimo
                                                    ? 'bg-success'
                                                    : producto.stock_actual > 0
                                                      ? 'bg-warning'
                                                      : 'bg-danger'
                                            "
                                        >
                                            {{ producto.stock_actual }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div
                                            class="btn-group btn-group-sm"
                                            role="group"
                                            aria-label="Acciones del producto"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'productos.show',
                                                        producto.id,
                                                    )
                                                "
                                                class="btn btn-outline-info"
                                                title="Ver detalles del producto"
                                            >
                                                <i class="bi bi-eye-fill"></i>
                                            </Link>
                                            <Link
                                                v-if="
                                                    $page.props.auth.permissions
                                                        ?.productos?.update
                                                "
                                                :href="
                                                    route(
                                                        'productos.edit',
                                                        producto.id,
                                                    )
                                                "
                                                class="btn btn-outline-warning"
                                                title="Editar producto"
                                            >
                                                <i
                                                    class="bi bi-pencil-fill"
                                                ></i>
                                            </Link>
                                            <button
                                                v-if="
                                                    $page.props.auth.permissions
                                                        ?.productos?.delete
                                                "
                                                @click="confirmDelete(producto)"
                                                class="btn btn-outline-danger"
                                                title="Eliminar producto"
                                            >
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="productos.links.length > 3" class="mt-4">
                <nav>
                    <ul class="pagination justify-content-center">
                        <li
                            v-for="(link, index) in productos.links"
                            :key="index"
                            class="page-item"
                            :class="{
                                active: link.active,
                                disabled: !link.url,
                            }"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="page-link"
                                v-html="link.label"
                                preserve-state
                            />
                            <span
                                v-else
                                class="page-link"
                                v-html="link.label"
                            ></span>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <ConfirmationModal
            :show="showDeleteModal"
            @close="cancelDelete"
            max-width="sm"
        >
            <template #title> Confirmar Eliminación </template>

            <template #content>
                <p class="mb-0">
                    ¿Está seguro de que desea eliminar el producto
                    <strong>{{ productToDelete?.nombre }}</strong
                    >?
                </p>
                <p class="text-muted small mb-0 mt-2">
                    Esta acción no se puede deshacer.
                </p>
            </template>

            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="cancelDelete"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-danger"
                    @click="deleteProducto"
                >
                    <i class="bi bi-trash-fill me-2"></i>
                    Eliminar
                </button>
            </template>
        </ConfirmationModal>

        <!-- Modal para Agregar al Carrito (solo clientes) -->
        <ConfirmationModal
            :show="showCantidadModal"
            @close="showCantidadModal = false"
            max-width="sm"
        >
            <template #title>
                <i class="bi bi-cart-plus me-2"></i>Agregar al Carrito
            </template>
            <template #content>
                <div v-if="productoSeleccionado">
                    <p class="mb-3">
                        ¿Cuántas unidades de
                        <strong>{{ productoSeleccionado.nombre }}</strong>
                        deseas agregar?
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cantidad</label>
                        <input
                            type="number"
                            v-model.number="cantidadAgregar"
                            min="1"
                            :max="productoSeleccionado.stock_actual"
                            class="form-control form-control-lg"
                        />
                        <small class="text-muted">
                            <i class="bi bi-box-seam me-1"></i>
                            Stock disponible:
                            {{ productoSeleccionado.stock_actual }} unidades
                        </small>
                    </div>
                    <div class="alert alert-info mb-0">
                        <strong>Precio unitario:</strong>
                        {{ formatPrice(productoSeleccionado.precio_venta) }}
                        <br />
                        <strong>Total:</strong>
                        {{
                            formatPrice(
                                productoSeleccionado.precio_venta *
                                    cantidadAgregar,
                            )
                        }}
                    </div>
                </div>
            </template>
            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="showCantidadModal = false"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-primary"
                    @click="agregarAlCarrito"
                    :disabled="
                        cantidadAgregar < 1 ||
                        cantidadAgregar >
                            (productoSeleccionado?.stock_actual || 1)
                    "
                >
                    <i class="bi bi-cart-plus me-2"></i>Agregar al carrito
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>

<style scoped>
.product-card {
    transition:
        transform 0.2s,
        box-shadow 0.2s;
    border: none;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.card-img-top {
    transition: transform 0.3s;
}

.product-card:hover .card-img-top {
    transform: scale(1.05);
}
</style>
