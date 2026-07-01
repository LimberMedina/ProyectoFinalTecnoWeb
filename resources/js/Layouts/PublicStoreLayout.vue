<script setup>
import PublicStoreHeader from "@/Components/PublicStoreHeader.vue";
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <PublicStoreHeader />

        <main>
            <slot />
        </main>
    </div>
</template>
