<script setup>
defineProps({
    value: String,
    for: String,
});
</script>

<template>
    <label :for="for" class="block text-sm font-semibold text-slate-700 mb-2">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
