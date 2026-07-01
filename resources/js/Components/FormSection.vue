<script setup>
import { computed, useSlots } from "vue";
import SectionTitle from "./SectionTitle.vue";

defineEmits(["submitted"]);

const hasActions = computed(() => !!useSlots().actions);
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div>
            <SectionTitle>
                <template #title>
                    <slot name="title" />
                </template>
                <template #description>
                    <slot name="description" />
                </template>
            </SectionTitle>
        </div>

        <div class="md:col-span-2">
            <form @submit.prevent="$emit('submitted')">
                <div
                    class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"
                >
                    <div class="p-6">
                        <slot name="form" />
                    </div>

                    <div
                        v-if="hasActions"
                        class="bg-slate-50 border-t border-slate-200 px-6 py-4 flex justify-end gap-3"
                    >
                        <slot name="actions" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
