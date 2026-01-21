import { showMessage } from "./utils";
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
    const couponUsage = checkoutPageEl.querySelector(".coupon-usage");
    const csrfToken =
        document.head.querySelector(`[name="csrf_token"]`).content;
    const discountValueEl = checkoutPageEl.querySelector(".discount-value");
    const totalValueList = checkoutPageEl.querySelectorAll(`.total_value`);
    const qrImgEl = checkoutPageEl.querySelector(".qr-image");
    let isPolling = true;
    let qrUrl = qrImgEl.src;
    if (couponsForm && couponUsage) {
        couponsForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const couponsEl = couponsForm.querySelector("input");
            const fieldset = couponsEl.closest("fieldset");
            const coupon = couponsEl.value;
            const error = couponsForm.querySelector(".error");
            error.innerText = "";
            // if (!coupon) {
            //     error.innerText = "Vui lòng nhập mã giảm giá";
            //     couponsEl.focus();
            //     return;
            // }

            const verifyCoupon = async () => {
                try {
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
                            orderId,
                        }),
                    });
                    const { success, errors, data } = await response.json();
                    if (!success) {
                        throw new Error(errors);
                    }
                    showMessage("Áp mã giảm giá thành công");
                    couponsForm.reset();
                    couponsForm.classList.add("d-none");
                    couponUsage.classList.remove("d-none");

                    couponUsage.querySelector(".coupon-value").innerText =
                        coupon;

                    discountValueEl.innerText =
                        "-" + data.discount.toLocaleString() + " đ";
                    totalValueList.forEach((el) => {
                        el.innerText =
                            data.total_after_discount.toLocaleString() + " đ";
                    });
                    qrUrl = qrUrl.replace(
                        /amount=(\d+)/,
                        "amount=" + data.total_after_discount,
                    );
                    qrImgEl.src = qrUrl;
                    if (isPolling) {
                        pollingCoupon();
                    }
                } catch (errors) {
                    error.innerText = errors.message;
                } finally {
                    fieldset.disabled = false;
                }
            };
            verifyCoupon();
            const pollingCoupon = async () => {
                const response = await fetch(`/tai-khoan/coupons/polling`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        coupon,
                        orderId,
                    }),
                });
                const data = await response.json();
                if (data) {
                    console.log(data);
                    if (isPolling) {
                        pollingCoupon();
                    }
                }
            };
        });
        const removeCouponEl = couponUsage.querySelector(".js-remove-coupon");
        removeCouponEl.addEventListener("click", () => {
            const removeCoupon = async () => {
                const response = await fetch(`/tai-khoan/coupons/remove`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        orderId,
                    }),
                });
                const { success, data } = await response.json();
                if (!success) {
                    return showMessage("Xóa mã giảm giá không thành công");
                }
                couponUsage.classList.add("d-none");
                couponsForm.classList.remove("d-none");
                showMessage("Xóa mã giảm giá thành công");

                discountValueEl.innerText = "-0 đ";
                totalValueList.forEach((el) => {
                    el.innerText = data.total.toLocaleString() + " đ";
                });
                qrUrl = qrUrl.replace(/amount=(\d+)/, "amount=" + data.total);
                qrImgEl.src = qrUrl;
                isPolling = false;
            };
            removeCoupon();
        });
    }
}
