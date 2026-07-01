<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
});

const classes = computed(() => {
    return props.active
        ? "w-full text-start px-4 py-2 border-l-4 border-emerald-600 bg-emerald-50 text-emerald-700 font-semibold"
        : "w-full text-start px-4 py-2 border-l-4 border-transparent text-slate-600 hover:bg-slate-100 transition";
});
</script>

<template>
    <div>
        <button
            v-if="as == 'button'"
            :class="classes"
            class="w-full text-start"
        >
            <slot />
        </button>

        <Link v-else :href="href" :class="classes">
            <slot />
        </Link>
    </div>
</template>
