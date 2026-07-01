<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useRegisterValidation } from "@/composables/useRegisterValidation";

const props = defineProps({
    usuario: Object,
    roles: Array,
});

const form = useForm({
    _method: "put",
    nombre: props.usuario.nombre,
    apellidos: props.usuario.apellidos,
    ci: props.usuario.ci,
    telefono: props.usuario.telefono,
    foto_perfil: null,
    role_id: props.usuario.role_id,
    estado: props.usuario.estado,
    fecha_nacimiento: props.usuario.fecha_nacimiento || "",
});

const previewFoto = ref(null);

const {
    errors: validationErrors,
    updateError,
    validateAll,
} = useRegisterValidation();

watch(
    () => form.nombre,
    (value) => updateError("nombre", value),
);
watch(
    () => form.apellidos,
    (value) => updateError("apellidos", value),
);
watch(
    () => form.ci,
    (value) => updateError("ci", value),
);
watch(
    () => form.telefono,
    (value) => updateError("telefono", value),
);
watch(
    () => form.fecha_nacimiento,
    (value) => updateError("fecha_nacimiento", value),
);

const handleFotoPerfil = (event) => {
    const [file] = event.target.files;
    form.foto_perfil = file || null;
    previewFoto.value = file ? URL.createObjectURL(file) : null;
};

const submit = () => {
    if (!validateAll(form)) {
        return;
    }

    form.post(route("usuarios.update", props.usuario.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout title="Editar Usuario">
        <Head title="Editar Usuario" />

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
                            Usuarios
                        </div>
                        <h1
                            class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl"
                        >
                            Editar usuario
                        </h1>
                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-600"
                        >
                            Modifique los datos del usuario.
                        </p>
                    </div>

                    <Link
                        :href="route('usuarios.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </Link>
                </div>

                <form
                    @submit.prevent="submit"
                    class="rounded-[2rem] border border-white bg-white/90 p-6 shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)] sm:p-8"
                >
                    <div class="mb-6 flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700"
                            >
                                Formulario
                            </p>
                            <h2 class="mt-2 text-xl font-black text-slate-900">
                                Editar datos del usuario
                            </h2>
                        </div>
                        <i
                            class="bi bi-pencil-square text-2xl text-emerald-600"
                        ></i>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label
                                for="nombre"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Nombre
                                <span class="text-rose-500">*</span></label
                            >
                            <input
                                id="nombre"
                                v-model="form.nombre"
                                type="text"
                                class="w-full rounded-xl border px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                :class="
                                    validationErrors.nombre ||
                                    form.errors.nombre
                                        ? 'border-rose-400 bg-rose-50'
                                        : 'border-slate-200 bg-white'
                                "
                                required
                            />
                            <p
                                v-if="
                                    validationErrors.nombre ||
                                    form.errors.nombre
                                "
                                class="mt-2 text-sm text-rose-600"
                            >
                                {{
                                    validationErrors.nombre ||
                                    form.errors.nombre
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="apellidos"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Apellidos
                                <span class="text-rose-500">*</span></label
                            >
                            <input
                                id="apellidos"
                                v-model="form.apellidos"
                                type="text"
                                class="w-full rounded-xl border px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                :class="
                                    validationErrors.apellidos ||
                                    form.errors.apellidos
                                        ? 'border-rose-400 bg-rose-50'
                                        : 'border-slate-200 bg-white'
                                "
                                required
                            />
                            <p
                                v-if="
                                    validationErrors.apellidos ||
                                    form.errors.apellidos
                                "
                                class="mt-2 text-sm text-rose-600"
                            >
                                {{
                                    validationErrors.apellidos ||
                                    form.errors.apellidos
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="ci"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >CI <span class="text-rose-500">*</span></label
                            >
                            <input
                                id="ci"
                                v-model="form.ci"
                                type="text"
                                class="w-full rounded-xl border px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                :class="
                                    validationErrors.ci || form.errors.ci
                                        ? 'border-rose-400 bg-rose-50'
                                        : 'border-slate-200 bg-white'
                                "
                                required
                            />
                            <p
                                v-if="validationErrors.ci || form.errors.ci"
                                class="mt-2 text-sm text-rose-600"
                            >
                                {{ validationErrors.ci || form.errors.ci }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="fecha_nacimiento"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Fecha de nacimiento</label
                            >
                            <input
                                id="fecha_nacimiento"
                                v-model="form.fecha_nacimiento"
                                type="date"
                                class="w-full rounded-xl border px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                :class="
                                    validationErrors.fecha_nacimiento ||
                                    form.errors.fecha_nacimiento
                                        ? 'border-rose-400 bg-rose-50'
                                        : 'border-slate-200 bg-white'
                                "
                            />
                            <p
                                v-if="
                                    validationErrors.fecha_nacimiento ||
                                    form.errors.fecha_nacimiento
                                "
                                class="mt-2 text-sm text-rose-600"
                            >
                                {{
                                    validationErrors.fecha_nacimiento ||
                                    form.errors.fecha_nacimiento
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="telefono"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Teléfono
                                <span class="text-rose-500">*</span></label
                            >
                            <input
                                id="telefono"
                                v-model="form.telefono"
                                type="tel"
                                class="w-full rounded-xl border px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                :class="
                                    validationErrors.telefono ||
                                    form.errors.telefono
                                        ? 'border-rose-400 bg-rose-50'
                                        : 'border-slate-200 bg-white'
                                "
                                required
                            />
                            <p
                                v-if="
                                    validationErrors.telefono ||
                                    form.errors.telefono
                                "
                                class="mt-2 text-sm text-rose-600"
                            >
                                {{
                                    validationErrors.telefono ||
                                    form.errors.telefono
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Email</label
                            >
                            <input
                                id="email"
                                :value="usuario.email"
                                type="email"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-500 outline-none"
                                readonly
                                disabled
                            />
                            <p class="mt-2 text-xs text-slate-500">
                                El email no se puede modificar.
                            </p>
                        </div>

                        <div>
                            <label
                                for="role_id"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                                >Rol <span class="text-rose-500">*</span></label
                            >
                            <select
                                id="role_id"
                                v-model="form.role_id"
                                class="w-full rounded-xl border px-4 py-3 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                                :class="
                                    validationErrors.role_id ||
                                    form.errors.role_id
                                        ? 'border-rose-400 bg-rose-50'
                                        : 'border-slate-200 bg-white'
                                "
                                required
                            >
                                <option value="">Seleccione un rol</option>
                                <option
                                    v-for="rol in roles"
                                    :key="rol.id"
                                    :value="rol.id"
                                >
                                    {{ rol.nombre }}
                                </option>
                            </select>
                            <p
                                v-if="
                                    validationErrors.role_id ||
                                    form.errors.role_id
                                "
                                class="mt-2 text-sm text-rose-600"
                            >
                                {{
                                    validationErrors.role_id ||
                                    form.errors.role_id
                                }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label
                                for="foto_perfil"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Foto de perfil
                            </label>
                            <div
                                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 p-4"
                            >
                                <div class="mb-4 flex items-center gap-3">
                                    <img
                                        :src="
                                            previewFoto ||
                                            usuario.profile_photo_url
                                        "
                                        alt="Foto de perfil"
                                        class="h-14 w-14 rounded-full border border-slate-200 object-cover"
                                    />
                                    <span class="text-sm text-slate-600">
                                        {{
                                            previewFoto
                                                ? "Vista previa de la nueva foto seleccionada."
                                                : "Foto de perfil actual del usuario."
                                        }}
                                    </span>
                                </div>
                                <input
                                    id="foto_perfil"
                                    type="file"
                                    accept="image/png,image/jpeg,image/jpg,image/webp"
                                    class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-emerald-700"
                                    @change="handleFotoPerfil"
                                />
                                <p class="mt-2 text-xs text-slate-500">
                                    Formatos permitidos: JPG, JPEG, PNG, WEBP.
                                    Max 2MB.
                                </p>
                                <p
                                    v-if="form.errors.foto_perfil"
                                    class="mt-2 text-sm text-rose-600"
                                >
                                    {{ form.errors.foto_perfil }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-end">
                            <div
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                            >
                                <label
                                    for="estado"
                                    class="flex items-center justify-between gap-4 text-sm font-semibold text-slate-700"
                                >
                                    <span>Estado</span>
                                    <span
                                        class="text-xs font-medium"
                                        :class="
                                            form.estado
                                                ? 'text-emerald-700'
                                                : 'text-slate-500'
                                        "
                                        >{{
                                            form.estado ? "Activo" : "Inactivo"
                                        }}</span
                                    >
                                </label>
                                <input
                                    id="estado"
                                    v-model="form.estado"
                                    type="checkbox"
                                    class="mt-3 h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                    role="switch"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap justify-end gap-3">
                        <Link
                            :href="route('usuarios.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <i class="bi bi-x-circle"></i>
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70"
                            :disabled="form.processing"
                        >
                            <span
                                v-if="form.processing"
                                class="inline-flex items-center gap-2"
                            >
                                <span
                                    class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                                ></span>
                                Guardando...
                            </span>
                            <span v-else class="inline-flex items-center gap-2">
                                <i class="bi bi-check-circle"></i>
                                Guardar cambios
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
