import "./bootstrap.bundle.min.js";
import "./theme.js";
import "./ui-loader.js";
import "./jquery.min.js";
import "./jquery-migrate-1.2.1.min.js";

window.$ = window.$ || window.jQuery;
window.jQuery = window.jQuery || window.$;

if (document.querySelector("div.burger, div.menu, div.screen")) {
    import("./home.js");
}

if (document.querySelector(".banner-slider")) {
    import("./slick.min.js").then(() => import("./slider-home.js"));
}

if (document.querySelector(".accordion-group .accordion-title")) {
    import("./accordion.js");
}

if (document.querySelector(".nav p") && document.querySelector(".group .title")) {
    import("./tab.js");
}

if (
    document.querySelector(".js-profile-btn, form.js-profile, .js-select2, .download-btn, .js-logout")
) {
    import("./account.js");
}

if (document.querySelector(".checkout-page")) {
    import("./checkout.js");
}
