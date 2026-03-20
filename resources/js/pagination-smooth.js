const paginationContainerSelector = "[data-pagination-container]";

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

const scrollToPaginationBlock = (element) => {
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

const setLoadingState = (container, isLoading) => {
    container.classList.toggle("is-loading", isLoading);
    container.setAttribute("aria-busy", isLoading ? "true" : "false");
};

const replacePaginationContainer = (containerId, html) => {
    const parser = new DOMParser();
    const nextDocument = parser.parseFromString(html, "text/html");
    const nextContainer = nextDocument.querySelector(
        `[data-pagination-container="${escapeSelector(containerId)}"]`,
    );

    if (!nextContainer) {
        return null;
    }

    const currentContainer = document.querySelector(
        `[data-pagination-container="${escapeSelector(containerId)}"]`,
    );

    if (!currentContainer) {
        return null;
    }

    currentContainer.outerHTML = nextContainer.outerHTML;

    return document.querySelector(
        `[data-pagination-container="${escapeSelector(containerId)}"]`,
    );
};

document.addEventListener("click", async (event) => {
    const link = event.target.closest(".pagination a[href], .page-link-compact[href]");

    if (!link) {
        return;
    }

    if (
        event.defaultPrevented ||
        event.button !== 0 ||
        link.target === "_blank" ||
        event.metaKey ||
        event.ctrlKey ||
        event.shiftKey ||
        event.altKey
    ) {
        return;
    }

    const container = link.closest(paginationContainerSelector);

    if (!container || !container.dataset.paginationContainer) {
        return;
    }

    const containerId = container.dataset.paginationContainer;

    event.preventDefault();
    setLoadingState(container, true);

    try {
        const response = await fetch(link.href, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!response.ok) {
            window.location.assign(link.href);
            return;
        }

        const html = await response.text();
        const nextContainer = replacePaginationContainer(containerId, html);

        if (!nextContainer) {
            window.location.assign(link.href);
            return;
        }

        const scrollTarget =
            nextContainer.closest("[data-pagination-scroll]") || nextContainer;

        nextContainer.classList.add("is-loaded");
        window.setTimeout(() => {
            nextContainer.classList.remove("is-loaded");
        }, 420);

        window.history.pushState(
            {
                pagination: true,
                url: link.href,
            },
            "",
            link.href,
        );

        scrollToPaginationBlock(scrollTarget);
    } catch (error) {
        window.location.assign(link.href);
    } finally {
        const refreshedContainer = document.querySelector(
            `[data-pagination-container="${escapeSelector(containerId)}"]`,
        );

        if (refreshedContainer) {
            setLoadingState(refreshedContainer, false);
        }
    }
});

window.addEventListener("popstate", (event) => {
    if (event.state && event.state.pagination) {
        window.location.reload();
    }
});
