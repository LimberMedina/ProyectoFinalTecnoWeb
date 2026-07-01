<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";

const page = usePage();
const user = page.props.auth?.user || {};
const userRole = computed(() => page.props.auth?.user?.role || {});
const isOwnerOrSeller = computed(() => {
    const roleId = String(userRole.value.id || "");
    const roleName = (userRole.value.nombre || "").toLowerCase();

    return (
        ["1", "2"].includes(roleId) ||
        ["propietario", "vendedor"].includes(roleName)
    );
});
const currentLayout = computed(() =>
    isOwnerOrSeller.value ? AppLayout : PublicStoreLayout,
);
const layoutProps = computed(() =>
    isOwnerOrSeller.value ? { title: "Editar mi información" } : {},
);
</script>

<template>
    <Head title="Editar Mi Información" />

    <component :is="currentLayout" v-bind="layoutProps">
        <div>
            <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
                <FlashNotification />

                <div
                    class="mb-6 flex flex-wrap items-start justify-between gap-4"
                >
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">
                            Editar mi información
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm text-slate-600">
                            Actualiza tus datos personales y tu foto de perfil.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            :href="route('perfil.show')"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Volver al perfil
                        </Link>
                    </div>
                </div>

                <UpdateProfileInformationForm :user="user" />
            </main>
        </div>
    </component>
</template>
