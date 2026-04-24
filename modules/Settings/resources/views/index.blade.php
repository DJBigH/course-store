@extends('layouts.backend')

@section('content')
    @php
        $announcementLocales = [
            'vi' => 'Tiếng Việt',
            'en' => 'English',
            'ko' => '한국어',
            'ja' => '日本語',
            'zh' => '中文'
        ];
    @endphp
    <div class="admin-form">
        <div class="admin-form__header d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">{{ $pageTitle }}</h2>
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
                <div class="col-xl-3 col-lg-4">
                    <div class="card border-0 shadow-sm settings-sidebar">
                        <div class="card-body p-3">
                            <div class="settings-sidebar__title mb-3 fw-bold text-muted small text-uppercase">Nhóm cấu hình</div>
                            <div class="settings-sidebar__nav d-flex flex-column gap-2">
                                <button type="button" class="btn settings-tab-btn is-active text-start px-3 py-2 border-0" data-settings-tab="general">Hệ thống</button>
                                <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="operations">Vận hành</button>
                                <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="banners">Banner</button>
                                <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="currency">Tỷ giá</button>
                                @if ($canUpdateGeneralSettings)
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="mail">Cấu hình mail</button>
                                @endif
                                <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="announcements">Thông báo</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9 col-lg-8">
                    <div class="settings-panels">
                        {{-- Panel: Hệ thống --}}
                        <div class="settings-panel is-active" data-settings-panel="general">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-header fw-bold bg-white border-bottom-0 pt-3">Thông tin chung</div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Tên website</label>
                                                <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $settings['site_name'] ?? '') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" value="{{ old('email', $settings['email'] ?? '') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Số điện thoại</label>
                                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $settings['phone'] ?? '') }}">
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label">Địa chỉ</label>
                                                <textarea name="address" class="form-control" rows="2">{{ old('address', $settings['address'] ?? '') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-header fw-bold bg-white border-bottom-0 pt-3">Mạng xã hội</div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Facebook</label>
                                                <input type="text" name="facebook" class="form-control" value="{{ old('facebook', $settings['facebook'] ?? '') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Instagram</label>
                                                <input type="text" name="instagram" class="form-control" value="{{ old('instagram', $settings['instagram'] ?? '') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Youtube</label>
                                                <input type="text" name="youtube" class="form-control" value="{{ old('youtube', $settings['youtube'] ?? '') }}">
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label">TikTok</label>
                                                <input type="text" name="tiktok" class="form-control" value="{{ old('tiktok', $settings['tiktok'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel: Vận hành --}}
                        <div class="settings-panel" data-settings-panel="operations">
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">Cài đặt vận hành</div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Checkout countdown (phút)</label>
                                            <input type="number" min="0" max="10080" name="checkout_countdown_minutes" class="form-control" value="{{ old('checkout_countdown_minutes', $settings['checkout_countdown_minutes'] ?? config('checkout.checkout_countdown', 0)) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">2FA xác thực lại (giây)</label>
                                            <input type="number" min="60" max="86400" name="student_two_factor_timeout" class="form-control" value="{{ old('student_two_factor_timeout', $settings['student_two_factor_timeout'] ?? config('auth.student_two_factor_timeout', 600)) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">2FA hết hạn mã (giây)</label>
                                            <input type="number" min="60" max="86400" name="student_two_factor_code_expire" class="form-control" value="{{ old('student_two_factor_code_expire', $settings['student_two_factor_code_expire'] ?? config('auth.student_two_factor_code_expire', 600)) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">2FA gửi lại mã (giây)</label>
                                            <input type="number" min="10" max="3600" name="student_two_factor_resend_cooldown" class="form-control" value="{{ old('student_two_factor_resend_cooldown', $settings['student_two_factor_resend_cooldown'] ?? config('auth.student_two_factor_resend_cooldown', 60)) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Thiết bị tối đa</label>
                                            <input type="number" min="1" max="10" name="max_devices" class="form-control" value="{{ old('max_devices', $settings['max_devices'] ?? config('auth.max_devices', 1)) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Chatbot TTL (phút)</label>
                                            <input type="number" min="5" max="1440" name="chatbot_message_ttl_minutes" class="form-control" value="{{ old('chatbot_message_ttl_minutes', $settings['chatbot_message_ttl_minutes'] ?? env('CHATBOT_MESSAGE_TTL_MINUTES', 10)) }}">
                                        </div>
                                        <div class="col-12 mt-4">
                                            <div class="border rounded-4 p-3 bg-light">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" id="chatbot_widget_enabled" name="chatbot_widget_enabled" value="1" @checked(old('chatbot_widget_enabled', $settings['chatbot_widget_enabled'] ?? '1') == '1')>
                                                    <label class="form-check-label fw-bold" for="chatbot_widget_enabled">Bật chatbot thường (Widget)</label>
                                                </div>
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" id="chatbot_enabled" name="chatbot_enabled" value="1" @checked(old('chatbot_enabled', $settings['chatbot_enabled'] ?? '1') == '1')>
                                                    <label class="form-check-label fw-bold" for="chatbot_enabled">Bật trí tuệ nhân tạo Gemini</label>
                                                </div>
                                                <div class="alert alert-warning mt-3 mb-0 d-none" data-chatbot-gemini-note role="alert">Hãy bật chatbot thường trước rồi mới bật được Gemini</div>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-3">
                                            <div class="border rounded-4 p-3 bg-light">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" id="ai_quiz_enabled" name="ai_quiz_enabled" value="1" @checked(old('ai_quiz_enabled', $settings['ai_quiz_enabled'] ?? '1') == '1')>
                                                    <label class="form-check-label fw-bold" for="ai_quiz_enabled">Cho phép Giảng viên tạo Quiz bằng AI (Generative AI)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel: Banner --}}
                        <div class="settings-panel" data-settings-panel="banners">
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">Cấu hình Banner & Logo</div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <label class="form-label">Logo website</label>
                                        <input type="file" name="logo" class="form-control">
                                        @if (!empty($settings['logo']))
                                            <div class="mt-2 p-2 border rounded d-inline-block bg-light">
                                                <img src="{{ asset('storage/' . $settings['logo']) }}" height="60">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Banner Slider (Nhiều ảnh)</label>
                                        <input type="file" name="banner_slider[]" class="form-control" multiple>
                                        @if (!empty($settings['banner_slider']))
                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                @foreach (json_decode($settings['banner_slider'], true) as $img)
                                                    <img src="{{ asset('storage/' . $img) }}" class="rounded shadow-sm" height="60">
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Banner bên phải (Tối đa 3 ảnh)</label>
                                        <input type="file" name="banner_right[]" class="form-control" multiple>
                                        @if (!empty($settings['banner_right']))
                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                @foreach (json_decode($settings['banner_right'], true) as $img)
                                                    <img src="{{ asset('storage/' . $img) }}" class="rounded shadow-sm" height="60">
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label">Banner Full Width</label>
                                        <input type="file" name="banner_full" class="form-control">
                                        @if (!empty($settings['banner_full']))
                                            <div class="mt-2 p-2 border rounded d-inline-block bg-light">
                                                <img src="{{ asset('storage/' . $settings['banner_full']) }}" height="80">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel: Tỷ giá --}}
                        <div class="settings-panel" data-settings-panel="currency">
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                    <span>Cấu hình tiền tệ & Tỷ giá</span>
                                    @if ($canUpdateGeneralSettings)
                                        <button type="submit" formaction="{{ route('settings.sync-exchange-rates') }}" formmethod="POST" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                            <i class="bi bi-arrow-repeat me-1"></i> Cập nhật tỷ giá mới nhất (Không cần Key)
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Tỷ giá USD / VND</label>
                                            <input type="number" step="0.01" name="currency_rate_usd" class="form-control" value="{{ old('currency_rate_usd', $settings['currency_rate_usd'] ?? '') }}">
                                            <small class="text-muted">1 USD tương đương bao nhiêu VND.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tỷ giá KRW / VND</label>
                                            <input type="number" step="0.01" name="currency_rate_krw" class="form-control" value="{{ old('currency_rate_krw', $settings['currency_rate_krw'] ?? '') }}">
                                            <small class="text-muted">1 Won Hàn tương đương bao nhiêu VND.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tỷ giá JPY / VND</label>
                                            <input type="number" step="0.01" name="currency_rate_jpy" class="form-control" value="{{ old('currency_rate_jpy', $settings['currency_rate_jpy'] ?? '') }}">
                                            <small class="text-muted">1 Yên Nhật tương đương bao nhiêu VND.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tỷ giá CNY / VND</label>
                                            <input type="number" step="0.01" name="currency_rate_cny" class="form-control" value="{{ old('currency_rate_cny', $settings['currency_rate_cny'] ?? '') }}">
                                            <small class="text-muted">1 Nhân dân tệ tương đương bao nhiêu VND.</small>
                                        </div>
                                    </div>
                                    <hr class="opacity-10">
                                    <div class="row g-4">
                                        
                                        <div class="col-md-6">
                                            <label class="form-label">Phí chuyển đổi tiền tệ (%)</label>
                                            <div class="input-group">
                                                <input type="number" step="0.1" name="currency_conversion_fee" class="form-control" value="{{ old('currency_conversion_fee', $settings['currency_conversion_fee'] ?? '0') }}">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <small class="text-muted">Phí cộng thêm khi quy đổi ngoại tệ.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel: Mail --}}
                        <div class="settings-panel" data-settings-panel="mail">
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">Cấu hình Mail hệ thống</div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="mail_enabled" name="mail_enabled" value="1" @checked(old('mail_enabled', $settings['mail_enabled'] ?? '0') == '1')>
                                        <label class="form-check-label fw-bold" for="mail_enabled">Bật hệ thống gửi mail</label>
                                    </div>
                                    <div class="settings-toggle-panel" data-settings-toggle-target="mail_enabled">
                                        
                                        <button type="submit" formaction="{{ route('settings.test-mail') }}" formmethod="POST" class="btn btn-outline-primary rounded-pill px-4 btn-sm">
                                            <i class="bi bi-send me-1"></i> Gửi email kiểm tra (Test Mail)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel: Thông báo --}}
                        <div class="settings-panel" data-settings-panel="announcements">
                            {{-- Global Notice --}}
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">Thông báo toàn website</div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-4">
                                        <input class="form-check-input" type="checkbox" id="global_notice_enabled" name="global_notice_enabled" value="1" @checked(old('global_notice_enabled', $settings['global_notice_enabled'] ?? '0') == '1')>
                                        <label class="form-check-label fw-bold" for="global_notice_enabled">Hiển thị thanh thông báo trên Client</label>
                                    </div>
                                    <div class="settings-visibility-panel" data-settings-visibility-target="global_notice_enabled">
                                        <div class="btn-group mb-4 w-100 shadow-sm rounded-3 overflow-hidden" role="group">
                                            @foreach ($announcementLocales as $locale => $label)
                                                <input type="radio" class="btn-check" name="global_notice_lang" id="global_notice_lang_{{ $locale }}" value="{{ $locale }}" @checked($locale === 'vi')>
                                                <label class="btn btn-outline-primary border-0 py-2" for="global_notice_lang_{{ $locale }}">{{ $label }}</label>
                                            @endforeach
                                        </div>
                                        @foreach ($announcementLocales as $locale => $label)
                                            @php $suffix = $locale === 'vi' ? '' : '_' . $locale; @endphp
                                            <div class="announcement-lang-block announcement-global-lang announcement-global-lang-{{ $locale }} {{ $locale !== 'vi' ? 'd-none' : '' }}">
                                                <div class="mb-3">
                                                    <label class="form-label">Tiêu đề ({{ $label }})</label>
                                                    <input type="text" name="global_notice_title{{ $suffix }}" class="form-control" value="{{ old('global_notice_title' . $suffix, $settings['global_notice_title' . $suffix] ?? '') }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nội dung ({{ $label }})</label>
                                                    <textarea name="global_notice_content{{ $suffix }}" class="form-control ckeditor" rows="3">{{ old('global_notice_content' . $suffix, $settings['global_notice_content' . $suffix] ?? '') }}</textarea>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Text nút ({{ $label }})</label>
                                                        <input type="text" name="global_notice_link_label{{ $suffix }}" class="form-control" value="{{ old('global_notice_link_label' . $suffix, $settings['global_notice_link_label' . $suffix] ?? '') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">URL nút ({{ $label }})</label>
                                                        <input type="text" name="global_notice_link_url{{ $suffix }}" class="form-control" value="{{ old('global_notice_link_url' . $suffix, $settings['global_notice_link_url' . $suffix] ?? '') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Popup Notice --}}
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">Popup thông báo</div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-4">
                                        <input class="form-check-input" type="checkbox" id="popup_notice_enabled" name="popup_notice_enabled" value="1" @checked(old('popup_notice_enabled', $settings['popup_notice_enabled'] ?? '0') == '1')>
                                        <label class="form-check-label fw-bold" for="popup_notice_enabled">Bật popup khi vào website</label>
                                    </div>
                                    <div class="settings-visibility-panel" data-settings-visibility-target="popup_notice_enabled">
                                        <div class="btn-group mb-4 w-100 shadow-sm rounded-3 overflow-hidden" role="group">
                                            @foreach ($announcementLocales as $locale => $label)
                                                <input type="radio" class="btn-check" name="popup_notice_lang" id="popup_notice_lang_{{ $locale }}" value="{{ $locale }}" @checked($locale === 'vi')>
                                                <label class="btn btn-outline-primary border-0 py-2" for="popup_notice_lang_{{ $locale }}">{{ $label }}</label>
                                            @endforeach
                                        </div>
                                        @foreach ($announcementLocales as $locale => $label)
                                            @php $suffix = $locale === 'vi' ? '' : '_' . $locale; @endphp
                                            <div class="announcement-lang-block announcement-popup-lang announcement-popup-lang-{{ $locale }} {{ $locale !== 'vi' ? 'd-none' : '' }}">
                                                <div class="mb-3">
                                                    <label class="form-label">Tiêu đề ({{ $label }})</label>
                                                    <input type="text" name="popup_notice_title{{ $suffix }}" class="form-control" value="{{ old('popup_notice_title' . $suffix, $settings['popup_notice_title' . $suffix] ?? '') }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nội dung ({{ $label }})</label>
                                                    <textarea name="popup_notice_content{{ $suffix }}" class="form-control ckeditor" rows="4">{{ old('popup_notice_content' . $suffix, $settings['popup_notice_content' . $suffix] ?? '') }}</textarea>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Text nút ({{ $label }})</label>
                                                        <input type="text" name="popup_notice_link_label{{ $suffix }}" class="form-control" value="{{ old('popup_notice_link_label' . $suffix, $settings['popup_notice_link_label' . $suffix] ?? '') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">URL nút ({{ $label }})</label>
                                                        <input type="text" name="popup_notice_link_url{{ $suffix }}" class="form-control" value="{{ old('popup_notice_link_url' . $suffix, $settings['popup_notice_link_url' . $suffix] ?? '') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="mt-4 pt-3 border-top">
                                            <label class="form-label">Số phút ẩn popup tạm thời</label>
                                            <input type="number" name="popup_notice_snooze_minutes" class="form-control" style="max-width: 200px;" value="{{ old('popup_notice_snooze_minutes', $settings['popup_notice_snooze_minutes'] ?? '60') }}">
                                            <small class="text-muted">Khi người dùng tắt popup, sau bao nhiêu phút mới hiện lại.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> {{-- end settings-panels --}}
                </div> {{-- end col-xl-9 --}}
            </div> {{-- end row --}}

            <div class="admin-form__footer mt-4 pt-4 border-top">
                @if ($canUpdateGeneralSettings)
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow-sm">
                        Lưu cấu hình hệ thống
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
            top: 90px;
            border-radius: 16px;
        }
        .settings-tab-btn {
            border-radius: 12px;
            transition: all 0.2s ease;
            font-weight: 500;
            color: #64748b;
        }
        .settings-tab-btn:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .settings-tab-btn.is-active {
            background-color: #2563eb !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .settings-panel {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .settings-panel.is-active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .settings-visibility-panel.is-hidden {
            display: none;
        }
        .settings-toggle-panel.is-disabled {
            opacity: 0.5;
            pointer-events: none;
            filter: grayscale(1);
        }
        [data-theme='dark'] .card {
            background-color: #1e293b;
        }
        [data-theme='dark'] .bg-white {
            background-color: #1e293b !important;
            color: #f8fafc;
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const tabButtons = document.querySelectorAll('[data-settings-tab]');
            const panels = document.querySelectorAll('[data-settings-panel]');

            const activateTab = (tab) => {
                tabButtons.forEach(btn => btn.classList.toggle('is-active', btn.dataset.settingsTab === tab));
                panels.forEach(panel => panel.classList.toggle('is-active', panel.dataset.settingsPanel === tab));
                localStorage.setItem('admin_settings_tab', tab);
            };

            const savedTab = localStorage.getItem('admin_settings_tab');
            const initialTab = Array.from(tabButtons).find(btn => btn.dataset.settingsTab === savedTab) ? savedTab : tabButtons[0].dataset.settingsTab;
            activateTab(initialTab);

            tabButtons.forEach(btn => btn.addEventListener('click', () => activateTab(btn.dataset.settingsTab)));

            const bindTogglePanel = (toggleId) => {
                const toggle = document.getElementById(toggleId);
                const panel = document.querySelector(`[data-settings-toggle-target="${toggleId}"]`);
                if (!toggle || !panel) return;
                const sync = () => {
                    panel.classList.toggle('is-disabled', !toggle.checked);
                    panel.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = !toggle.checked);
                };
                sync();
                toggle.addEventListener('change', sync);
            };

            const bindVisibilityPanel = (toggleId) => {
                const toggle = document.getElementById(toggleId);
                const panel = document.querySelector(`[data-settings-visibility-target="${toggleId}"]`);
                if (!toggle || !panel) return;
                const sync = () => panel.classList.toggle('is-hidden', !toggle.checked);
                sync();
                toggle.addEventListener('change', sync);
            };

            const bindLangSwitcher = (groupName, blockClass, storageKey) => {
                const inputs = document.querySelectorAll(`input[name="${groupName}"]`);
                const show = (locale) => {
                    document.querySelectorAll(`.${blockClass}`).forEach(el => el.classList.add('d-none'));
                    document.querySelectorAll(`.${blockClass}-${locale}`).forEach(el => el.classList.remove('d-none'));
                    localStorage.setItem(storageKey, locale);
                };
                const saved = localStorage.getItem(storageKey) || 'vi';
                const current = document.getElementById(`${groupName}_${saved}`) || document.getElementById(`${groupName}_vi`);
                if (current) { current.checked = true; show(current.value); }
                inputs.forEach(input => input.addEventListener('change', () => show(input.value)));
            };

            bindLangSwitcher('global_notice_lang', 'announcement-global-lang', 'admin_global_notice_lang');
            bindLangSwitcher('popup_notice_lang', 'announcement-popup-lang', 'admin_popup_notice_lang');
            bindTogglePanel('mail_enabled');
            bindVisibilityPanel('global_notice_enabled');
            bindVisibilityPanel('popup_notice_enabled');

            // Chatbot Dependency
            const widgetToggle = document.getElementById('chatbot_widget_enabled');
            const geminiToggle = document.getElementById('chatbot_enabled');
            const note = document.querySelector('[data-chatbot-gemini-note]');
            if (widgetToggle && geminiToggle) {
                const sync = () => {
                    geminiToggle.disabled = !widgetToggle.checked;
                    if (!widgetToggle.checked) geminiToggle.checked = false;
                    if (note) note.classList.toggle('d-none', widgetToggle.checked);
                };
                widgetToggle.addEventListener('change', sync);
                sync();
            }
        })();
    </script>
@endsection
