<script setup>
defineProps({
    type: {
        type: String,
        default: "submit",
    },
    disabled: Boolean,
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="w-full px-4 py-2.5 rounded-lg bg-emerald-600 text-white font-semibold text-sm transition hover:bg-emerald-700 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-emerald-200 hover:shadow-emerald-300"
    >
        <slot />
    </button>
</template>
