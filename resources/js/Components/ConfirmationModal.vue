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
        default: "sm",
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
            class="overflow-hidden rounded-[1.5rem] border border-slate-100 bg-white shadow-[0_24px_80px_-30px_rgba(15,23,42,0.35)]"
        >
            <div
                class="flex items-start justify-between gap-4 border-b border-slate-100 px-4 py-4 sm:px-5"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700"
                    >
                        <svg
                            class="h-6 w-6"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-slate-400"
                        >
                            Confirmación
                        </p>
                        <h5
                            class="mt-1 text-base font-black text-slate-900 sm:text-lg"
                        >
                            <slot name="title" />
                        </h5>
                    </div>
                </div>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    @click="close"
                    aria-label="Cerrar"
                >
                    <svg
                        class="h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="px-4 py-5 sm:px-5">
                <div
                    class="rounded-[1.25rem] bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600"
                >
                    <slot name="content" />
                </div>
            </div>

            <div
                class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-white px-4 py-4 sm:flex-row sm:justify-end sm:px-5"
            >
                <slot name="footer" />
            </div>
        </div>
    </Modal>
</template>
