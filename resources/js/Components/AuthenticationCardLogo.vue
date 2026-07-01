<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <Link :href="'/'" class="inline-block">
        <div class="flex items-center justify-center gap-3">
            <div
                class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center shadow-lg shadow-emerald-200"
            >
                <svg
                    class="w-8 h-8 text-white"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                </svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900">Tienda</div>
                <div class="text-sm font-semibold text-emerald-600">Elena</div>
            </div>
        </div>
    </Link>
</template>
