const localeLinks = Array.from(document.querySelectorAll("[data-locale-link]"));

if (localeLinks.length) {
    const prefetchedUrls = new Set();
    let transitionOverlay = null;
    const loadingMessage =
        document.body?.dataset.localeSwitchLoading || "Loading content...";

    const prefetchLocaleUrl = (url) => {
        if (!url || prefetchedUrls.has(url)) {
            return;
        }

        prefetchedUrls.add(url);

        const link = document.createElement("link");
        link.rel = "prefetch";
        link.as = "document";
        link.href = url;
        document.head.appendChild(link);
    };

    const showTransitionOverlay = (label) => {
        if (!transitionOverlay) {
            transitionOverlay = document.createElement("div");
            transitionOverlay.className = "page-loader page-loader--locale";
            transitionOverlay.setAttribute("aria-hidden", "true");
            transitionOverlay.innerHTML = `
                <div class="page-loader__panel">
                    <div class="page-loader__brand">
                        <i class="fa-solid fa-language"></i>
                    </div>
                    <p class="page-loader__title">BigK Udemy</p>
                    <p class="page-loader__subtitle"></p>
                    <div class="page-loader__track">
                        <div class="page-loader__bar"></div>
                    </div>
                    <div class="page-loader__dots" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            `;
        }

        const subtitle = transitionOverlay.querySelector(".page-loader__subtitle");
        if (subtitle) {
            subtitle.textContent = label ? `${loadingMessage} ${label}` : loadingMessage;
        }

        if (!transitionOverlay.isConnected) {
            document.body.appendChild(transitionOverlay);
        }

        requestAnimationFrame(() => {
            transitionOverlay.classList.remove("is-hidden");
        });
    };

    localeLinks.forEach((link) => {
        const prefetch = () => prefetchLocaleUrl(link.href);
        link.addEventListener("mouseenter", prefetch, { once: true });
        link.addEventListener("focus", prefetch, { once: true });
        link.addEventListener("touchstart", prefetch, { once: true, passive: true });

        link.addEventListener("click", (event) => {
            const href = link.href;
            if (!href || href === window.location.href) {
                event.preventDefault();
                return;
            }

            localeLinks.forEach((item) => item.classList.remove("is-loading"));
            link.classList.add("is-loading");
            showTransitionOverlay(link.dataset.localeLabel);
        });
    });
}
