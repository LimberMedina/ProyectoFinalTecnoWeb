<script setup>
import { onMounted, ref } from "vue";

defineProps({
    modelValue: String,
    type: {
        type: String,
        default: "text",
    },
});

defineEmits(["update:modelValue"]);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        :type="type"
        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-900 placeholder-slate-400 transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none hover:border-slate-300"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
