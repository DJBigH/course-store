const initCountdowns = () => {
    const timerEls = document.querySelectorAll(".countdown-timer");
    if (!timerEls.length) return;

    timerEls.forEach((timerEl) => {
        const targetDate = new Date(timerEl.dataset.time).getTime();
        if (isNaN(targetDate)) return;

        const update = () => {
            const now = new Date().getTime();
            const diff = targetDate - now;

            if (diff <= 0) {
                if (timerEl.closest(".flash-sale-timer-wrapper")) {
                    window.location.reload();
                }
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            const d = timerEl.querySelector(".days");
            const h = timerEl.querySelector(".hours");
            const m = timerEl.querySelector(".minutes");
            const s = timerEl.querySelector(".seconds");

            if (d) d.innerText = String(days).padStart(2, "0");
            if (h) h.innerText = String(hours).padStart(2, "0");
            if (m) m.innerText = String(minutes).padStart(2, "0");
            if (s) s.innerText = String(seconds).padStart(2, "0");
        };

        update();
        setInterval(update, 1000);
    });
};

document.addEventListener("DOMContentLoaded", initCountdowns);

// Re-init for AJAX content if needed
window.initGlobalCountdowns = initCountdowns;
