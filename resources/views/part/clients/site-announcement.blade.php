@php
    $announcementSettings = $announcementSettings ?? [];
    $currentLocale = app()->getLocale();
    $localeSuffix = match ($currentLocale) {
        'en' => '_en',
        'ko' => '_ko',
        'ja' => '_ja',
        'zh' => '_zh',
        default => '',
    };

    $localizedSetting = function (string $key) use ($announcementSettings, $localeSuffix) {
        $localizedValue = trim((string) ($announcementSettings[$key . $localeSuffix] ?? ''));

        if ($localizedValue !== '') {
            return $localizedValue;
        }

        return trim((string) ($announcementSettings[$key] ?? ''));
    };

    $globalNoticeEnabled = (int) ($announcementSettings['global_notice_enabled'] ?? 0) === 1;
    $globalNoticeTitle = $localizedSetting('global_notice_title');
    $globalNoticeContent = $localizedSetting('global_notice_content');
    $globalNoticeLinkLabel = $localizedSetting('global_notice_link_label');
    $globalNoticeLinkUrl = $localizedSetting('global_notice_link_url');
    $hasGlobalNotice = $globalNoticeEnabled && ($globalNoticeTitle !== '' || $globalNoticeContent !== '');

    $popupNoticeEnabled = (int) ($announcementSettings['popup_notice_enabled'] ?? 0) === 1;
    $popupNoticeTitle = $localizedSetting('popup_notice_title');
    $popupNoticeContent = $localizedSetting('popup_notice_content');
    $popupNoticeLinkLabel = $localizedSetting('popup_notice_link_label');
    $popupNoticeLinkUrl = $localizedSetting('popup_notice_link_url');
    $popupSnoozeMinutes = max(1, (int) ($announcementSettings['popup_notice_snooze_minutes'] ?? 60));
    $popupVersion = (string) ($popupAnnouncementVersion ?? '0');
    $hasPopupNotice = $popupNoticeEnabled && ($popupNoticeTitle !== '' || $popupNoticeContent !== '');
@endphp

@if ($hasGlobalNotice)
    <section class="site-announcement" aria-label="Thông báo toàn website">
        <div class="container">
            <div class="site-announcement__inner">
                <div class="site-announcement__icon" aria-hidden="true">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <div class="site-announcement__content">
                    @if ($globalNoticeTitle !== '')
                        <div class="site-announcement__title-marquee" data-announcement-marquee>
                            <div class="site-announcement__track" data-announcement-track>
                                <p class="site-announcement__title" data-announcement-copy>{{ $globalNoticeTitle }}</p>
                                <p class="site-announcement__title site-announcement__title--clone" aria-hidden="true">
                                    {{ $globalNoticeTitle }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if ($globalNoticeContent !== '')
                        <div class="site-announcement__marquee" data-announcement-marquee>
                            <div class="site-announcement__track" data-announcement-track>
                                <div class="site-announcement__text" data-announcement-copy>{!! $globalNoticeContent !!}</div>
                                <div class="site-announcement__text site-announcement__text--clone" aria-hidden="true">
                                    {!! $globalNoticeContent !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($globalNoticeLinkLabel !== '' && $globalNoticeLinkUrl !== '')
                    <a class="site-announcement__link" href="{{ $globalNoticeLinkUrl }}">
                        {{ $globalNoticeLinkLabel }}
                    </a>
                @endif
            </div>
        </div>
    </section>

    <script>
        (() => {
            const marquees = document.querySelectorAll('[data-announcement-marquee]');

            marquees.forEach((marquee) => {
                const track = marquee.querySelector('[data-announcement-track]');
                const copy = marquee.querySelector('[data-announcement-copy]');

                if (!track || !copy) {
                    return;
                }

                const syncMarqueeState = () => {
                    const shouldScroll = copy.scrollWidth > marquee.clientWidth;
                    marquee.classList.toggle('is-animated', shouldScroll);
                };

                syncMarqueeState();
                window.addEventListener('resize', syncMarqueeState);
            });
        })();
    </script>
@endif

@if ($hasPopupNotice)
    <div class="site-popup" data-site-popup data-popup-version="{{ $popupVersion }}"
        data-popup-snooze-minutes="{{ $popupSnoozeMinutes }}" hidden>
        <div class="site-popup__backdrop" data-popup-close></div>
        <div class="site-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="site-popup-title">
            <button class="site-popup__close" type="button" aria-label="Đóng thông báo" data-popup-close>
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="site-popup__badge">
                <i class="fa-solid fa-sparkles"></i>
                <span>Tin mới</span>
            </div>

            @if ($popupNoticeTitle !== '')
                <h2 class="site-popup__title" id="site-popup-title">{{ $popupNoticeTitle }}</h2>
            @endif

            @if ($popupNoticeContent !== '')
                <div class="site-popup__content">{!! $popupNoticeContent !!}</div>
            @endif

            <div class="site-popup__actions">
                <div class="site-popup__actions-left">
                    @if ($popupNoticeLinkLabel !== '' && $popupNoticeLinkUrl !== '')
                        <a class="site-popup__primary" href="{{ $popupNoticeLinkUrl }}">
                            {{ $popupNoticeLinkLabel }}
                        </a>
                    @endif
                </div>

                <div class="site-popup__actions-right">
                    <button class="site-popup__ghost" type="button" data-popup-snooze>
                        Tắt trong {{ $popupSnoozeMinutes }} phút
                    </button>
                    <button class="site-popup__secondary" type="button" data-popup-close>Đã hiểu</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const popup = document.querySelector('[data-site-popup]');

            if (!popup) {
                return;
            }

            const version = popup.dataset.popupVersion || '0';
            const snoozeKey = `site-popup-snooze-until-${version}`;
            const snoozeMinutes = Math.max(1, Number.parseInt(popup.dataset.popupSnoozeMinutes || '60', 10) || 60);
            const closeButtons = popup.querySelectorAll('[data-popup-close]');
            const snoozeButton = popup.querySelector('[data-popup-snooze]');
            let snoozeUntil = null;

            try {
                snoozeUntil = localStorage.getItem(snoozeKey);
            } catch (error) {
                snoozeUntil = null;
            }

            const closePopup = () => {
                popup.setAttribute('hidden', 'hidden');
                document.body.classList.remove('site-popup-open');
            };

            const snoozePopup = () => {
                popup.setAttribute('hidden', 'hidden');
                document.body.classList.remove('site-popup-open');

                try {
                    const nextVisibleAt = Date.now() + (snoozeMinutes * 60 * 1000);
                    localStorage.setItem(snoozeKey, String(nextVisibleAt));
                } catch (error) {
                    // Ignore storage failures and still allow the popup to close.
                }
            };

            const openPopup = () => {
                popup.removeAttribute('hidden');
                document.body.classList.add('site-popup-open');
            };

            closeButtons.forEach((button) => {
                button.addEventListener('click', closePopup);
            });

            if (snoozeButton) {
                snoozeButton.addEventListener('click', snoozePopup);
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !popup.hasAttribute('hidden')) {
                    closePopup();
                }
            });

            if (snoozeUntil && Number.parseInt(snoozeUntil, 10) > Date.now()) {
                return;
            }

            window.setTimeout(openPopup, 450);
        })();
    </script>
@endif
