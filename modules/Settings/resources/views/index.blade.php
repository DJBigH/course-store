@extends('layouts.backend')

@php
    $announcementLocales = [
        'vi' => 'VI',
        'en' => 'EN',
        'ko' => 'KO',
        'ja' => 'JA',
        'zh' => 'ZH',
    ];
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
            <div class="alert alert-success border-0 rounded-4">
                {{ session('msg') }}
            </div>
        @endif

        <form action="{{ route('settings.post-setting') }}" method="POST" enctype="multipart/form-data">
            @csrf

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

            <div class="admin-form__footer">
                @if (auth()->user()?->hasPermission('settings.logs'))
                    <a href="{{ route('settings.logs') }}" class="btn btn-warning">Lịch sử</a>
                @endif
                @if (auth()->user()?->hasPermission('settings.update'))
                    <button type="submit" class="btn btn-primary">
                        Lưu cấu hình
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
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
            bindLanguageSwitcher('popup_notice_lang', 'announcement-popup-lang', 'admin_popup_notice_lang');
        })();
    </script>
@endsection
