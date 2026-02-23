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

        const { errors, success } = await response.json();

        if (errors) {
            showErrors(errors);
        } else {
            if (success) {
                showMessage(msgSuccess, "success");
                setTimeout(() => window.location.reload(), 300);
            } else {
                showMessage(msgError, "error");
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
    profileForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const formData = Object.fromEntries(new FormData(e.target));
        const csrfToken =
            document.head.querySelector(`[name="csrf_token"]`).content;
        updateProfile(formData, csrfToken);
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
