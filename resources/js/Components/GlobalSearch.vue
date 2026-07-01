<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import SearchResults from "@/Components/SearchResults.vue";
import { useSearch } from "@/composables/useSearch";

const root = ref(null);

const {
    searchQuery,
    searchResults,
    isSearching,
    showResults,
    search,
    clearSearch,
    closeResults,
} = useSearch();

const searchQueryLocal = ref(searchQuery.value || "");

const onInput = (e) => {
    const q = e.target.value;
    search(q);
};

const onFocus = () => {
    if (
        searchResults.value &&
        (searchResults.value.productos?.length ||
            searchResults.value.promociones?.length ||
            searchResults.value.menus?.length)
    ) {
        showResults.value = true;
    }
};

const clearAndClose = () => {
    clearSearch();
    closeResults();
};

const handleClickOutside = (event) => {
    if (root.value && !root.value.contains(event.target)) {
        closeResults();
    }
};

const handleKeydown = (e) => {
    if (e.key === "Escape") {
        clearAndClose();
    }
};

watch(searchQuery, (val) => {
    searchQueryLocal.value = val;
});

onMounted(() => {
    document.addEventListener("mousedown", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("mousedown", handleClickOutside);
});
</script>

<template>
    <div ref="root" class="relative w-full max-w-[22rem]">
        <form class="relative" role="search" @submit.prevent>
            <div
                class="group flex min-h-10 items-center rounded-2xl border border-slate-200 bg-white shadow-sm transition focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-100"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center text-slate-400 transition group-focus-within:text-emerald-600"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3.5-3.5" />
                    </svg>
                </div>

                <input
                    v-model="searchQueryLocal"
                    type="search"
                    autocomplete="off"
                    placeholder="Buscar..."
                    aria-label="Buscar información en Tienda Elena"
                    class="h-10 min-w-0 flex-1 border-0 bg-transparent px-0 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-0 focus:ring-0"
                    @input="onInput"
                    @focus="onFocus"
                    @keydown="handleKeydown"
                />

                <div
                    v-if="isSearching"
                    class="mr-3 h-4 w-4 animate-spin rounded-full border-2 border-emerald-200 border-t-emerald-600"
                    aria-label="Buscando"
                ></div>

                <button
                    v-else-if="searchQueryLocal"
                    type="button"
                    class="mr-2 inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Limpiar búsqueda"
                    @click="clearAndClose"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        viewBox="0 0 24 24"
                    >
                        <path d="M6 6l12 12" />
                        <path d="M18 6 6 18" />
                    </svg>
                </button>
            </div>
        </form>

        <!-- Renderizar resultados usando SearchResults.vue -->
        <SearchResults
            v-if="showResults && searchQueryLocal.trim().length >= 2"
            :productos="searchResults.productos || []"
            :promociones="searchResults.promociones || []"
            :categorias="searchResults.categorias || []"
            :proveedores="searchResults.proveedores || []"
            :menus="searchResults.menus || []"
            :usuarios="searchResults.usuarios || []"
            @close="closeResults"
        />
    </div>
</template>
