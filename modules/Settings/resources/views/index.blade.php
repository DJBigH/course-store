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

        $hasAnyTab = $currentUser?->hasPermission('settings.view') 
            || $currentUser?->hasPermission('settings.update') 
            || $canCleanup 
            || $canMaintenance 
            || $canViewHealth;
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

            @if (!$hasAnyTab)
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <i class="bi bi-shield-lock text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-3">Truy cập bị từ chối</h3>
                        <p class="text-muted mb-4">Bạn không có quyền xem bất kỳ mục cấu hình nào trong trang này.<br>Vui lòng liên hệ quản trị viên để được cấp quyền.</p>
                        <a href="{{ route('admin.index') }}" class="btn btn-primary rounded-pill px-5">Quay lại Tổng quan</a>
                    </div>
                </div>
            @else
            <div class="row g-4">
                <div class="col-xl-3 col-lg-4">
                    <div class="card border-0 shadow-sm settings-sidebar">
                        <div class="card-body p-3">
                            <div class="settings-sidebar__title mb-3 fw-bold text-muted small text-uppercase">Nhóm cấu hình</div>
                            <div class="settings-sidebar__nav d-flex flex-column gap-2">
                                @if ($currentUser?->hasPermission('settings.view'))
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="general">Hệ thống</button>
                                @endif
                                
                                @if ($currentUser?->hasPermission('settings.update'))
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="operations">Vận hành</button>
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="banners">Banner</button>
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="currency">Tỷ giá</button>
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="mail">Cấu hình mail</button>
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="announcements">Thông báo</button>
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="payments">Thanh toán</button>
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="bots">Cấu hình Bot</button>
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="firewall">Tường lửa (IP Firewall)</button>
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="backup">Sao lưu Cloud</button>
                                @endif

                                @if ($canCleanup || $canMaintenance)
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="cleanup">Vận hành & Bảo trì</button>
                                @endif
                                
                                @if ($canViewHealth)
                                    <button type="button" class="btn settings-tab-btn text-start px-3 py-2 border-0" data-settings-tab="health">Sức khỏe hệ thống</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-9 col-lg-8">
                    <div class="settings-panels">
                        {{-- Panel: Hệ thống --}}
                        @if ($currentUser?->hasPermission('settings.view'))
                        <div class="settings-panel" data-settings-panel="general">
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
                        @endif

                        {{-- Panel: Vận hành --}}
                        @if ($currentUser?->hasPermission('settings.update'))
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
                                            <label class="form-label">Thiết bị tối đa của Học viên</label>
                                            <input type="number" min="1" max="10" name="max_devices" class="form-control" value="{{ old('max_devices', $settings['max_devices'] ?? config('auth.max_devices', 1)) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Thiết bị tối đa của Admin</label>
                                            <input type="number" min="1" max="10" name="admin_max_devices" class="form-control" value="{{ old('admin_max_devices', $settings['admin_max_devices'] ?? config('auth.admin_max_devices', 1)) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Số tiền rút tối thiểu (VND)</label>
                                            <input type="number" min="1000" step="1000" name="min_payout_amount" class="form-control" value="{{ old('min_payout_amount', $settings['min_payout_amount'] ?? '50000') }}">
                                            <small class="text-muted">Hạn mức tối thiểu một giảng viên cần đạt để gửi yêu cầu rút tiền.</small>
                                        </div>

                                        <div class="col-12 mt-3">
                                            <div class="border rounded-4 p-3 bg-light">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" @checked(old('maintenance_mode', $settings['maintenance_mode'] ?? '0') == '1')>
                                                    <label class="form-check-label fw-bold" for="maintenance_mode">Kích hoạt chế độ Bảo trì Website (Smart Maintenance)</label>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-secondary">Danh sách IP Whitelist (Cách nhau bởi dấu phẩy)</label>
                                                    <input type="text" name="maintenance_whitelist_ips" class="form-control" value="{{ old('maintenance_whitelist_ips', $settings['maintenance_whitelist_ips'] ?? '127.0.0.1') }}" placeholder="Ví dụ: 127.0.0.1, 192.168.1.1">
                                                    <span class="small text-muted">IP hiện tại của bạn: {{ request()->ip() }}</span>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label fw-bold small text-secondary">Lời nhắn thông báo bảo trì</label>
                                                    <input type="text" name="maintenance_message" class="form-control" value="{{ old('maintenance_message', $settings['maintenance_message'] ?? 'Website đang bảo trì định kỳ. Vui lòng quay lại sau.') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Panel: Banner --}}
                        @if ($currentUser?->hasPermission('settings.update'))
                        <div class="settings-panel" data-settings-panel="banners">
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">Cấu hình Banner & Logo</div>
                                <div class="card-body">
                                    <div class="row g-4 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-secondary">Logo website</label>
                                            <input type="file" name="logo" class="form-control" id="theme-logo-input">
                                            @if (!empty($settings['logo']))
                                                <div class="mt-2 p-2 border rounded d-inline-block bg-light">
                                                    <img src="{{ asset('storage/' . $settings['logo']) }}" height="60" id="preview-logo">
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-secondary">Favicon (.ico, .png)</label>
                                            <input type="file" name="favicon" class="form-control" id="theme-favicon-input">
                                            @if (!empty($settings['favicon']))
                                                <div class="mt-2 p-2 border rounded d-inline-block bg-light">
                                                    <img src="{{ asset('storage/' . $settings['favicon']) }}" height="32" id="preview-favicon">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small text-secondary">Màu chủ đạo Website (Primary Color)</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="color" name="theme_primary_color" class="form-control form-control-color" id="theme-color-picker" value="{{ old('theme_primary_color', $settings['theme_primary_color'] ?? '#0d6efd') }}" title="Chọn màu chủ đạo">
                                            <div class="border rounded px-3 py-1 bg-light fw-bold text-secondary" id="color-hex-display">{{ old('theme_primary_color', $settings['theme_primary_color'] ?? '#0d6efd') }}</div>
                                        </div>
                                        <small class="text-muted d-block mt-1">Thay đổi tông màu cốt lõi cho Client layout (nút bấm, hover,...).</small>
                                    </div>
                                    <div class="mb-4 border rounded-4 p-3 bg-light">
                                        <div class="small fw-bold text-muted text-uppercase mb-2">Live Preview (Xem trước)</div>
                                        <div class="p-4 border rounded-3 bg-white text-center shadow-sm d-flex flex-column align-items-center justify-content-center" id="theme-preview-box" style="border-top: 5px solid {{ old('theme_primary_color', $settings['theme_primary_color'] ?? '#0d6efd') }} !important;">
                                            <button type="button" class="btn btn-sm text-white px-4 py-2 rounded-pill shadow-sm" id="preview-button" style="background-color: {{ old('theme_primary_color', $settings['theme_primary_color'] ?? '#0d6efd') }};">Nút Demo</button>
                                            <span class="small text-muted mt-2 d-block">Mô phỏng tức thì tông màu Client</span>
                                        </div>
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
                        @endif

                        {{-- Panel: Tỷ giá --}}
                        @if ($currentUser?->hasPermission('settings.update'))
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
                        @endif

                        {{-- Panel: Mail --}}
                        @if ($currentUser?->hasPermission('settings.update'))
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
                        @endif

                        {{-- Panel: Thông báo --}}
                        @if ($currentUser?->hasPermission('settings.update'))
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
                            {{-- Teacher Badge Notification --}}
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">Thông báo Giảng viên</div>
                                <div class="card-body">
                                    <div class="border rounded-4 p-3 bg-light">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="teacher_badge_notification_email_enabled" name="teacher_badge_notification_email_enabled" value="1" @checked(old('teacher_badge_notification_email_enabled', $settings['teacher_badge_notification_email_enabled'] ?? '0') == '1')>
                                            <label class="form-check-label fw-bold" for="teacher_badge_notification_email_enabled">Gửi email cho giảng viên khi được cấp huy hiệu mới</label>
                                        </div>
                                        <small class="text-muted d-block mt-2">Thông báo nội bộ (Noti) luôn được gửi mặc định khi có thay đổi huy hiệu.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Panel: Thanh toán --}}
                        @if ($currentUser?->hasPermission('settings.update'))
                        <div class="settings-panel" data-settings-panel="payments">
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">Cấu hình phương thức thanh toán</div>
                                <div class="card-body">
                                    <div class="alert alert-info border-0 rounded-4 mb-4 small">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Khi tắt một phương thức, hệ thống sẽ hiển thị trạng thái <strong>"Bảo trì"</strong> tại trang thanh toán của Học viên và Giảng viên.
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <div class="p-3 border rounded-4 h-100 bg-light d-flex flex-column justify-content-between">
                                                <div>
                                                    <h6 class="fw-bold mb-1">Chuyển khoản ngân hàng</h6>
                                                    <p class="text-muted small mb-3">Thanh toán qua QR Code ngân hàng (VietQR)</p>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" id="payment_bank_enabled" name="payment_bank_enabled" value="1" @checked(old('payment_bank_enabled', $settings['payment_bank_enabled'] ?? '1') == '1')>
                                                    <label class="form-check-label fw-bold text-primary" for="payment_bank_enabled">Đang hoạt động</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="p-3 border rounded-4 h-100 bg-light d-flex flex-column justify-content-between">
                                                <div>
                                                    <h6 class="fw-bold mb-1">Ví điện tử Momo</h6>
                                                    <p class="text-muted small mb-3">Cổng thanh toán Momo (Online Payment)</p>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" id="payment_momo_enabled" name="payment_momo_enabled" value="1" @checked(old('payment_momo_enabled', $settings['payment_momo_enabled'] ?? '1') == '1')>
                                                    <label class="form-check-label fw-bold text-primary" for="payment_momo_enabled">Đang hoạt động</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="p-3 border rounded-4 h-100 bg-light d-flex flex-column justify-content-between">
                                                <div>
                                                    <h6 class="fw-bold mb-1">Cổng VNPAY</h6>
                                                    <p class="text-muted small mb-3">Thanh toán qua ứng dụng ngân hàng & thẻ</p>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" id="payment_vnpay_enabled" name="payment_vnpay_enabled" value="1" @checked(old('payment_vnpay_enabled', $settings['payment_vnpay_enabled'] ?? '1') == '1')>
                                                    <label class="form-check-label fw-bold text-primary" for="payment_vnpay_enabled">Đang hoạt động</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4 opacity-10">

                                    <div class="settings-toggle-panel" data-settings-toggle-target="payment_bank_enabled">
                                        <h6 class="fw-bold mb-3"><i class="bi bi-bank me-2"></i>Chi tiết tài khoản nhận tiền (VietQR)</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Chọn Ngân hàng</label>
                                                <select name="bank_transfer_bank_bin" class="form-control" id="bank_selector">
                                                    <option></option>
                                                    @php
                                                        $banks = [
                                                            '970407' => 'Techcombank',
                                                            '970436' => 'Vietcombank',
                                                            '970422' => 'MBBank',
                                                            '970416' => 'ACB',
                                                            '970415' => 'VietinBank',
                                                            '970418' => 'BIDV',
                                                            '970405' => 'Agribank',
                                                            '970423' => 'TPBank',
                                                            '970432' => 'VPBank',
                                                            '970403' => 'Sacombank',
                                                            '970441' => 'VIB',
                                                            '970406' => 'DongA Bank',
                                                            '970437' => 'HDBank',
                                                            '970449' => 'LienVietPostBank',
                                                            '970443' => 'SHB',
                                                            '970440' => 'SeABank',
                                                        ];
                                                        $currentBin = old('bank_transfer_bank_bin', $settings['bank_transfer_bank_bin'] ?? '970407');
                                                    @endphp
                                                    @foreach($banks as $bin => $name)
                                                        <option value="{{ $bin }}" data-name="{{ $name }}" @selected($currentBin == $bin)>{{ $name }} ({{ $bin }})</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="bank_transfer_bank_name" id="bank_name_hidden" value="{{ old('bank_transfer_bank_name', $settings['bank_transfer_bank_name'] ?? 'Techcombank') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Số tài khoản</label>
                                                <input type="text" name="bank_transfer_account_number" class="form-control" value="{{ old('bank_transfer_account_number', $settings['bank_transfer_account_number'] ?? '') }}" placeholder="Nhập số tài khoản">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tên chủ tài khoản</label>
                                                <input type="text" name="bank_transfer_account_name" class="form-control" value="{{ old('bank_transfer_account_name', $settings['bank_transfer_account_name'] ?? '') }}" placeholder="NGUYEN VAN A">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tiền tố nội dung chuyển khoản</label>
                                                <input type="text" name="bank_transfer_note_prefix" class="form-control" value="{{ old('bank_transfer_note_prefix', $settings['bank_transfer_note_prefix'] ?? 'CK') }}" placeholder="VD: CK, THANHTOAN">
                                                <small class="text-muted">Hệ thống sẽ tự động ghép: [Tiền tố] + [Mã đơn hàng]</small>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <div class="p-3 border rounded-4 bg-white shadow-sm">
                                                    <label class="form-label d-block fw-bold mb-3 text-primary"><i class="bi bi-qr-code-scan me-2"></i>Xem trước mã QR (VietQR)</label>
                                                    <div class="d-flex align-items-center gap-4">
                                                        <div id="qr_preview_container" class="bg-white p-2 border rounded-4 text-center d-flex align-items-center justify-content-center" style="width: 180px; height: 180px;">
                                                            <img id="qr_preview_img" src="" alt="QR Preview" class="img-fluid" style="max-height: 164px; display: none;">
                                                            <div id="qr_preview_placeholder" class="text-muted small px-3">
                                                                Vui lòng nhập số tài khoản và chọn ngân hàng để xem trước
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="alert alert-info border-0 rounded-4 mb-0 py-2 px-3 small">
                                                                <h6 class="fw-bold mb-1" style="font-size: 0.85rem;">Thông tin hiển thị</h6>
                                                                <p class="mb-0">Mã QR này được sinh tự động dựa trên cấu hình phía trên. Bạn có thể dùng ứng dụng ngân hàng quét thử để xác thực trước khi lưu.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        {{-- Panel: Cấu hình Bot (AI & Telegram) --}}
                        @if ($currentUser?->hasPermission('settings.update'))
                        <div class="settings-panel" data-settings-panel="bots">
                            {{-- Chatbot & AI Settings --}}
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">
                                    <i class="fa-solid fa-brain me-1 text-primary"></i> Trợ lý Ảo & Trí tuệ Nhân tạo (AI Assistant)
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold small text-secondary">Chatbot TTL (phút)</label>
                                            <input type="number" min="5" max="1440" name="chatbot_message_ttl_minutes" class="form-control" value="{{ old('chatbot_message_ttl_minutes', $settings['chatbot_message_ttl_minutes'] ?? env('CHATBOT_MESSAGE_TTL_MINUTES', 10)) }}">
                                            <small class="text-muted">Thời gian tối đa lưu vết hội thoại cũ của người dùng.</small>
                                        </div>
                                    </div>

                                    <div class="border rounded-4 p-3 bg-light mb-3">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="chatbot_widget_enabled" name="chatbot_widget_enabled" value="1" @checked(old('chatbot_widget_enabled', $settings['chatbot_widget_enabled'] ?? '1') == '1')>
                                            <label class="form-check-label fw-bold" for="chatbot_widget_enabled">Bật chatbot thường (Widget hỗ trợ)</label>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="chatbot_enabled" name="chatbot_enabled" value="1" @checked(old('chatbot_enabled', $settings['chatbot_enabled'] ?? '1') == '1')>
                                            <label class="form-check-label fw-bold" for="chatbot_enabled">Bật trí tuệ nhân tạo Gemini AI</label>
                                        </div>
                                        <div class="alert alert-warning mt-3 mb-0 d-none" data-chatbot-gemini-note role="alert">Hãy bật chatbot thường trước rồi mới bật được Gemini AI</div>
                                    </div>

                                    <div class="border rounded-4 p-3 bg-light">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="ai_quiz_enabled" name="ai_quiz_enabled" value="1" @checked(old('ai_quiz_enabled', $settings['ai_quiz_enabled'] ?? '1') == '1')>
                                            <label class="form-check-label fw-bold" for="ai_quiz_enabled">Cho phép Giảng viên tạo Quiz bằng AI (Generative AI)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Telegram Bot Settings --}}
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">
                                    <i class="fa-solid fa-robot me-1 text-primary"></i> Quản lý Telegram Bot Cảnh báo Lỗi
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="telegram_bot_enabled" name="telegram_bot_enabled" value="1" {{ old('telegram_bot_enabled', $settings['telegram_bot_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="telegram_bot_enabled">Kích hoạt Cảnh báo Lỗi qua Telegram</label>
                                        </div>
                                        <small class="text-muted">Khi bật, hệ thống sẽ tự động gửi tin nhắn báo cáo lỗi 500/Critical về máy bạn.</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Test Connection --}}
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0">
                                    <i class="fa-solid fa-paper-plane me-1 text-success"></i> Kiểm tra Kết nối
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Bấm nút bên dưới để gửi thử một tin nhắn mẫu tới Telegram của bạn.</p>
                                    <button type="submit" id="btn_test_telegram" formaction="{{ route('settings.test-telegram') }}" class="btn btn-success px-4 rounded-pill" {{ (old('telegram_bot_enabled', $settings['telegram_bot_enabled'] ?? '0') == '1') ? '' : 'disabled' }}>
                                        <i class="fa-solid fa-bolt me-1"></i> Gửi thử Tin nhắn
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Panel: Tường lửa --}}
                        @if ($currentUser?->hasPermission('settings.update'))
                        <div class="settings-panel" data-settings-panel="firewall">
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white pt-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                    <span>Danh sách đen IP (Firewall Blacklist)</span>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addBlacklistModal">
                                        <i class="bi bi-plus-circle me-1"></i> Thêm IP Chặn
                                    </button>
                                </div>
                                <div class="card-body">
                                    @php
                                        $blacklists = \Modules\Settings\src\Models\IpBlacklist::latest()->get();
                                    @endphp
                                    <div class="table-responsive">
                                        <table class="table align-middle table-hover mb-0 mt-2">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 50px;">ID</th>
                                                    <th>Địa chỉ IP</th>
                                                    <th>Lý do chặn</th>
                                                    <th>Thời gian chặn</th>
                                                    <th class="text-end">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($blacklists as $item)
                                                    <tr>
                                                        <td>{{ $item->id }}</td>
                                                        <td><code class="fw-bold text-danger">{{ $item->ip_address }}</code></td>
                                                        <td>{{ $item->reason ?: 'Không có' }}</td>
                                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                                        <td class="text-end">
                                                            <button type="submit" formaction="{{ route('settings.firewall.delete', $item->id) }}" formmethod="POST" class="btn btn-sm btn-outline-danger px-3 rounded-pill" onclick="return confirm('Bạn có chắc muốn xóa IP này khỏi danh sách đen?')">
                                                                <i class="bi bi-trash me-1"></i> Xóa
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">Chưa có IP nào bị chặn.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Panel: Sao lưu Cloud --}}
                        @if ($currentUser?->hasPermission('settings.update'))
                        <div class="settings-panel" data-settings-panel="backup">
                            <div class="card shadow-sm border-0">
                                <div class="card-header fw-bold bg-white border-bottom-0 pt-3">Sao lưu dữ liệu (Google Drive)</div>
                                <div class="card-body">
                                    <div class="alert alert-warning border-0 rounded-4 d-flex align-items-center mb-4">
                                        <i class="fas fa-exclamation-triangle fs-4 me-3 text-warning"></i>
                                        <div>
                                            <strong>Lưu ý:</strong> Tác vụ sao lưu dữ liệu toàn diện (Database + File đính kèm) có thể mất vài phút tùy thuộc vào dung lượng website của bạn. Vui lòng không tắt trình duyệt trong quá trình chạy.
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-3">
                                        <div class="p-3 bg-light rounded-4 border d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="fw-bold mb-1">Dung lượng ổ đĩa Drive: 15GB miễn phí</h6>
                                                <p class="text-muted small mb-0">Hệ thống sẽ nén và đồng bộ dữ liệu của bạn an toàn nhất.</p>
                                            </div>
                                            <button type="button" id="btn-run-backup" class="btn btn-primary fw-bold px-4 rounded-pill shadow-sm">
                                                <i class="fas fa-cloud-upload-alt me-2"></i> Tạo bản sao lưu ngay
                                            </button>
                                        </div>
                                        
                                        <div class="p-3 bg-light rounded-4 border">
                                            <h6 class="fw-bold mb-2">Trạng thái kết nối Google Drive</h6>
                                            <div id="backup-status-text" class="text-muted small">
                                                Chưa khởi chạy tác vụ.
                                            </div>
                                        </div>

                                        {{-- Backup History --}}
                                        <div class="mt-4 pt-3 border-top">
                                            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Lịch sử sao lưu gần nhất</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr class="small">
                                                            <th>Thời gian</th>
                                                            <th>Mô tả</th>
                                                            <th class="text-end">Trạng thái</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($backupLogs as $log)
                                                            <tr>
                                                                <td class="small">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                                                <td class="small text-muted">{{ $log->description }}</td>
                                                                <td class="text-end">
                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Thành công</span>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3" class="text-center py-3 text-muted small">Chưa có lịch sử sao lưu.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        {{-- Panel: Vận hành & Bảo trì --}}
                        @if ($canCleanup || $canMaintenance)
                            <div class="settings-panel" data-settings-panel="cleanup">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header fw-bold bg-white border-bottom-0 pt-3">Vận hành & Bảo trì (Maintenance)</div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            @if ($canCleanup)
                                            <div class="col-md-6">
                                                <div class="p-4 bg-light rounded-4 border h-100 d-flex flex-column justify-content-between">
                                                    <div>
                                                        <h5 class="fw-bold mb-2"><i class="bi bi-brush me-2"></i>Dọn dẹp hệ thống</h5>
                                                        <p class="text-muted small mb-3">Xóa log cũ (>60 ngày), session rác và các token hết hạn.</p>
                                                    </div>
                                                    <button type="submit" formaction="{{ route('settings.cleanup') }}" formmethod="POST" class="btn btn-outline-danger fw-bold rounded-pill shadow-sm w-100" onclick="return confirm('Xác nhận dọn dẹp hệ thống?')">
                                                        Dọn dẹp ngay
                                                    </button>
                                                </div>
                                            </div>
                                            @endif

                                            @if ($canMaintenance)
                                            <div class="col-md-6">
                                                <div class="p-4 bg-light rounded-4 border h-100 d-flex flex-column justify-content-between">
                                                    <div>
                                                        <h5 class="fw-bold mb-2"><i class="bi bi-lightning-charge me-2"></i>Xóa Cache</h5>
                                                        <p class="text-muted small mb-3">Làm mới toàn bộ bộ nhớ đệm (Cache), cấu hình và giao diện (View).</p>
                                                    </div>
                                                    <button type="submit" formaction="{{ route('settings.clear-cache') }}" formmethod="POST" class="btn btn-outline-primary fw-bold rounded-pill shadow-sm w-100" onclick="return confirm('Xác nhận xóa toàn bộ cache?')">
                                                        Xóa Cache ngay
                                                    </button>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Panel: Sức khỏe hệ thống --}}
                        @if ($canViewHealth && $systemHealth)
                            <div class="settings-panel" data-settings-panel="health">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0 pt-3">
                                        <span class="fw-bold">Sức khỏe hệ thống (System Health)</span>
                                        <span class="badge bg-light text-muted fw-normal">Cập nhật: {{ $systemHealth['timestamp'] }}</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <!-- Disk Space -->
                                            <div class="col-md-6">
                                                <div class="p-3 border rounded-4">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="text-muted small fw-bold text-uppercase">Dung lượng ổ đĩa</span>
                                                        <span class="badge {{ $systemHealth['disk']['status'] === 'healthy' ? 'bg-success' : 'bg-warning' }}">
                                                            {{ $systemHealth['disk']['status'] }}
                                                        </span>
                                                    </div>
                                                    <h4 class="fw-bold mb-1">{{ $systemHealth['disk']['percent'] }}%</h4>
                                                    <div class="progress mb-2" style="height: 6px;">
                                                        <div class="progress-bar {{ $systemHealth['disk']['status'] === 'healthy' ? 'bg-primary' : 'bg-danger' }}" style="width: {{ $systemHealth['disk']['percent'] }}%"></div>
                                                    </div>
                                                    <div class="text-muted small">Đã dùng {{ $systemHealth['disk']['used'] }} / {{ $systemHealth['disk']['total'] }}</div>
                                                </div>
                                            </div>

                                            <!-- Database -->
                                            <div class="col-md-6">
                                                <div class="p-3 border rounded-4 h-100">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="text-muted small fw-bold text-uppercase">Cơ sở dữ liệu</span>
                                                        <span class="badge bg-success">Kết nối tốt</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <i class="bi bi-database fs-3 text-primary"></i>
                                                        <div>
                                                            <div class="fw-bold">{{ $systemHealth['database']['driver'] }}</div>
                                                            <div class="text-muted small">Độ trễ: {{ $systemHealth['database']['latency'] }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Queue -->
                                            <div class="col-md-4">
                                                <div class="p-3 border rounded-4">
                                                    <span class="text-muted small fw-bold text-uppercase d-block mb-2">Hàng đợi (Queue)</span>
                                                    @if($systemHealth['queue']['status'] === 'healthy')
                                                        <div class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Đang hoạt động</div>
                                                        <div class="text-muted small mt-1">Phản hồi cuối: {{ $systemHealth['queue']['seconds_ago'] }} giây trước</div>
                                                    @else
                                                        <div class="text-warning fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> Không hoạt động</div>
                                                        <div class="text-muted small mt-1">{{ $systemHealth['queue']['message'] ?? 'Worker đang dừng.' }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Mail -->
                                            <div class="col-md-4">
                                                <div class="p-3 border rounded-4">
                                                    <span class="text-muted small fw-bold text-uppercase d-block mb-2">Máy chủ Mail</span>
                                                    @if($systemHealth['mail']['status'] === 'healthy')
                                                        <div class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Đã kết nối</div>
                                                        <div class="text-muted small mt-1">{{ $systemHealth['mail']['host'] }}</div>
                                                    @else
                                                        <div class="text-warning fw-bold"><i class="bi bi-x-circle-fill me-1"></i> {{ $systemHealth['mail']['status'] }}</div>
                                                        <div class="text-muted small mt-1">{{ $systemHealth['mail']['message'] ?? 'Chưa cấu hình hoặc lỗi kết nối.' }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Cache -->
                                            <div class="col-md-4">
                                                <div class="p-3 border rounded-4">
                                                    <span class="text-muted small fw-bold text-uppercase d-block mb-2">Bộ nhớ đệm (Cache)</span>
                                                    @if($systemHealth['cache']['status'] === 'healthy')
                                                        <div class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Hoạt động tốt</div>
                                                        <div class="text-muted small mt-1">Driver: {{ $systemHealth['cache']['driver'] }}</div>
                                                    @else
                                                        <div class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Lỗi kết nối</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div> {{-- end settings-panels --}}
                </div> {{-- end col-xl-9 --}}
            </div> {{-- end row --}}
            @endif

            <div class="admin-form__footer mt-4 pt-4 border-top">
                @if ($canUpdateGeneralSettings)
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow-sm">
                        Lưu cấu hình hệ thống
                    </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Modal Thêm Blacklist IP -->
    <div class="modal fade" id="addBlacklistModal" tabindex="-1" aria-labelledby="addBlacklistModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form action="{{ route('settings.firewall.store') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold text-dark" id="addBlacklistModalLabel">
                            <i class="bi bi-shield-slash me-2 text-danger"></i>Thêm IP vào danh sách chặn
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Địa chỉ IP <span class="text-danger">*</span></label>
                            <input type="text" name="ip_address" class="form-control" required placeholder="Ví dụ: 123.45.67.89">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold small text-secondary">Lý do chặn</label>
                            <input type="text" name="reason" class="form-control" placeholder="Ví dụ: Spam bình luận, Brute force...">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pb-4 px-4">
                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4">Xác nhận chặn</button>
                    </div>
                </form>
            </div>
        </div>
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

        /* Select2 Customization */
        .select2-container--default .select2-selection--single {
            height: 44px !important;
            border-radius: 12px !important;
            border-color: var(--admin-border) !important;
            background-color: var(--admin-input-bg) !important;
            display: flex !important;
            align-items: center !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--admin-text) !important;
            line-height: 44px !important;
            padding-left: 12px !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
        }

        .select2-dropdown {
            border-radius: 12px !important;
            border-color: var(--admin-border) !important;
            background-color: var(--admin-surface) !important;
            box-shadow: var(--admin-dropdown-shadow) !important;
            overflow: hidden;
            z-index: 1060 !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border-radius: 8px !important;
            background-color: var(--admin-bg) !important;
            color: var(--admin-text) !important;
            border-color: var(--admin-border) !important;
            padding: 6px 12px !important;
        }

        .select2-results__option {
            padding: 8px 12px !important;
            color: var(--admin-text) !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--admin-primary) !important;
            color: white !important;
        }

        html[data-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected] {
            color: #0f172a !important;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const tabButtons = document.querySelectorAll('[data-settings-tab]');
            const panels = document.querySelectorAll('[data-settings-panel]');

            const activateTab = (tab) => {
                tabButtons.forEach(btn => btn.classList.toggle('is-active', btn.dataset.settingsTab === tab));
                panels.forEach(panel => panel.classList.toggle('is-active', panel.dataset.settingsPanel === tab));
                localStorage.setItem('admin_settings_tab', tab);
            };

            @if(session('active_tab'))
                localStorage.setItem('admin_settings_tab', '{{ session('active_tab') }}');
            @endif

            const savedTab = localStorage.getItem('admin_settings_tab');
            const initialTab = (savedTab && Array.from(tabButtons).find(btn => btn.dataset.settingsTab === savedTab)) 
                ? savedTab 
                : (tabButtons.length > 0 ? tabButtons[0].dataset.settingsTab : null);
            
            if (initialTab) {
                activateTab(initialTab);
            }

            const initBankSelect2 = () => {
                const $bankSelector = $('#bank_selector');
                const bankNameHidden = document.getElementById('bank_name_hidden');
                
                if ($bankSelector.length && typeof $.fn.select2 !== 'undefined') {
                    // Initialize or Re-initialize
                    $bankSelector.select2({
                        width: '100%',
                        placeholder: 'Tìm kiếm ngân hàng...',
                        allowClear: false
                    }).off('select2:select').on('select2:select', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        if (selectedOption && bankNameHidden) {
                            bankNameHidden.value = selectedOption.dataset.name || '';
                        }
                        updateQrPreview();
                    });
                }
            };

            tabButtons.forEach(btn => btn.addEventListener('click', () => {
                const tab = btn.dataset.settingsTab;
                activateTab(tab);
                
                if (tab === 'payments') {
                    setTimeout(initBankSelect2, 50);
                }
            }));

            const bindTogglePanel = (toggleId) => {
                const toggle = document.getElementById(toggleId);
                const panel = document.querySelector(`[data-settings-toggle-target="${toggleId}"]`);
                if (!toggle || !panel) return;
                const sync = () => {
                    panel.classList.toggle('is-disabled', !toggle.checked);
                    panel.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = !toggle.checked);
                    if (toggleId === 'payment_bank_enabled' && toggle.checked) {
                        setTimeout(initBankSelect2, 100);
                    }
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
            bindTogglePanel('payment_bank_enabled');
            bindVisibilityPanel('global_notice_enabled');
            bindVisibilityPanel('popup_notice_enabled');

            // Initial call
            if (initialTab === 'payments') {
                initBankSelect2();
            }

            const accountNumberInput = document.querySelector('input[name="bank_transfer_account_number"]');
            const bankSelectorEl = document.getElementById('bank_selector');

            function updateQrPreview() {
                const bin = bankSelectorEl?.value;
                const acc = accountNumberInput?.value;
                const img = document.getElementById('qr_preview_img');
                const placeholder = document.getElementById('qr_preview_placeholder');

                if (bin && acc && acc.length >= 6) {
                    img.src = `https://img.vietqr.io/image/${bin}-${acc}-compact2.jpg?amount=10000&addInfo=ChuyenKhoanTest`;
                    img.style.display = 'inline-block';
                    placeholder.style.display = 'none';
                } else {
                    img.style.display = 'none';
                    placeholder.style.display = 'flex';
                }
            }

            if (accountNumberInput) {
                accountNumberInput.addEventListener('input', updateQrPreview);
            }

            updateQrPreview();

            const colorPicker = document.getElementById('theme-color-picker');
            const colorDisplay = document.getElementById('color-hex-display');
            const previewBox = document.getElementById('theme-preview-box');
            const previewBtn = document.getElementById('preview-button');

            if (colorPicker) {
                colorPicker.addEventListener('input', function(e) {
                    const color = e.target.value;
                    if(colorDisplay) colorDisplay.innerText = color;
                    if(previewBox) previewBox.style.setProperty('border-top', `5px solid ${color}`, 'important');
                    if(previewBtn) previewBtn.style.backgroundColor = color;
                });
            }

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

            const telegramToggle = document.getElementById('telegram_bot_enabled');
            const btnTestTelegram = document.getElementById('btn_test_telegram');
            if (telegramToggle && btnTestTelegram) {
                telegramToggle.addEventListener('change', function() {
                    btnTestTelegram.disabled = !this.checked;
                });
            }

            const btnRunBackup = document.getElementById('btn-run-backup');
            const statusText = document.getElementById('backup-status-text');
            if (btnRunBackup) {
                btnRunBackup.addEventListener('click', function() {
                    btnRunBackup.disabled = true;
                    btnRunBackup.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Đang sao lưu...';
                    statusText.innerHTML = '<span class="text-primary"><i class="fas fa-sync fa-spin me-1"></i> Đang nén dữ liệu và đẩy lên Google Drive. Vui lòng đợi...</span>';

                    fetch("{{ route('settings.run-backup') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        btnRunBackup.disabled = false;
                        btnRunBackup.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i> Tạo bản sao lưu ngay';
                        if (data.success) {
                            statusText.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i> ' + data.message + '</span>';
                            alert(data.message);
                        } else {
                            statusText.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i> ' + data.message + '</span>';
                            alert(data.message);
                        }
                    })
                    .catch(err => {
                        btnRunBackup.disabled = false;
                        btnRunBackup.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i> Tạo bản sao lưu ngay';
                        statusText.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i> Lỗi kết nối máy chủ!</span>';
                        console.error(err);
                    });
                });
            }
        });
    </script>
@endsection
