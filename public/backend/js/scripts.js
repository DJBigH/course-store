/*!
 * Start Bootstrap - SB Admin v7.0.7 (https://startbootstrap.com/template/sb-admin)
 * Copyright 2013-2023 Start Bootstrap
 * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
 */
//
// Scripts
//

window.addEventListener("DOMContentLoaded", (event) => {
    $(".select2").select2();
    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector("#sidebarToggle");
    if (sidebarToggle) {
        const isOverlaySidebarViewport = () => window.innerWidth < 1200;
        const closeSidebarOnOverlayViewport = () => {
            if (!isOverlaySidebarViewport()) {
                return;
            }

            document.body.classList.remove("sb-sidenav-toggled");
            localStorage.setItem("sb|sidebar-toggle", "false");
        };

        // Uncomment Below to persist sidebar toggle between refreshes
        // if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        //     document.body.classList.toggle('sb-sidenav-toggled');
        // }
        sidebarToggle.addEventListener("click", (event) => {
            event.preventDefault();
            document.body.classList.toggle("sb-sidenav-toggled");
            localStorage.setItem(
                "sb|sidebar-toggle",
                document.body.classList.contains("sb-sidenav-toggled"),
            );
        });

        document.addEventListener("click", (event) => {
            if (!isOverlaySidebarViewport() || !document.body.classList.contains("sb-sidenav-toggled")) {
                return;
            }

            const sidebar = document.querySelector("#layoutSidenav_nav");
            const clickedToggle = event.target.closest("#sidebarToggle");

            if (clickedToggle) {
                return;
            }

            if (sidebar && !sidebar.contains(event.target)) {
                closeSidebarOnOverlayViewport();
            }
        });

        window.addEventListener("resize", () => {
            if (!isOverlaySidebarViewport()) {
                document.body.classList.remove("sb-sidenav-toggled");
                localStorage.setItem("sb|sidebar-toggle", "false");
            }
        });
    }

    const tableList = document.querySelector("#datatable");
    const deleteForm = document.querySelector(".delete-form");
    if (tableList) {
        tableList.addEventListener("click", (e) => {
            if (e.target.classList.contains("delete-action")) {
                e.preventDefault();
                Swal.fire({
                    title: "Bạn có chắc chăn muốn xóa",
                    text: "Nếu xóa bạn không thể khôi phục",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Tôi đồng ý",
                }).then((result) => {
                    if (result.isConfirmed) {
                        const action = e.target.href;
                        deleteForm.action = action;
                        deleteForm.submit();
                        Swal.fire({
                            title: "Thành công!",
                            text: "Đã xóa dữ liệu.",
                            icon: "success",
                            confirmButtonText: false,
                        });
                    }
                });
            }
        });
    }
    function getSlug(title) {
        //Đổi chữ hoa thành chữ thường
        let slug = title.toLowerCase();

        //Đổi ký tự có dấu thành không dấu
        slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, "a");
        slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, "e");
        slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, "i");
        slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, "o");
        slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, "u");
        slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, "y");
        slug = slug.replace(/đ/gi, "d");
        //Xóa các ký tự đặt biệt
        slug = slug.replace(
            /\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi,
            "",
        );
        //Đổi khoảng trắng thành ký tự gạch ngang
        slug = slug.replace(/ /gi, "-");
        //Đổi nhiều ký tự gạch ngang liên tiếp thành 1 ký tự gạch ngang
        //Phòng trường hợp người nhập vào quá nhiều ký tự trắng
        slug = slug.replace(/\-\-\-\-\-/gi, "-");
        slug = slug.replace(/\-\-\-\-/gi, "-");
        slug = slug.replace(/\-\-\-/gi, "-");
        slug = slug.replace(/\-\-/gi, "-");
        //Xóa các ký tự gạch ngang ở đầu và cuối
        slug = "@" + slug + "@";
        slug = slug.replace(/\@\-|\-\@|\@/gi, "");
        return slug;
    }

    const slug = document.querySelector(".slug");
    const title = document.querySelector(".title");
    let isChangeSlug = false;

    if (slug) {
        if (slug.value === "") {
            title.addEventListener("keyup", (e) => {
                if (!isChangeSlug) {
                    const titleValue = e.target.value;
                    slug.value = getSlug(titleValue);
                }
            });
        }

        slug.addEventListener("change", () => {
            if (slug.value === "") {
                const title = document.querySelector(".title");
                const titleValue = title.value;
                slug.value = getSlug(titleValue);
            }
            isChangeSlug = true;
        });
    }

    const logoutAction = document.querySelector(".logout-action");
    const formLogout = document.querySelector(".form-logout");
    if (logoutAction && formLogout) {
        logoutAction.addEventListener("click", (e) => {
            e.preventDefault();
            Swal.fire({
                title: "Đăng xuất?",
                text: "Bạn chắc chắn muốn đăng xuất khỏi hệ thống?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Đăng xuất",
                cancelButtonText: "Hủy",
                confirmButtonColor: "#d33",
            }).then((result) => {
                if (result.isConfirmed) {
                    const action = e.target.href;
                    formLogout.action = action;
                    formLogout.submit();
                }
            });
        });
    }

    function getSlugLocale(title) {
        //Đổi chữ hoa thành chữ thường
        let slug = title.toLowerCase();

        //Đổi ký tự có dấu thành không dấu
        slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, "a");
        slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, "e");
        slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, "i");
        slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, "o");
        slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, "u");
        slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, "y");
        slug = slug.replace(/đ/gi, "d");
        //Xóa các ký tự đặt biệt
        slug = slug.replace(
            /\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi,
            "",
        );
        //Đổi khoảng trắng thành ký tự gạch ngang
        slug = slug.replace(/ /gi, "-");
        //Đổi nhiều ký tự gạch ngang liên tiếp thành 1 ký tự gạch ngang
        //Phòng trường hợp người nhập vào quá nhiều ký tự trắng
        slug = slug.replace(/\-\-\-\-\-/gi, "-");
        slug = slug.replace(/\-\-\-\-/gi, "-");
        slug = slug.replace(/\-\-\-/gi, "-");
        slug = slug.replace(/\-\-/gi, "-");
        //Xóa các ký tự gạch ngang ở đầu và cuối
        slug = "@" + slug + "@";
        slug = slug.replace(/\@\-|\-\@|\@/gi, "");
        return slug;
    }

    const slugEn = document.querySelector(".slug-en");
    const titleEn = document.querySelector(".title-en");
    let isChangeSlugEN = false;

    if (slugEn && titleEn) {
        titleEn.addEventListener("input", (e) => {
            if (!isChangeSlugEN) {
                slugEn.value = getSlugLocale(e.target.value);
            }
        });

        slugEn.addEventListener("input", () => {
            isChangeSlugEN = slugEn.value.trim() !== "";
        });
    }
});
