import { showMessage } from "./utils";

const checkoutPageEl = document.querySelector(".checkout-page");
const PAYMENT_METHOD_STORAGE_KEY = "checkout-payment-method";

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

    const downloadQrEl = checkoutPageEl.querySelector(".download-qr");
    const qrImgEl = checkoutPageEl.querySelector("#vietqr-img");

    if (downloadQrEl && qrImgEl) {
        downloadQrEl.addEventListener("click", async (e) => {
            e.preventDefault();

            const qrUrl = qrImgEl.src;
            const a = document.createElement("a");

            a.href = await toDataUrl(qrUrl);
            a.download = `qr-${Date.now()}.png`;

            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    }

    const expireObj = new Date(paymentDate);
    expireObj.setTime(
        expireObj.getTimezoneOffset() * 60 * 1000 + expireObj.getTime(),
    );
    const expire = expireObj.getTime() + checkoutCountdown * 60 * 1000;
    const countdownEl = checkoutPageEl.querySelector(".countdown");

    if (countdownEl) {
        const calculatorTimer = () => {
            const d = new Date();
            d.setTime(d.getTimezoneOffset() * 60 * 1000 + d.getTime());
            const now = d.getTime();
            const distance = expire - now;

            if (distance <= 0) {
                return;
            }

            const minutes = Math.floor(
                (distance % (1000 * 60 * 60)) / (1000 * 60),
            );
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

    const csrfToken =
        document.head.querySelector(`[name="csrf_token"]`).content;
    const locale = document.documentElement.lang || "vi";
    const controller = new AbortController();
    const couponsForms = [...checkoutPageEl.querySelectorAll(".coupon-form")];
    const couponUsages = [...checkoutPageEl.querySelectorAll(".coupon-usage")];
    const discountValueEl = checkoutPageEl.querySelector(".discount-value");
    const totalValueList = checkoutPageEl.querySelectorAll(".total_value");
    let qrUrl = qrImgEl ? qrImgEl.src : "";

    const couponSourceForm = couponsForms[0];
    const msg = couponSourceForm
        ? {
              couponApplySuccess:
                  couponSourceForm.dataset.msgCouponApplySuccess,
              couponRemoveSuccess:
                  couponSourceForm.dataset.msgCouponRemoveSuccess,
              couponRemoveFailed:
                  couponSourceForm.dataset.msgCouponRemoveFailed,
              couponRequired: couponSourceForm.dataset.msgCouponRequired,
          }
        : null;

    const syncCouponState = ({
        couponCode = "",
        total = null,
        discount = null,
        applied = false,
    }) => {
        couponsForms.forEach((form) => {
            form.reset();
            form.classList.toggle("d-none", applied);
            const errorEl = form.querySelector(".error");
            if (errorEl) {
                errorEl.innerText = "";
            }
        });

        couponUsages.forEach((usage) => {
            usage.classList.toggle("d-none", !applied);
            const couponValueEl = usage.querySelector(".coupon-value");
            if (couponValueEl && couponCode) {
                couponValueEl.innerText = couponCode;
            }
        });

        if (discountValueEl && discount !== null) {
            discountValueEl.innerText = "-" + discount.toLocaleString() + " đ";
        }

        if (total !== null) {
            totalValueList.forEach((el) => {
                el.innerText = total.toLocaleString() + " đ";
            });
        }

        if (qrImgEl && total !== null && qrUrl) {
            qrUrl = qrUrl.replace(/amount=(\d+)/, "amount=" + total);
            qrImgEl.src = qrUrl;
        }
    };

    if (couponSourceForm && msg) {
        couponsForms.forEach((couponsForm) => {
            couponsForm.addEventListener("submit", (e) => {
                e.preventDefault();

                const couponsEl = couponsForm.querySelector("input[name='coupon_code']");
                const fieldset = couponsEl?.closest("fieldset");
                const coupon = couponsEl?.value?.trim() || "";
                const errorEl = couponsForm.querySelector(".error");

                if (errorEl) {
                    errorEl.innerText = "";
                }

                if (!coupon) {
                    if (errorEl) {
                        errorEl.innerText = msg.couponRequired;
                    }
                    couponsEl?.focus();
                    return;
                }

                const verifyCoupon = async () => {
                    try {
                        if (fieldset) {
                            fieldset.disabled = true;
                        }

                        const response = await fetch(
                            `/${locale}/tai-khoan/coupons/verify`,
                            {
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
                            },
                        );

                        const { success, errors, data } =
                            await response.json();

                        if (!success) {
                            throw new Error(errors);
                        }

                        showMessage(msg.couponApplySuccess, "success");
                        syncCouponState({
                            couponCode: coupon,
                            discount: data.discount,
                            total: data.total_after_discount,
                            applied: true,
                        });
                    } catch (error) {
                        if (errorEl) {
                            errorEl.innerText = error.message;
                        }
                    } finally {
                        if (fieldset) {
                            fieldset.disabled = false;
                        }
                    }
                };

                verifyCoupon();
            });
        });

        couponUsages.forEach((couponUsage) => {
            const removeCouponEl = couponUsage.querySelector(".js-remove-coupon");

            if (!removeCouponEl) {
                return;
            }

            removeCouponEl.addEventListener("click", () => {
                controller.abort();

                const removeCoupon = async () => {
                    const response = await fetch(
                        `/${locale}/tai-khoan/coupons/remove`,
                        {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": csrfToken,
                                "Content-Type": "application/json",
                                Accept: "application/json",
                            },
                            body: JSON.stringify({
                                orderId,
                            }),
                        },
                    );

                    const { success, data } = await response.json();

                    if (!success) {
                        return showMessage(msg.couponRemoveFailed, "error");
                    }

                    showMessage(msg.couponRemoveSuccess, "success");
                    syncCouponState({
                        total: data.total,
                        discount: 0,
                        applied: false,
                    });
                };

                removeCoupon();
            });
        });
    }

    const paymentSections = {
        bank: document.getElementById("payment-bank"),
        vnpay: document.getElementById("payment-vnpay"),
        momo: document.getElementById("payment-momo"),
    };

    const setPaymentMethod = (method) => {
        Object.entries(paymentSections).forEach(([key, section]) => {
            if (!section) {
                return;
            }

            section.classList.toggle("d-none", key !== method);
        });

        checkoutPageEl.querySelectorAll(".payment-method").forEach((input) => {
            input.checked = input.value === method;
        });

        sessionStorage.setItem(PAYMENT_METHOD_STORAGE_KEY, method);
    };

    const initialPaymentMethod =
        sessionStorage.getItem(PAYMENT_METHOD_STORAGE_KEY) || "bank";
    setPaymentMethod(
        paymentSections[initialPaymentMethod] ? initialPaymentMethod : "bank",
    );

    document.querySelectorAll(".payment-method").forEach((el) => {
        el.addEventListener("change", function () {
            setPaymentMethod(this.value);
        });
    });

    document.addEventListener("submit", function (e) {
        const form = e.target.closest(".js-cancel-order");
        if (!form) return;

        const msgText = form.dataset.confirm;
        if (!confirm(msgText)) {
            e.preventDefault();
        }
    });
}
