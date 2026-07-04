<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import PublicStoreLayout from "@/Layouts/PublicStoreLayout.vue";
import homeSvg from "./assets/Home.svg";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const isAuthenticated = computed(() => Boolean(usePage().props.auth?.user));
</script>

<template>
    <Head title="Bienvenido - Tienda Elena" />

    <PublicStoreLayout>
        <main>
            <section
                class="relative overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-sky-50"
            >
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.16),transparent_32%),radial-gradient(circle_at_bottom_left,rgba(59,130,246,0.12),transparent_28%)]"
                ></div>
                <div
                    class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-24"
                >
                    <div class="space-y-8">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-4 py-2 text-sm font-medium text-emerald-700 shadow-sm"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-emerald-500"
                            ></span>
                            Compra fácil, rápido y seguro
                        </div>

                        <div class="space-y-5">
                            <h1
                                class="max-w-xl text-4xl font-black tracking-tight text-slate-900 sm:text-5xl lg:text-6xl"
                            >
                                ¡Los mejores productos,
                                <span class="block text-emerald-600"
                                    >al mejor precio!</span
                                >
                            </h1>
                            <p
                                class="max-w-lg text-base leading-7 text-slate-600 sm:text-lg"
                            >
                                Encuentra productos seleccionados, promociones
                                destacadas y una experiencia de compra clara en
                                un solo lugar.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Link
                                href="/tienda/productos"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="18"
                                    height="18"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"
                                    />
                                    <line x1="3" y1="6" x2="21" y2="6" />
                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                </svg>
                                Comprar ahora
                            </Link>
                            <a
                                href="/categorias"
                                class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-6 py-3 text-sm font-semibold text-emerald-700 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50"
                            >
                                Explorar categorías
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </a>
                            <Link
                                href="/tienda/promociones"
                                class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-white px-6 py-3 text-sm font-semibold text-orange-700 transition hover:-translate-y-0.5 hover:border-orange-300 hover:bg-orange-50"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"
                                    />
                                    <polyline points="13 2 13 9 20 9" />
                                    <line x1="9" y1="17" x2="15" y2="17" />
                                    <line x1="9" y1="13" x2="15" y2="13" />
                                </svg>
                                Ver promociones
                            </Link>
                        </div>

                        <div
                            class="grid max-w-xl grid-cols-3 gap-4 pt-2 text-center"
                        >
                            <div
                                class="rounded-2xl border border-white bg-white/80 p-4 shadow-sm backdrop-blur"
                            >
                                <div
                                    class="text-2xl font-black text-emerald-600"
                                >
                                    +120
                                </div>
                                <div
                                    class="mt-1 text-xs font-medium text-slate-500"
                                >
                                    Productos
                                </div>
                            </div>
                            <div
                                class="rounded-2xl border border-white bg-white/80 p-4 shadow-sm backdrop-blur"
                            >
                                <div
                                    class="text-2xl font-black text-emerald-600"
                                >
                                    24/7
                                </div>
                                <div
                                    class="mt-1 text-xs font-medium text-slate-500"
                                >
                                    Disponible
                                </div>
                            </div>
                            <div
                                class="rounded-2xl border border-white bg-white/80 p-4 shadow-sm backdrop-blur"
                            >
                                <div
                                    class="text-2xl font-black text-emerald-600"
                                >
                                    100%
                                </div>
                                <div
                                    class="mt-1 text-xs font-medium text-slate-500"
                                >
                                    Seguro
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative flex justify-center lg:justify-end">
                        <div
                            class="relative w-full max-w-xl rounded-[2rem] border border-emerald-100 bg-white p-6 shadow-2xl shadow-emerald-100/60"
                        >
                            <div
                                class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.12),transparent_35%),radial-gradient(circle_at_bottom_left,rgba(59,130,246,0.08),transparent_30%)]"
                            ></div>
                            <img
                                :src="homeSvg"
                                alt="Ilustración de tienda online"
                                class="relative mx-auto w-full max-w-lg select-none object-contain"
                            />
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </PublicStoreLayout>
</template>
