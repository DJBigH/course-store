const escapeSelector = (value) => {
    if (window.CSS && typeof window.CSS.escape === "function") {
        return window.CSS.escape(value);
    }

    return String(value).replace(/"/g, '\\"');
};

const getHeaderOffset = () => {
    const header = document.querySelector("[data-fixed-header]");

    if (!header) {
        return 24;
    }

    return Math.ceil(header.getBoundingClientRect().height) + 24;
};

const scrollToBlock = (element) => {
    if (!element) {
        return;
    }

    const targetTop = Math.max(
        element.getBoundingClientRect().top + window.scrollY - getHeaderOffset(),
        0,
    );

    window.scrollTo({
        top: targetTop,
        behavior: "smooth",
    });
};

const setLoadingState = (block, isLoading) => {
    block.classList.toggle("is-loading", isLoading);
    block.setAttribute("aria-busy", isLoading ? "true" : "false");
};

const initSelect2 = (scope = document) => {
    if (!window.$ || !window.$.fn?.select2) {
        return;
    }

    window.$(scope).find(".js-select2").select2();
};

const initOrderTotalInput = (scope = document) => {
    const displayInput = scope.querySelector("#total_display");
    const hiddenInput = scope.querySelector("#total");

    if (!displayInput || !hiddenInput || displayInput.dataset.formatted === "true") {
        return;
    }

    displayInput.dataset.formatted = "true";

    displayInput.addEventListener("input", function () {
        const rawValue = this.value.replace(/[^\d]/g, "");
        hiddenInput.value = rawValue;
        this.value = rawValue ? Number(rawValue).toLocaleString("en-US") : "";
    });
};

const hydrateFilterBlock = (scope = document) => {
    initSelect2(scope);
    initOrderTotalInput(scope);
};

const buildFilterUrl = (form) => {
    const action = form.action || window.location.href;
    const url = new URL(action, window.location.origin);
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
        const normalized = typeof value === "string" ? value.trim() : value;

        if (normalized === "" || normalized == null) {
            continue;
        }

        params.append(key, normalized);
    }

    url.search = params.toString();
    return url;
};

const replaceFilterBlock = (blockId, html) => {
    const parser = new DOMParser();
    const nextDocument = parser.parseFromString(html, "text/html");
    const selector = `[data-filter-block="${escapeSelector(blockId)}"]`;
    const nextBlock = nextDocument.querySelector(selector);
    const currentBlock = document.querySelector(selector);

    if (!nextBlock || !currentBlock) {
        return null;
    }

    currentBlock.outerHTML = nextBlock.outerHTML;

    return document.querySelector(selector);
};

document.addEventListener("submit", async (event) => {
    const form = event.target.closest("form.js-smooth-filter");

    if (!form || (form.method || "GET").toUpperCase() !== "GET") {
        return;
    }

    const blockId = form.dataset.filterBlockTarget;
    const block = blockId
        ? document.querySelector(`[data-filter-block="${escapeSelector(blockId)}"]`)
        : null;

    if (!block) {
        return;
    }

    event.preventDefault();

    const url = buildFilterUrl(form);
    setLoadingState(block, true);

    try {
        const response = await fetch(url.toString(), {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!response.ok) {
            window.location.assign(url.toString());
            return;
        }

        const html = await response.text();
        const nextBlock = replaceFilterBlock(blockId, html);

        if (!nextBlock) {
            window.location.assign(url.toString());
            return;
        }

        hydrateFilterBlock(nextBlock);

        nextBlock.classList.add("is-loaded");
        window.setTimeout(() => {
            nextBlock.classList.remove("is-loaded");
        }, 420);

        window.history.pushState(
            {
                smoothFilter: true,
                url: url.toString(),
            },
            "",
            url.toString(),
        );

        scrollToBlock(nextBlock.closest("[data-pagination-scroll]") || nextBlock);
    } catch (error) {
        window.location.assign(url.toString());
    } finally {
        const refreshedBlock = document.querySelector(
            `[data-filter-block="${escapeSelector(blockId)}"]`,
        );

        if (refreshedBlock) {
            setLoadingState(refreshedBlock, false);
        }
    }
});

hydrateFilterBlock(document);

window.addEventListener("popstate", (event) => {
    if (event.state && event.state.smoothFilter) {
        window.location.reload();
    }
});
