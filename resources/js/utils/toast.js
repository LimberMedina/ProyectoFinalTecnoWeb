import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";

const variants = {
    success: {
        accent: "#22c55e",
        border: "#bbf7d0",
        background: "#ffffff",
        text: "#0f172a",
        iconBackground: "#22c55e",
    },
    error: {
        accent: "#ef4444",
        border: "#fecaca",
        background: "#ffffff",
        text: "#0f172a",
        iconBackground: "#ef4444",
    },
};

const buildIcon = (variant) => {
    if (variant === "error") {
        return `
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="none">
                <path d="M12 8v5" stroke="white" stroke-width="2.5" stroke-linecap="round" />
                <path d="M12 16.5h.01" stroke="white" stroke-width="3.2" stroke-linecap="round" />
            </svg>
        `;
    }

    return `
        <svg viewBox="0 0 24 24" aria-hidden="true" fill="none">
            <path d="M20 7L10.5 16.5L4 10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    `;
};

const escapeHtml = (value) =>
    String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");

const buildToastNode = (message, variant) => {
    const theme = variants[variant] || variants.success;
    const wrapper = document.createElement("div");
    wrapper.style.cssText = `
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: flex-start;
        gap: 0.875rem;
        border-radius: 1rem;
        border: 1px solid ${theme.border};
        background: ${theme.background};
        color: ${theme.text};
        box-shadow: 0 20px 50px -28px rgba(15, 23, 42, 0.45);
        padding: 0.95rem 1rem 1rem 1rem;
        width: 100%;
    `;

    wrapper.innerHTML = `
        <div style="flex: 0 0 auto; display: grid; place-items: center; width: 2.45rem; height: 2.45rem; border-radius: 999px; background: ${theme.iconBackground}; color: white;">
            ${buildIcon(variant)}
        </div>
        <div style="min-width: 0; flex: 1; padding-right: 0.5rem; font-size: 0.95rem; line-height: 1.45; font-weight: 700; word-break: break-word;">
            ${escapeHtml(message)}
        </div>
        <div style="position: absolute; left: 0; right: 0; bottom: 0; height: 0.34rem; background: rgba(148, 163, 184, 0.18);">
            <div style="height: 100%; width: 100%; background: ${theme.accent}; transform-origin: left center; animation: elena-toast-progress 3s linear forwards;"></div>
        </div>
    `;

    return wrapper;
};

export function showToast(message, variant = "success") {
    if (typeof document === "undefined") return;

    const toast = Toastify({
        node: buildToastNode(message, variant),
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        stopOnFocus: true,
        offset: {
            x: 16,
            y: 16,
        },
        className: `elena-toast elena-toast-${variant}`,
        style: {
            background: "transparent",
            boxShadow: "none",
            padding: "0",
            width: "min(92vw, 28rem)",
        },
    }).showToast();

    const closeButton = toast.toastElement?.querySelector(".toast-close");
    if (closeButton) {
        closeButton.innerHTML = `
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" width="18" height="18">
                <path d="M6.5 6.5L17.5 17.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
                <path d="M17.5 6.5L6.5 17.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
            </svg>
        `;
        closeButton.setAttribute("aria-label", "Cerrar notificación");
    }
}
