<template>
    <div class="search-box relative w-full sm:max-w-[400px]">
        <div
            class="flex w-full items-stretch overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition focus-within:border-emerald-300 focus-within:ring-4 focus-within:ring-emerald-100"
        >
            <span
                class="flex items-center justify-center border-r border-slate-200 bg-white px-3 text-slate-500"
            >
                <i class="bi bi-search text-sm"></i>
            </span>
            <input
                type="search"
                class="min-w-0 flex-1 bg-transparent px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none"
                placeholder="Buscar productos, promociones o menús..."
                v-model="searchQueryLocal"
                @input="onInput"
                @keydown.esc="clearAndClose"
                @focus="onFocus"
                aria-label="Búsqueda global"
            />
            <button
                v-if="isSearching"
                class="flex items-center justify-center border-l border-slate-200 bg-slate-50 px-3 text-slate-500"
                type="button"
                disabled
                aria-label="Buscando"
            >
                <span
                    class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-emerald-600"
                    aria-hidden="true"
                ></span>
            </button>
        </div>

        <!-- Renderizar resultados (componente separado) -->
        <SearchResults
            v-if="showResults"
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

<script setup>
import { ref, watch } from "vue";
import SearchResults from "@/Components/SearchResults.vue";
import { useSearch } from "@/composables/useSearch";

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

watch(searchQuery, (val) => {
    searchQueryLocal.value = val;
});
</script>
