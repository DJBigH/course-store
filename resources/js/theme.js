const THEME_STORAGE_KEY = "client-theme";
const root = document.documentElement;
const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)");

function resolveTheme(preferredTheme) {
    if (preferredTheme === "light" || preferredTheme === "dark") {
        return preferredTheme;
    }

    return mediaQuery.matches ? "dark" : "light";
}

function applyTheme(preferredTheme) {
    const theme = resolveTheme(preferredTheme);

    root.dataset.theme = theme;
    root.style.colorScheme = theme;

    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
        const isDark = theme === "dark";
        const lightLabel = button.dataset.themeLabelLight || "Light";
        const darkLabel = button.dataset.themeLabelDark || "Dark";
        const switchToLight =
            button.dataset.themeSwitchLight || "Switch to light mode";
        const switchToDark =
            button.dataset.themeSwitchDark || "Switch to dark mode";
        button.setAttribute("aria-pressed", String(isDark));
        button.setAttribute(
            "aria-label",
            isDark ? switchToLight : switchToDark
        );
        button.setAttribute(
            "title",
            isDark ? switchToLight : switchToDark
        );

        const label = button.querySelector(".theme-toggle__label");
        if (label) {
            label.textContent = isDark ? lightLabel : darkLabel;
        }
    });
}

function toggleTheme() {
    const nextTheme = root.dataset.theme === "dark" ? "light" : "dark";
    localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
    applyTheme(nextTheme);
}

document.addEventListener("DOMContentLoaded", () => {
    applyTheme(localStorage.getItem(THEME_STORAGE_KEY));

    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
        button.addEventListener("click", toggleTheme);
    });
});

if (typeof mediaQuery.addEventListener === "function") {
    mediaQuery.addEventListener("change", () => {
        if (!localStorage.getItem(THEME_STORAGE_KEY)) {
            applyTheme(null);
        }
    });
}
