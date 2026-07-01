<script setup>
import { Link } from "@inertiajs/vue3";

defineOptions({ inheritAttrs: false });

defineProps({
    href: String,
    as: String,
});
</script>

<template>
    <li>
        <button
            v-if="as == 'button'"
            type="submit"
            class="dropdown-item"
            v-bind="$attrs"
        >
            <slot />
        </button>

        <a
            v-else-if="as == 'a'"
            :href="href"
            class="dropdown-item"
            v-bind="$attrs"
        >
            <slot />
        </a>

        <Link v-else :href="href" class="dropdown-item" v-bind="$attrs">
            <slot />
        </Link>
    </li>
</template>
