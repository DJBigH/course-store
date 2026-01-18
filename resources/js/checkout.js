const checkoutPageEl = document.querySelector(".checkout-page");

if (checkoutPageEl) {
    checkoutPageEl.querySelectorAll(".bank-copy").forEach((icon) => {
        icon.addEventListener("click", (e) => {
            e.preventDefault();

            const text = icon.previousElementSibling.dataset.copy;

            navigator.clipboard.writeText(text).then(() => {
                icon.classList.replace("fa-regular", "fa-solid");

                setTimeout(() => {
                    icon.classList.replace("fa-solid", "fa-regular");
                }, 1000);
            });
        });
    });

    async function toDataUrl(url) {
        const res = await fetch(url);
        const blob = await res.blob();
        return URL.createObjectURL(blob);
    }

    if (checkoutPageEl) {
        const downloadQrEl = checkoutPageEl.querySelector(".download-qr");
        const qrImg = checkoutPageEl.querySelector("#vietqr-img");

        if (downloadQrEl && qrImg) {
            downloadQrEl.addEventListener("click", async (e) => {
                e.preventDefault();

                const qrUrl = qrImg.src;
                const a = document.createElement("a");

                a.href = await toDataUrl(qrUrl);
                a.download = `qr-${Date.now()}.png`;

                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        }
    }

    const expire = new Date("2026-01-18 09:30:00").getTime();
    const countdownEl = checkoutPageEl.querySelector(".countdown");
    const calculatorTimer = () => {
        const d = new Date();
        d.setTime(d.getTimezoneOffset() * 60 * 1000 + d.getTime());
        const now = d.getTime();
        const distance = expire - now;
        if (distance <= 0) {
            return;
        }
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        countdownEl.firstElementChild.innerText = `${
            minutes < 10 ? "0" + minutes : minutes
        }`;
        countdownEl.lastElementChild.innerText = `${
            seconds < 10 ? "0" + seconds : seconds
        }`;
    };
    
    setInterval(calculatorTimer, 1000);
}
