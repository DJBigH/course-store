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
    $package = $package ?? null;
    $locales = [
        'vi' => ['label' => 'VI', 'name' => 'Tiếng Việt'],
        'en' => ['label' => 'EN', 'name' => 'English'],
        'ko' => ['label' => 'KO', 'name' => 'Korean'],
        'ja' => ['label' => 'JA', 'name' => 'Japanese'],
        'zh' => ['label' => 'ZH', 'name' => 'Chinese'],
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
@endphp

<div class="row g-4">
    <div class="col-12">
        <div class="border rounded-4 p-4 bg-light-subtle">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                <div>
                    <h6 class="mb-1">Thong tin he thong</h6>
                    <p class="text-muted mb-0">Code, trang thai va quy tac sap xep cua goi.</p>
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
                        <label class="form-check-label">Hien cong khai cho nguoi mua</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                            {{ old('is_featured', $package->is_featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Featured card tren landing</label>
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Khi da tat cong khai</label>
                    <select name="hidden_mode" class="form-select">
                        <option value="available" {{ $defaultHiddenMode === 'available' ? 'selected' : '' }}>
                            An di nhung nguoi dang dung goi nay van tiep tuc su dung duoc
                        </option>
                        <option value="unavailable" {{ $defaultHiddenMode === 'unavailable' ? 'selected' : '' }}>
                            An di va khong cho nguoi mua tiep tuc chon / su dung nua
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                <div>
                    <h6 class="mb-1">Noi dung da ngon ngu</h6>
                    <p class="text-muted mb-0">Ten goi, tagline, badge, mo ta va muc ho tro hien thi theo locale.</p>
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
                                                {{ $isDefault ? 'Day la noi dung mac dinh va duoc dung lam fallback cho cac locale khac.' : 'Neu de trong, he thong se fallback ve ban mac dinh.' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Ten goi</label>
                                            <input type="text" class="form-control" name="{{ $nameField }}"
                                                value="{{ old($nameField, $package->{$nameField} ?? '') }}" {{ $isDefault ? 'required' : '' }}
                                                data-preview-field="name" data-preview-locale="{{ $locale }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Muc ho tro</label>
                                            <input type="text" class="form-control" name="{{ $supportField }}"
                                                value="{{ old($supportField, $package->{$supportField} ?? '') }}"
                                                data-preview-field="support" data-preview-locale="{{ $locale }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tagline ngan</label>
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
                                            <label class="form-label">Mo ta chi tiet</label>
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
                                <p class="text-muted mb-0 small">Xem nhanh giao dien package theo tab dang mo.</p>
                            </div>
                            <span class="badge text-bg-dark" data-preview-locale-badge>VI</span>
                        </div>

                        <div class="package-preview-card {{ $defaultPreviewFeatured ? 'is-featured' : '' }}" data-package-preview-card>
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <span class="package-preview-card__code" data-preview-code>{{ $defaultPreviewCode }}</span>
                                <span class="package-preview-card__badge {{ $defaultPreviewBadge ? '' : 'd-none' }}" data-preview-badge>{{ $defaultPreviewBadge }}</span>
                            </div>

                            <h5 class="package-preview-card__name" data-preview-name>{{ $defaultPreviewName ?: 'Ten goi' }}</h5>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-4 bg-light-subtle">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                <div>
                    <h6 class="mb-1">Gia va quyen loi</h6>
                    <p class="text-muted mb-0">Phan du lieu van hanh dung chung cho moi locale.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Gia</label>
                    <input type="number" class="form-control" name="price" min="0" step="0.01"
                        value="{{ old('price', $package->price ?? 0) }}" required data-preview-global="price">
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
                    <label class="form-label">Course limit</label>
                    <input type="number" class="form-control" name="course_limit" min="1"
                        value="{{ old('course_limit', $package->course_limit ?? '') }}" data-preview-global="course_limit">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payout account limit</label>
                    <input type="number" class="form-control" name="payout_account_limit" min="1" max="3"
                        value="{{ old('payout_account_limit', $package->effective_payout_account_limit ?? 3) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Commission rate</label>
                    <input type="number" class="form-control" name="commission_rate" min="0" max="100" step="0.01"
                        value="{{ old('commission_rate', $package->commission_rate ?? 50) }}" required data-preview-global="commission_rate">
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <div class="form-check mt-md-4">
                        <input class="form-check-input" type="checkbox" name="priority_review" value="1"
                            {{ old('priority_review', $package->priority_review ?? false) ? 'checked' : '' }} data-preview-global="priority_review">
                        <label class="form-check-label">Priority review</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Coupon limit</label>
                    <input type="number" class="form-control" name="coupon_limit" min="1"
                        value="{{ old('coupon_limit', $package->coupon_limit ?? '') }}"
                        placeholder="De trong = khong gioi han">
                    <div class="form-text">Chi ap dung khi da bat quyen quan ly coupon.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="border rounded-4 p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                <div>
                    <h6 class="mb-1">Feature flags theo goi</h6>
                    <p class="text-muted mb-0">Bat/tat tung quyen trong teacher portal de phan hoa ro giua free, starter va pro.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_duplicate_courses" value="1"
                            {{ old('can_duplicate_courses', $package->can_duplicate_courses ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep nhan ban khoa hoc</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_manage_comments" value="1"
                            {{ old('can_manage_comments', $package->can_manage_comments ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep quan ly binh luan</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_manage_coupons" value="1"
                            {{ old('can_manage_coupons', $package->can_manage_coupons ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep quan ly coupon</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_manage_students" value="1"
                            {{ old('can_manage_students', $package->can_manage_students ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep quan ly hoc vien</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_view_student_progress" value="1"
                            {{ old('can_view_student_progress', $package->can_view_student_progress ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep xem % tien do hoc cua hoc vien</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_view_activity_logs" value="1"
                            {{ old('can_view_activity_logs', $package->can_view_activity_logs ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep xem nhat ky hoat dong giang vien</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_grant_courses" value="1"
                            {{ old('can_grant_courses', $package->can_grant_courses ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep cap quyen hoc thu cong</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_export_orders" value="1"
                            {{ old('can_export_orders', $package->can_export_orders ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep export don hang</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_export_students" value="1"
                            {{ old('can_export_students', $package->can_export_students ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep export hoc vien</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_sell_bundles" value="1"
                            {{ old('can_sell_bundles', $package->can_sell_bundles ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep ban combo / bundle khoa hoc</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_schedule_content" value="1"
                            {{ old('can_schedule_content', $package->can_schedule_content ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep mo bai hoc theo lich</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_send_promotions" value="1"
                            {{ old('can_send_promotions', $package->can_send_promotions ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep gui thong bao khuyen mai</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_issue_certificates" value="1"
                            {{ old('can_issue_certificates', $package->can_issue_certificates ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep cap chung chi hoan thanh</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_customize_teacher_landing" value="1"
                            {{ old('can_customize_teacher_landing', $package->can_customize_teacher_landing ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep tuy chinh landing page giang vien</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_use_affiliate_links" value="1"
                            {{ old('can_use_affiliate_links', $package->can_use_affiliate_links ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Cho phep dung link gioi thieu rieng</label>
                    </div>
                </div>
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
            radial-gradient(circle at top right, rgba(96, 165, 250, 0.14), transparent 28%),
            linear-gradient(180deg, #111827 0%, #16233a 100%);
        border: 1px solid rgba(96, 165, 250, 0.18);
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
        color: #12325b;
        background: linear-gradient(180deg, #ffffff 0%, #dbeafe 100%);
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

        const updatePreview = () => {
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

            document.querySelector('[data-package-preview-card]')?.classList.toggle('is-featured', isFeatured);
            document.querySelector('[data-preview-locale-badge]').textContent = localeMeta[activeLocale]?.label || activeLocale.toUpperCase();
        };

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
