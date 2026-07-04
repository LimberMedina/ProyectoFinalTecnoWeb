<script setup>
import { computed, ref } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { showToast } from "@/utils/toast";

const props = defineProps({
    user: Object,
});

const formatDateInput = (value) => {
    if (!value) return "";
    const stringValue = String(value);
    return stringValue.length >= 10 ? stringValue.slice(0, 10) : stringValue;
};

const form = useForm({
    _method: "PUT",
    nombre: props.user.nombre || "",
    apellidos: props.user.apellidos || "",
    ci: props.user.ci || "",
    telefono: props.user.telefono || "",
    direccion: props.user.direccion || "",
    fecha_nacimiento: formatDateInput(props.user.fecha_nacimiento),
    email: props.user.email || "",
    photo: null,
});

const verificationLinkSent = ref(false);
const photoPreview = ref(null);
const photoInput = ref(null);

const displayName = computed(() => {
    const rawName = props.user?.nombre || props.user?.name || "";
    const name = String(rawName).trim();

    if (!name) {
        return "Usuario";
    }

    return name.split(" ")[0];
});
const fallbackPhotoUrl = computed(
    () =>
        `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName.value)}&background=10b981&color=ffffff`,
);
const profilePhotoUrl = computed(() => {
    if (props.user?.profile_photo_path) {
        return `/storage/${props.user.profile_photo_path}`;
    }

    if (props.user?.profile_photo_url) {
        return props.user.profile_photo_url;
    }

    return fallbackPhotoUrl.value;
});
const hasProfilePhoto = computed(() =>
    Boolean(props.user.profile_photo_path || props.user.profile_photo_url),
);

const handlePhotoError = (event) => {
    event.target.src = fallbackPhotoUrl.value;
};

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files?.[0] ?? null;
    }

    form.post(route("user-profile-information.update"), {
        forceFormData: true,
        errorBag: "updateProfileInformation",
        preserveScroll: true,
        onSuccess: () => {
            clearPhotoFileInput();
            showToast("Perfil actualizado exitosamente.", "success");
            router.visit(route("perfil.show"), {
                preserveState: false,
                preserveScroll: true,
            });
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            showToast(
                firstError ||
                    "No se pudo actualizar el perfil. Verifica los datos e inténtalo nuevamente.",
                "error",
            );
        },
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value?.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value?.files?.[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route("current-user-photo.destroy"), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <div>
        <form
            @submit.prevent="updateProfileInformation"
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-slate-900">
                    Editar Información
                </h3>
                <p class="mt-1 text-sm text-slate-600">
                    Actualiza tu nombre, contacto, dirección y correo
                    electrónico.
                </p>
            </div>

            <div class="space-y-6">
                <div>
                    <label
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Foto de perfil
                    </label>

                    <input
                        id="photo"
                        ref="photoInput"
                        type="file"
                        accept="image/*"
                        class="hidden"
                        @change="updatePhotoPreview"
                    />

                    <div class="flex flex-wrap items-center gap-4">
                        <div v-show="!photoPreview">
                            <img
                                :src="profilePhotoUrl"
                                :alt="displayName"
                                class="h-24 w-24 rounded-full border border-slate-200 object-cover"
                                @error="handlePhotoError"
                            />
                        </div>

                        <div v-show="photoPreview">
                            <span
                                class="block h-24 w-24 rounded-full border border-slate-200 bg-cover bg-center bg-no-repeat"
                                :style="
                                    'background-image: url(\'' +
                                    photoPreview +
                                    '\');'
                                "
                            />
                        </div>

                        <div class="flex flex-col gap-2">
                            <button
                                type="button"
                                @click.prevent="selectNewPhoto"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                Cambiar foto
                            </button>

                            <button
                                v-if="hasProfilePhoto"
                                type="button"
                                @click.prevent="deletePhoto"
                                class="inline-flex items-center gap-2 rounded-xl border border-rose-300 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                                Eliminar foto
                            </button>
                        </div>
                    </div>

                    <p
                        v-if="form.errors.photo"
                        class="mt-2 text-sm text-rose-600"
                    >
                        {{ form.errors.photo }}
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            for="nombre"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Nombre
                        </label>
                        <input
                            id="nombre"
                            v-model="form.nombre"
                            type="text"
                            required
                            autocomplete="given-name"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        />
                        <p
                            v-if="form.errors.nombre"
                            class="mt-1 text-sm text-rose-600"
                        >
                            {{ form.errors.nombre }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="apellidos"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Apellidos
                        </label>
                        <input
                            id="apellidos"
                            v-model="form.apellidos"
                            type="text"
                            autocomplete="family-name"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        />
                        <p
                            v-if="form.errors.apellidos"
                            class="mt-1 text-sm text-rose-600"
                        >
                            {{ form.errors.apellidos }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            for="ci"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            CI
                        </label>
                        <input
                            id="ci"
                            v-model="form.ci"
                            type="text"
                            autocomplete="off"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        />
                        <p
                            v-if="form.errors.ci"
                            class="mt-1 text-sm text-rose-600"
                        >
                            {{ form.errors.ci }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="telefono"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Teléfono
                        </label>
                        <input
                            id="telefono"
                            v-model="form.telefono"
                            type="text"
                            autocomplete="tel"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        />
                        <p
                            v-if="form.errors.telefono"
                            class="mt-1 text-sm text-rose-600"
                        >
                            {{ form.errors.telefono }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label
                            for="direccion"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Dirección
                        </label>
                        <textarea
                            id="direccion"
                            v-model="form.direccion"
                            rows="3"
                            autocomplete="street-address"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            placeholder="Ingresa tu dirección"
                        />
                        <p
                            v-if="form.errors.direccion"
                            class="mt-1 text-sm text-rose-600"
                        >
                            {{ form.errors.direccion }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="fecha_nacimiento"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Fecha de nacimiento
                        </label>
                        <input
                            id="fecha_nacimiento"
                            v-model="form.fecha_nacimiento"
                            type="date"
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                        />
                        <p
                            v-if="form.errors.fecha_nacimiento"
                            class="mt-1 text-sm text-rose-600"
                        >
                            {{ form.errors.fecha_nacimiento }}
                        </p>
                    </div>
                </div>

                <div>
                    <label
                        for="email"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Correo electrónico
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-sm text-rose-600"
                    >
                        {{ form.errors.email }}
                    </p>

                    <div
                        v-if="
                            $page.props.jetstream.hasEmailVerification &&
                            user.email_verified_at === null
                        "
                        class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4"
                    >
                        <p class="text-sm text-amber-800">
                            Tu dirección de correo no está verificada.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="font-semibold text-amber-900 transition hover:text-amber-700"
                                @click.prevent="sendEmailVerification"
                            >
                                Reenviar correo de verificación
                            </Link>
                        </p>

                        <div
                            v-show="verificationLinkSent"
                            class="mt-2 text-sm font-medium text-emerald-700"
                        >
                            ✓ Se ha enviado un nuevo enlace de verificación.
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <span
                        v-if="form.processing"
                        class="inline-block animate-spin"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                    </span>
                    {{ form.processing ? "Guardando..." : "Guardar cambios" }}
                </button>
            </div>
        </form>
    </div>
</template>
