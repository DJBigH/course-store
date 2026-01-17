import "select2/dist/css/select2.min.css";
import $ from "jquery";
import select2 from "select2";
select2();
import { showMessage } from "./message";
const profileBtn = document.querySelector(".js-profile-btn");
if (profileBtn) {
    let status = "table";

    const renderButton = () => {
        profileBtn.innerHTML =
            status === "table" ? "Cập nhập thông tin" : "Hủy";
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
if (profileForm) {
    const updateProfile = async (formData, token) => {
        const response = await fetch(`/tai-khoan/thong-tin`, {
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
            const msgSuccess = `Cập nhật thông tin thành công`;
            const msgError = `Không thể cập nhật vào lúc này`;
            if (success) {
                showMessage(msgSuccess, "success");
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            } else {
                showMessage(msgError, "error");
            }
        }
    };
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
