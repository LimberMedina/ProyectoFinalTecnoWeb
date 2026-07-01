<template>
    <div
        class="min-h-screen w-full flex flex-col justify-center items-center bg-gradient-to-br from-slate-50 via-white to-emerald-50 px-4 py-12"
    >
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.08),transparent_32%),radial-gradient(circle_at_bottom_left,rgba(59,130,246,0.06),transparent_28%)] pointer-events-none"
        ></div>

        <div class="relative z-10 mb-8">
            <slot name="logo" />
        </div>

        <div
            class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-xl shadow-slate-200 border border-slate-100 overflow-hidden"
        >
            <div
                class="absolute inset-0 bg-gradient-to-br from-emerald-50/30 to-blue-50/30"
            ></div>
            <div class="relative p-8">
                <slot />
            </div>
        </div>
    </div>
</template>
