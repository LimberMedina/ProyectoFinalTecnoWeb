<script setup>
defineProps({
    type: {
        type: String,
        default: "button",
    },
});
</script>

<template>
    <button
        :type="type"
        class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold text-sm transition hover:bg-red-700 hover:-translate-y-0.5 shadow-lg shadow-red-200 hover:shadow-red-300"
    >
        <slot />
    </button>
</template>
