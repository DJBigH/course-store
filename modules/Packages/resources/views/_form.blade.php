@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
        <div class="d-flex align-items-center mb-2">
            <i class="fas fa-exclamation-circle me-2"></i>
            <h6 class="mb-0 fw-bold">Đã có lỗi xảy ra:</h6>
        </div>
        <ul class="mb-0 ps-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $globalFeatureStatus = \Modules\Packages\src\Models\PackageFeature::pluck('is_enabled', 'key')->toArray();
    $package = $package ?? null;
    $locales = [
        'vi' => ['label' => 'VI', 'name' => 'Tiếng Việt', 'flag' => 'vn'],
        'en' => ['label' => 'EN', 'name' => 'English', 'flag' => 'us'],
        'ko' => ['label' => 'KO', 'name' => 'Korean', 'flag' => 'kr'],
        'ja' => ['label' => 'JA', 'name' => 'Japanese', 'flag' => 'jp'],
        'zh' => ['label' => 'ZH', 'name' => 'Chinese', 'flag' => 'cn'],
    ];
    $featureBadges = [
        ['key' => 'can_manage_students', 'label' => 'Quản lý học viên'],
        ['key' => 'can_view_student_progress', 'label' => 'Xem tiến độ'],
        ['key' => 'can_view_activity_logs', 'label' => 'Nhật ký hoạt động'],
        ['key' => 'can_manage_quizzes', 'label' => 'Quản lý quiz'],
        ['key' => 'can_use_ai_quiz', 'label' => 'AI Quiz Generator'],
        ['key' => 'can_import_export_lessons', 'label' => 'Import/Export bài học'],
        ['key' => 'can_sell_bundles', 'label' => 'Combo khóa học'],
        ['key' => 'can_send_promotions', 'label' => 'Gửi khuyến mại'],
        ['key' => 'can_issue_certificates', 'label' => 'Cấp chứng chỉ'],
    ];
    $defaultPreviewName = old('name', $package->name ?? '');
    $defaultPreviewTagline = old('tagline', $package->tagline ?? '');
    $defaultPreviewBadge = old('badge_text', $package->badge_text ?? '');
    $defaultPreviewDescription = old('description', $package->description ?? '');
    $defaultPreviewCode = strtoupper(old('code', $package->code ?? 'PKG'));
    $defaultPreviewPrice = (float) old('price', $package->price ?? 0);
    $defaultPreviewCommission = (float) old('commission_rate', $package->commission_rate ?? 0);
    $defaultPreviewCourseLimit = old('course_limit', $package->course_limit ?? '');
    $defaultPreviewSupport = old('support_level', $package->support_level ?? '');
    $defaultPreviewFeatured = old('is_featured', $package->is_featured ?? false);
    $defaultPreviewPriority = old('priority_review', $package->priority_review ?? false);
    $defaultHiddenMode = old('hidden_mode', $package->hidden_mode ?? 'unavailable');
    $resolvedSortOrder = old('sort_order', $package->sort_order ?? ($nextSortOrder ?? 1));
    $defaultBadgeTone = old('badge_tone', $package->badge_tone ?? '#2563eb');
@endphp

<div class="row g-4 admin-package-form">
    {{-- Left Column: Main Configuration --}}
    <div class="col-xl-8 col-lg-7">
        {{-- Section 1: Core Identity --}}
        <div class="glass-section mb-4">
            <div class="section-header">
                <div class="icon-box"><i class="fas fa-fingerprint"></i></div>
                <div>
                    <h6 class="title">Định danh & Phân loại</h6>
                    <p class="subtitle">Thiết lập mã định danh và nhóm cho gói dịch vụ.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label-custom">Code định danh</label>
                    <div class="input-group-custom">
                        <span class="input-icon"><i class="fas fa-hashtag"></i></span>
                        <input type="text" class="form-control" name="code" value="{{ old('code', $package->code ?? '') }}" 
                            placeholder="VD: VIP_GOLD" required>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label-custom d-flex justify-content-between">
                        Danh mục gói
                        <a href="{{ route('teacher-packages.categories.index') }}" class="manage-link">
                            <i class="fas fa-external-link-alt me-1"></i>Quản lý
                        </a>
                    </label>
                    <div class="input-group-custom">
                        <span class="input-icon"><i class="fas fa-layer-group"></i></span>
                        <select name="category_id" class="form-select select-custom">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $package->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom">Thứ tự</label>
                    <div class="input-group-custom">
                        <span class="input-icon"><i class="fas fa-sort-numeric-down"></i></span>
                        <input type="number" class="form-control" name="sort_order" min="1" value="{{ $resolvedSortOrder }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Multilingual Content --}}
        <div class="glass-section mb-4">
            <div class="section-header justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box"><i class="fas fa-language"></i></div>
                    <div>
                        <h6 class="title">Nội dung đa ngôn ngữ</h6>
                        <p class="subtitle">Tùy chỉnh thông tin hiển thị cho từng thị trường.</p>
                    </div>
                </div>
                <ul class="nav nav-pills locale-pills" id="package-locale-tabs" role="tablist">
                    @foreach ($locales as $locale => $meta)
                        <li class="nav-item">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" 
                                data-bs-target="#package-locale-{{ $locale }}" data-package-locale-tab="{{ $locale }}">
                                <span class="flag-icon flag-{{ $meta['flag'] }}"></span> {{ $meta['label'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="tab-content pt-2">
                @foreach ($locales as $locale => $meta)
                    @php
                        $isDefault = $locale === 'vi';
                        $nameField = $isDefault ? 'name' : 'name_' . $locale;
                        $taglineField = $isDefault ? 'tagline' : 'tagline_' . $locale;
                        $badgeField = $isDefault ? 'badge_text' : 'badge_text_' . $locale;
                        $descriptionField = $isDefault ? 'description' : 'description_' . $locale;
                        $supportField = $isDefault ? 'support_level' : 'support_level_' . $locale;
                    @endphp
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="package-locale-{{ $locale }}">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label-custom">Tên gói ({{ $meta['name'] }})</label>
                                <input type="text" class="form-control input-premium" name="{{ $nameField }}"
                                    value="{{ old($nameField, $package->{$nameField} ?? '') }}" {{ $isDefault ? 'required' : '' }}
                                    data-preview-field="name" data-preview-locale="{{ $locale }}" placeholder="VD: Gói Chuyên Nghiệp">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label-custom">Mức hỗ trợ</label>
                                <input type="text" class="form-control input-premium" name="{{ $supportField }}"
                                    value="{{ old($supportField, $package->{$supportField} ?? '') }}"
                                    data-preview-field="support" data-preview-locale="{{ $locale }}" placeholder="VD: 24/7 VIP">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label-custom">Tagline ngắn</label>
                                <input type="text" class="form-control input-premium" name="{{ $taglineField }}" maxlength="190"
                                    value="{{ old($taglineField, $package->{$taglineField} ?? '') }}"
                                    data-preview-field="tagline" data-preview-locale="{{ $locale }}" placeholder="Slogan thu hút người mua">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label-custom">Badge Text</label>
                                <input type="text" class="form-control input-premium" name="{{ $badgeField }}" maxlength="100"
                                    value="{{ old($badgeField, $package->{$badgeField} ?? '') }}"
                                    data-preview-field="badge" data-preview-locale="{{ $locale }}" placeholder="VD: Phổ biến nhất">
                            </div>
                            <div class="col-12">
                                <label class="form-label-custom">Mô tả chi tiết</label>
                                <textarea class="form-control input-premium" name="{{ $descriptionField }}" rows="3"
                                    data-preview-field="description" data-preview-locale="{{ $locale }}" placeholder="Mô tả ngắn gọn về giá trị của gói...">{{ old($descriptionField, $package->{$descriptionField} ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 3: Pricing & Limits --}}
        <div class="glass-section mb-4">
            <div class="section-header">
                <div class="icon-box"><i class="fas fa-hand-holding-usd"></i></div>
                <div>
                    <h6 class="title">Giá & Hạn mức vận hành</h6>
                    <p class="subtitle">Thiết lập chi phí và giới hạn kỹ thuật cho gói.</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="pricing-card-input">
                        <div class="d-flex justify-content-between mb-2">
                            <label class="form-label-custom mb-0">Giá bán</label>
                            <span class="badge text-bg-soft-primary">VND</span>
                        </div>
                        <input type="hidden" name="price" id="admin-package-price" value="{{ old('price', $package->price ?? 0) }}" data-preview-global="price">
                        <div class="input-money-wrapper">
                            <input type="text" class="form-control price-input" id="admin-package-price-display"
                                value="{{ number_format(old('price', $package->price ?? 0), 0, ',', '.') }}" 
                                data-money-target="admin-package-price" required>
                            <span class="currency-symbol">₫</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="pricing-card-input h-100 d-flex flex-column justify-content-center px-4 border-start">
                        <label class="form-label-custom mb-2">Chu kỳ thanh toán</label>
                        <div class="d-flex gap-2">
                            @foreach (['one_time' => 'Một lần', 'monthly' => 'Hàng tháng', 'yearly' => 'Hàng năm'] as $val => $lbl)
                                <input type="radio" class="btn-check" name="billing_cycle" id="bc_{{ $val }}" value="{{ $val }}" 
                                    {{ old('billing_cycle', $package->billing_cycle ?? 'one_time') === $val ? 'checked' : '' }}>
                                <label class="btn btn-outline-soft-primary btn-sm rounded-pill px-3" for="bc_{{ $val }}">{{ $lbl }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-2">
                    <hr class="opacity-10">
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Hạn mức khóa học</label>
                    <input type="number" class="form-control input-premium" name="course_limit" min="1"
                        value="{{ old('course_limit', $package->course_limit ?? '') }}" data-preview-global="course_limit"
                        placeholder="Không giới hạn">
                </div>
                <div class="col-md-4">
                    <label class="form-label-custom">Commission Rate (%)</label>
                    <div class="input-group-custom">
                        <span class="input-icon"><i class="fas fa-percentage"></i></span>
                        <input type="number" class="form-control" name="commission_rate" min="0" max="100" step="0.01"
                            value="{{ old('commission_rate', $package->commission_rate ?? 50) }}" required data-preview-global="commission_rate">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label-custom">Payout Limit/Ngày</label>
                    <input type="hidden" name="max_payout_per_day" id="admin-package-payout-limit" value="{{ old('max_payout_per_day', $package->max_payout_per_day ?? '') }}">
                    <input type="text" class="form-control input-premium" id="admin-package-payout-limit-display"
                        value="{{ isset($package) && $package->max_payout_per_day ? number_format($package->max_payout_per_day, 0, ',', '.') : '' }}" 
                        data-money-target="admin-package-payout-limit" placeholder="Không giới hạn">
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">Hạn mức ví liên kết</label>
                    <input type="number" class="form-control input-premium" name="payout_account_limit" min="1" max="10"
                        value="{{ old('payout_account_limit', $package->payout_account_limit ?? 3) }}" placeholder="Mặc định: 3">
                </div>
                <div class="col-md-4">
                    <label class="form-label-custom">Giới hạn AI Quiz (lần/tháng)</label>
                    <input type="number" class="form-control input-premium" name="ai_quiz_limit" min="1"
                        value="{{ old('ai_quiz_limit', $package->ai_quiz_limit ?? '') }}" placeholder="Không giới hạn">
                </div>
                <div class="col-md-4">
                    <label class="form-label-custom">Giới hạn Coupon</label>
                    <input type="number" class="form-control input-premium" name="coupon_limit" min="1"
                        value="{{ old('coupon_limit', $package->coupon_limit ?? '') }}" placeholder="Không giới hạn">
                </div>
            </div>
        </div>

        {{-- Section 4: Features Grid --}}
        <div class="glass-section mb-4">
            <div class="section-header">
                <div class="icon-box"><i class="fas fa-tasks"></i></div>
                <div>
                    <h6 class="title">Tính năng đi kèm</h6>
                    <p class="subtitle">Phân hóa quyền lợi đặc quyền của gói.</p>
                </div>
            </div>

            @php
                $systemFeaturesGrouped = [
                    'Nội dung & Học tập' => [
                        'can_duplicate_courses' => 'Nhân bản khóa học',
                        'can_manage_quizzes' => 'Quản lý quiz',
                        'can_use_ai_quiz' => 'AI Quiz Generator',
                        'can_schedule_content' => 'Lịch trình bài học',
                    ],
                    'Vận hành & Học viên' => [
                        'can_manage_students' => 'Quản lý học viên',
                        'can_view_student_progress' => 'Xem tiến độ học',
                        'can_manage_comments' => 'Quản lý bình luận',
                        'can_issue_certificates' => 'Cấp chứng chỉ',
                        'can_verify_certificates' => 'Xác minh chứng chỉ',
                        'can_grant_courses' => 'Cấp quyền khóa học',
                    ],
                    'Bán hàng & Marketing' => [
                        'can_manage_coupons' => 'Quản lý Coupon',
                        'can_sell_bundles' => 'Combo khóa học',
                        'can_send_promotions' => 'Gửi khuyến mại',
                        'can_use_affiliate_links' => 'Link tiếp thị (Affiliate)',
                        'can_customize_teacher_landing' => 'Tùy chỉnh Landing',
                    ],
                    'Tài chính & Hệ thống' => [
                        'can_request_payouts' => 'Yêu cầu rút tiền',
                        'can_view_activity_logs' => 'Nhật ký hoạt động',
                        'can_import_export' => 'Import/Export dữ liệu',
                        'can_export_orders' => 'Xuất đơn hàng',
                    ]
                ];
            @endphp

            <div class="row g-3 mt-1">
                @foreach($systemFeaturesGrouped as $groupName => $features)
                    <div class="col-md-6">
                        <div class="feature-group-card">
                            <h6 class="feature-group-title">{{ $groupName }}</h6>
                            <div class="feature-grid">
                                @foreach($features as $key => $label)
                                    @php
                                        $status = isset($globalFeatureStatus[$key]) ? (int) $globalFeatureStatus[$key] : 1;
                                        $isMaintenance = $status !== 1;
                                        $isChecked = (bool) old($key, $package->{$key} ?? false);
                                    @endphp
                                    <div class="feature-item {{ $isMaintenance ? 'is-maintenance' : '' }}">
                                        <div class="form-check form-switch-premium">
                                            <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1"
                                                id="feat_{{ $key }}" {{ $isChecked ? 'checked' : '' }} {{ $isMaintenance ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="feat_{{ $key }}">
                                                {{ $label }}
                                                @if($isMaintenance) <span class="maint-tag">Maint</span> @endif
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right Column: Preview & Status --}}
    <div class="col-xl-4 col-lg-5">
        <div class="sticky-top" style="top: 1.5rem; z-index: 10;">
            {{-- Preview Box --}}
            <div class="glass-section mb-4 overflow-hidden position-relative">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="title mb-0">Preview Giao diện</h6>
                    <span class="preview-locale-badge" data-preview-locale-badge>VI</span>
                </div>

                <div class="package-card-premium {{ $defaultPreviewFeatured ? 'is-featured' : '' }}" 
                    data-package-preview-card style="--package-tone: {{ $defaultBadgeTone }}">
                    <div class="premium-badge-wrapper">
                        <span class="p-card-code" data-preview-code>{{ $defaultPreviewCode }}</span>
                        <span class="p-card-badge {{ $defaultPreviewBadge ? '' : 'd-none' }}" data-preview-badge>{{ $defaultPreviewBadge }}</span>
                    </div>

                    <div class="p-card-header">
                        <h4 class="p-card-name" data-preview-name>{{ $defaultPreviewName ?: 'Tên gói' }}</h4>
                        <p class="p-card-tagline {{ $defaultPreviewTagline ? '' : 'd-none' }}" data-preview-tagline>{{ $defaultPreviewTagline }}</p>
                    </div>

                    <div class="p-card-pricing">
                        <div class="p-card-price" data-preview-price>{{ number_format($defaultPreviewPrice, 0, ',', '.') }} đ</div>
                        <div class="p-card-cycle">/ thanh toán</div>
                    </div>

                    <div class="p-card-description {{ $defaultPreviewDescription ? '' : 'd-none' }}" data-preview-description>
                        {{ $defaultPreviewDescription }}
                    </div>

                    <div class="p-card-divider"></div>

                    <ul class="p-card-features">
                        <li data-preview-commission><i class="fas fa-check-circle"></i> Chia doanh thu {{ rtrim(rtrim(number_format($defaultPreviewCommission, 2, '.', ''), '0'), '.') }}%</li>
                        <li data-preview-course-limit><i class="fas fa-check-circle"></i> 
                            {{ $defaultPreviewCourseLimit !== '' ? "Tối đa $defaultPreviewCourseLimit khóa" : 'Khóa học không giới hạn' }}
                        </li>
                        <li data-preview-priority><i class="fas fa-check-circle"></i> {{ $defaultPreviewPriority ? 'Ưu tiên duyệt hồ sơ' : 'Duyệt tiêu chuẩn' }}</li>
                        <li data-preview-support><i class="fas fa-check-circle"></i> {{ $defaultPreviewSupport ? 'Hỗ trợ ' . $defaultPreviewSupport : 'Hỗ trợ cơ bản' }}</li>
                    </ul>

                    <div class="p-card-action">Chọn gói dịch vụ này</div>
                </div>

                <div class="active-features-pills mt-3">
                    @foreach ($featureBadges as $badge)
                        <span class="f-pill {{ old($badge['key'], $package->{$badge['key']} ?? false) ? 'active' : '' }}">
                            {{ $badge['label'] }}
                        </span>
                    @endforeach
                    <span class="f-pill {{ old('can_use_affiliate_links', $package->can_use_affiliate_links ?? false) ? 'active' : '' }}">
                        Link tiếp thị
                    </span>
                </div>
            </div>

            {{-- Visibility & Advanced --}}
            <div class="glass-section">
                <h6 class="title mb-3">Cài đặt hiển thị</h6>
                
                <div class="mb-4">
                    <label class="form-label-custom">Màu chủ đạo (Theme Color)</label>
                    <div class="color-picker-premium">
                        <input type="color" class="form-control-color" name="badge_tone" value="{{ $defaultBadgeTone }}">
                        <input type="text" class="form-control text-uppercase" id="badge_tone_hex" value="{{ $defaultBadgeTone }}" maxlength="7">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label-custom">Khi đã tắt công khai</label>
                    <select name="hidden_mode" class="form-select select-custom">
                        <option value="available" {{ $defaultHiddenMode === 'available' ? 'selected' : '' }}>Tiếp tục dùng (Legacy)</option>
                        <option value="unavailable" {{ $defaultHiddenMode === 'unavailable' ? 'selected' : '' }}>Ngắt hoàn toàn</option>
                    </select>
                </div>

                <div class="status-switches p-3 border rounded-4 bg-white">
                    <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-3">
                        <label class="form-check-label fw-bold">Trạng thái công khai</label>
                        <input class="form-check-input ms-0" type="checkbox" name="status" value="1" {{ old('status', $package->status ?? true) ? 'checked' : '' }}>
                    </div>
                    <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                        <label class="form-check-label fw-bold">Nổi bật (Landing Page)</label>
                        <input class="form-check-input ms-0" type="checkbox" name="is_featured" value="1" {{ old('is_featured', $package->is_featured ?? false) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --admin-bg: #f8fafc;
        --admin-card-bg: rgba(255, 255, 255, 0.8);
        --admin-accent: #2563eb;
        --admin-accent-soft: rgba(37, 99, 235, 0.1);
        --admin-text-main: #1e293b;
        --admin-text-muted: #64748b;
        --admin-border: #e2e8f0;
    }

    .admin-package-form {
        color: var(--admin-text-main);
        font-family: 'Inter', sans-serif;
    }

    /* Glass Section Layout */
    .glass-section {
        background: #ffffff;
        border: 1px solid var(--admin-border);
        border-radius: 24px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        backdrop-filter: blur(10px);
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 1rem;
    }

    .icon-box {
        width: 42px;
        height: 42px;
        background: var(--admin-accent-soft);
        color: var(--admin-accent);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.1rem;
    }

    .title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.1rem;
        color: #0f172a;
    }

    .subtitle {
        font-size: 0.8rem;
        color: var(--admin-text-muted);
        margin: 0;
    }

    /* Inputs Styling */
    .form-label-custom {
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.5rem;
        display: block;
    }

    .input-group-custom {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon {
        position: absolute;
        left: 1rem;
        color: #94a3b8;
        font-size: 0.9rem;
        z-index: 5;
    }

    .input-group-custom .form-control,
    .input-group-custom .form-select {
        padding-left: 2.75rem;
    }

    .form-control, .form-select {
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        padding: 0.65rem 1rem;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--admin-accent);
        box-shadow: 0 0 0 4px var(--admin-accent-soft);
    }

    .input-premium {
        background: #fcfdfe;
    }

    /* Tabs Styling */
    .locale-pills {
        background: #f1f5f9;
        padding: 0.3rem;
        border-radius: 12px;
    }

    .locale-pills .nav-link {
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.4rem 0.8rem;
        color: #64748b;
        transition: all 0.2s;
    }

    .locale-pills .nav-link.active {
        background: #ffffff;
        color: var(--admin-accent);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    /* Pricing Section */
    .pricing-card-input {
        padding: 0.5rem;
    }

    .input-money-wrapper {
        position: relative;
    }

    .price-input {
        font-size: 1.5rem !important;
        font-weight: 800;
        padding-right: 2.5rem !important;
        color: #0f172a;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
    }

    .currency-symbol {
        position: absolute;
        right: 1.2rem;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 800;
        color: #94a3b8;
        font-size: 1.2rem;
    }

    .btn-outline-soft-primary {
        border: 1.5px solid #e2e8f0;
        color: #64748b;
        font-weight: 600;
    }

    .btn-check:checked + .btn-outline-soft-primary {
        background: var(--admin-accent-soft);
        color: var(--admin-accent);
        border-color: var(--admin-accent);
    }

    /* Features Styling */
    .feature-group-card {
        background: #f8fafc;
        border-radius: 18px;
        padding: 1.2rem;
        height: 100%;
        border: 1px solid #eef2f6;
    }

    .feature-group-title {
        font-size: 0.85rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .feature-grid {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .feature-item {
        background: #ffffff;
        padding: 0.5rem 0.8rem;
        border-radius: 10px;
        border: 1px solid #f1f5f9;
        transition: transform 0.1s;
    }

    .feature-item:hover {
        border-color: #cbd5e1;
    }

    .form-switch-premium .form-check-input {
        width: 2.2em;
        height: 1.1em;
        cursor: pointer;
    }

    .form-switch-premium .form-check-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }

    /* Premium Preview Card */
    .package-card-premium {
        background: linear-gradient(165deg, #0f172a 0%, #1e293b 100%);
        border-radius: 28px;
        padding: 1.75rem;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }

    .package-card-premium::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, var(--package-tone, #2563eb) 0%, transparent 70%);
        opacity: 0.15;
        pointer-events: none;
    }

    .p-card-code {
        background: rgba(255,255,255,0.08);
        color: #94a3b8;
        padding: 0.3rem 0.7rem;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .p-card-badge {
        background: var(--package-tone, #2563eb);
        color: #fff;
        padding: 0.3rem 0.8rem;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 700;
        margin-left: 0.5rem;
    }

    .p-card-name {
        font-size: 1.5rem;
        font-weight: 800;
        margin-top: 1.2rem;
        margin-bottom: 0.3rem;
    }

    .p-card-tagline {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-bottom: 1.5rem;
    }

    .p-card-price {
        font-size: 2.25rem;
        font-weight: 900;
        line-height: 1;
    }

    .p-card-cycle {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 0.4rem;
    }

    .p-card-divider {
        height: 1px;
        background: rgba(255,255,255,0.06);
        margin: 1.5rem 0;
    }

    .p-card-features {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .p-card-features li {
        font-size: 0.85rem;
        color: #cbd5e1;
        margin-bottom: 0.6rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .p-card-features i {
        color: var(--package-tone, #2563eb);
        font-size: 0.9rem;
    }

    .p-card-action {
        margin-top: 2rem;
        background: #ffffff;
        color: #0f172a;
        padding: 0.9rem;
        border-radius: 16px;
        text-align: center;
        font-weight: 800;
        font-size: 0.95rem;
        box-shadow: 0 4px 12px rgba(255,255,255,0.1);
    }

    /* Helper pills */
    .f-pill {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        background: #f1f5f9;
        color: #94a3b8;
        border-radius: 6px;
        font-size: 0.65rem;
        font-weight: 700;
        margin: 0.2rem;
        border: 1px solid #e2e8f0;
    }

    .f-pill.active {
        background: var(--admin-accent-soft);
        color: var(--admin-accent);
        border-color: var(--admin-accent);
    }

    .color-picker-premium {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .form-control-color {
        width: 48px;
        height: 40px;
        padding: 0.2rem;
        border-radius: 10px;
    }

    /* Dark Mode Overrides */
    html[data-theme="dark"] .glass-section {
        background: #1e293b;
        border-color: #334155;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    html[data-theme="dark"] .section-header {
        border-bottom-color: #334155;
    }

    html[data-theme="dark"] .title {
        color: #f1f5f9;
    }

    html[data-theme="dark"] .form-label-custom {
        color: #94a3b8;
    }

    html[data-theme="dark"] .form-control, 
    html[data-theme="dark"] .form-select {
        background-color: #0f172a;
        border-color: #334155;
        color: #f1f5f9;
    }

    html[data-theme="dark"] .input-premium {
        background: #111827;
    }

    html[data-theme="dark"] .locale-pills {
        background: #0f172a;
    }

    html[data-theme="dark"] .locale-pills .nav-link:not(.active) {
        color: #94a3b8;
    }

    html[data-theme="dark"] .locale-pills .nav-link.active {
        background: #1e293b;
        color: #38bdf8;
    }

    html[data-theme="dark"] .price-input {
        background: #0f172a;
        color: #f1f5f9;
        border-color: #334155;
    }

    html[data-theme="dark"] .feature-group-card {
        background: #111827;
        border-color: #1e293b;
    }

    html[data-theme="dark"] .feature-group-title {
        color: #f1f5f9;
    }

    html[data-theme="dark"] .feature-item {
        background: #1e293b;
        border-color: #334155;
    }

    html[data-theme="dark"] .feature-item:hover {
        border-color: #475569;
    }

    html[data-theme="dark"] .status-switches {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    html[data-theme="dark"] .status-switches label {
        color: #f1f5f9;
    }

    /* Hover Fixes for Dark Mode */
    html[data-theme="dark"] .btn-outline-soft-primary:hover {
        background: #38bdf8;
        color: #0f172a !important;
        border-color: #38bdf8;
    }

    html[data-theme="dark"] .feature-item:hover {
        background: #1e293b;
        border-color: #38bdf8;
    }

    html[data-theme="dark"] .locale-pills .nav-link:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #f1f5f9;
    }

    html[data-theme="dark"] .form-check-label {
        color: #e2e8f0 !important;
    }

    html[data-theme="dark"] .subtitle {
        color: #94a3b8 !important;
    }

    html[data-theme="dark"] .feature-group-title {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .p-card-cycle {
        color: #94a3b8;
    }

    html[data-theme="dark"] .form-control::placeholder {
        color: #475569;
    }

    html[data-theme="dark"] .f-pill {
        background: #0f172a;
        border-color: #334155;
    }

    html[data-theme="dark"] .fw-black {
        color: #f1f5f9 !important;
    }

    html[data-theme="dark"] .manage-link {
        color: #38bdf8 !important;
    }
</style>

<script>
    (() => {
        const defaultLocale = 'vi';
        let activeLocale = defaultLocale;
        const localeMeta = @json($locales);

        const getLocaleValue = (field, locale) => {
            const localeInput = document.querySelector(`[data-preview-field="${field}"][data-preview-locale="${locale}"]`);
            const defaultInput = document.querySelector(`[data-preview-field="${field}"][data-preview-locale="${defaultLocale}"]`);
            const value = localeInput ? localeInput.value.trim() : '';
            if (value !== '') {
                return value;
            }
            return defaultInput ? defaultInput.value.trim() : '';
        };

        const formatPrice = (value) => {
            const amount = Number(value || 0);
            if (amount <= 0) {
                return 'Miễn phí';
            }
            return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
        };

        const hexToRgb = (hex) => {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}` : null;
        };

        const updatePreview = () => {
            const toneInput = document.querySelector('input[name="badge_tone"]');
            const toneValue = toneInput?.value || '#2563eb';
            
            const code = document.querySelector('input[name="code"]')?.value.trim() || 'PKG';
            const price = document.querySelector('[data-preview-global="price"]')?.value ?? 0;
            const courseLimit = document.querySelector('[data-preview-global="course_limit"]')?.value.trim() ?? '';
            const commission = document.querySelector('[data-preview-global="commission_rate"]')?.value ?? 0;
            const priority = document.querySelector('[data-preview-global="priority_review"]')?.checked ?? false;
            const isFeatured = document.querySelector('input[name="is_featured"]')?.checked ?? false;
            
            const support = getLocaleValue('support', activeLocale);
            const name = getLocaleValue('name', activeLocale) || 'Tên gói dịch vụ';
            const tagline = getLocaleValue('tagline', activeLocale);
            const badge = getLocaleValue('badge', activeLocale);
            const description = getLocaleValue('description', activeLocale);

            document.querySelector('[data-preview-code]').textContent = code.toUpperCase();
            document.querySelector('[data-preview-name]').textContent = name;
            document.querySelector('[data-preview-price]').textContent = formatPrice(price);
            document.querySelector('[data-preview-commission]').innerHTML = `<i class="fas fa-check-circle"></i> Chia doanh thu ${Number(commission || 0)}%`;
            document.querySelector('[data-preview-course-limit]').innerHTML = `<i class="fas fa-check-circle"></i> ${courseLimit !== '' ? `Tối đa ${courseLimit} khóa` : 'Khóa học không giới hạn'}`;
            document.querySelector('[data-preview-priority]').innerHTML = `<i class="fas fa-check-circle"></i> ${priority ? 'Ưu tiên duyệt hồ sơ' : 'Duyệt tiêu chuẩn'}`;
            document.querySelector('[data-preview-support]').innerHTML = `<i class="fas fa-check-circle"></i> ${support !== '' ? `Hỗ trợ ${support}` : 'Hỗ trợ cơ bản'}`;

            const taglineNode = document.querySelector('[data-preview-tagline]');
            taglineNode.textContent = tagline;
            taglineNode.classList.toggle('d-none', tagline === '');

            const descriptionNode = document.querySelector('[data-preview-description]');
            descriptionNode.textContent = description;
            descriptionNode.classList.toggle('d-none', description === '');

            const badgeNode = document.querySelector('[data-preview-badge]');
            badgeNode.textContent = badge;
            badgeNode.classList.toggle('d-none', badge === '');

            const previewCard = document.querySelector('[data-package-preview-card]');
            if (previewCard) {
                previewCard.style.setProperty('--package-tone', toneValue);
                previewCard.classList.toggle('is-featured', isFeatured);
            }

            document.querySelector('[data-preview-locale-badge]').textContent = localeMeta[activeLocale]?.label || activeLocale.toUpperCase();
        };

        const toneInput = document.querySelector('input[name="badge_tone"]');
        const toneHexInput = document.getElementById('badge_tone_hex');

        if (toneInput && toneHexInput) {
            toneInput.addEventListener('input', (e) => {
                toneHexInput.value = e.target.value.toUpperCase();
                updatePreview();
            });
            toneHexInput.addEventListener('input', (e) => {
                let val = e.target.value;
                if (!val.startsWith('#')) val = '#' + val;
                if (/^#[0-9A-F]{6}$/i.test(val)) {
                    toneInput.value = val;
                    updatePreview();
                }
            });
        }

        // Money Input Formatting Logic
        document.querySelectorAll('[data-money-target]').forEach(displayInput => {
            const targetId = displayInput.getAttribute('data-money-target');
            const hiddenInput = document.getElementById(targetId);
            if (!hiddenInput) return;

            displayInput.addEventListener('input', (e) => {
                let cursorPosition = e.target.selectionStart;
                let originalLength = e.target.value.length;
                let rawValue = e.target.value.replace(/\D/g, '');
                
                if (rawValue === '') {
                    hiddenInput.value = '';
                    displayInput.value = '';
                    updatePreview();
                    return;
                }

                hiddenInput.value = rawValue;
                let formattedValue = new Intl.NumberFormat('vi-VN').format(rawValue);
                displayInput.value = formattedValue;

                let lengthDiff = formattedValue.length - originalLength;
                displayInput.setSelectionRange(cursorPosition + lengthDiff, cursorPosition + lengthDiff);
                updatePreview();
            });
        });

        document.querySelectorAll('[data-preview-field], [data-preview-global], input[name="code"], input[name="is_featured"], .btn-check').forEach((element) => {
            element.addEventListener('input', updatePreview);
            element.addEventListener('change', updatePreview);
        });

        document.querySelectorAll('[data-package-locale-tab]').forEach((tab) => {
            tab.addEventListener('shown.bs.tab', () => {
                activeLocale = tab.dataset.packageLocaleTab || defaultLocale;
                updatePreview();
            });
        });

        updatePreview();
    })();
</script>