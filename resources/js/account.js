import "select2/dist/css/select2.min.css";
import $ from "jquery";
import select2 from "select2";
import { showMessage } from "./message";
import html2canvas from "html2canvas-pro";
import { jsPDF } from "jspdf";

select2();

const profileBtn = document.querySelector(".js-profile-btn");

if (profileBtn) {
    let status = "table";

    const renderButton = () => {
        profileBtn.innerHTML =
            status === "table"
                ? window.i18n.profile_edit
                : window.i18n.profile_cancel;

        if (status === "form") {
            profileBtn.classList.replace("btn-warning", "btn-danger");
        } else {
            profileBtn.classList.replace("btn-danger", "btn-warning");
        }
    };

    const renderTableForm = () => {
        const profileList = document.querySelectorAll(".js-profile");
        let indexActive = null;

        profileList.forEach((profile, index) => {
            if (profile.classList.contains("active")) {
                indexActive = index;
            }
        });

        profileList[indexActive].classList.remove("active");

        if (indexActive === 0) {
            profileList[1].classList.add("active");
        } else {
            profileList[0].classList.add("active");
        }
    };

    profileBtn.addEventListener("click", (e) => {
        e.preventDefault();
        status = status === "table" ? "form" : "table";
        renderButton();
        renderTableForm();
    });
}

const profileForm = document.querySelector("form.js-profile");
const locale = document.documentElement.lang || "vi";

if (profileForm) {
    const msgSuccess = profileForm.dataset.msgSuccess;
    const msgError = profileForm.dataset.msgError;
    const profileTable = document.querySelector(".table-profile");

    const showErrors = (errors) => {
        const errorList = profileForm.querySelectorAll(".error");

        errorList.forEach((error) => {
            error.innerText = "";
        });

        Object.keys(errors).forEach((key) => {
            const errorEl = profileForm.querySelector(`.error-${key}`);

            if (errorEl) {
                errorEl.innerText = errors[key];
            }
        });
    };

    const updateTable = (student) => {
        if (!profileTable || !student) return;

        const rows = profileTable.querySelectorAll("tbody tr td");

        if (rows[0]) rows[0].innerText = student.name || "";
        if (rows[1]) rows[1].innerText = student.email || "";
        if (rows[2]) rows[2].innerText = student.phone || "";
        if (rows[3]) rows[3].innerText = student.address || "Chua cap nhat";
    };

    const updateProfile = async (formData, token) => {
        const response = await fetch(`/${locale}/tai-khoan/thong-tin`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": token,
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify(formData),
        });

        const { errors, success, message, student, redirect } =
            await response.json();

        if (response.status === 423 && redirect) {
            window.location.href = redirect;
            return;
        }

        if (errors) {
            showErrors(errors);
            return;
        }

        if (success) {
            updateTable(student);
            showMessage(message || msgSuccess, "success");
        } else {
            showMessage(message || msgError, "error");
        }
    };

    profileForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const formData = Object.fromEntries(new FormData(e.target));
        const csrfToken =
            document.head.querySelector('[name="csrf_token"]').content;

        updateProfile(formData, csrfToken);
    });
}

const changePasswordForm = document.querySelector("form.js-change-password");

if (changePasswordForm) {
    const msgSuccess = changePasswordForm.dataset.msgSuccess;
    const msgError = changePasswordForm.dataset.msgError;
    const alertBox = document.querySelector("[data-account-alerts]");

    const showAccountAlert = (message, type = "success") => {
        if (!alertBox) return;

        alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    };

    const showPasswordErrors = (errors) => {
        changePasswordForm.querySelectorAll(".error").forEach((error) => {
            error.innerText = "";
        });

        Object.keys(errors || {}).forEach((key) => {
            const errorEl = changePasswordForm.querySelector(`.error-${key}`);

            if (errorEl) {
                errorEl.innerText = Array.isArray(errors[key])
                    ? errors[key][0]
                    : errors[key];
            }
        });
    };

    changePasswordForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        showPasswordErrors({});

        if (alertBox) {
            alertBox.innerHTML = "";
        }

        const submitButton = changePasswordForm.querySelector(
            'button[type="submit"]'
        );

        submitButton.disabled = true;

        try {
            const response = await fetch(window.location.href, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": document.head.querySelector(
                        '[name="csrf_token"]'
                    ).content,
                },
                body: new FormData(changePasswordForm),
            });

            const data = await response.json().catch(() => ({}));

            if (response.status === 423 && data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            if (!response.ok) {
                showPasswordErrors(data.errors || {});
                showAccountAlert(data.message || msgError, "danger");
                return;
            }

            showAccountAlert(data.message || msgSuccess, "success");

            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 900);
                return;
            }

            changePasswordForm.reset();
        } catch (error) {
            showAccountAlert(msgError, "danger");
        } finally {
            submitButton.disabled = false;
        }
    });
}

$(".js-select2").select2();

const downloadBtn = document.querySelector(".download-btn");

if (downloadBtn) {
    downloadBtn.addEventListener("click", async () => {
        const orderDetailEl = document.querySelector(".order-detail");

        if (!orderDetailEl) return;

        const root = document.documentElement;
        const previousTheme = root.getAttribute("data-theme");
        const previousColorScheme = root.style.colorScheme;

        downloadBtn.disabled = true;
        root.setAttribute("data-theme", "light");
        root.style.colorScheme = "light";
        orderDetailEl.classList.add("pdf-export");

        try {
            await new Promise((resolve) => requestAnimationFrame(resolve));

            const canvas = await html2canvas(orderDetailEl, {
                scale: 2,
                useCORS: true,
                backgroundColor: "#ffffff",
            });

            const image = canvas.toDataURL("image/png");
            const pdf = new jsPDF({
                orientation: "portrait",
                unit: "mm",
                format: "a4",
            });
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const imageHeight = (canvas.height * pdfWidth) / canvas.width;
            let remainingHeight = imageHeight;
            let positionY = 0;

            pdf.addImage(image, "PNG", 0, positionY, pdfWidth, imageHeight);
            remainingHeight -= pageHeight;

            while (remainingHeight > 0) {
                positionY -= pageHeight;
                pdf.addPage();
                pdf.addImage(image, "PNG", 0, positionY, pdfWidth, imageHeight);
                remainingHeight -= pageHeight;
            }

            pdf.save(`order-${Date.now()}.pdf`);
        } finally {
            orderDetailEl.classList.remove("pdf-export");
            if (previousTheme === null) {
                root.removeAttribute("data-theme");
            } else {
                root.setAttribute("data-theme", previousTheme);
            }
            root.style.colorScheme = previousColorScheme;
            downloadBtn.disabled = false;
        }
    });
}

document.addEventListener("click", function (e) {
    const el = e.target.closest(".js-logout");

    if (!el) return;

    e.preventDefault();

    if (confirm(el.dataset.confirm)) {
        el.closest("form").submit();
    }
});
