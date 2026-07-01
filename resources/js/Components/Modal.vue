<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: "2xl",
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["close"]);
const dialog = ref();
const showSlot = ref(props.show);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = "hidden";
            showSlot.value = true;
            dialog.value?.showModal();
        } else {
            document.body.style.overflow = null;
            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    },
);

const close = () => {
    if (props.closeable) {
        emit("close");
    }
};

const closeOnEscape = (e) => {
    if (e.key === "Escape" && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener("keydown", closeOnEscape));

onUnmounted(() => {
    document.removeEventListener("keydown", closeOnEscape);
    document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
    return {
        sm: "max-w-sm",
        md: "max-w-md",
        lg: "max-w-lg",
        xl: "max-w-xl",
        "2xl": "max-w-2xl",
    }[props.maxWidth];
});
</script>

<template>
    <dialog
        class="fixed inset-0 z-50 m-0 h-full w-full overflow-y-auto bg-transparent p-4 outline-none"
        :class="{ 'show d-block': show }"
        ref="dialog"
        tabindex="-1"
        style="
            background: transparent;
            border: none;
            padding: 0;
            max-width: none;
            max-height: none;
        "
    >
        <div class="flex min-h-full items-center justify-center px-4 py-6">
            <div v-if="showSlot" class="w-full" :class="maxWidthClass">
                <slot />
            </div>
        </div>
    </dialog>
    <div
        class="fixed inset-0 z-40 bg-slate-950/20 transition-opacity"
        :class="{ show: show }"
        v-if="show"
        @click="close"
    ></div>
</template>
