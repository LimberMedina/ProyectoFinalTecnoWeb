import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import { useTheme } from "@/composables/useTheme";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({
            setup() {
                useTheme(props.initialPage.props.auth?.user || {});
                return () => h(App, props);
            },
        });

        app.use(plugin).use(ZiggyVue).mount(el);

        return app;
    },
    progress: {
        color: "#4B5563",
    },
});
