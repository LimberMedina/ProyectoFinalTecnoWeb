import { ref, onMounted } from "vue";

const theme = ref("");
const mode = ref("dia");
const fontSize = ref("normal");
const contrast = ref("normal");
let initialized = false;
let isAuthenticated = false;

const allowedThemes = ["", "ninos", "jovenes", "adultos"];
const allowedModes = ["dia", "noche"];
const allowedFontSizes = ["small", "normal", "large"];
const allowedContrast = ["normal", "high"];

const detectMode = () => {
    const hour = new Date().getHours();
    return hour >= 6 && hour < 18 ? "dia" : "noche";
};

const normalizeValue = (value, allowed, fallback) => {
    if (value === "auto") {
        return detectMode();
    }

    return allowed.includes(value) ? value : fallback;
};

const applyTheme = () => {
    const root = document.documentElement;

    root.setAttribute("data-theme", theme.value);
    root.setAttribute("data-mode", mode.value);
    root.setAttribute("data-font-size", fontSize.value);
    root.setAttribute("data-contrast", contrast.value);
};

const savePreference = async (payload) => {
    if (!isAuthenticated) {
        return;
    }

    try {
        await window.axios.post("/user/settings/preferences", payload);
    } catch (error) {
        console.warn(
            "No se pudo guardar la preferencia de tema en el servidor.",
            error,
        );
    }
};

const setTheme = (newTheme) => {
    theme.value = normalizeValue(newTheme, allowedThemes, "");
    if (theme.value) {
        localStorage.setItem("theme", theme.value);
    } else {
        localStorage.removeItem("theme");
    }
    applyTheme();
    savePreference({ theme: theme.value });
};

const setMode = (newMode) => {
    mode.value = normalizeValue(newMode, allowedModes, "dia");
    localStorage.setItem("mode", mode.value);
    applyTheme();
    savePreference({ mode: mode.value });
};

const setFontSize = (size) => {
    fontSize.value = normalizeValue(size, allowedFontSizes, "normal");
    localStorage.setItem("fontSize", fontSize.value);
    applyTheme();
    savePreference({ font_size: fontSize.value });
};

const setContrast = (level) => {
    contrast.value = normalizeValue(level, allowedContrast, "normal");
    localStorage.setItem("contrast", contrast.value);
    applyTheme();
    savePreference({ contrast: contrast.value });
};

const syncPreference = (storageKey, value) => {
    if (value) {
        localStorage.setItem(storageKey, value);
    } else {
        localStorage.removeItem(storageKey);
    }
};

const initTheme = (initialPreferences = {}) => {
    if (initialized) {
        return;
    }

    const storedTheme = localStorage.getItem("theme");
    const storedMode = localStorage.getItem("mode");
    const storedFontSize = localStorage.getItem("fontSize");
    const storedContrast = localStorage.getItem("contrast");

    isAuthenticated = Boolean(initialPreferences?.id);

    theme.value = normalizeValue(
        initialPreferences.theme !== undefined
            ? initialPreferences.theme
            : storedTheme,
        allowedThemes,
        "",
    );
    mode.value = normalizeValue(
        initialPreferences.mode !== undefined
            ? initialPreferences.mode
            : storedMode,
        allowedModes,
        "dia",
    );
    fontSize.value = normalizeValue(
        initialPreferences.font_size !== undefined
            ? initialPreferences.font_size
            : storedFontSize,
        allowedFontSizes,
        "normal",
    );
    contrast.value = normalizeValue(
        initialPreferences.contrast !== undefined
            ? initialPreferences.contrast
            : storedContrast,
        allowedContrast,
        "normal",
    );

    if (isAuthenticated) {
        syncPreference("theme", theme.value);
        syncPreference("mode", mode.value);
        syncPreference("fontSize", fontSize.value);
        syncPreference("contrast", contrast.value);
    }

    initialized = true;
    applyTheme();
};

export function useTheme(initialPreferences = {}) {
    initTheme(initialPreferences);

    onMounted(() => {
        applyTheme();
    });

    return {
        theme,
        mode,
        fontSize,
        contrast,
        setTheme,
        setMode,
        setFontSize,
        setContrast,
    };
}
