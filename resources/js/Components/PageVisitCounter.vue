<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

const currentVisits = computed(() =>
    Number(page.props.pageVisits?.current ?? 0),
);
</script>

<template>
    <div
        class="fixed right-4 bottom-4 z-50 rounded-2xl border border-slate-200 bg-white/95 px-4 py-2 text-sm font-semibold text-slate-900 shadow-xl shadow-slate-900/10 backdrop-blur-sm"
    >
        Visitas: {{ currentVisits }}
    </div>
</template>
