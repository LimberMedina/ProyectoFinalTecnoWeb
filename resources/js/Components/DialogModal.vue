<script setup>
import Modal from "./Modal.vue";

const emit = defineEmits(["close"]);

defineProps({
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

const close = () => {
    emit("close");
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div
            class="rounded-[2rem] border border-slate-200 bg-white shadow-[0_18px_60px_-30px_rgba(15,23,42,0.25)] overflow-hidden"
        >
            <div
                class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-6 py-4"
            >
                <h5 class="text-lg font-black text-slate-900">
                    <slot name="title" />
                </h5>
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:border-slate-300 hover:text-slate-700"
                    @click="close"
                    aria-label="Close"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <slot name="content" />
            </div>

            <div
                class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end"
            >
                <slot name="footer" />
            </div>
        </div>
    </Modal>
</template>
