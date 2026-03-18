import "select2/dist/css/select2.min.css";
import $ from "jquery";
import select2 from "select2";
select2();
import { showMessage } from "./message";
import html2canvas from "html2canvas-pro";
import { jsPDF } from "jspdf";
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
        var indexActive = null;
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

//Xử lý profile
const profileForm = document.querySelector("form.js-profile");
const locale = document.documentElement.lang || "vi";

if (profileForm) {
    const msgSuccess = profileForm.dataset.msgSuccess;
    const msgError = profileForm.dataset.msgError;
    const profileTable = document.querySelector(".table-profile");

    const updateProfile = async (formData, token) => {
        const response = await fetch(`/${locale}/tai-khoan/thong-tin`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": token,
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify(formData),
        });

        const { errors, success, message, student } = await response.json();

        if (errors) {
            showErrors(errors);
        } else {
            if (success) {
                updateTable(student);
                showMessage(message || msgSuccess, "success");
            } else {
                showMessage(message || msgError, "error");
            }
        }
    };
    // phần còn lại giữ nguyên
    const showErrors = (errors) => {
        const errorList = profileForm.querySelectorAll(".error");
        errorList.forEach((error) => {
            error.innerText = "";
        });
        Object.keys(errors).forEach((key) => {
            const errorEl = profileForm.querySelector(`.error-${key}`);
            errorEl.innerText = errors[key];
        });
    };
    const updateTable = (student) => {
        if (!profileTable || !student) return;

        const rows = profileTable.querySelectorAll("tbody tr td");
        if (rows[0]) rows[0].innerText = student.name || "";
        if (rows[1]) rows[1].innerText = student.email || "";
        if (rows[2]) rows[2].innerText = student.phone || "";
        if (rows[3]) rows[3].innerText = student.address || "Chưa cập nhật";
    };
    profileForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const formData = Object.fromEntries(new FormData(e.target));
        const csrfToken =
            document.head.querySelector(`[name="csrf_token"]`).content;
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
                errorEl.innerText = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
            }
        });
    };

    changePasswordForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        showPasswordErrors({});
        if (alertBox) {
            alertBox.innerHTML = "";
        }

        const submitButton = changePasswordForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        try {
            const response = await fetch(window.location.href, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": document.head.querySelector(`[name="csrf_token"]`).content,
                },
                body: new FormData(changePasswordForm),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                showPasswordErrors(data.errors || {});
                showAccountAlert(data.message || msgError, "danger");
                return;
            }

            changePasswordForm.reset();
            showAccountAlert(data.message || msgSuccess, "success");
        } catch (error) {
            showAccountAlert(msgError, "danger");
        } finally {
            submitButton.disabled = false;
        }
    });
}

//Select2
$(".js-select2").select2();

//Đơn hàng
const downloadBtn = document.querySelector(".download-btn");

if (downloadBtn) {
    downloadBtn.addEventListener("click", async () => {
        const orderDetailEl = document.querySelector(".order-detail");

        if (!orderDetailEl) return;

        const canvas = await html2canvas(orderDetailEl, {
            scale: 2, // nét hơn
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
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        pdf.addImage(image, "PNG", 0, 0, pdfWidth, pdfHeight);

        pdf.save(`order-${Date.now()}.pdf`);
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
