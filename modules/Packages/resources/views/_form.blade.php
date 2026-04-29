@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
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
        'vi' => ['label' => 'VI', 'name' => 'Tiếng Việt'],
        'en' => ['label' => 'EN', 'name' => 'English'],
        'ko' => ['label' => 'KO', 'name' => 'Korean'],
        'ja' => ['label' => 'JA', 'name' => 'Japanese'],
        'zh' => ['label' => 'ZH', 'name' => 'Chinese'],
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

<div class="row g-4">
    <div class="col-12">
        <div class="border rounded-4 p-4 bg-light-subtle">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                <div>
                    <h6 class="mb-1">Thông tin hệ thống</h6>
                    <p class="text-muted mb-0">Code, trạng thái và quy tắc sắp xếp của gói.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Code</label>
                    <input type="text" class="form-control" name="code" value="{{ old('code', $package->code ?? '') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sort order</label>
                    <input type="number" class="form-control" name="sort_order" min="1"
                        value="{{ $resolvedSortOrder }}">
                </div>
                <div class="col-md-4 d-flex flex-column justify-content-center gap-2">
                    <div class="form-check mt-md-4">
                        <input class="form-check-input" type="checkbox" name="status" value="1"
                            {{ old('status', $package->status ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Hiện công khai cho người mua</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                            {{ old('is_featured', $package->is_featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Đính trên landing page</label>
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Khi đã tắt công khai</label>
                    <select name="hidden_mode" class="form-select">
                        <option value="available" {{ $defaultHiddenMode === 'available' ? 'selected' : '' }}>
                            Ẩn đi nhưng người đang dùng gói này vẫn tiếp tục sử dụng được
                        </option>
                        <option value="unavailable" {{ $defaultHiddenMode === 'unavailable' ? 'selected' : '' }}>
                            Ẩn đi và không cho người mua tiếp tục chọn / sử dụng nữa
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label d-flex justify-content-between">
                        Màu chủ đạo (Tone màu)
                        <a href="https://flatuicolors.com/" target="_blank" class="text-primary small" style="text-decoration: none;">
                            <i class="fas fa-palette me-1"></i>Gợi ý màu đẹp
                        </a>
                    </label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="color" class="form-control form-control-color" name="badge_tone" 
                            value="{{ $defaultBadgeTone }}" title="Chọn màu đặc trưng cho gói">
                        <input type="text" class="form-control font-monospace" id="badge_tone_hex" 
                            value="{{ $defaultBadgeTone }}" placeholder="#000000" maxlength="7">
                    </div>
                    <div class="form-text">Màu này sẽ hiển thị trên Card và Sidebar giảng viên.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                <div>
                    <h6 class="mb-1">Nội dung đa ngôn ngữ</h6>
                    <p class="text-muted mb-0">Tên gói, tagline, badge, mô tả và mức hỗ trợ hiển thị theo locale.</p>
                </div>
            </div>
            <div class="row g-4 align-items-start">
                <div class="col-xl-8">
                    <ul class="nav nav-pills gap-2 mb-4" id="package-locale-tabs" role="tablist">
                        @foreach ($locales as $locale => $meta)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" type="button"
                                    data-bs-toggle="tab" data-bs-target="#package-locale-{{ $locale }}" role="tab"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                    data-package-locale-tab="{{ $locale }}">
                                    {{ $meta['label'] }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach ($locales as $locale => $meta)
                            @php
                                $isDefault = $locale === 'vi';
                                $nameField = $isDefault ? 'name' : 'name_' . $locale;
                                $taglineField = $isDefault ? 'tagline' : 'tagline_' . $locale;
                                $badgeField = $isDefault ? 'badge_text' : 'badge_text_' . $locale;
                                $descriptionField = $isDefault ? 'description' : 'description_' . $locale;
                                $supportField = $isDefault ? 'support_level' : 'support_level_' . $locale;
                            @endphp

                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="package-locale-{{ $locale }}" role="tabpanel">
                                <div class="border rounded-4 p-4 bg-light-subtle">
                                    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                                        <div>
                                            <h6 class="mb-1">{{ $meta['name'] }}</h6>
                                            <p class="text-muted mb-0">
                                                {{ $isDefault ? 'Đây là nội dung mặc định và được dùng làm fallback cho các locale khác.' : 'Nếu để trống, hệ thống sẽ fallback về bản mặc định.' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tên gói</label>
                                            <input type="text" class="form-control" name="{{ $nameField }}"
                                                value="{{ old($nameField, $package->{$nameField} ?? '') }}" {{ $isDefault ? 'required' : '' }}
                                                data-preview-field="name" data-preview-locale="{{ $locale }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Mức hỗ trợ</label>
                                            <input type="text" class="form-control" name="{{ $supportField }}"
                                                value="{{ old($supportField, $package->{$supportField} ?? '') }}"
                                                data-preview-field="support" data-preview-locale="{{ $locale }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tagline ngắn</label>
                                            <input type="text" class="form-control" name="{{ $taglineField }}" maxlength="190"
                                                value="{{ old($taglineField, $package->{$taglineField} ?? '') }}"
                                                data-preview-field="tagline" data-preview-locale="{{ $locale }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Badge text</label>
                                            <input type="text" class="form-control" name="{{ $badgeField }}" maxlength="100"
                                                value="{{ old($badgeField, $package->{$badgeField} ?? '') }}"
                                                data-preview-field="badge" data-preview-locale="{{ $locale }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Mô tả chi tiết</label>
                                            <textarea class="form-control" name="{{ $descriptionField }}" rows="4"
                                                data-preview-field="description" data-preview-locale="{{ $locale }}">{{ old($descriptionField, $package->{$descriptionField} ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="border rounded-4 p-4 bg-light-subtle position-sticky" style="top: 1rem;">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <div>
                                <h6 class="mb-1">Preview card</h6>
                                <p class="text-muted mb-0 small">Xem nhanh giao diện gói theo tab đang mở.</p>
                            </div>
                            <span class="badge text-bg-dark" data-preview-locale-badge>VI</span>
                        </div>

                        <div class="package-preview-card {{ $defaultPreviewFeatured ? 'is-featured' : '' }}" 
                            data-package-preview-card
                            style="--package-tone: {{ $defaultBadgeTone }}">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <span class="package-preview-card__code" data-preview-code>{{ $defaultPreviewCode }}</span>
                                <span class="package-preview-card__badge {{ $defaultPreviewBadge ? '' : 'd-none' }}" data-preview-badge>{{ $defaultPreviewBadge }}</span>
                            </div>

                            <h5 class="package-preview-card__name" data-preview-name>{{ $defaultPreviewName ?: 'Tên gói' }}</h5>
                            <p class="package-preview-card__tagline {{ $defaultPreviewTagline ? '' : 'd-none' }}" data-preview-tagline>{{ $defaultPreviewTagline }}</p>
                            <div class="package-preview-card__price" data-preview-price>{{ number_format($defaultPreviewPrice, 0, ',', '.') }} đ</div>
                            <p class="package-preview-card__description {{ $defaultPreviewDescription ? '' : 'd-none' }}" data-preview-description>{{ $defaultPreviewDescription }}</p>

                            <ul class="package-preview-card__features">
                                <li data-preview-commission>Chia doanh thu {{ rtrim(rtrim(number_format($defaultPreviewCommission, 2, '.', ''), '0'), '.') }}%</li>
                                <li data-preview-course-limit>
                                    @if ($defaultPreviewCourseLimit !== '' && $defaultPreviewCourseLimit !== null)
                                        Đăng tối đa {{ $defaultPreviewCourseLimit }} khóa
                                    @else
                                        Đăng khóa học không giới hạn
                                    @endif
                                </li>
                                <li data-preview-priority>{{ $defaultPreviewPriority ? 'Ưu tiên duyệt hồ sơ' : 'Duyệt theo hàng chờ tiêu chuẩn' }}</li>
                                <li data-preview-support>{{ $defaultPreviewSupport ? 'Hỗ trợ ' . $defaultPreviewSupport : 'Hỗ trợ cơ bản' }}</li>
                            </ul>

                            <div class="package-preview-card__cta">Đăng ký với gói này</div>
                        </div>

                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @foreach ($featureBadges as $badge)
                                <span class="badge rounded-pill text-bg-light border {{ old($badge['key'], $package->{$badge['key']} ?? false) ? 'text-success border-success' : 'text-muted' }}">
                                    {{ $badge['label'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-4 bg-light-subtle">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                <div>
                    <h6 class="mb-1">Giá và quyền lợi</h6>
                    <p class="text-muted mb-0">Phần dữ liệu vận hành dùng chung cho mọi locale.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Giá</label>
                    <input type="hidden" name="price" id="admin-package-price" value="{{ old('price', $package->price ?? 0) }}" data-preview-global="price">
                    <input type="text" class="form-control" id="admin-package-price-display"
                        value="{{ number_format(old('price', $package->price ?? 0), 0, ',', '.') }}" 
                        data-money-target="admin-package-price"
                        required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Billing cycle</label>
                    <select name="billing_cycle" class="form-select">
                        @foreach (['one_time' => 'One time', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $value => $label)
                            <option value="{{ $value }}" {{ old('billing_cycle', $package->billing_cycle ?? 'one_time') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Course limit {!! isset($globalFeatureStatus['course_limit']) && !$globalFeatureStatus['course_limit'] ? '<span class="badge text-bg-warning ms-1" style="font-size: 10px;">BẢO TRÌ</span>' : '' !!}</label>
                    <input type="number" class="form-control" name="course_limit" min="1"
                        value="{{ old('course_limit', $package->course_limit ?? '') }}" data-preview-global="course_limit"
                        {{ isset($globalFeatureStatus['course_limit']) && !$globalFeatureStatus['course_limit'] ? 'disabled' : '' }}>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payout account limit</label>
                    <input type="number" class="form-control" name="payout_account_limit" min="1" max="3"
                        value="{{ old('payout_account_limit', isset($package) ? $package->effective_payout_account_limit : 3) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hạn mức rút tối đa / ngày (VND)</label>
                    <input type="hidden" name="max_payout_per_day" id="admin-package-payout-limit" value="{{ old('max_payout_per_day', $package->max_payout_per_day ?? '') }}">
                    <input type="text" class="form-control" id="admin-package-payout-limit-display"
                        value="{{ isset($package) && $package->max_payout_per_day ? number_format($package->max_payout_per_day, 0, ',', '.') : '' }}" 
                        data-money-target="admin-package-payout-limit"
                        placeholder="Để trống = Không giới hạn">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Commission rate</label>
                    <input type="number" class="form-control" name="commission_rate" min="0" max="100" step="0.01"
                        value="{{ old('commission_rate', $package->commission_rate ?? 50) }}" required data-preview-global="commission_rate">
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <div class="form-check mt-md-4">
                        <input class="form-check-input" type="checkbox" name="priority_review" value="1"
                            {{ old('priority_review', $package->priority_review ?? false) ? 'checked' : '' }} data-preview-global="priority_review"
                            {{ isset($globalFeatureStatus['priority_review']) && !$globalFeatureStatus['priority_review'] ? 'disabled' : '' }}>
                        <label class="form-check-label">Priority review {!! isset($globalFeatureStatus['priority_review']) && !$globalFeatureStatus['priority_review'] ? '<small class="text-warning ms-1">(Bảo trì)</small>' : '' !!}</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Coupon limit {!! isset($globalFeatureStatus['can_manage_coupons']) && !$globalFeatureStatus['can_manage_coupons'] ? '<small class="text-warning ms-1">(Maint)</small>' : '' !!}</label>
                    <input type="number" class="form-control" name="coupon_limit" min="1"
                        value="{{ old('coupon_limit', $package->coupon_limit ?? '') }}"
                        placeholder="Để trống = không giới hạn"
                        {{ isset($globalFeatureStatus['can_manage_coupons']) && !$globalFeatureStatus['can_manage_coupons'] ? 'disabled' : '' }}>
                    <div class="form-text">Chỉ áp dụng khi đã bật quyền quản lý coupon.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">AI Quiz Limit / ngày {!! isset($globalFeatureStatus['can_use_ai_quiz']) && !$globalFeatureStatus['can_use_ai_quiz'] ? '<small class="text-warning ms-1">(Maint)</small>' : '' !!}</label>
                    <input type="number" class="form-control" name="ai_quiz_limit" min="1"
                        value="{{ old('ai_quiz_limit', $package->ai_quiz_limit ?? '') }}"
                        placeholder="Để trống = 3 (Miễn phí) / Không giới hạn"
                        {{ isset($globalFeatureStatus['can_use_ai_quiz']) && !$globalFeatureStatus['can_use_ai_quiz'] ? 'disabled' : '' }}>
                    <div class="form-text">Số Quiz tối đa tạo bằng AI 1 ngày. Cần bật quyền "Quản lý quiz".</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                <div>
                    <h6 class="mb-1">Tính năng đi kèm theo gói</h6>
                    <p class="text-muted mb-0">Bật/tắt từng quyền trong portal để phân hóa quyền lợi của các gói.</p>
                </div>
            </div>

            <div class="row g-4 mt-1">
                @php
                    $systemFeaturesGrouped = [
                        'Quản lý Khóa học & Nội dung' => [
                            'can_duplicate_courses' => 'Cho phép nhân bản khóa học',
                            'can_manage_quizzes' => 'Cho phép quản lý quiz',
                            'can_use_ai_quiz' => 'Cho phép dùng AI tạo Quiz (Gemini)',
                            'can_schedule_content' => 'Cho phép mở bài học theo lịch',
                            'can_import_export_lessons' => 'Cho phép Import/Export bài học',
                        ],
                        'Quản lý Học viên & Tương tác' => [
                            'can_manage_students' => 'Cho phép quản lý học viên',
                            'can_view_student_progress' => 'Cho phép xem % tiến độ học của học viên',
                            'can_manage_comments' => 'Cho phép quản lý bình luận',
                            'can_issue_certificates' => 'Cho phép cấp chứng chỉ hoàn thành',
                            'can_verify_certificates' => 'Xác thực chứng chỉ công khai (QR Code)',
                            'can_grant_courses' => 'Cho phép cấp quyền học thủ công',
                        ],
                        'Marketing & Bán hàng' => [
                            'can_manage_coupons' => 'Cho phép quản lý mã giảm giá',
                            'can_sell_bundles' => 'Cho phép bán combo / bundle khóa học',
                            'can_send_promotions' => 'Cho phép gửi thông báo khuyến mại',
                            'can_use_affiliate_links' => 'Cho phép dùng link giới thiệu riêng',
                            'can_customize_teacher_landing' => 'Cho phép tùy chỉnh landing page giảng viên',
                        ],
                        'Tài chính & Hệ thống' => [
                            'can_request_payouts' => 'Cho phép gửi yêu cầu rút tiền',
                            'can_view_activity_logs' => 'Cho phép xem nhật ký hoạt động giảng viên',
                            'can_import_export' => 'Cho phép Import/Export dữ liệu (Đơn hàng, Học viên, Quiz)',
                            'can_export_orders' => 'Cho phép xuất dữ liệu đơn hàng (CSV/Excel)',
                            'can_export_students' => 'Cho phép xuất danh sách học viên (CSV/Excel)',
                        ]
                    ];
                @endphp

                @foreach($systemFeaturesGrouped as $groupName => $features)
                    <div class="col-md-6">
                        <div class="border rounded-4 p-4 bg-light-subtle h-100 shadow-sm">
                            <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">
                                <i class="fa-solid fa-folder-open me-2"></i>{{ $groupName }}
                            </h6>
                            <div class="row g-2">
                                @foreach($features as $key => $label)
                                    @php
                                        $status = isset($globalFeatureStatus[$key]) ? (int) $globalFeatureStatus[$key] : 1;
                                        $isMaintenance = $status !== 1;
                                        $isChecked = (bool) old($key, $package->{$key} ?? false);
                                    @endphp
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input feature-checkbox" type="checkbox" name="{{ $key }}" value="1"
                                                {{ $isChecked ? 'checked' : '' }}
                                                {{ $isMaintenance ? 'disabled' : '' }}>
                                            
                                            @if($isMaintenance && $isChecked)
                                                <input type="hidden" name="{{ $key }}" value="1">
                                            @endif

                                            <label class="form-check-label text-dark" style="font-size: 0.9rem;">
                                                {{ $label }}
                                                @if($isMaintenance)
                                                    <small class="text-warning ms-1 fw-bold text-uppercase" style="font-size: 0.72rem;">(Bảo trì)</small>
                                                @endif
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

</div>

<style>
    .package-preview-card {
        border-radius: 24px;
        padding: 1.35rem;
        color: #e5eefc;
        background: 
            radial-gradient(circle at top right, rgba(var(--package-tone-rgb, 96, 165, 250), 0.14), transparent 28%),
            linear-gradient(180deg, #111827 0%, #16233a 100%);
        border: 1px solid var(--package-tone, rgba(96, 165, 250, 0.18));
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }

    .package-preview-card.is-featured {
        background:
            radial-gradient(circle at top right, rgba(45, 212, 191, 0.16), transparent 28%),
            linear-gradient(180deg, #0f2748 0%, #143961 100%);
        border-color: rgba(125, 211, 252, 0.34);
    }

    .package-preview-card__code,
    .package-preview-card__badge {
        display: inline-flex;
        align-items: center;
        padding: 0.45rem 0.7rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 800;
    }

    .package-preview-card__code {
        color: #8fc3ff;
        background: rgba(96, 165, 250, 0.14);
    }

    .package-preview-card__badge {
        color: #7cead9;
        background: rgba(45, 212, 191, 0.14);
    }

    .package-preview-card__name {
        font-size: 1.55rem;
        font-weight: 800;
        margin-bottom: 0.6rem;
    }

    .package-preview-card__tagline,
    .package-preview-card__description {
        color: #b7c7df;
        line-height: 1.7;
    }

    .package-preview-card__price {
        font-size: 2rem;
        font-weight: 900;
        margin: 0.7rem 0 1rem;
        color: #ffffff;
    }

    .package-preview-card__features {
        padding-left: 1.1rem;
        margin-bottom: 0;
    }

    .package-preview-card__features li + li {
        margin-top: 0.35rem;
    }

    .package-preview-card__cta {
        margin-top: 1.2rem;
        border-radius: 14px;
        padding: 0.8rem 1rem;
        text-align: center;
        font-weight: 800;
        background: var(--package-tone, linear-gradient(180deg, #ffffff 0%, #dbeafe 100%));
        @if($package && $package->badge_tone)
            color: #fff;
        @else
            color: #12325b;
            background: linear-gradient(180deg, #ffffff 0%, #dbeafe 100%);
        @endif
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
            const toneHexInput = document.getElementById('badge_tone_hex');
            const toneValue = toneInput?.value || '#2563eb';
            
            const code = document.querySelector('input[name="code"]')?.value.trim() || 'PKG';
            const price = document.querySelector('[data-preview-global="price"]')?.value ?? 0;
            const courseLimit = document.querySelector('[data-preview-global="course_limit"]')?.value.trim() ?? '';
            const commission = document.querySelector('[data-preview-global="commission_rate"]')?.value ?? 0;
            const priority = document.querySelector('[data-preview-global="priority_review"]')?.checked ?? false;
            const isFeatured = document.querySelector('input[name="is_featured"]')?.checked ?? false;
            const support = getLocaleValue('support', activeLocale);
            const name = getLocaleValue('name', activeLocale) || 'Ten goi';
            const tagline = getLocaleValue('tagline', activeLocale);
            const badge = getLocaleValue('badge', activeLocale);
            const description = getLocaleValue('description', activeLocale);

            document.querySelector('[data-preview-code]').textContent = code.toUpperCase();
            document.querySelector('[data-preview-name]').textContent = name;
            document.querySelector('[data-preview-price]').textContent = formatPrice(price);
            document.querySelector('[data-preview-commission]').textContent = `Chia doanh thu ${Number(commission || 0)}%`;
            document.querySelector('[data-preview-course-limit]').textContent = courseLimit !== ''
                ? `Đăng tối đa ${courseLimit} khóa`
                : 'Đăng khóa học không giới hạn';
            document.querySelector('[data-preview-priority]').textContent = priority
                ? 'Ưu tiên duyệt hồ sơ'
                : 'Duyệt theo hàng chờ tiêu chuẩn';
            document.querySelector('[data-preview-support]').textContent = support !== ''
                ? `Hỗ trợ ${support}`
                : 'Hỗ trợ cơ bản';

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
                previewCard.style.setProperty('--package-tone-rgb', hexToRgb(toneValue));
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
                
                // Keep only numeric characters
                let rawValue = e.target.value.replace(/\D/g, '');
                
                if (rawValue === '') {
                    hiddenInput.value = '';
                    displayInput.value = '';
                    updatePreview();
                    return;
                }

                hiddenInput.value = rawValue;
                
                // Format with dots
                let formattedValue = new Intl.NumberFormat('vi-VN').format(rawValue);
                displayInput.value = formattedValue;

                // Restore cursor position
                let newLength = formattedValue.length;
                let lengthDiff = newLength - originalLength;
                displayInput.setSelectionRange(cursorPosition + lengthDiff, cursorPosition + lengthDiff);

                updatePreview();
            });

            // Clean input on blur just in case
            displayInput.addEventListener('blur', () => {
                if (displayInput.value.trim() === '') {
                    hiddenInput.value = '';
                }
            });
        });

        document.querySelectorAll('[data-preview-field], [data-preview-global], input[name="code"], input[name="is_featured"]').forEach((element) => {
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