const alertIcons = {
    success: "fa-solid fa-circle-check",
    danger: "fa-solid fa-circle-exclamation",
};

const createAlert = (type, title, message) => {
    const wrapper = document.createElement("div");
    wrapper.className = `alert alert-${type} d-flex align-items-center gap-2 mb-3`;
    wrapper.setAttribute("role", "alert");
    wrapper.innerHTML = `
        <i class="${alertIcons[type] || alertIcons.danger}"></i>
        <span>${title ? `<strong>${title}</strong> ` : ""}${message || ""}</span>
    `;
    return wrapper;
};

const getAlertContainer = (form) =>
    form.closest(".sign-in, .sign-up, .card")?.querySelector("[data-auth-alerts]");

const clearFieldErrors = (form) => {
    form.querySelectorAll(".field-error.is-dynamic").forEach((error) => error.remove());
};

const clearAlerts = (form) => {
    const alerts = getAlertContainer(form);
    if (alerts) {
        alerts.innerHTML = "";
    }
};

const setAlert = (form, type, message) => {
    const alerts = getAlertContainer(form);
    if (!alerts) {
        return;
    }

    alerts.innerHTML = "";
    alerts.appendChild(
        createAlert(
            type,
            type === "success" ? form.dataset.successTitle : form.dataset.errorTitle,
            message
        )
    );
};

const appendFieldErrors = (form, errors = {}) => {
    Object.entries(errors).forEach(([field, messages]) => {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input || !messages?.length) {
            return;
        }

        const error = document.createElement("span");
        error.className = "text-start text-danger field-error is-dynamic";
        error.textContent = messages[0];
        input.insertAdjacentElement("afterend", error);
    });
};

const getValidationFallbackMessage = (form) => {
    const title = form.dataset.errorTitle || "";
    const container = getAlertContainer(form);
    const serverAlertText = container?.querySelector(".alert-danger span")?.textContent?.trim();

    if (serverAlertText) {
        return title && serverAlertText.startsWith(title)
            ? serverAlertText.slice(title.length).trim()
            : serverAlertText;
    }

    return "Vui lòng kiểm tra lại dữ liệu nhập vào.";
};

const setSubmittingState = (form, isSubmitting) => {
    const submitButton = form.querySelector('button[type="submit"]');
    if (!submitButton) {
        return;
    }

    if (!submitButton.dataset.submitLabel) {
        submitButton.dataset.submitLabel = submitButton.innerHTML;
    }

    submitButton.disabled = isSubmitting;
    submitButton.classList.toggle("is-loading", isSubmitting);
    submitButton.innerHTML = isSubmitting ? "Đang xử lý..." : submitButton.dataset.submitLabel;
};

const handleSuccess = (form, data) => {
    if (data.message) {
        setAlert(form, "success", data.message);
    }

    if (form.classList.contains("js-verify-resend-form") && typeof window.startVerifyCountdown === "function") {
        window.startVerifyCountdown();
    }

    if (data.redirect) {
        window.location.href = data.redirect;
    }
};

const submitAuthForm = async (form) => {
    if (form.dataset.submitting === "1") {
        return;
    }

    form.dataset.submitting = "1";
    clearFieldErrors(form);
    clearAlerts(form);
    setSubmittingState(form, true);

    try {
        const response = await fetch(form.action || window.location.href, {
            method: form.method || "POST",
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
            },
            body: new FormData(form),
            credentials: "same-origin",
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            appendFieldErrors(form, data.errors || {});
            const hasValidationErrors = response.status === 422 && Object.keys(data.errors || {}).length > 0;
            setAlert(
                form,
                "danger",
                hasValidationErrors
                    ? getValidationFallbackMessage(form)
                    : data.message || "Không thể xử lý yêu cầu lúc này."
            );
            return;
        }

        handleSuccess(form, data);
    } catch (error) {
        setAlert(form, "danger", "Không thể kết nối tới máy chủ. Vui lòng thử lại.");
    } finally {
        form.dataset.submitting = "0";
        setSubmittingState(form, false);
    }
};

document.addEventListener("submit", (event) => {
    const form = event.target.closest("form.js-auth-form");
    if (!form) {
        return;
    }

    event.preventDefault();
    submitAuthForm(form);
});
