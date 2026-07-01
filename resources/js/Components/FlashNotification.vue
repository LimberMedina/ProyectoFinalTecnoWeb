<script setup>
import { watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { showToast } from "@/utils/toast";

const page = usePage();

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            showToast(flash.success, "success");
        } else if (flash?.error) {
            showToast(flash.error, "error");
        }
    },
    { immediate: true, deep: true },
);

watch(
    () => page.props.errors,
    (errors) => {
        if (errors?.stock) {
            showToast(errors.stock, "error");
        } else if (errors?.error) {
            showToast(errors.error, "error");
        }
    },
    { immediate: true, deep: true },
);
</script>

<template></template>
