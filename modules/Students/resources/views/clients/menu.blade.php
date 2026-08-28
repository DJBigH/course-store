@php
    $student = auth('students')->user();
    $latestTeacherApplication = $student?->teacherApplications()?->latest('id')->first();
    $teacherPortalUrl = $student?->teacher && $student->teacher->status === 'active'
        ? route('teacher.dashboard.index')
        : ($latestTeacherApplication
            ? route('teacher.account.status', ['locale' => app()->getLocale()])
            : null);
    $teacherMenuLabel = match (app()->getLocale()) {
        'vi' => $student?->teacher && $student->teacher->status === 'active' ? 'Kênh giảng viên' : 'Đơn đăng ký giảng viên',
        'ko' => $student?->teacher && $student->teacher->status === 'active' ? '강사 채널' : '강사 신청서',
        'ja' => $student?->teacher && $student->teacher->status === 'active' ? '講師チャンネル' : '講師申請',
        'zh' => $student?->teacher && $student->teacher->status === 'active' ? '讲师频道' : '讲师申请',
        default => $student?->teacher && $student->teacher->status === 'active' ? 'Instructor Hub' : 'Instructor Application',
    };
@endphp

<ul class="nav flex-column" style="border: none">
    <li class="nav-item">
        <a href="{{ route('students.account.index', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.index') ? 'active' : '' }}"
            data-account-nav>
            <i class="fa-solid fa-gauge"></i>
            {{ __('students::clients/account.menu.dashbroad') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.profile', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.profile') ? 'active' : '' }}"
            data-account-nav>
            <i class="fa-solid fa-user"></i>
            {{ __('students::clients/account.menu.profile') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.my-courses', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.my-courses') ? 'active' : '' }}"
            data-account-nav>
            <i class="fa-solid fa-book-open"></i>
            {{ __('students::clients/account.menu.my_course') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.certificates.index', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ request()->routeIs('students.account.certificates.*') ? 'active' : '' }}"
            data-account-nav>
            <i class="fa-solid fa-award"></i>
            {{ __('students::clients/account.menu.my_certificates') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.my-quizzes', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.my-quizzes') ? 'active' : '' }}"
            data-account-nav>
            <i class="fa-solid fa-vial-circle-check"></i>
            {{ __('students::clients/account.menu.my_quizzes') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.my-coupon', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.my-coupon') ? 'active' : '' }}"
            data-account-nav>
            <i class="fa-solid fa-ticket-alt"></i>
            {{ __('students::clients/account.menu.coupons') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.my-order', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.my-order') ? 'active' : '' }}"
            data-account-nav>
            <i class="fa-solid fa-receipt"></i>
            {{ __('students::clients/account.menu.order') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.change-password', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.change-password') ? 'active' : '' }}"
            data-account-nav>
            <i class="fa-solid fa-lock"></i>
            {{ __('students::clients/account.menu.change_password') }}
        </a>
    </li>
    <li class="nav-item">
        <a href="{{ route('students.account.activity-history', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.account.activity-history') ? 'active' : '' }}"
            data-account-nav>
            <i class="fa-solid fa-clock-rotate-left"></i>
            {{ __('students::clients/account.menu.activity_history') }}
        </a>
    </li>
    @if ($teacherPortalUrl)
        <li class="nav-item">
            <a href="{{ $teacherPortalUrl }}"
                class="nav-link {{ request()->routeIs('teacher.account.*', 'teacher.dashboard.*') ? 'active' : '' }}"
                data-no-ajax-account>
                <i class="fa-solid {{ $student?->teacher && $student->teacher->status === 'active' ? 'fa-chalkboard-user' : 'fa-file-signature' }}"></i>
                {{ $teacherMenuLabel }}
            </a>
        </li>
    @endif
    <li class="nav-item">
        <a href="{{ route('students.notifications.index', ['locale' => app()->getLocale()]) }}"
            class="nav-link {{ activeMenu('students.notifications.index') || activeMenu('clients.inbox.index') ? 'active' : '' }}"
            data-account-nav>
            <i class="fa-solid fa-envelope"></i>
            {{ __('students::clients/account.menu.inbox') }}
        </a>
    </li>
    <li class="nav-item">
        <form action="{{ route('clients-logout', ['locale' => app()->getLocale()]) }}" method="POST" class="d-inline">
            @csrf
            <a href="#" class="nav-link text-danger js-logout"
                data-confirm="{{ __('students::clients/account.logout.confirm_logout') }}">
                <i class="bi bi-box-arrow-right me-1"></i>
                {{ __('students::clients/account.menu.logout') }}
            </a>
        </form>
    </li>
</ul>

<style>
    .account-page .account-content {
        position: relative;
        transition: opacity 0.16s ease, transform 0.16s ease, filter 0.16s ease;
    }

    .account-page.is-loading .account-content {
        opacity: 0.72;
        transform: translateY(1px);
        filter: saturate(0.94);
    }

    .account-page.is-loading .account-content > * {
        transition: opacity 0.16s ease;
    }

    .account-page.is-loading .account-content > *:not(.account-sidebar):not(.sidebar-account) {
        opacity: 0.94;
    }
</style>

<script>
    (() => {
        if (window.__studentAccountNavInitialized) {
            return;
        }

        window.__studentAccountNavInitialized = true;

        const ACCOUNT_ROUTE_PREFIX = /^\/(?:vi|en|ko|ja|zh)\/tai-khoan(?:\/|$)/i;
        let currentAbortController = null;

        const isAccountUrl = (url) => {
            try {
                const parsed = new URL(url, window.location.origin);
                return parsed.origin === window.location.origin && ACCOUNT_ROUTE_PREFIX.test(parsed.pathname);
            } catch (error) {
                return false;
            }
        };

        const canHandleAccountLink = (link, event) => {
            if (!link || !link.href || !isAccountUrl(link.href)) {
                return false;
            }

            if (
                event.defaultPrevented ||
                event.button !== 0 ||
                event.metaKey ||
                event.ctrlKey ||
                event.shiftKey ||
                event.altKey
            ) {
                return false;
            }

            if (
                link.hasAttribute('download') ||
                link.getAttribute('target') === '_blank' ||
                link.dataset.noAjaxAccount !== undefined ||
                link.classList.contains('js-logout')
            ) {
                return false;
            }

            return true;
        };

        const setMenuLoading = (isLoading) => {
            document.querySelectorAll('[data-account-nav]').forEach((link) => {
                link.classList.toggle('is-loading', isLoading);
                link.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            });
        };

        const setAccountLoading = (isLoading) => {
            document.querySelectorAll('.account-page').forEach((page) => {
                page.classList.toggle('is-loading', isLoading);
                page.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            });

            document.querySelectorAll('.account-page .account-content').forEach((content) => {
                content.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            });
        };

        const syncActiveLink = (targetUrl) => {
            const normalizedTarget = new URL(targetUrl, window.location.origin).pathname;

            document.querySelectorAll('[data-account-nav]').forEach((link) => {
                const linkPath = new URL(link.href, window.location.origin).pathname;
                link.classList.toggle('active', linkPath === normalizedTarget);
            });
        };

        const replaceAccountStyles = (nextDocument) => {
            document.head.querySelectorAll('style[data-account-page-style]').forEach((style) => style.remove());

            nextDocument.head.querySelectorAll('style[data-account-page-style]').forEach((style) => {
                document.head.appendChild(style.cloneNode(true));
            });
        };

        const replacePageModals = (nextDocument) => {
            const currentModals = document.getElementById('page-modals');
            const nextModals = nextDocument.getElementById('page-modals');

            if (currentModals && nextModals) {
                currentModals.innerHTML = nextModals.innerHTML;
            }
        };

        const runPageScripts = (nextDocument) => {
            const currentScriptsHost = document.getElementById('page-inline-scripts');
            const nextScriptsHost = nextDocument.getElementById('page-inline-scripts');

            if (!currentScriptsHost || !nextScriptsHost) {
                return;
            }

            currentScriptsHost.innerHTML = nextScriptsHost.innerHTML;

            currentScriptsHost.querySelectorAll('script').forEach((oldScript) => {
                const script = document.createElement('script');

                Array.from(oldScript.attributes).forEach((attribute) => {
                    script.setAttribute(attribute.name, attribute.value);
                });

                script.textContent = oldScript.textContent;
                oldScript.replaceWith(script);
            });
        };

        const replaceMainContent = (nextDocument) => {
            const currentMain = document.querySelector('main');
            const nextMain = nextDocument.querySelector('main');

            if (!currentMain || !nextMain) {
                throw new Error('Main content not found');
            }

            currentMain.innerHTML = nextMain.innerHTML;
        };

        const loadAccountPage = async (url, { pushState = true } = {}) => {
            if (!isAccountUrl(url)) {
                window.location.href = url;
                return;
            }

            if (currentAbortController) {
                currentAbortController.abort();
            }

            currentAbortController = new AbortController();
            setMenuLoading(true);
            setAccountLoading(true);

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    signal: currentAbortController.signal,
                });

                if (!response.ok) {
                    throw new Error('Failed to load account page');
                }

                const html = await response.text();
                const parser = new DOMParser();
                const nextDocument = parser.parseFromString(html, 'text/html');

                replaceAccountStyles(nextDocument);
                replaceMainContent(nextDocument);
                replacePageModals(nextDocument);
                runPageScripts(nextDocument);

                document.title = nextDocument.title || document.title;
                syncActiveLink(url);

                if (pushState) {
                    window.history.pushState({ accountPartial: true, url }, '', url);
                }

                const accountPage = document.querySelector('.account-page');
                if (accountPage) {
                    accountPage.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    window.location.href = url;
                }
            } finally {
                setMenuLoading(false);
                setAccountLoading(false);
            }
        };

        document.addEventListener('click', (event) => {
            const menuLink = event.target.closest('[data-account-nav]');

            if (menuLink && canHandleAccountLink(menuLink, event)) {
                event.preventDefault();
                loadAccountPage(menuLink.href);
                return;
            }

            const contentLink = event.target.closest('.account-page a[href]');

            if (!contentLink || contentLink.closest('[data-pagination-container]')) {
                return;
            }

            if (!canHandleAccountLink(contentLink, event)) {
                return;
            }

            event.preventDefault();
            loadAccountPage(contentLink.href);
        });

        window.addEventListener('popstate', () => {
            if (isAccountUrl(window.location.href)) {
                loadAccountPage(window.location.href, { pushState: false });
            }
        });
    })();
</script>

