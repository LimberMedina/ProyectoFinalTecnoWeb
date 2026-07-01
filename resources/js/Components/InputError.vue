<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div
        v-show="message"
        class="mt-2 p-3 rounded-lg bg-red-50 border border-red-200"
    >
        <p class="text-sm font-medium text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path
                    fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd"
                />
            </svg>
            {{ message }}
        </p>
    </div>
</template>
