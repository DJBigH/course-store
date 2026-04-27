@extends('layouts.backend')

@section('content')
    @if (session('msg'))
        <div class="alert alert-success">{{ session('msg') }}</div>
    @endif
    @if (session('msg_danger'))
        <div class="alert alert-danger">{{ session('msg_danger') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            Vui lòng kiểm tra lại dữ liệu đã nhập.
        </div>
    @endif

    <form action="" method="post" class="admin-form">
        @csrf

        <div class="admin-form__header d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="mb-0">Thông tin giảng viên</h5>
                <p class="text-muted mb-0">Cập nhật hồ sơ giảng viên đa ngôn ngữ và thông tin hiển thị.</p>
            </div>

            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="content_lang" id="lang_vi" checked>
                <label class="btn btn-outline-primary" for="lang_vi">VI</label>

                <input type="radio" class="btn-check" name="content_lang" id="lang_en">
                <label class="btn btn-outline-primary" for="lang_en">EN</label>

                <input type="radio" class="btn-check" name="content_lang" id="lang_ko">
                <label class="btn btn-outline-primary" for="lang_ko">KO</label>

                <input type="radio" class="btn-check" name="content_lang" id="lang_ja">
                <label class="btn btn-outline-primary" for="lang_ja">JA</label>

                <input type="radio" class="btn-check" name="content_lang" id="lang_zh">
                <label class="btn btn-outline-primary" for="lang_zh">ZH</label>
            </div>
        </div>

        <div class="row">
            <div class="col-12 lang-block lang-vi">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label>Tên (VI)</label>
                            <input type="text" class="form-control title {{ $errors->has('name') ? ' is-invalid' : '' }}"
                                name="name" placeholder="Tên..." value="{{ old('name') ?? $teacher->name }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="mb-3">
                            <label>Slug (VI)</label>
                            <input type="text" class="form-control slug {{ $errors->has('slug') ? ' is-invalid' : '' }}"
                                name="slug" placeholder="Tự động tạo..." value="{{ old('slug') ?? $teacher->slug }}" readonly>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            @foreach (['en' => 'EN', 'ko' => 'KO', 'ja' => 'JA', 'zh' => 'ZH'] as $locale => $label)
                <div class="col-12 lang-block lang-{{ $locale }} d-none">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Tên ({{ $label }})</label>
                                <input type="text" class="form-control title-{{ $locale }} {{ $errors->has('name_' . $locale) ? ' is-invalid' : '' }}"
                                    name="name_{{ $locale }}" placeholder="Name..." value="{{ old('name_' . $locale) ?? $teacher->{'name_' . $locale} }}">
                                @error('name_' . $locale)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label>Slug ({{ $label }})</label>
                                <input type="text" class="form-control slug-{{ $locale }} {{ $errors->has('slug_' . $locale) ? ' is-invalid' : '' }}"
                                    name="slug_{{ $locale }}" placeholder="Tự động tạo..." value="{{ old('slug_' . $locale) ?? $teacher->{'slug_' . $locale} }}" readonly>
                                @error('slug_' . $locale)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-12">
                <div class="mb-3">
                    <label>Kinh nghiệm</label>
                    <input type="number" class="form-control {{ $errors->has('exp') ? ' is-invalid' : '' }}" name="exp"
                        placeholder="Số năm kinh nghiệm..." value="{{ old('exp') ?? $teacher->exp }}">
                    @error('exp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label">Huy hiệu giảng viên (Cũ)</label>
                    @php
                        $selectedBadgeKey = old('badge_key', $teacher->badge_key ?: ($teacher->is_verified_badge ? 'verified' : ($teacher->is_premium_badge ? 'premium' : 'none')));
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-6">
                            <select class="form-select" id="badge_key" name="badge_key">
                                <option value="none" @selected($selectedBadgeKey === 'none')>Không hiển thị huy hiệu</option>
                                <option value="verified" @selected($selectedBadgeKey === 'verified')>Verified Teacher</option>
                                <option value="premium" @selected($selectedBadgeKey === 'premium')>Premium Teacher</option>
                                <option value="top_seller" @selected($selectedBadgeKey === 'top_seller')>Top Seller</option>
                                <option value="expert" @selected($selectedBadgeKey === 'expert')>Expert Mentor</option>
                                <option value="featured" @selected($selectedBadgeKey === 'featured')>Featured Teacher</option>
                                <option value="custom" @selected($selectedBadgeKey === 'custom')>Tự tạo huy hiệu</option>
                            </select>
                        </div>
                        <div class="col-md-4" data-custom-badge-wrap @if ($selectedBadgeKey !== 'custom') style="display:none;" @endif>
                            <input type="text" class="form-control" name="badge_label" maxlength="100"
                                value="{{ old('badge_label', $teacher->badge_label) }}" placeholder="Ví dụ: Best Mentor 2026">
                        </div>
                        <div class="col-md-2" data-custom-badge-wrap @if ($selectedBadgeKey !== 'custom') style="display:none;" @endif>
                            <select class="form-select" name="badge_tone">
                                <option value="blue" @selected(old('badge_tone', $teacher->badge_tone) === 'blue')>Blue</option>
                                <option value="gold" @selected(old('badge_tone', $teacher->badge_tone) === 'gold')>Gold</option>
                                <option value="emerald" @selected(old('badge_tone', $teacher->badge_tone) === 'emerald')>Emerald</option>
                                <option value="violet" @selected(old('badge_tone', $teacher->badge_tone) === 'violet')>Violet</option>
                                <option value="rose" @selected(old('badge_tone', $teacher->badge_tone) === 'rose')>Rose</option>
                                <option value="slate" @selected(old('badge_tone', $teacher->badge_tone ?: 'slate') === 'slate')>Slate</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12" id="badges-assignment-section">
                <div class="mb-4">
                    <label class="form-label fw-bold">Gán huy hiệu hệ thống mới</label>
                    <div class="row g-3">
                        @php
                            $currentBadges = $teacher->badges->pluck('id')->toArray();
                        @endphp
                        @foreach ($badges as $badge)
                            <div class="col-md-3">
                                <div class="form-check badge-selection-card p-3 rounded-4 border">
                                    <input class="form-check-input" type="checkbox" name="badges[]" value="{{ $badge->id }}" id="badge_{{ $badge->id }}" @checked(in_array($badge->id, old('badges', $currentBadges)))>
                                    <label class="form-check-label d-block ms-2 cursor-pointer" for="badge_{{ $badge->id }}">
                                        <div class="badge mb-2" style="background-color: {{ $badge->color_bg }}; color: {{ $badge->color_text }};">
                                            <i class="{{ $badge->icon }} me-1"></i> {{ $badge->name_locale }}
                                        </div>
                                        <div class="small text-muted text-truncate">{{ $badge->description_locale }}</div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                        @if ($badges->isEmpty())
                            <div class="col-12">
                                <div class="alert alert-light border small text-muted">Chưa có huy hiệu hệ thống nào. <a href="{{ route('teacher.badges.create') }}" target="_blank">Tạo ngay</a></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @foreach (['vi' => 'description', 'en' => 'description_en', 'ko' => 'description_ko', 'ja' => 'description_ja', 'zh' => 'description_zh'] as $locale => $field)
                <div class="col-12">
                    <div class="lang-block lang-{{ $locale }} {{ $locale !== 'vi' ? 'd-none' : '' }}">
                        <div class="mb-3">
                            <label>Mô tả ({{ strtoupper($locale) }})</label>
                            <textarea name="{{ $field }}" class="form-control ckeditor {{ $errors->has($field) ? ' is-invalid' : '' }}"
                                cols="30" rows="10" placeholder="Mô tả...">{{ old($field, $teacher->{$field} ?? '') }}</textarea>
                            @error($field)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-12">
                <div class="mb-3">
                    <div class="row {{ $errors->has('image') ? 'g-2 align-items-center' : 'g-2 align-items-end' }}">
                        <label class="form-label">Hình ảnh</label>
                        <div class="col-7 position-relative">
                            <input type="text" class="form-control{{ $errors->has('image') ? ' is-invalid' : '' }}"
                                name="image" placeholder="Ảnh đại diện..." id="image"
                                value="{{ old('image') ?? $teacher->image }}">

                            @error('image')
                                <div class="invalid-feedback position-absolute">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-2 d-grid">
                            <button type="button" class="btn btn-primary" id="lfm" data-input="image"
                                data-preview="holder">
                                Chọn ảnh <i class="fa-solid fa-file-arrow-up"></i>
                            </button>
                        </div>

                        <div class="col-3">
                            <div id="holder" class="rounded p-1 text-center">
                                @if (old('image') || $teacher->image)
                                    <img src="{{ old('image') ?? $teacher->image }}" class="img-fluid">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 text-end admin-form__footer">
                <button type="submit" class="btn btn-success">Lưu</button>
                
                @if ($teacher->status === \Modules\Teacher\src\Models\Teacher::STATUS_CEASED)
                    <button type="button" class="btn btn-outline-success" 
                            onclick="if(confirm('Khôi phục hợp tác với giảng viên này?')) document.getElementById('toggle-ceased-form').submit();">
                        <i class="fa-solid fa-handshake-angle me-1"></i> Khôi phục hợp tác
                    </button>
                @else
                    <button type="button" class="btn btn-outline-danger" 
                            onclick="if(confirm('Bạn có chắc chắn muốn huỷ hợp tác với giảng viên này? Trang cá nhân và khóa học sẽ bị ẩn (404), giảng viên sẽ không thể truy cập Dashboard.')) document.getElementById('toggle-ceased-form').submit();">
                        <i class="fa-solid fa-user-slash me-1"></i> Huỷ hợp tác
                    </button>
                @endif

                @can('teachers.edit')
                <a href="{{ route('teacher-packages.grant', ['teacher_id' => $teacher->id]) }}"
                   class="btn btn-warning"
                   title="Tặng gói đặc quyền cho giáo viên này">
                    <i class="fa-solid fa-gift me-1"></i> Tặng gói
                </a>
                @endcan
                <a href="{{ route('teacher.index') }}" class="btn btn-secondary">Trở về</a>
            </div>
        </div>
    </form>

    <form id="toggle-ceased-form" action="{{ route('teacher.toggle-ceased', ['teacher' => $teacher->id]) }}" method="POST" style="display:none;">
        @csrf
    </form>
@endsection

@section('stylesheets')
    <style>
        img {
            max-width: 100%;
            height: auto !important;
        }

        #holder img {
            width: 100% !important;
        }

        .badge-selection-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .badge-selection-card:hover {
            border-color: #3b82f6 !important;
            background-color: #f8fafc;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Dark Mode Support */
        html[data-theme="dark"] .badge-selection-card {
            background-color: #1e293b;
            border-color: #334155 !important;
        }

        html[data-theme="dark"] .badge-selection-card:hover {
            background-color: #334155;
            border-color: #3b82f6 !important;
        }

        html[data-theme="dark"] .alert-light {
            background-color: #1e293b;
            border-color: #334155;
            color: #94a3b8 !important;
        }
    </style>
@endsection

@section('scripts')
    <script>
        (() => {
            const locales = ['vi', 'en', 'ko', 'ja', 'zh'];

            function showLang(lang) {
                document.querySelectorAll('.lang-block').forEach((el) => el.classList.add('d-none'));
                document.querySelectorAll('.lang-' + lang).forEach((el) => el.classList.remove('d-none'));
                localStorage.setItem('admin_teacher_lang', lang);
            }

            const saved = localStorage.getItem('admin_teacher_lang') || 'vi';
            const input = document.getElementById('lang_' + saved) || document.getElementById('lang_vi');

            if (input) {
                input.checked = true;
                showLang(saved);
            }

            locales.forEach((locale) => {
                document.getElementById('lang_' + locale)?.addEventListener('change', () => showLang(locale));
            });
        })();

        (() => {
            const badgeSelect = document.getElementById('badge_key');
            const customWraps = Array.from(document.querySelectorAll('[data-custom-badge-wrap]'));
            if (!badgeSelect || !customWraps.length) return;

            const syncBadgeFields = () => {
                const isCustom = badgeSelect.value === 'custom';
                customWraps.forEach((item) => {
                    item.style.display = isCustom ? '' : 'none';
                });
            };

            badgeSelect.addEventListener('change', syncBadgeFields);
            syncBadgeFields();
        })();

        function toSlug(title) {
            return (title || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/[^\p{L}\p{N}\s-]/gu, '')
                .trim()
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        function bindSlug(titleSelector, slugSelector) {
            const title = document.querySelector(titleSelector);
            const slug = document.querySelector(slugSelector);
            if (!title || !slug) return;

            title.addEventListener('input', (e) => {
                if (!slug.dataset.manual) {
                    slug.value = toSlug(e.target.value);
                }
            });

            slug.addEventListener('input', () => {
                slug.dataset.manual = '1';
            });
        }

        bindSlug('.title', '.slug');
        bindSlug('.title-en', '.slug-en');
        bindSlug('.title-ko', '.slug-ko');
        bindSlug('.title-ja', '.slug-ja');
        bindSlug('.title-zh', '.slug-zh');
    </script>
@endsection
