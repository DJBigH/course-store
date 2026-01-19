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

    const expireObj = new Date(paymentDate);
    expireObj.setTime(
        expireObj.getTimezoneOffset() * 60 * 1000 + expireObj.getTime(),
    );
    const expire = expireObj.getTime() + checkoutCountdown * 60 * 1000;
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

    //Coupons

    const couponsForm = checkoutPageEl.querySelector(".coupon-form");
    if (couponsForm) {
        couponsForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const couponsEl = couponsForm.querySelector("input");
            const fieldset = couponsEl.closest("fieldset");
            const coupon = couponsEl.value;
            const error = couponsForm.querySelector(".error");
            const csrfToken =
                document.head.querySelector(`[name="csrf_token"]`).content;
            error.innerText = "";
            if (!coupon) {
                error.innerText = "Vui lòng nhập mã giảm giá";
                couponsEl.focus();
                return;
            }

            const verifyCoupon = async () => {
                fieldset.disabled = true;
                //Call Api
                const response = await fetch(`/tai-khoan/coupons/verify`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        coupon,
                    }),
                });
                const data = await response.json();
                fieldset.disabled = false;
                console.log(data);
            };
            verifyCoupon();
        });
    }
}
