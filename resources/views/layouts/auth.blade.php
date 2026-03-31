<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>{{ $pageTitle }}</title>
    <link href="{{ asset('backend/css/styles.css') }}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.18), transparent 24%),
                radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.18), transparent 28%),
                linear-gradient(135deg, #0f172a 0%, #172554 58%, #1d4ed8 100%);
        }

        #layoutAuthentication {
            min-height: 100vh;
        }

        #layoutAuthentication_content {
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }

        .auth-brand {
            color: #fff;
        }

        .auth-brand__eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .auth-brand__title {
            font-size: clamp(2rem, 4vw, 3.1rem);
            font-weight: 700;
            line-height: 1.15;
            margin: 0;
        }

        .auth-brand__desc {
            max-width: 560px;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
        }
    </style>
    @yield('stylesheets')
</head>

<body>
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main class="w-100">
                <div class="container">
                    <div class="row justify-content-center align-items-center g-4">
                        <div class="col-12 col-lg-6">
                            <div class="auth-brand">
                                <span class="auth-brand__eyebrow">BigK Admin</span>
                                <h1 class="auth-brand__title mt-3 mb-3">Khu vực quản trị dành riêng cho đội vận hành</h1>
                                <p class="auth-brand__desc">
                                    Đăng nhập để quản lý khóa học, đơn hàng, học viên và toàn bộ nội dung trên hệ thống
                                    trong một giao diện tập trung.
                                </p>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5 col-xl-4">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutAuthentication_footer">
            @include('part.backend.footer')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ asset('backend/js/scripts.js') }}"></script>
</body>

</html>
