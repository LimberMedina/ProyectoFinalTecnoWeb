<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() => {
    return props.active
        ? "flex items-center px-4 py-2 text-emerald-600 font-semibold border-b-2 border-emerald-600 transition"
        : "flex items-center px-4 py-2 text-slate-600 font-medium hover:text-emerald-600 hover:bg-emerald-50 transition";
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
