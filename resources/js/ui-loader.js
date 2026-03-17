const loader = document.getElementById("page-loader");

if (loader) {
    let hidden = false;

    const hideLoader = () => {
        if (hidden) {
            return;
        }

        hidden = true;
        loader.classList.add("is-hidden");

        window.setTimeout(() => {
            loader.remove();
        }, 320);
    };

    const queueHide = () => {
        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(hideLoader);
        });
    };

    if (document.readyState === "interactive" || document.readyState === "complete") {
        queueHide();
    } else {
        document.addEventListener("DOMContentLoaded", queueHide, { once: true });
    }

    window.addEventListener("load", hideLoader, { once: true });
    window.addEventListener("pageshow", hideLoader, { once: true });
    window.setTimeout(hideLoader, 900);
}
