<script setup>
import { Link } from "@inertiajs/vue3";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";

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

const hasUnreadNotifications = () =>
    (props.notifications?.data || []).some(
        (notification) => !notification.read,
    );
</script>

<template>
    <PublicStoreLayout>
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
                            Notificaciones
                        </div>

                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Mis notificaciones
                        </h1>

                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Mantente informado sobre el estado de tus pedidos y
                            movimientos importantes.
                        </p>
                    </div>

                    <Link
                        v-if="hasUnreadNotifications()"
                        :href="route('notifications.mark-all-read')"
                        method="post"
                        as="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50"
                    >
                        <i class="bi bi-check-all"></i>
                        Marcar todas como leídas
                    </Link>
                </div>

                <div
                    class="mx-auto max-w-4xl overflow-hidden rounded-[2rem] border border-white bg-white/90 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)]"
                >
                    <div
                        v-if="notifications.data.length === 0"
                        class="flex flex-col items-center justify-center px-6 py-16 text-center"
                    >
                        <div
                            class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400"
                        >
                            <i class="bi bi-bell-slash text-2xl"></i>
                        </div>
                        <p class="text-lg font-semibold text-slate-900">
                            No tienes notificaciones
                        </p>
                        <p class="mt-2 text-sm text-slate-500">
                            Cuando tu pedido cambie de estado, aparecerá aquí.
                        </p>
                    </div>

                    <div v-else class="divide-y divide-slate-100">
                        <article
                            v-for="notification in notifications.data"
                            :key="notification.id"
                            class="p-5 transition"
                            :class="
                                notification.read
                                    ? 'bg-white'
                                    : 'bg-emerald-50/50'
                            "
                        >
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-600 shadow-sm ring-1 ring-slate-100"
                                >
                                    <i
                                        :class="[
                                            'bi',
                                            getBadgeIcon(notification.type),
                                            'text-xl',
                                        ]"
                                    ></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
                                    >
                                        <div>
                                            <div
                                                class="flex flex-wrap items-center gap-2"
                                            >
                                                <h2
                                                    class="text-base font-bold text-slate-900"
                                                >
                                                    {{ notification.title }}
                                                </h2>
                                                <span
                                                    v-if="!notification.read"
                                                    class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-rose-700"
                                                >
                                                    Nueva
                                                </span>
                                            </div>
                                            <p
                                                class="mt-1 text-xs font-medium uppercase tracking-[0.18em] text-slate-400"
                                            >
                                                {{
                                                    formatDate(
                                                        notification.created_at,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <p
                                        class="mt-3 text-sm leading-6 text-slate-600"
                                    >
                                        {{ notification.message }}
                                    </p>

                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <Link
                                            v-if="
                                                notification.data &&
                                                notification.data.venta_id
                                            "
                                            :href="
                                                route(
                                                    'mis-pedidos.show',
                                                    notification.data.venta_id,
                                                )
                                            "
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-200 hover:text-emerald-700 hover:bg-emerald-50"
                                        >
                                            <i class="bi bi-eye mr-2"></i>
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
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-emerald-200 hover:text-emerald-700 hover:bg-emerald-50"
                                        >
                                            <i class="bi bi-check mr-2"></i>
                                            Marcar como leída
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <div
                    v-if="notifications.links && notifications.links.length > 3"
                    class="mt-6 flex justify-center"
                >
                    <nav
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white p-1.5 shadow-sm"
                    >
                        <template
                            v-for="(link, index) in notifications.links"
                            :key="index"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-state
                                v-html="link.label"
                                class="rounded-full px-3.5 py-2 text-sm font-medium transition"
                                :class="
                                    link.active
                                        ? 'bg-emerald-600 text-white'
                                        : 'text-slate-600 hover:bg-slate-50'
                                "
                            />
                            <span
                                v-else
                                v-html="link.label"
                                class="rounded-full px-3.5 py-2 text-sm font-medium text-slate-300"
                            />
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </PublicStoreLayout>
</template>
