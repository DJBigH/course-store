@php
    $student = auth('students')->user();
    $unreadCount = $student?->unreadNotifications()->count() ?? 0;
    $notifications = $student ? $student->notifications()->latest()->take(20)->get() : collect();

    $localeOptions = [
        'vi' => ['flag' => '', 'short' => 'VI', 'label' => 'Vietnamese'],
        'en' => ['flag' => '', 'short' => 'EN', 'label' => 'English'],
        'ko' => ['flag' => '', 'short' => 'KO', 'label' => 'Korean'],
        'ja' => ['flag' => '', 'short' => 'JA', 'label' => 'Japanese'],
        'zh' => ['flag' => '', 'short' => 'ZH', 'label' => 'Chinese'],
    ];
    $supportedLocales = array_keys($localeOptions);
    $localeFlags = [
        'vi' => 'VN',
        'en' => 'EN',
        'ko' => 'KO',
        'ja' => 'JA',
        'zh' => 'ZH',
    ];

    foreach ($localeFlags as $locale => $flag) {
        if (isset($localeOptions[$locale])) {
            $localeOptions[$locale]['flag'] = $flag;
        }
    }

    $currentLocale = app()->getLocale();
    if (!in_array($currentLocale, $supportedLocales)) {
        $currentLocale = 'vi';
    }

    $currentRoute = request()->route();
    $currentRouteName = $currentRoute?->getName();
    $routeParameters = $currentRoute?->parameters() ?? [];
    $phone = setting('phone', '012345678');
    $email = setting('email', 'bigk@gmail.com');
    $phoneHref = 'tel:' . preg_replace('/[^\d+]/', '', (string) $phone);
    $teacherLabels = [
        'vi' => [
            'portal' => 'Kênh giảng viên',
            'become' => 'Trở thành giảng viên',
            'application' => 'Đơn đăng ký giảng viên',
        ],
        'en' => [
            'portal' => 'Instructor Hub',
            'become' => 'Become an Instructor',
            'application' => 'Instructor Application',
        ],
        'ko' => [
            'portal' => '강사 채널',
            'become' => '강사 되기',
            'application' => '강사 신청서',
        ],
        'ja' => [
            'portal' => '講師チャンネル',
            'become' => '講師になる',
            'application' => '講師申請',
        ],
        'zh' => [
            'portal' => '讲师频道',
            'become' => '成为讲师',
            'application' => '讲师申请',
        ],
    ];
    $teacherUi = $teacherLabels[$currentLocale] ?? $teacherLabels['en'];
    $latestTeacherApplication = $student?->teacherApplications()?->latest('id')->first();
    $teacherPortalHeaderUrl = $student && $student->teacher && $student->teacher->status === 'active'
        ? route('teacher.dashboard.index')
        : null;
    $teacherApplicationHeaderUrl = $latestTeacherApplication
        ? route('teacher.account.status', ['locale' => $currentLocale])
        : route('teacher.account.begin', ['locale' => $currentLocale]);
    $fallbackLocaleUrl = function (string $locale): string {
        $path = trim(request()->path(), '/');
        $segments = $path === '' ? [] : explode('/', $path);

        if (!empty($segments) && in_array($segments[0], ['vi', 'en', 'ko', 'ja', 'zh'], true)) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        return url(implode('/', $segments));
    };

    $resolveLocalizedSlug = function ($model, string $locale): string {
        if (!$model) {
            return '';
        }

        $slugByLocale = match ($locale) {
            'zh' => $model->slug_zh ?? null,
            'ja' => $model->slug_ja ?? null,
            'ko' => $model->slug_ko ?? null,
            'en' => $model->slug_en ?? null,
            default => $model->slug ?? null,
        };

        return $slugByLocale
            ?: ($model->slug ?? null)
            ?: ($model->slug_en ?? null)
            ?: ($model->slug_ko ?? null)
            ?: ($model->slug_ja ?? null)
            ?: ($model->slug_zh ?? null)
            ?: '';
    };

    $findByLocalizedSlug = function (string $modelClass, ?string $slug) {
        if (!$slug || !class_exists($modelClass)) {
            return null;
        }

        return $modelClass::query()
            ->where('slug', $slug)
            ->orWhere('slug_en', $slug)
            ->orWhere('slug_ko', $slug)
            ->orWhere('slug_ja', $slug)
            ->orWhere('slug_zh', $slug)
            ->first();
    };

    $buildLocaleUrl = function (string $locale) use (
        $currentRouteName,
        $routeParameters,
        $fallbackLocaleUrl,
        $findByLocalizedSlug,
        $resolveLocalizedSlug
    ): string {
        $params = $routeParameters;
        unset($params['locale']);

        try {
            return match ($currentRouteName) {
                'home',
                'home.about',
                'home.student-support',
                'home.faq',
                'home.testimonials',
                'home.payment-policy',
                'home.refund-policy',
                'home.terms-of-service',
                'home.privacy-policy',
                'courses.home',
                'contacts.home',
                'coupons.home',
                'clients-login',
                'clients-register',
                'clients-forgot',
                'block-index',
                'verification.notice',
                'students.account.index',
                'students.account.profile',
                'students.account.my-courses',
                'students.account.my-coupon',
                'students.account.my-order',
                'students.account.change-password',
                'students.account.activity-history',
                'students.account.deactivate',
                'students.account.delete',
                'students.account.order-detail',
                'students.account.checkout',
                'teacher.portal.index' => route('teacher.portal.index', ['locale' => $locale]),
                'teacher.account.begin',
                'teacher.account.apply',
                'teacher.account.status',
                'teacher.account.edit',
                'teacher.account.mark-paid' => route($currentRouteName, array_merge(['locale' => $locale], $params)),
                'teacher.dashboard.index',
                'teacher.dashboard.courses',
                'teacher.dashboard.earnings',
                'teacher.dashboard.payouts' => route($currentRouteName, $params),
                'courses.detail' => (($course = $findByLocalizedSlug(\Modules\Courses\src\Models\Courses::class, $params['slug'] ?? null)) && ($slug = $resolveLocalizedSlug($course, $locale)))
                    ? route('courses.detail', ['locale' => $locale, 'slug' => $slug])
                    : route('courses.home', ['locale' => $locale]),
                'categories.category' => (($category = $findByLocalizedSlug(\Modules\Categories\src\Models\Category::class, $params['slug'] ?? null)) && ($slug = $resolveLocalizedSlug($category, $locale)))
                    ? route('categories.category', ['locale' => $locale, 'slug' => $slug])
                    : route('home', ['locale' => $locale]),
                'lessons.home',
                'lessons.toggle-completion' => (($lesson = $findByLocalizedSlug(\Modules\Lessons\src\Models\Lesson::class, $params['slug'] ?? null)) && ($slug = $resolveLocalizedSlug($lesson, $locale)))
                    ? route('lessons.home', ['locale' => $locale, 'slug' => $slug])
                    : route('home', ['locale' => $locale]),
                default => $currentRouteName
                    ? route($currentRouteName, array_merge(['locale' => $locale], $params))
                    : $fallbackLocaleUrl($locale),
            };
        } catch (\Throwable $exception) {
            return $fallbackLocaleUrl($locale);
        }
    };

    $localeUrls = [];
    foreach ($supportedLocales as $locale) {
        $localeUrls[$locale] = $buildLocaleUrl($locale);
    }

    $qs = request()->getQueryString();
    if ($qs) {
        foreach ($localeUrls as $locale => $localeUrl) {
            $localeUrls[$locale] = $localeUrl . '?' . $qs;
        }
    }
@endphp

<header class="header">
    <div class="action-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="d-none d-xl-block col-xl-3">
                    <form class="header-search" action="{{ route('courses.home', ['locale' => app()->getLocale()]) }}"
                        method="GET" role="search">
                        <label class="visually-hidden"
                            for="header-search-input">{{ __('clients/common.search') }}</label>
                        <span class="header-search__icon" aria-hidden="true">
                            <i class="fas fa-search"></i>
                        </span>
                        <input id="header-search-input" type="text" name="keyword"
                            value="{{ request('keyword') }}"
                            placeholder="{{ __('clients/common.search_placeholder') }}" />
                        <button type="submit" class="btn btn-primary header-search__button">
                            {{ __('clients/common.search') }}
                        </button>
                    </form>
                </div>
                <div class="d-none d-xl-block col-xl-4">
                    <div class="d-flex">
                        <p class="slogan">
                            <i class="fas fa-phone"></i>{{ __('clients/common.support') }}
                            <a href="{{ $phoneHref }}">{{ $phone }}</a>
                        </p>
                        <p class="mail">
                            <i class="far fa-envelope"></i>
                            <a href="mailto:{{ $email }}">{{ $email }}</a>
                        </p>
                    </div>
                </div>
                <div class="col-12 col-xl-5">
                    <div class="social d-flex align-items-center justify-content-end gap-2">
                        <button class="btn btn-outline-primary theme-toggle" type="button" data-theme-toggle
                            data-theme-label-light="{{ __('clients/common.theme_light') }}"
                            data-theme-label-dark="{{ __('clients/common.theme_dark') }}"
                            data-theme-switch-light="{{ __('clients/common.theme_switch_to_light') }}"
                            data-theme-switch-dark="{{ __('clients/common.theme_switch_to_dark') }}"
                            aria-label="{{ __('clients/common.theme_switch_to_dark') }}"
                            aria-pressed="false" title="{{ __('clients/common.theme_switch_to_dark') }}">
                            <i class="bi bi-moon-stars-fill theme-toggle__icon theme-toggle__icon--dark"></i>
                            <i class="bi bi-sun-fill theme-toggle__icon theme-toggle__icon--light"></i>
                            <span class="theme-toggle__label">{{ __('clients/common.theme_dark') }}</span>
                        </button>

                        {{-- LANGUAGE SWITCH --}}
                        <div class="dropdown locale-switcher">
                            <button
                                class="btn btn-outline-primary dropdown-toggle d-flex align-items-center gap-2 locale-switcher__toggle"
                                data-bs-toggle="dropdown" type="button" aria-expanded="false">
                                <span class="locale-switcher__flag">{!! $localeOptions[$currentLocale]['flag'] !!}</span>
                                <span class="locale-switcher__short">{{ $localeOptions[$currentLocale]['short'] }}</span>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end locale-switcher__menu">
                                @foreach ($localeOptions as $locale => $option)
                                    <li>
                                        <a class="dropdown-item d-flex gap-2 align-items-center locale-switcher__item {{ $currentLocale === $locale ? 'active' : '' }}"
                                            href="{{ $localeUrls[$locale] }}" data-locale-link
                                            data-locale-code="{{ strtoupper($locale) }}"
                                            data-locale-label="{{ $option['label'] }}"
                                            @if ($currentLocale === $locale) aria-current="true" @endif>
                                            <span class="locale-switcher__flag">{!! $option['flag'] !!}</span>
                                            <span class="locale-switcher__label">{{ $option['label'] }}</span>
                                            <span class="locale-switcher__code">{{ strtoupper($locale) }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @if (auth('students')->check())
                            <div class="header-user-actions d-flex align-items-center gap-3">

                                {{-- Notification bell --}}
                                <div class="nav-item dropdown notification-hover position-relative" data-notification-dropdown>

                                    <a class="nav-link dropdown-toggle header-notification-toggle" href="#"
                                        id="notificationDropdown" role="button" data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside" aria-expanded="false"
                                        data-notification-toggle>

                                        <i class="fas fa-bell"></i>

                                        {{-- Notification count badge --}}
                                        @if ($unreadCount > 0)
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                                data-unread-badge>
                                                {{ $unreadCount }}
                                            </span>
                                        @endif
                                    </a>

                                    <ul class="dropdown-menu dropdown-menu-end notification-dropdown"
                                        data-notification-menu
                                        aria-labelledby="notificationDropdown">

                                        <li class="dropdown-header fw-bold">
                                            {{ __('clients/common.notifications') }}
                                        </li>

                                        @forelse($notifications as $notification)
                                            <li>
                                                <a class="dropdown-item notification-item {{ is_null($notification->read_at) ? 'unread' : '' }}"
                                                    href="{{ route('students.notifications.read', ['locale' => app()->getLocale(), 'id' => $notification->id]) }}">

                                                    <div class="notification-content">
                                                        <div class="notification-title">
                                                            {{ notificationText($notification, 'title', __('clients/common.notifications')) }}
                                                        </div>

                                                        <div class="notification-message">
                                                            {{ notificationText($notification, 'message', '') }}
                                                        </div>

                                                        <div class="notification-time">
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>

                                                </a>
                                            </li>
                                        @empty
                                            <li class="dropdown-item text-muted small">
                                                {{ __('clients/common.no_notifications') }}
                                            </li>
                                        @endforelse

                                        @if ($student)
                                            <li><hr class="dropdown-divider"></li>
                                            <li class="px-3 py-2 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                                <a class="small fw-semibold"
                                                    href="{{ route('students.notifications.index', ['locale' => app()->getLocale()]) }}">
                                                    Xem tất cả
                                                </a>
                                                @if ($unreadCount > 0)
                                                    <form action="{{ route('students.notifications.mark-all-read', ['locale' => app()->getLocale()]) }}"
                                                        method="POST" class="m-0" data-mark-all-read-form>
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-primary"
                                                            data-mark-all-read-button>
                                                            Đánh dấu đã đọc
                                                        </button>
                                                    </form>
                                                @endif
                                            </li>
                                        @endif

                                    </ul>
                                </div>
                                {{-- User dropdown --}}
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle d-flex align-items-center gap-2 header-user-toggle"
                                        type="button" id="userDropdown" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fas fa-user-circle"></i>
                                        <span>{{ auth('students')->user()->name }}</span>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2"
                                                href="{{ route('students.account.index', ['locale' => app()->getLocale()]) }}">
                                                <i class="fas fa-user-circle"></i>
                                                {{ __('clients/common.my_account') }}
                                            </a>
                                        </li>
                                        @if ($teacherPortalHeaderUrl)
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2"
                                                    href="{{ $teacherPortalHeaderUrl }}">
                                                    <i class="fas fa-chalkboard-user"></i>
                                                    {{ $teacherUi['portal'] }}
                                                </a>
                                            </li>
                                        @elseif ($latestTeacherApplication)
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2"
                                                    href="{{ $teacherApplicationHeaderUrl }}">
                                                    <i class="fas fa-file-signature"></i>
                                                    {{ $teacherUi['application'] }}
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                                href="#"
                                                onclick="document['form-logout'].submit(); return false;">
                                                <i class="fas fa-sign-out-alt"></i> {{ __('clients/common.logout') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        @else
                            <div class="header-auth-actions d-flex align-items-center gap-2">
                                @if (auth()->check())
                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center gap-2"
                                            type="button" id="adminDropdown" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fas fa-user-shield"></i>
                                            <div class="text-start">
                                                <div class="small fw-bold lh-1">{{ auth()->user()->name }}</div>
                                                <div class="tiny-role-client mt-1 opacity-75">{{ auth()->user()->group?->name ?? 'Admin' }}</div>
                                            </div>
                                        </button>
                                        <style>
                                            .tiny-role-client {
                                                font-size: 0.6rem;
                                                text-transform: uppercase;
                                                letter-spacing: 0.03em;
                                            }
                                        </style>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="adminDropdown">
                                            @if(auth()->user()->hasPermission('dashboard.view'))
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2"
                                                        href="{{ route('admin.index') }}">
                                                        <i class="fas fa-tachometer-alt"></i> Trang quản trị
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                                    href="#"
                                                    onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                                                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                                @else
                                    <a href="{{ route('clients-register', ['locale' => app()->getLocale()]) }}"
                                        class="btn btn-primary header-auth-btn">
                                        <i class="fas fa-user"></i>
                                        <span>{{ __('clients/common.register') }}</span>
                                    </a>
                                    <a href="{{ route('clients-login', ['locale' => app()->getLocale()]) }}"
                                        class="btn btn-primary header-auth-btn">
                                        <i class="fas fa-key"></i>
                                        <span>{{ __('clients/common.login') }}</span>
                                    </a>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <nav class="navbar navbar-expand-xl navbar-light bg-light">
        <div class="container">

            {{-- Logo --}}
            <a class="navbar-brand" href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                <img src="{{ setting('logo') ? asset('storage/' . setting('logo')) : asset('clients/assets/logo.png') }}"
                    alt="Logo" width="20px">
            </a>


            {{-- Toggle mobile --}}
            <button class="navbar-toggler d-xl-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Menu --}}
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                            href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-home"></i>
                            {{ __('clients/common.home') }}
                        </a>
                    </li>

                    <li class="nav-item dropdown dropdown-hover">
                        <a class="nav-link nav-link--submenu-toggle
        {{ request()->routeIs('courses.*') ? 'active' : '' }}"
                            id="coursesDropdown" role="button" href="#"
                            data-mobile-submenu-toggle="courses-submenu"
                            aria-expanded="false">
                            <i class="fas fa-tv"></i>
                            <span>{{ __('clients/common.course_categories') }}</span>
                            <i class="fas fa-chevron-down nav-link__chevron" aria-hidden="true"></i>
                        </a>

                        <ul class="dropdown-menu mobile-nav-submenu" id="courses-submenu">
                            @foreach ($courseCategories as $category)
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item"
                                        href="{{ route('categories.category', [
                                            'locale' => app()->getLocale(),
                                            'slug' => $category->slug_locale,
                                        ]) }}">
                                        {{ $category->name_locale }}
                                    </a>
                                    {{-- <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('courses.category', $category->slug) }}">
                                                {{ $category->name }} ({{ $category->courses_count }})
                                            </a>
                                        </li>
                                    </ul> --}}
                                </li>
                            @endforeach

                        </ul>
                    </li>


                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('coupons.*') ? 'active' : '' }}"
                            href="{{ route('coupons.home', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-ticket-alt"></i>
                            {{ __('clients/common.coupons') }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('teacher.portal.*', 'teacher.account.*', 'teacher.dashboard.*') ? 'active' : '' }}"
                            href="{{ route('teacher.portal.index', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-chalkboard-user"></i>
                            {{ $teacherUi['become'] }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}"
                            href="{{ route('contacts.home', ['locale' => app()->getLocale()]) }}">
                            <i class="fas fa-phone-alt"></i>
                            {{ __('clients/common.contact') }}
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

</header>
<form action="{{ route('clients-logout', ['locale' => app()->getLocale()]) }}" method="post" name="form-logout">@csrf</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggles = document.querySelectorAll('[data-mobile-submenu-toggle]');
        const notificationRoot = document.querySelector('[data-notification-dropdown]');
        const notificationToggle = notificationRoot?.querySelector('[data-notification-toggle]');
        const notificationMenu = notificationRoot?.querySelector('[data-notification-menu]');

        const mobileOnly = () => window.innerWidth < 1200;

        const resetSubmenus = () => {
            toggles.forEach((toggle) => {
                const submenuId = toggle.dataset.mobileSubmenuToggle;
                const submenu = submenuId ? document.getElementById(submenuId) : null;

                toggle.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');

                if (submenu) {
                    submenu.classList.remove('is-open');
                }
            });
        };

        if (toggles.length) {
            toggles.forEach((toggle) => {
                toggle.addEventListener('click', (event) => {
                    if (!mobileOnly()) {
                        return;
                    }

                    event.preventDefault();

                    const submenuId = toggle.dataset.mobileSubmenuToggle;
                    const submenu = submenuId ? document.getElementById(submenuId) : null;

                    if (!submenu) {
                        return;
                    }

                    const willOpen = !submenu.classList.contains('is-open');
                    resetSubmenus();

                    if (willOpen) {
                        toggle.classList.add('is-open');
                        toggle.setAttribute('aria-expanded', 'true');
                        submenu.classList.add('is-open');
                    }
                });
            });
        }

        const closeNotificationDropdown = () => {
            if (!notificationRoot || !notificationToggle || !notificationMenu) {
                return;
            }

            notificationRoot.classList.remove('is-open');
            notificationToggle.setAttribute('aria-expanded', 'false');
            notificationMenu.classList.remove('show');
        };

        const openNotificationDropdown = () => {
            if (!notificationRoot || !notificationToggle || !notificationMenu) {
                return;
            }

            notificationRoot.classList.add('is-open');
            notificationToggle.setAttribute('aria-expanded', 'true');
            notificationMenu.classList.add('show');
        };

        if (notificationToggle && notificationMenu) {
            notificationToggle.addEventListener('click', (event) => {
                if (!mobileOnly()) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                if (notificationRoot.classList.contains('is-open')) {
                    closeNotificationDropdown();
                    return;
                }

                openNotificationDropdown();
            });

            document.addEventListener('click', (event) => {
                if (!mobileOnly() || !notificationRoot) {
                    return;
                }

                if (notificationRoot.contains(event.target)) {
                    return;
                }

                closeNotificationDropdown();
            });
        }

        window.addEventListener('resize', () => {
            if (!mobileOnly()) {
                resetSubmenus();
                closeNotificationDropdown();
            }
        });

        const updateNotificationUiAfterMarkAllRead = () => {
            document.querySelectorAll('[data-unread-badge]').forEach((badge) => badge.remove());

            document.querySelectorAll('.notification-item.unread').forEach((item) => {
                item.classList.remove('unread');
            });

            document.querySelectorAll('[data-notification-status]').forEach((badge) => {
                badge.className = 'badge bg-success';
                badge.textContent = badge.dataset.readLabel || 'Da doc';
            });

            document.querySelectorAll('[data-mark-all-read-form]').forEach((form) => {
                form.remove();
            });
        };

        document.querySelectorAll('[data-mark-all-read-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const submitButton = form.querySelector('[data-mark-all-read-button]');
                const token = form.querySelector('input[name="_token"]')?.value;

                if (!token) {
                    form.submit();
                    return;
                }

                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({}),
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    updateNotificationUiAfterMarkAllRead();
                } catch (error) {
                    form.submit();
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            });
        });

        document.querySelectorAll('[data-mark-read-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const submitButton = form.querySelector('[data-mark-read-button]');
                const token = form.querySelector('input[name="_token"]')?.value;

                if (!token) {
                    form.submit();
                    return;
                }

                if (submitButton) {
                    submitButton.disabled = true;
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({}),
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error('Request failed');
                    }

                    const data = await response.json();
                    
                    // Update badge
                    const badges = document.querySelectorAll('[data-unread-badge]');
                    if (data.unread_count > 0) {
                        badges.forEach(badge => {
                            badge.textContent = data.unread_count;
                        });
                    } else {
                        badges.forEach(badge => badge.remove());
                        document.querySelectorAll('[data-mark-all-read-form]').forEach(f => f.remove());
                    }

                    // Update item status
                    const item = form.closest('[data-notification-item]');
                    if (item) {
                        const statusBadge = item.querySelector('[data-notification-status]');
                        if (statusBadge) {
                            statusBadge.className = 'badge bg-success';
                            statusBadge.textContent = statusBadge.dataset.readLabel || 'Da doc';
                        }
                    }
                    
                    form.remove();
                } catch (error) {
                    form.submit();
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            });
        });
    });
</script>
<style>
    .header-auth-actions .header-auth-btn.btn-primary {
        color: #fff !important;
        border-color: var(--bs-primary);
    }
    .header-auth-actions .header-auth-btn.btn-primary:hover,
    .header-auth-actions .header-auth-btn.btn-primary:focus {
        color: #fff !important;
    }
    .header-auth-actions .header-auth-btn.btn-outline-primary {
        color: var(--bs-primary) !important;
        background: #fff;
    }
    html[data-theme="dark"] .header-auth-actions .header-auth-btn.btn-outline-primary {
        background: transparent;
        color: #dbeafe !important;
    }
</style>

