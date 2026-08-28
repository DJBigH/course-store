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

const debounce = (func, wait) => {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
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

const handleInput = debounce((event) => {
    const input = event.target;
    const form = input.closest("form.js-smooth-filter");
    if (form && input.name === "keyword") {
        form.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
    }
}, 500);

document.addEventListener("input", handleInput);

const initAutocomplete = () => {
    const input = document.getElementById("header-search-input");
    const suggestions = document.getElementById("search-suggestions");

    if (!input || !suggestions) {
        return;
    }

    const locale = document.documentElement.lang || "vi";
    const suggestUrl = `/${locale}/data/search/suggest`;

    const fetchSuggestions = debounce(async (query) => {
        if (query.length < 2) {
            suggestions.innerHTML = "";
            suggestions.classList.remove("show");
            return;
        }

        try {
            const response = await fetch(`${suggestUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            if (!response.ok) throw new Error("Network response was not ok");

            const data = await response.json();
            renderSuggestions(data);
        } catch (error) {
            console.error("Autocomplete error:", error);
        }
    }, 300);

    const renderSuggestions = (data) => {
        if (!data.length) {
            suggestions.innerHTML = "";
            suggestions.classList.remove("show");
            return;
        }

        let html = "";
        data.forEach((item) => {
            const priceHtml = item.sale_price
                ? `<span class="price-old">${item.price}${item.currency}</span><span class="price-new">${item.sale_price}${item.currency}</span>`
                : `<span class="price-new">${item.price}${item.currency}</span>`;

            html += `
                <a href="${item.url}" class="suggestion-item">
                    <img src="${item.thumbnail}" alt="${item.name}" class="suggestion-thumb">
                    <div class="suggestion-info">
                        <div class="suggestion-name">${item.name}</div>
                        <div class="suggestion-price">${priceHtml}</div>
                    </div>
                </a>
            `;
        });

        suggestions.innerHTML = html;
        suggestions.classList.add("show");
    };

    input.addEventListener("input", (e) => fetchSuggestions(e.target.value));

    // Close suggestions when clicking outside
    document.addEventListener("click", (e) => {
        if (!input.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.classList.remove("show");
        }
    });

    // Re-open if input focused and has value
    input.addEventListener("focus", () => {
        if (suggestions.children.length > 0) {
            suggestions.classList.add("show");
        }
    });
};

hydrateFilterBlock(document);
initAutocomplete();

window.addEventListener("popstate", (event) => {
    if (event.state && event.state.smoothFilter) {
        window.location.reload();
    }
});

