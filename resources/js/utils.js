import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";
export const showMessage = (message, type = "success") => {
    const colors = {
        success: "linear-gradient(to right, #00b09b, #96c93d)",
        error: "linear-gradient(to right, #ff416c, #ff4b2b)",
        warning: "linear-gradient(to right, #f7971e, #ffd200)",
        info: "linear-gradient(to right, #2193b0, #6dd5ed)",
    };
    Toastify({
        text: message,
        duration: 3000,
        destination: "https://github.com/apvarun/toastify-js",
        newWindow: true,
        close: true,
        gravity: "top", // `top` or `bottom`
        position: "right", // `left`, `center` or `right`
        stopOnFocus: true, // Prevents dismissing of toast on hover
        style: {
            background: colors[type] || colors.success,
        },
    }).showToast();
};
