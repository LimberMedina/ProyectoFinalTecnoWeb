<script setup>
import { computed } from "vue";

const emit = defineEmits(["update:checked"]);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        type: String,
        default: null,
    },
    id: String,
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit("update:checked", val);
    },
});
</script>

<template>
    <div class="flex items-center">
        <input
            v-model="proxyChecked"
            :id="id"
            :value="value"
            type="checkbox"
            class="w-5 h-5 rounded border-slate-300 text-emerald-600 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 cursor-pointer"
        />
    </div>
</template>
