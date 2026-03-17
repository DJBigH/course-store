<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>{{ $pageTitle ?? 'Không tìm thấy trang' }} - BigK Udemy</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.3.5/datatables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link href="{{ asset('backend/css/styles.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    @yield('stylesheets')
</head>

<body class="sb-nav-fixed">
    @include('part.backend.header')
    <div id="layoutSidenav">
        @include('part.backend.sidebar')
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    @include('part.backend.page_title')
                    @yield('content')
                </div>
            </main>
            @include('part.backend.footer')
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="https://cdn.datatables.net/v/bs5/dt-2.3.5/datatables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('backend/plugins/ckeditor/ckeditor.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>
    <script src="{{ asset('backend/js/scripts.js') }}"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $('#lfm').filemanager('image');
        $('#lfm-file').filemanager('file');
        $('#lfm-video').filemanager('video');
        $('#lfm-document').filemanager('document');
    </script>
    <script>
        window.AdminSlug = {
            vi(title) {
                let slug = (title || '').toLowerCase();
                slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
                slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
                slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
                slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
                slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
                slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
                slug = slug.replace(/đ/gi, 'd');
                slug = slug.replace(/[^\p{L}\p{N}\s-]/gu, '');
                return slug.replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-+|-+$/g, '');
            },
            intl(title) {
                return (title || '').toLowerCase().trim().replace(/[^\p{L}\p{N}\s-]/gu, '').replace(/\s+/g, '-')
                    .replace(/-+/g, '-').replace(/^-+|-+$/g, '');
            },
            byLocale(title, locale = 'vi') {
                const normalizedLocale = (locale || 'vi').toLowerCase();
                if (normalizedLocale === 'vi') {
                    return this.vi(title);
                }

                return this.intl(title);
            },
            bindAuto(titleSelector, slugSelector, locale = 'vi') {
                const titleEl = document.querySelector(titleSelector);
                const slugEl = document.querySelector(slugSelector);
                if (!titleEl || !slugEl) return;

                titleEl.addEventListener('input', (event) => {
                    if (!slugEl.dataset.manual) {
                        slugEl.value = this.byLocale(event.target.value, locale);
                    }
                });

                slugEl.addEventListener('input', () => {
                    slugEl.dataset.manual = '1';
                });
            },
            bindIfEmpty(titleSelector, slugSelector, locale = 'vi') {
                const titleEl = document.querySelector(titleSelector);
                const slugEl = document.querySelector(slugSelector);
                if (!titleEl || !slugEl) return;

                titleEl.addEventListener('input', (event) => {
                    if (slugEl.value.trim() === '') {
                        slugEl.value = this.byLocale(event.target.value, locale);
                    }
                });
            }
        };

        window.getSlugByLocale = function(title, locale) {
            return window.AdminSlug.byLocale(title, locale);
        };
    </script>
    @yield('scripts')
    @stack('scripts')
</body>

</html>
