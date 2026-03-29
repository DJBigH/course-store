@extends('layouts.backend')

@php
    $announcementLocales = [
        'vi' => 'VI',
        'en' => 'EN',
        'ko' => 'KO',
        'ja' => 'JA',
        'zh' => 'ZH',
    ];
    $canUpdateGeneralSettings = $canUpdateGeneralSettings ?? (auth()->user()?->hasPermission('settings.update') ?? false);
    $canSubmitSettings = $canUpdateGeneralSettings;
@endphp

@section('content')
    <div class="admin-form">
        <div class="admin-form__header">
            <div>
                <h5 class="mb-1">Cấu hình hệ thống</h5>
                <p class="text-muted mb-0">Quản lý thông tin website, banner, tỷ giá và thông báo toàn hệ thống.</p>
            </div>
            @if (auth()->user()?->hasPermission('settings.logs'))
                <a href="{{ route('settings.logs') }}" class="btn btn-light border">Lịch sử</a>
            @endif
        </div>
        @if (session('msg'))
            <div class="alert alert-{{ session('msgType', 'success') }} border-0 rounded-4">
                {{ session('msg') }}
            </div>
        @endif

        <form action="{{ route('settings.post-setting') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <div class="col-xl-3">
                    <div class="card border-0 shadow-sm settings-sidebar">
                        <div class="card-body p-3">
                            <div class="settings-sidebar__title">Nhóm cấu hình</div>
                            <div class="settings-sidebar__nav">
                                <button type="button" class="btn settings-tab-btn is-active" data-settings-tab="general">Hệ thống</button>
                                <button type="button" class="btn settings-tab-btn" data-settings-tab="operations">Vận hành</button>
                                <button type="button" class="btn settings-tab-btn" data-settings-tab="banners">Banner</button>
                                <button type="button" class="btn settings-tab-btn" data-settings-tab="currency">Tỷ giá</button>
                                @if ($canUpdateGeneralSettings)
                                    <button type="button" class="btn settings-tab-btn" data-settings-tab="mail">Cấu hình mail</button>
                                @endif
                                <button type="button" class="btn settings-tab-btn" data-settings-tab="announcements">Thông báo</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9">
                    <div class="settings-panels">
                        <div class="settings-panel is-active" data-settings-panel="general">
                            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header fw-bold">
                            Thông tin chung
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tên website</label>
                                <input type="text" name="site_name" class="form-control"
                                    value="{{ old('site_name', $settings['site_name'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ old('email', $settings['email'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control"
                                    value="{{ old('phone', $settings['phone'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Địa chỉ</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $settings['address'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header fw-bold">
                            Mạng xã hội
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Facebook</label>
                                <input type="text" name="facebook" class="form-control"
                                    value="{{ old('facebook', $settings['facebook'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Instagram</label>
                                <input type="text" name="instagram" class="form-control"
                                    value="{{ old('instagram', $settings['instagram'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Youtube</label>
                                <input type="text" name="youtube" class="form-control"
                                    value="{{ old('youtube', $settings['youtube'] ?? '') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">TikTok</label>
                                <input type="text" name="tiktok" class="form-control"
                                    value="{{ old('tiktok', $settings['tiktok'] ?? '') }}">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
                        </div>
                        <div class="settings-panel" data-settings-panel="operations">
                            <div class="card mt-3">
                                <div class="card-header fw-bold">
                                    Cài đặt vận hành
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Checkout countdown (phút)</label>
                                            <input type="number" min="0" max="10080" name="checkout_countdown_minutes" class="form-control"
                                                value="{{ old('checkout_countdown_minutes', $settings['checkout_countdown_minutes'] ?? config('checkout.checkout_countdown', 0)) }}">
                                            <small class="text-muted">0 là giới hạn thời gian thanh toán</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">2FA xác thực lại (giây)</label>
                                            <input type="number" min="60" max="86400" name="student_two_factor_timeout" class="form-control"
                                                value="{{ old('student_two_factor_timeout', $settings['student_two_factor_timeout'] ?? config('auth.student_two_factor_timeout', 600)) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">2FA hết hạn mã (giây)</label>
                                            <input type="number" min="60" max="86400" name="student_two_factor_code_expire" class="form-control"
                                                value="{{ old('student_two_factor_code_expire', $settings['student_two_factor_code_expire'] ?? config('auth.student_two_factor_code_expire', 600)) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">2FA gửi lại mã (giây)</label>
                                            <input type="number" min="10" max="3600" name="student_two_factor_resend_cooldown" class="form-control"
                                                value="{{ old('student_two_factor_resend_cooldown', $settings['student_two_factor_resend_cooldown'] ?? config('auth.student_two_factor_resend_cooldown', 60)) }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Giới hạn thiết bị đăng nhập</label>
                                            <input type="number" min="1" max="10" name="max_devices" class="form-control"
                                                value="{{ old('max_devices', $settings['max_devices'] ?? config('auth.max_devices', 1)) }}">
                                            <small class="text-muted">Số thiết bị học viên được phép đăng nhập đồng thời</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Thời gian nhớ hội thoại chatbot (phút)</label>
                                            <input type="number" min="5" max="1440" name="chatbot_message_ttl_minutes" class="form-control"
                                                value="{{ old('chatbot_message_ttl_minutes', $settings['chatbot_message_ttl_minutes'] ?? env('CHATBOT_MESSAGE_TTL_MINUTES', 10)) }}">
                                            <small class="text-muted">Bot sẽ quên hội thoại sau số phút này nếu không có tương tác mới</small>
                                        </div>
                                        <div class="col-12">
                                            <div class="border rounded-3 p-3">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" id="chatbot_widget_enabled"
                                                        name="chatbot_widget_enabled" value="1"
                                                        @checked(old('chatbot_widget_enabled', $settings['chatbot_widget_enabled'] ?? '1') == '1')>
                                                    <label class="form-check-label" for="chatbot_widget_enabled">Bật chatbot thường</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="border rounded-3 p-3">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" id="chatbot_enabled"
                                                        name="chatbot_enabled" value="1"
                                                        @checked(old('chatbot_enabled', $settings['chatbot_enabled'] ?? '1') == '1')>
                                                    <label class="form-check-label" for="chatbot_enabled">Bật chatbot Gemini</label>
                                                </div>
                                            </div>
                                            <div class="alert alert-warning mt-2 mb-0 d-none" data-chatbot-gemini-note role="alert">Hãy bật chatbot thường trước rồi mới bật được Gemini</div>
                                            @if (($geminiHealth['has_issue'] ?? false) && in_array($geminiHealth['reason'] ?? '', ['quota_exceeded', 'auth_error'], true))
                                                <div class="alert alert-{{ ($geminiHealth['is_disabled'] ?? false) ? 'danger' : 'warning' }} mt-2 mb-0" role="alert">
                                                    <div class="fw-semibold mb-1">Gemini gap loi {{ $geminiHealth['reason'] === 'quota_exceeded' ? 'quota' : 'xac thuc API' }}</div>
                                                    <div>{{ ($geminiHealth['is_disabled'] ?? false) ? 'He thong da tu dong tat Gemini sau khi loi lap lai qua nguong.' : 'Gemini dang gan nguong tu tat neu loi lap lai them.' }} Hien tai: {{ $geminiHealth['failure_count'] ?? 0 }}/{{ $geminiHealth['threshold'] ?? 3 }}.</div>
                                                    @if (!empty($geminiHealth['last_at']))
                                                        <div class="small mt-1 text-muted">Lan loi gan nhat: {{ $geminiHealth['last_at']->format('d/m/Y H:i:s') }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="settings-panel" data-settings-panel="banners">
                            <div class="card mt-3">
                <div class="card-header fw-bold">
                    Banner trang chủ
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" class="form-control">

                        @if (!empty($settings['logo']))
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settings['logo']) }}" height="80">
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Banner slider (nhiều ảnh)</label>
                        <input type="file" name="banner_slider[]" class="form-control" multiple>

                        @if (!empty($settings['banner_slider']))
                            <div class="d-flex gap-2 mt-2">
                                @foreach (json_decode($settings['banner_slider'], true) as $img)
                                    <img src="{{ asset('storage/' . $img) }}" height="60">
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Banner bên phải (tối đa 3 ảnh)</label>
                        <input type="file" name="banner_right[]" class="form-control" multiple>

                        @if (!empty($settings['banner_right']))
                            <div class="d-flex gap-2 mt-2">
                                @foreach (json_decode($settings['banner_right'], true) as $img)
                                    <img src="{{ asset('storage/' . $img) }}" height="60">
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Banner full width</label>
                        <input type="file" name="banner_full" class="form-control">

                        @if (!empty($settings['banner_full']))
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $settings['banner_full']) }}" height="80">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
                        </div>

                        <div class="settings-panel" data-settings-panel="currency">
                            <div class="card mt-3">
                <div class="card-header fw-bold">
                    Cấu hình tiền tệ
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tỷ giá USD / VND</label>
                                <input type="number" step="0.01" min="0.01" name="currency_rate_usd"
                                    class="form-control"
                                    value="{{ old('currency_rate_usd', $settings['currency_rate_usd'] ?? config('currency.rates.usd')) }}">
                                <small class="text-muted">1 USD tương ứng bao nhiêu VND.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tỷ giá KRW / VND</label>
                                <input type="number" step="0.01" min="0.01" name="currency_rate_krw"
                                    class="form-control"
                                    value="{{ old('currency_rate_krw', $settings['currency_rate_krw'] ?? config('currency.rates.krw')) }}">
                                <small class="text-muted">1 Won Hàn tương ứng bao nhiêu VND.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tỷ giá JPY / VND</label>
                                <input type="number" step="0.01" min="0.01" name="currency_rate_jpy"
                                    class="form-control"
                                    value="{{ old('currency_rate_jpy', $settings['currency_rate_jpy'] ?? config('currency.rates.jpy')) }}">
                                <small class="text-muted">1 Yên Nhật tương ứng bao nhiêu VND.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tỷ giá CNY / VND</label>
                                <input type="number" step="0.01" min="0.01" name="currency_rate_cny"
                                    class="form-control"
                                    value="{{ old('currency_rate_cny', $settings['currency_rate_cny'] ?? config('currency.rates.cny')) }}">
                                <small class="text-muted">1 Nhân dân tệ tương ứng bao nhiêu VND.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                        </div>

                        @if ($canUpdateGeneralSettings)
                        <div class="settings-panel" data-settings-panel="mail">
                            <div class="card mt-3">
                                <div class="card-header fw-bold">Cấu hình mail</div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="mail_enabled" name="mail_enabled" value="1"
                                            @checked(old('mail_enabled', $settings['mail_enabled'] ?? '0') == '1')>
                                        <label class="form-check-label" for="mail_enabled">Bật gửi mail</label>
                                    </div>

                                    <div class="settings-toggle-panel" data-settings-toggle-target="mail_enabled">
                                        <button type="submit" formaction="{{ route('settings.test-mail') }}" formmethod="POST" class="btn btn-outline-primary btn-sm">Gửi mail test</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @endif
                        <div class="settings-panel" data-settings-panel="announcements">
                            <div class="card mt-3">
                <div class="card-header fw-bold">
                    Thông báo toàn website
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="global_notice_enabled"
                            name="global_notice_enabled" value="1"
                            @checked(old('global_notice_enabled', $settings['global_notice_enabled'] ?? '0') == '1')>
                        <label class="form-check-label" for="global_notice_enabled">
                            Hiển thị thanh thông báo trên tất cả trang client
                        </label>
                    </div>

                    <div class="settings-visibility-panel" data-settings-visibility-target="global_notice_enabled">
                    <div class="btn-group mb-3" role="group">
                        @foreach ($announcementLocales as $locale => $label)
                            <input type="radio" class="btn-check" name="global_notice_lang"
                                id="global_notice_lang_{{ $locale }}" value="{{ $locale }}"
                                @checked($locale === 'vi')>
                            <label class="btn btn-outline-primary"
                                for="global_notice_lang_{{ $locale }}">{{ $label }}</label>
                        @endforeach
                    </div>

                    @foreach ($announcementLocales as $locale => $label)
                        @php
                            $suffix = $locale === 'vi' ? '' : '_' . $locale;
                        @endphp
                        <div class="announcement-lang-block announcement-global-lang announcement-global-lang-{{ $locale }} {{ $locale !== 'vi' ? 'd-none' : '' }}">
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề thông báo ({{ $label }})</label>
                                <input type="text" name="global_notice_title{{ $suffix }}" class="form-control"
                                    value="{{ old('global_notice_title' . $suffix, $settings['global_notice_title' . $suffix] ?? '') }}"
                                    placeholder="Ví dụ: Ưu đãi tháng này">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nội dung thông báo ({{ $label }})</label>
                                <textarea name="global_notice_content{{ $suffix }}" class="form-control ckeditor" rows="5"
                                    placeholder="Nhập nội dung, có thể thêm định dạng cơ bản">{{ old('global_notice_content' . $suffix, $settings['global_notice_content' . $suffix] ?? '') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-0">
                                        <label class="form-label">Text nút ({{ $label }})</label>
                                        <input type="text" name="global_notice_link_label{{ $suffix }}" class="form-control"
                                            value="{{ old('global_notice_link_label' . $suffix, $settings['global_notice_link_label' . $suffix] ?? '') }}"
                                            placeholder="Xem ngay">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-0">
                                        <label class="form-label">Liên kết nút ({{ $label }})</label>
                                        <input type="text" name="global_notice_link_url{{ $suffix }}" class="form-control"
                                            value="{{ old('global_notice_link_url' . $suffix, $settings['global_notice_link_url' . $suffix] ?? '') }}"
                                            placeholder="https://... hoặc /{{ $locale }}/khoa-hoc">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header fw-bold">
                    Popup khi vào web
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="popup_notice_enabled"
                            name="popup_notice_enabled" value="1"
                            @checked(old('popup_notice_enabled', $settings['popup_notice_enabled'] ?? '0') == '1')>
                        <label class="form-check-label" for="popup_notice_enabled">
                            Bật popup khi khách vào web
                        </label>
                    </div>

                    <div class="settings-visibility-panel" data-settings-visibility-target="popup_notice_enabled">
                    <div class="btn-group mb-3" role="group">
                        @foreach ($announcementLocales as $locale => $label)
                            <input type="radio" class="btn-check" name="popup_notice_lang"
                                id="popup_notice_lang_{{ $locale }}" value="{{ $locale }}"
                                @checked($locale === 'vi')>
                            <label class="btn btn-outline-primary"
                                for="popup_notice_lang_{{ $locale }}">{{ $label }}</label>
                        @endforeach
                    </div>

                    @foreach ($announcementLocales as $locale => $label)
                        @php
                            $suffix = $locale === 'vi' ? '' : '_' . $locale;
                        @endphp
                        <div class="announcement-lang-block announcement-popup-lang announcement-popup-lang-{{ $locale }} {{ $locale !== 'vi' ? 'd-none' : '' }}">
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề popup ({{ $label }})</label>
                                <input type="text" name="popup_notice_title{{ $suffix }}" class="form-control"
                                    value="{{ old('popup_notice_title' . $suffix, $settings['popup_notice_title' . $suffix] ?? '') }}"
                                    placeholder="Ví dụ: Tin mới, sự kiện, lịch nghỉ...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nội dung popup ({{ $label }})</label>
                                <textarea name="popup_notice_content{{ $suffix }}" class="form-control ckeditor" rows="6"
                                    placeholder="Nhập nội dung popup">{{ old('popup_notice_content' . $suffix, $settings['popup_notice_content' . $suffix] ?? '') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-0">
                                        <label class="form-label">Text nút chính ({{ $label }})</label>
                                        <input type="text" name="popup_notice_link_label{{ $suffix }}" class="form-control"
                                            value="{{ old('popup_notice_link_label' . $suffix, $settings['popup_notice_link_label' . $suffix] ?? '') }}"
                                            placeholder="Xem chi tiết">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-0">
                                        <label class="form-label">Liên kết nút chính ({{ $label }})</label>
                                        <input type="text" name="popup_notice_link_url{{ $suffix }}" class="form-control"
                                            value="{{ old('popup_notice_link_url' . $suffix, $settings['popup_notice_link_url' . $suffix] ?? '') }}"
                                            placeholder="https://... hoặc /{{ $locale }}/lien-he">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-3">
                        <label class="form-label">Số phút tắt popup tạm thời</label>
                        <input type="number" min="1" max="10080" name="popup_notice_snooze_minutes"
                            class="form-control"
                            value="{{ old('popup_notice_snooze_minutes', $settings['popup_notice_snooze_minutes'] ?? '60') }}">
                        <small class="text-muted">Nút tắt tạm sẽ ẩn popup theo số phút này. Ví dụ: `60` là 1 giờ.</small>
                    </div>
                    </div>
                </div>
            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-form__footer">
                @if (auth()->user()?->hasPermission('settings.logs'))
                    <a href="{{ route('settings.logs') }}" class="btn btn-warning">Lịch sử</a>
                @endif
                @if ($canSubmitSettings)
                    <button type="submit" class="btn btn-primary">
                        Lưu cấu hình
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection

@section('stylesheets')
    <style>
        .settings-sidebar {
            position: sticky;
            top: 88px;
            border-radius: 20px;
        }

        .settings-sidebar__title {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 12px;
        }

        .settings-sidebar__nav {
            display: grid;
            gap: 10px;
        }

        .settings-tab-btn {
            text-align: left;
            border: 1px solid #dbe3ef;
            background: #fff;
            color: #334155;
            border-radius: 14px;
            padding: 12px 14px;
            font-weight: 600;
            transition: 0.2s ease;
        }

        .settings-tab-btn.is-active {
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 12px 32px rgba(29, 78, 216, 0.18);
        }

        .settings-panel {
            display: none;
        }

        .settings-panel.is-active {
            display: block;
        }

        .settings-visibility-panel.is-hidden {
            display: none;
        }

        .settings-toggle-panel {
            transition: opacity 0.2s ease, filter 0.2s ease;
        }

        .settings-toggle-panel.is-disabled {
            opacity: 0.45;
            filter: grayscale(0.2);
            pointer-events: none;
        }

        @media (max-width: 1199.98px) {
            .settings-sidebar {
                position: static;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const tabButtons = document.querySelectorAll('[data-settings-tab]');
            const panels = document.querySelectorAll('[data-settings-panel]');

            const activateTab = (tab) => {
                tabButtons.forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.settingsTab === tab);
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('is-active', panel.dataset.settingsPanel === tab);
                });

                localStorage.setItem('admin_settings_tab', tab);
            };

            const availableTabs = Array.from(tabButtons).map((button) => button.dataset.settingsTab);
            const savedTab = localStorage.getItem('admin_settings_tab');
            const initialTab = availableTabs.includes(savedTab) ? savedTab : (tabButtons[0]?.dataset.settingsTab || null);

            if (initialTab) {
                activateTab(initialTab);
            }

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => activateTab(button.dataset.settingsTab));
            });

            const bindTogglePanel = (toggleId) => {
                const toggle = document.getElementById(toggleId);
                const panel = document.querySelector(`[data-settings-toggle-target="${toggleId}"]`);

                if (!toggle || !panel) {
                    return;
                }

                const syncState = () => {
                    const enabled = toggle.checked;
                    panel.classList.toggle('is-disabled', !enabled);

                    panel.querySelectorAll('input, select, textarea').forEach((field) => {
                        field.disabled = !enabled;
                    });
                };

                syncState();
                toggle.addEventListener('change', syncState);
            };

            const bindVisibilityPanel = (toggleId) => {
                const toggle = document.getElementById(toggleId);
                const panel = document.querySelector(`[data-settings-visibility-target="${toggleId}"]`);

                if (!toggle || !panel) {
                    return;
                }

                const syncVisibility = () => {
                    panel.classList.toggle('is-hidden', !toggle.checked);
                };

                syncVisibility();
                toggle.addEventListener('change', syncVisibility);
            };

            const bindLanguageSwitcher = (groupName, blockPrefix, storageKey) => {
                const inputs = document.querySelectorAll(`input[name="${groupName}"]`);

                if (!inputs.length) {
                    return;
                }

                const showLanguage = (locale) => {
                    document.querySelectorAll(`.${blockPrefix}`).forEach((block) => {
                        block.classList.add('d-none');
                    });

                    document.querySelectorAll(`.${blockPrefix}-${locale}`).forEach((block) => {
                        block.classList.remove('d-none');
                    });

                    localStorage.setItem(storageKey, locale);
                };

                const savedLocale = localStorage.getItem(storageKey) || 'vi';
                const fallbackInput = document.getElementById(`${groupName}_vi`);
                const targetInput = document.getElementById(`${groupName}_${savedLocale}`) || fallbackInput;

                if (targetInput) {
                    targetInput.checked = true;
                    showLanguage(targetInput.value);
                }

                inputs.forEach((input) => {
                    input.addEventListener('change', () => showLanguage(input.value));
                });
            };

            bindLanguageSwitcher('global_notice_lang', 'announcement-global-lang', 'admin_global_notice_lang');
            const bindChatbotDependency = () => {
                const widgetToggle = document.getElementById('chatbot_widget_enabled');
                const geminiToggle = document.getElementById('chatbot_enabled');
                const note = document.querySelector('[data-chatbot-gemini-note]');

                if (!widgetToggle || !geminiToggle) {
                    return;
                }

                const syncState = () => {
                    const widgetEnabled = widgetToggle.checked;

                    geminiToggle.disabled = !widgetEnabled;
                    if (!widgetEnabled) {
                        geminiToggle.checked = false;
                    }

                    if (note) {
                        note.classList.toggle('d-none', widgetEnabled);
                    }
                };

                widgetToggle.addEventListener('change', syncState);
                geminiToggle.addEventListener('change', () => {
                    if (geminiToggle.checked && !widgetToggle.checked) {
                        geminiToggle.checked = false;
                        if (note) {
                            note.classList.remove('d-none');
                        }
                    }
                });

                syncState();
            };

            bindLanguageSwitcher('popup_notice_lang', 'announcement-popup-lang', 'admin_popup_notice_lang');
            bindChatbotDependency();
            bindTogglePanel('mail_enabled');
            bindVisibilityPanel('global_notice_enabled');
            bindVisibilityPanel('popup_notice_enabled');
        })();
    </script>
@endsection
















