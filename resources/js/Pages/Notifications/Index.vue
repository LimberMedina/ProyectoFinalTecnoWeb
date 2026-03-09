<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    notifications: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleString("es-ES", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getBadgeIcon = (type) => {
    const icons = {
        pedido_enviado: "bi-truck",
        pedido_entregado: "bi-check-circle",
        pago_confirmado: "bi-credit-card",
        default: "bi-bell",
    };
    return icons[type] || icons.default;
};
</script>

<template>
    <AppLayout title="Notificaciones">
        <div class="container py-4">
            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2 class="mb-0">Mis Notificaciones</h2>
                            <p class="text-muted">
                                Mantente informado sobre tus pedidos
                            </p>
                        </div>
                        <Link
                            v-if="notifications.data.some((n) => !n.read)"
                            :href="route('notifications.mark-all-read')"
                            method="post"
                            as="button"
                            class="btn btn-outline-primary"
                        >
                            <i class="bi bi-check-all me-2"></i>
                            Marcar todas como leídas
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Lista de notificaciones -->
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card">
                        <div class="card-body p-0">
                            <div
                                v-if="notifications.data.length === 0"
                                class="text-center py-5"
                            >
                                <i
                                    class="bi bi-bell-slash text-muted"
                                    style="font-size: 4rem"
                                ></i>
                                <p class="text-muted mt-3 mb-0">
                                    No tienes notificaciones
                                </p>
                            </div>

                            <div
                                v-else
                                v-for="notification in notifications.data"
                                :key="notification.id"
                                class="border-bottom p-3"
                                :class="{ 'bg-light': !notification.read }"
                            >
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i
                                            :class="[
                                                'bi',
                                                getBadgeIcon(notification.type),
                                                'text-primary',
                                            ]"
                                            style="font-size: 2rem"
                                        ></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div
                                            class="d-flex justify-content-between align-items-start mb-2"
                                        >
                                            <h5 class="mb-1">
                                                {{ notification.title }}
                                                <span
                                                    v-if="!notification.read"
                                                    class="badge bg-primary ms-2"
                                                    >Nueva</span
                                                >
                                            </h5>
                                            <small class="text-muted">{{
                                                formatDate(
                                                    notification.created_at,
                                                )
                                            }}</small>
                                        </div>
                                        <p class="mb-2">
                                            {{ notification.message }}
                                        </p>

                                        <div class="d-flex gap-2">
                                            <Link
                                                v-if="
                                                    notification.data &&
                                                    notification.data.venta_id
                                                "
                                                :href="
                                                    route(
                                                        'mis-pedidos.show',
                                                        notification.data
                                                            .venta_id,
                                                    )
                                                "
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                <i class="bi bi-eye me-1"></i>
                                                Ver pedido
                                            </Link>

                                            <Link
                                                v-if="!notification.read"
                                                :href="
                                                    route(
                                                        'notifications.mark-read',
                                                        notification.id,
                                                    )
                                                "
                                                method="post"
                                                as="button"
                                                class="btn btn-sm btn-outline-secondary"
                                            >
                                                <i class="bi bi-check me-1"></i>
                                                Marcar como leída
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="
                            notifications.links &&
                            notifications.links.length > 3
                        "
                        class="mt-4"
                    >
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li
                                    v-for="(link, index) in notifications.links"
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
            </div>
        </div>
    </AppLayout>
</template>
