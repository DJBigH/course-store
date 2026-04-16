@extends('layouts.teacher')

@php
    $templateOptions = $promotionTemplates ?? [];
    $recipientModeValue = old('recipient_mode', $recipientMode ?? 'all');
    $selectedStudentIdValues = collect(old('student_ids', $selectedStudentIds ?? []))
        ->map(fn($id) => (int) $id)
        ->all();
    $selectedTemplate = old('promotion_template', 'custom');
    $activeTemplate =
        $templateOptions[$selectedTemplate] ??
        ($templateOptions['custom'] ?? [
            'label' => 'Tự viết',
            'title' => '',
            'html' => '',
            'cta_enabled' => false,
            'cta_label' => '',
        ]);

    $composeTitle = old('title', $activeTemplate['title'] ?? '');
    $composeHtml = old('message_html', $activeTemplate['html'] ?? '');
    $composeCtaEnabled = old('cta_enabled');
    if ($composeCtaEnabled === null) {
        $composeCtaEnabled = !empty($activeTemplate['cta_enabled']);
    }

    $composeCtaLabel = old('cta_label', $activeTemplate['cta_label'] ?? '');
    $composeCtaUrl = old('cta_url', $teacherPublicUrl ?? '');
    $previewSubject = trim((string) $composeTitle) !== '' ? $composeTitle : 'Thông báo từ giảng viên';
    $previewHeading = $previewSubject;
    $previewSubtitle = trim((string) ($teacher?->name_locale ?: $teacher?->name ?: 'Giảng viên'));
    $previewHtml = trim((string) $composeHtml) !== '' ? $composeHtml : '<p>Nội dung email sẽ hiển thị ở đây.</p>';
@endphp

@section('content')
    <div class="teacher-panel teacher-promotions-shell">
        <div class="teacher-promotions-hero">
            <div>
                <span class="teacher-promotions-kicker">{{ __('teacher::dashboard.promotions.hero_kicker') }}</span>
                <h3 class="teacher-promotions-title">{{ __('teacher::dashboard.promotions.title') }}</h3>
                <p class="teacher-promotions-desc mb-0">{{ __('teacher::dashboard.promotions.description') }}</p>
            </div>

            <div class="teacher-promotions-stat">
                <strong data-promo-live-count>{{ $recipientPreviewCount }}</strong>
                <span>{{ __('teacher::dashboard.promotions.recipient_preview') }}</span>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success border-0">{{ session('msg_success') }}</div>
        @endif

        @if (session('msg_danger'))
            <div class="alert alert-danger border-0">{{ session('msg_danger') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0">{{ __('teacher::dashboard.common.validation_summary') }}</div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-xl-6">
                <div class="teacher-promotions-card">
                    <div class="teacher-promotions-card__head">
                        <div>
                            <h4>{{ __('teacher::dashboard.promotions.form.title') }}</h4>
                            <p class="mb-0">Soạn một email khuyến mãi dùng giao diện chung của hệ thống, gửi notification
                                và queue email thật cùng lúc.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('teacher.dashboard.promotions.store') }}"
                        class="teacher-promotions-form">
                        @csrf

                        <div class="teacher-promotions-recipient-card">
                            <div class="teacher-promotions-section-head">
                                <div>
                                    <h5>Người nhận</h5>
                                    <p class="mb-0">Chọn gửi cho tất cả học viên, lọc theo điều kiện, hoặc tự tích chọn
                                        từng học viên.</p>
                                </div>
                                <span class="teacher-promotions-recipient-count"><span
                                        data-promo-live-count>{{ $recipientPreviewCount }}</span> người</span>
                            </div>

                            <div class="teacher-promotions-mode-grid">
                                <label
                                    class="teacher-promotions-mode {{ $recipientModeValue === 'all' ? 'is-active' : '' }}">
                                    <input type="radio" name="recipient_mode" value="all" @checked($recipientModeValue === 'all')
                                        data-promo-recipient-mode>
                                    <span>Tất cả học viên</span>
                                    <small>Gửi tới toàn bộ học viên đang thuộc kênh của bạn.</small>
                                </label>
                                <label
                                    class="teacher-promotions-mode {{ $recipientModeValue === 'filtered' ? 'is-active' : '' }}">
                                    <input type="radio" name="recipient_mode" value="filtered"
                                        @checked($recipientModeValue === 'filtered') data-promo-recipient-mode>
                                    <span>Lọc theo điều kiện</span>
                                    <small>Lọc theo khóa học, người mới mua hoặc học viên đã lâu chưa học.</small>
                                </label>
                                <label
                                    class="teacher-promotions-mode {{ $recipientModeValue === 'manual' ? 'is-active' : '' }}">
                                    <input type="radio" name="recipient_mode" value="manual" @checked($recipientModeValue === 'manual')
                                        data-promo-recipient-mode>
                                    <span>Chọn học viên riêng</span>
                                    <small>Tìm kiếm rồi tích chọn đúng những học viên bạn muốn gửi.</small>
                                </label>
                            </div>

                            <div class="teacher-promotions-manual" data-promo-manual-panel
                                @if ($recipientModeValue !== 'manual') hidden @endif>
                                <div class="teacher-promotions-manual__top">
                                    <input type="text" class="form-control"
                                        placeholder="Tìm theo tên, email hoặc số điện thoại" data-promo-student-search>
                                    <div class="teacher-promotions-manual__actions">
                                        <button type="button" class="btn btn-sm btn-outline-light"
                                            data-promo-select-all>Chọn tất cả</button>
                                        <button type="button" class="btn btn-sm btn-outline-light" data-promo-clear-all>Bỏ
                                            chọn tất cả</button>
                                    </div>
                                    <div class="teacher-promotions-manual__meta">
                                        <span data-promo-selected-count>{{ count($selectedStudentIdValues) }}</span> đã
                                        chọn
                                    </div>
                                </div>

                                <div class="teacher-promotions-student-list">
                                    @forelse ($availablePromotionStudents as $promotionStudent)
                                        <label class="teacher-promotions-student" data-promo-student-item
                                            data-search="{{ \Illuminate\Support\Str::lower(trim($promotionStudent->name . ' ' . $promotionStudent->email . ' ' . $promotionStudent->phone)) }}">
                                            <input type="checkbox" name="student_ids[]" value="{{ $promotionStudent->id }}"
                                                @checked(in_array($promotionStudent->id, $selectedStudentIdValues, true)) data-promo-student-checkbox>
                                            <div class="teacher-promotions-student__body">
                                                <strong>
                                                    {{ $promotionStudent->name }}
                                                    <span class="teacher-promotions-student__badges">
                                                        @if ($promotionStudent->has_paid_order)
                                                            <span
                                                                class="teacher-promotions-student__badge teacher-promotions-student__badge--paid">Đã
                                                                mua</span>
                                                        @endif
                                                        @if ($promotionStudent->has_grant)
                                                            <span
                                                                class="teacher-promotions-student__badge teacher-promotions-student__badge--grant">Được
                                                                tặng</span>
                                                        @endif
                                                    </span>
                                                </strong>
                                                <span>{{ $promotionStudent->email ?: 'Chưa có email' }}</span>
                                                @if ($promotionStudent->phone)
                                                    <small>{{ $promotionStudent->phone }}</small>
                                                @endif
                                            </div>
                                        </label>
                                    @empty
                                        <div class="teacher-promotions-manual-empty">Hiện chưa có học viên nào để chọn.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="teacher-promotions-filter-grid" data-promo-filter-panel
                                @if ($recipientModeValue !== 'filtered') hidden @endif>
                                <div class="teacher-promotions-grid teacher-promotions-grid--filters">
                                    <div>
                                        <label
                                            class="form-label">{{ __('teacher::dashboard.promotions.form.course') }}</label>
                                        <select name="course_id" class="form-select" data-promo-input="course">
                                            <option value="" data-course-url="{{ $teacherPublicUrl }}">
                                                {{ __('teacher::dashboard.promotions.form.all_students') }}
                                            </option>
                                            @foreach ($courseOptions as $courseOption)
                                                @php
                                                    $courseSlug = $courseOption->slug_locale ?: $courseOption->slug;
                                                    $courseUrl = $courseSlug
                                                        ? route('courses.detail', [
                                                            'locale' => app()->getLocale(),
                                                            'slug' => $courseSlug,
                                                        ])
                                                        : $teacherPublicUrl;
                                                @endphp
                                                <option value="{{ $courseOption->id }}"
                                                    data-course-url="{{ $courseUrl }}" @selected((int) old('course_id', $selectedCourseId) === (int) $courseOption->id)>
                                                    {{ $courseOption->name_locale ?: $courseOption->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">{{ __('teacher::dashboard.promotions.form.course_help') }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="form-label">{{ __('teacher::dashboard.promotions.form.recent_purchase_days') }}</label>
                                        <input type="number" min="1" max="3650" name="recent_purchase_days"
                                            class="form-control"
                                            value="{{ old('recent_purchase_days', $recentPurchaseDays > 0 ? $recentPurchaseDays : '') }}">
                                        <div class="form-text">
                                            {{ __('teacher::dashboard.promotions.form.recent_purchase_days_help') }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="form-label">{{ __('teacher::dashboard.promotions.form.inactive_learning_days') }}</label>
                                        <input type="number" min="1" max="3650" name="inactive_learning_days"
                                            class="form-control"
                                            value="{{ old('inactive_learning_days', $inactiveLearningDays > 0 ? $inactiveLearningDays : '') }}">
                                        <div class="form-text">
                                            {{ __('teacher::dashboard.promotions.form.inactive_learning_days_help') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="teacher-promotions-grid">
                            <div>
                                <label class="form-label">Template email</label>
                                <select name="promotion_template" class="form-select" data-promo-input="template">
                                    @foreach ($templateOptions as $templateKey => $template)
                                        <option value="{{ $templateKey }}" @selected($selectedTemplate === $templateKey)>
                                            {{ $template['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Chọn sẵn flash sale, tái kích hoạt học viên, ra khóa mới hoặc tự viết
                                    từ đầu.</div>
                            </div>

                            <div class="teacher-promotions-grid-note">
                                <label class="form-label">Kiểu nội dung</label>
                                <div class="teacher-promotions-grid-note__box">
                                    Chọn template nhanh ở đây, còn điều kiện người nhận nằm phía trên để teacher thao tác
                                    không bị rối.
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">{{ __('teacher::dashboard.promotions.form.title_label') }}</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                maxlength="160" value="{{ $composeTitle }}" required data-promo-input="title">
                            <div class="teacher-promotions-subject-preview">
                                <span>Subject preview</span>
                                <strong data-promo-preview="subject">{{ $previewSubject }}</strong>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Nội dung email</label>
                            <textarea id="teacher-promotion-message-html" name="message_html"
                                class="form-control ckeditor @error('message_html') is-invalid @enderror" rows="10">{{ $composeHtml }}</textarea>
                            <div class="form-text">CKEditor sẽ được preview ngay bên phải theo đúng khung email của web, hỗ
                                trợ cả dark mode và light mode.</div>
                        </div>

                        <div class="teacher-promotion-cta-card">
                            <div class="teacher-promotion-cta-card__top">
                                <div>
                                    <h5>Nút CTA trong email</h5>
                                    <p class="mb-0">Nếu bật, hệ thống sẽ đặt nút hành động vào email thật và preview.</p>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="promotion-cta-enabled" name="cta_enabled" value="1"
                                        @checked($composeCtaEnabled)>
                                    <label class="form-check-label" for="promotion-cta-enabled">Bật nút</label>
                                </div>
                            </div>

                            <div class="row g-3" data-promo-cta-fields
                                @if (!$composeCtaEnabled) style="display:none;" @endif>
                                <div class="col-md-5">
                                    <label class="form-label">Tên nút</label>
                                    <input type="text" name="cta_label" class="form-control" maxlength="80"
                                        value="{{ $composeCtaLabel }}" placeholder="Xem chi tiết"
                                        data-promo-input="cta_label">
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label">Link nút</label>
                                    <input type="url" name="cta_url" class="form-control" maxlength="1000"
                                        value="{{ $composeCtaUrl }}" placeholder="https://..."
                                        data-promo-input="cta_url">
                                </div>
                            </div>
                        </div>

                        <div class="teacher-promotions-channel-card mb-4" style="padding: 1rem; border-radius: 18px; background: rgba(12, 19, 34, 0.72); border: 1px solid rgba(96, 165, 250, 0.12);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <div>
                                    <h5 style="margin-bottom: 0.3rem; color: #f8fbff;">Kênh gửi thông báo</h5>
                                    <p class="mb-0" style="color: #94a3b8; font-size:0.9rem;">Chọn kênh bạn muốn truyền tải thông điệp này đến Học viên.</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-4 mt-3">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="send_via_web" id="send_via_web" value="1" checked required onchange="validateChannels()">
                                    <label class="form-check-label text-light fw-semibold" for="send_via_web">Gửi Web Notification</label>
                                </div>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="send_via_email" id="send_via_email" value="1" checked onchange="validateChannels()">
                                    <label class="form-check-label text-light fw-semibold" for="send_via_email">Gửi Email trực tiếp</label>
                                </div>
                            </div>
                            <script>
                                function validateChannels() {
                                    const web = document.getElementById('send_via_web');
                                    const email = document.getElementById('send_via_email');
                                    if(!web.checked && !email.checked) {
                                        web.setCustomValidity('Bạn phải chọn ít nhất 1 kênh nhận thông báo');
                                    } else {
                                        web.setCustomValidity('');
                                    }
                                }
                            </script>
                        </div>

                        <div class="teacher-promotions-form__footer">
                            <div class="teacher-promotions-form__hint">
                                Sẽ gửi tới khoảng <strong data-promo-live-count>{{ $recipientPreviewCount }}</strong> người
                                theo lựa chọn hiện tại.
                            </div>
                            <div class="teacher-promotions-actions">
                                <button type="submit" class="btn btn-outline-light"
                                    formaction="{{ route('teacher.dashboard.promotions.test') }}">
                                    Gửi thử cho tôi
                                </button>
                                <button type="submit"
                                    class="btn btn-primary">{{ __('teacher::dashboard.promotions.form.submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="teacher-promotions-card teacher-promotions-card--preview">
                    <div class="teacher-promotions-card__head">
                        <div>
                            <h4>Email preview</h4>
                            <p class="mb-0">Subject, body và CTA ở đây sẽ giống luồng email thật được queue cho học viên.
                            </p>
                        </div>
                    </div>

                    <div class="teacher-preview-meta">
                        <div class="teacher-preview-meta__item">
                            <span>Subject</span>
                            <strong data-promo-preview="subject_line">{{ $previewSubject }}</strong>
                        </div>
                        <div class="teacher-preview-meta__item">
                            <span>Template</span>
                            <strong
                                data-promo-preview="template_label">{{ $activeTemplate['label'] ?? 'Tự viết' }}</strong>
                        </div>
                    </div>

                    <div class="teacher-mail-preview">
                        <div class="teacher-mail-preview__frame">
                            <div class="teacher-mail-preview__header">
                                <div class="teacher-mail-preview__eyebrow">Khuyến mãi từ giảng viên</div>
                                <h5 data-promo-preview="title">{{ $previewHeading }}</h5>
                                <p data-promo-preview="subtitle">{{ $previewSubtitle }}</p>
                            </div>

                            <div class="teacher-mail-preview__body">
                                <div class="teacher-mail-preview__content" data-promo-preview="content">
                                    {!! $previewHtml !!}
                                </div>

                                <div class="teacher-mail-preview__cta" data-promo-preview="cta_wrap"
                                    @if (!$composeCtaEnabled) hidden @endif>
                                    <a href="{{ $composeCtaUrl ?: '#' }}"
                                        data-promo-preview="cta_link">{{ $composeCtaLabel ?: 'Xem chi tiết' }}</a>
                                </div>
                            </div>

                            <div class="teacher-mail-preview__footer">
                                <span>{{ setting('site_name', 'BigK Udemy') }}</span>
                                <span>Email tự động từ hệ thống</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="teacher-promotions-card mt-4">
                    <div class="teacher-promotions-card__head">
                        <div>
                            <h4>{{ __('teacher::dashboard.promotions.history_title') }}</h4>
                            <p class="mb-0">{{ __('teacher::dashboard.promotions.history_description') }}</p>
                        </div>
                    </div>

                    <div class="teacher-promotions-history">
                        @forelse ($promotions as $promotion)
                            @php
                                $promotionTemplate = trim((string) ($promotion->filters['template'] ?? 'custom'));
                                $promotionTemplateLabel = $templateOptions[$promotionTemplate]['label'] ?? 'Tự viết';
                                $promotionHtml = $promotion->filters['message_html'] ?? null;
                                $promotionCtaEnabled = !empty($promotion->filters['cta_enabled']);
                                $promotionCtaLabel = $promotion->filters['cta_label'] ?? null;
                                $promotionCtaUrl = $promotion->filters['cta_url'] ?? null;
                            @endphp
                            <div class="teacher-promotions-item">
                                <div class="teacher-promotions-item__meta">
                                    <span class="teacher-promotions-pill">
                                        {{ $promotion->course ? (localizedModelField($promotion->course, 'name', app()->getLocale()) ?: __('teacher::dashboard.common.unknown_course')) : __('teacher::dashboard.promotions.history_all_students') }}
                                    </span>
                                    <span
                                        class="teacher-promotions-time">{{ optional($promotion->created_at)->diffForHumans() }}</span>
                                </div>
                                <div class="teacher-promotions-item__subject">
                                    <span>Subject</span>
                                    <strong>{{ $promotion->title }}</strong>
                                </div>
                                <div class="teacher-promotions-item__filters">
                                    <span
                                        class="teacher-promotions-pill teacher-promotions-pill--soft">{{ $promotionTemplateLabel }}</span>
                                    <span class="teacher-promotions-pill teacher-promotions-pill--soft">Queue email</span>
                                    <span class="teacher-promotions-pill teacher-promotions-pill--soft">Thông báo</span>
                                    @if (!empty($promotion->filters['recent_purchase_days']))
                                        <span class="teacher-promotions-pill teacher-promotions-pill--soft">
                                            {{ __('teacher::dashboard.promotions.history_recent_purchase_days', ['days' => $promotion->filters['recent_purchase_days']]) }}
                                        </span>
                                    @endif
                                    @if (!empty($promotion->filters['inactive_learning_days']))
                                        <span class="teacher-promotions-pill teacher-promotions-pill--soft">
                                            {{ __('teacher::dashboard.promotions.history_inactive_learning_days', ['days' => $promotion->filters['inactive_learning_days']]) }}
                                        </span>
                                    @endif
                                </div>

                                @if ($promotionHtml)
                                    <div class="teacher-promotions-item__html">{!! $promotionHtml !!}</div>
                                @else
                                    <p>{{ $promotion->message }}</p>
                                @endif

                                @if ($promotionCtaEnabled && $promotionCtaLabel && $promotionCtaUrl)
                                    <div class="teacher-promotions-item__cta">
                                        <a href="{{ $promotionCtaUrl }}" target="_blank"
                                            rel="noopener noreferrer">{{ $promotionCtaLabel }}</a>
                                    </div>
                                @endif

                                <div class="teacher-promotions-item__footer">
                                    <strong>{{ __('teacher::dashboard.promotions.history_recipients', ['count' => $promotion->recipient_count]) }}</strong>
                                    <span>{{ optional($promotion->created_at)->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="teacher-promotions-empty">
                                <div class="teacher-promotions-empty__icon"><i class="fas fa-bullhorn"></i></div>
                                <h4>{{ __('teacher::dashboard.promotions.empty') }}</h4>
                                <p class="mb-0">{{ __('teacher::dashboard.promotions.empty_description') }}</p>
                            </div>
                        @endforelse
                    </div>

                    @if ($promotions->hasPages())
                        <div class="mt-4">{{ $promotions->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-promotions-shell {
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 28%),
                radial-gradient(circle at left bottom, rgba(16, 185, 129, 0.1), transparent 26%),
                linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
        }

        .teacher-promotions-hero {
            display: flex;
            justify-content: space-between;
            gap: 1.25rem;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .teacher-promotions-kicker {
            display: inline-flex;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.14);
            color: #8fc3ff;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .teacher-promotions-title {
            margin-top: 1rem;
            margin-bottom: 0.55rem;
            font-size: clamp(2rem, 3vw, 2.7rem);
            font-weight: 900;
            color: #f8fbff;
        }

        .teacher-promotions-desc {
            max-width: 760px;
            color: #a9bbd5;
            line-height: 1.75;
        }

        .teacher-promotions-stat,
        .teacher-promotions-card,
        .teacher-promotions-item,
        .teacher-promotions-empty {
            background: rgba(18, 28, 50, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.16);
            box-shadow: 0 18px 42px rgba(2, 6, 23, 0.18);
        }

        .teacher-promotions-stat {
            min-width: 200px;
            padding: 1.15rem 1.25rem;
            border-radius: 22px;
            text-align: right;
        }

        .teacher-promotions-stat strong {
            display: block;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            line-height: 1;
            color: #f8fbff;
        }

        .teacher-promotions-stat span {
            display: block;
            margin-top: 0.45rem;
            color: #a9bbd5;
        }

        .teacher-promotions-card {
            border-radius: 24px;
            padding: 1.4rem;
        }

        .teacher-promotions-card__head {
            margin-bottom: 1rem;
        }

        .teacher-promotions-card__head h4 {
            margin-bottom: 0.35rem;
            color: #f8fbff;
            font-weight: 800;
        }

        .teacher-promotions-card__head p {
            color: #a9bbd5;
        }

        .teacher-promotions-form {
            display: grid;
            gap: 1rem;
        }

        .teacher-promotions-recipient-card {
            padding: 1rem;
            border-radius: 20px;
            background: rgba(10, 17, 31, 0.58);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .teacher-promotions-section-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .teacher-promotions-section-head h5 {
            margin-bottom: 0.3rem;
            color: #f8fbff;
            font-weight: 800;
        }

        .teacher-promotions-section-head p {
            color: #94a3b8;
        }

        .teacher-promotions-recipient-count {
            display: inline-flex;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.16);
            color: #bfdbfe;
            font-weight: 800;
        }

        .teacher-promotions-mode-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .teacher-promotions-mode {
            display: grid;
            gap: 0.35rem;
            padding: 1rem;
            border-radius: 18px;
            border: 1px solid rgba(96, 165, 250, 0.12);
            background: rgba(15, 23, 42, 0.72);
            cursor: pointer;
        }

        .teacher-promotions-mode.is-active {
            border-color: rgba(59, 130, 246, 0.55);
            box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.28);
        }

        .teacher-promotions-mode input {
            margin-bottom: 0.15rem;
        }

        .teacher-promotions-mode span {
            color: #f8fbff;
            font-weight: 800;
        }

        .teacher-promotions-mode small {
            color: #94a3b8;
            line-height: 1.5;
        }

        .teacher-promotions-filter-grid,
        .teacher-promotions-manual {
            margin-top: 1rem;
        }

        .teacher-promotions-manual__top {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 0.85rem;
        }

        .teacher-promotions-manual__actions {
            display: inline-flex;
            gap: 0.5rem;
            margin-left: auto;
        }

        .teacher-promotions-manual__meta {
            white-space: nowrap;
            color: #bfdbfe;
            font-weight: 700;
        }

        .teacher-promotions-student-list {
            display: grid;
            gap: 0.65rem;
            max-height: 300px;
            overflow: auto;
            padding-right: 0.25rem;
        }

        .teacher-promotions-student {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            padding: 0.85rem 0.95rem;
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.1);
            cursor: pointer;
        }

        .teacher-promotions-student__body {
            display: grid;
            gap: 0.15rem;
        }

        .teacher-promotions-student__body strong {
            display: flex;
            gap: 0.55rem;
            flex-wrap: wrap;
            align-items: center;
            color: #f8fbff;
        }

        .teacher-promotions-student__badges {
            display: inline-flex;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .teacher-promotions-student__badge {
            display: inline-flex;
            align-items: center;
            padding: 0.18rem 0.5rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 800;
            line-height: 1;
        }

        .teacher-promotions-student__badge--paid {
            background: rgba(34, 197, 94, 0.16);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.28);
        }

        .teacher-promotions-student__badge--grant {
            background: rgba(245, 158, 11, 0.16);
            color: #fcd34d;
            border: 1px solid rgba(245, 158, 11, 0.28);
        }

        .teacher-promotions-student__body span,
        .teacher-promotions-student__body small,
        .teacher-promotions-manual-empty,
        .teacher-promotions-grid-note__box {
            color: #94a3b8;
        }

        .teacher-promotions-grid-note__box {
            min-height: 100%;
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: rgba(15, 23, 42, 0.72);
            border: 1px dashed rgba(96, 165, 250, 0.14);
            line-height: 1.7;
        }

        .teacher-promotions-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .teacher-promotions-form .form-label {
            color: #dce9ff;
            font-weight: 700;
        }

        .teacher-promotions-form .form-control,
        .teacher-promotions-form .form-select {
            background: rgba(15, 23, 42, 0.72);
            border-color: rgba(96, 165, 250, 0.18);
            color: #f8fbff;
        }

        .teacher-promotions-form .form-text,
        .teacher-promotions-form__hint {
            color: #94a3b8;
        }

        .teacher-promotions-subject-preview,
        .teacher-preview-meta__item {
            padding: 0.85rem 1rem;
            border-radius: 16px;
            background: rgba(10, 17, 31, 0.7);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .teacher-promotions-subject-preview {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            margin-top: 0.75rem;
        }

        .teacher-promotions-subject-preview span,
        .teacher-preview-meta__item span,
        .teacher-promotions-item__subject span {
            color: #94a3b8;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .teacher-promotions-subject-preview strong,
        .teacher-preview-meta__item strong,
        .teacher-promotions-item__subject strong {
            color: #f8fbff;
        }

        .teacher-promotions-form__footer {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
        }

        .teacher-promotions-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .teacher-promotions-form__hint span {
            display: block;
            margin-top: 0.35rem;
        }

        .teacher-promotion-cta-card {
            padding: 1rem;
            border-radius: 18px;
            background: rgba(12, 19, 34, 0.72);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .teacher-promotion-cta-card__top {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .teacher-promotion-cta-card__top h5 {
            margin-bottom: 0.3rem;
            color: #f8fbff;
            font-weight: 800;
        }

        .teacher-promotion-cta-card__top p,
        .teacher-promotion-cta-card .form-check-label {
            color: #a9bbd5;
        }

        .teacher-preview-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
            margin-bottom: 1rem;
        }

        .teacher-promotions-history {
            display: grid;
            gap: 1rem;
        }

        .teacher-promotions-item {
            border-radius: 20px;
            padding: 1rem 1.05rem;
        }

        .teacher-promotions-item__meta,
        .teacher-promotions-item__footer {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
        }

        .teacher-promotions-item__subject {
            margin: 0.8rem 0 0.55rem;
            display: grid;
            gap: 0.18rem;
        }

        .teacher-promotions-item__filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
        }

        .teacher-promotions-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.16);
            color: #bfdbfe;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .teacher-promotions-pill--soft {
            background: rgba(148, 163, 184, 0.16);
            color: #dbeafe;
        }

        .teacher-promotions-time,
        .teacher-promotions-item__footer span,
        .teacher-promotions-item p,
        .teacher-promotions-item__html {
            color: #a9bbd5;
        }

        .teacher-promotions-item p,
        .teacher-promotions-item__html {
            line-height: 1.75;
            margin-bottom: 0.85rem;
        }

        .teacher-promotions-item__html * {
            max-width: 100%;
        }

        .teacher-promotions-item__footer strong {
            color: #dce9ff;
        }

        .teacher-promotions-item__cta a,
        .teacher-mail-preview__cta a {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-weight: 800;
        }

        .teacher-promotions-item__cta {
            margin-bottom: 0.85rem;
        }

        .teacher-promotions-empty {
            border-radius: 22px;
            padding: 2.5rem 1.5rem;
            text-align: center;
        }

        .teacher-promotions-empty__icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 1rem;
            border-radius: 22px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.22), rgba(37, 99, 235, 0.24));
            color: #dbeafe;
            font-size: 1.8rem;
        }

        .teacher-promotions-empty h4 {
            color: #f8fbff;
            margin-bottom: 0.55rem;
        }

        .teacher-promotions-empty p {
            color: #a9bbd5;
            max-width: 560px;
            margin: 0 auto;
            line-height: 1.75;
        }

        .teacher-mail-preview {
            border-radius: 24px;
            background: rgba(8, 15, 29, 0.55);
            padding: 0.75rem;
        }

        .teacher-mail-preview__frame {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(17, 24, 39, 0.08);
        }

        .teacher-mail-preview__header {
            padding: 22px 26px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
        }

        .teacher-mail-preview__eyebrow {
            font-size: 13px;
            color: #dbeafe;
        }

        .teacher-mail-preview__header h5 {
            margin: 10px 0 0;
            font-size: 24px;
            color: #ffffff;
            font-weight: 800;
        }

        .teacher-mail-preview__header p {
            margin: 10px 0 0;
            font-size: 13px;
            color: #e0e7ff;
        }

        .teacher-mail-preview__body {
            padding: 26px;
            color: #111827;
            background: #ffffff;
        }

        .teacher-mail-preview__content {
            font-size: 14px;
            line-height: 1.7;
            color: #111827;
        }

        .teacher-mail-preview__content * {
            max-width: 100%;
        }

        .teacher-mail-preview__cta {
            margin-top: 18px;
        }

        .teacher-mail-preview__footer {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 18px 26px;
            background: #f8fafc;
            border-top: 1px solid #eef2f7;
            color: #6b7280;
            font-size: 12px;
        }

        .teacher-promotions-card .cke,
        .teacher-promotions-card .cke_chrome,
        .teacher-promotions-card .cke_top,
        .teacher-promotions-card .cke_bottom {
            border-color: rgba(96, 165, 250, 0.18);
        }

        .teacher-promotions-card .cke_top,
        .teacher-promotions-card .cke_bottom,
        .teacher-promotions-card .cke_chrome {
            background: #162033;
            box-shadow: none;
        }

        .teacher-promotions-card .cke_toolgroup {
            background: #1a2740;
            border-color: #334155;
            box-shadow: none;
        }

        .teacher-promotions-card a.cke_button_off:hover,
        .teacher-promotions-card a.cke_button_off:focus,
        .teacher-promotions-card .cke_combo_button:hover,
        .teacher-promotions-card .cke_combo_button:focus {
            background: #22314d;
            border-color: #3b4f70;
        }

        .teacher-promotions-card .cke_button_icon {
            filter: invert(0.9) hue-rotate(180deg);
        }

        .teacher-promotions-card .cke_button_label,
        .teacher-promotions-card .cke_combo_text,
        .teacher-promotions-card .cke_combo_open,
        .teacher-promotions-card .cke_toolgroup a,
        .teacher-promotions-card .cke_path_item,
        .teacher-promotions-card .cke_path_empty {
            color: #dbe7f5 !important;
        }

        .teacher-promotions-card .cke_contents {
            border-color: rgba(96, 165, 250, 0.18);
            background: #0b1324;
        }

        html[data-theme="light"] .teacher-promotions-shell {
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 28%),
                linear-gradient(180deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.98) 100%);
        }

        html[data-theme="light"] .teacher-promotions-kicker {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-promotions-title,
        html[data-theme="light"] .teacher-promotions-card__head h4,
        html[data-theme="light"] .teacher-promotions-empty h4,
        html[data-theme="light"] .teacher-promotions-subject-preview strong,
        html[data-theme="light"] .teacher-preview-meta__item strong,
        html[data-theme="light"] .teacher-promotions-item__subject strong {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-promotions-desc,
        html[data-theme="light"] .teacher-promotions-stat span,
        html[data-theme="light"] .teacher-promotions-card__head p,
        html[data-theme="light"] .teacher-promotions-item p,
        html[data-theme="light"] .teacher-promotions-item__html,
        html[data-theme="light"] .teacher-promotions-empty p,
        html[data-theme="light"] .teacher-promotions-time,
        html[data-theme="light"] .teacher-promotions-item__footer span,
        html[data-theme="light"] .teacher-promotions-form .form-text,
        html[data-theme="light"] .teacher-promotions-form__hint,
        html[data-theme="light"] .teacher-promotion-cta-card__top p,
        html[data-theme="light"] .teacher-promotion-cta-card .form-check-label,
        html[data-theme="light"] .teacher-promotions-subject-preview span,
        html[data-theme="light"] .teacher-preview-meta__item span,
        html[data-theme="light"] .teacher-promotions-item__subject span {
            color: #475569;
        }

        html[data-theme="light"] .teacher-promotions-stat,
        html[data-theme="light"] .teacher-promotions-card,
        html[data-theme="light"] .teacher-promotions-item,
        html[data-theme="light"] .teacher-promotions-empty,
        html[data-theme="light"] .teacher-preview-meta__item,
        html[data-theme="light"] .teacher-promotions-subject-preview,
        html[data-theme="light"] .teacher-promotions-recipient-card,
        html[data-theme="light"] .teacher-promotions-mode,
        html[data-theme="light"] .teacher-promotions-student,
        html[data-theme="light"] .teacher-promotions-grid-note__box {
            background: var(--admin-surface);
            border-color: var(--admin-border);
            box-shadow: var(--admin-card-shadow);
        }

        html[data-theme="light"] .teacher-promotions-stat strong,
        html[data-theme="light"] .teacher-promotions-item__footer strong,
        html[data-theme="light"] .teacher-promotions-form .form-label,
        html[data-theme="light"] .teacher-promotion-cta-card__top h5,
        html[data-theme="light"] .teacher-promotions-section-head h5,
        html[data-theme="light"] .teacher-promotions-mode span,
        html[data-theme="light"] .teacher-promotions-student__body strong {
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-promotions-student__badge--paid {
            background: rgba(34, 197, 94, 0.1);
            color: #166534;
            border-color: rgba(34, 197, 94, 0.2);
        }

        html[data-theme="light"] .teacher-promotions-student__badge--grant {
            background: rgba(245, 158, 11, 0.12);
            color: #92400e;
            border-color: rgba(245, 158, 11, 0.2);
        }

        html[data-theme="light"] .teacher-promotions-form .form-control,
        html[data-theme="light"] .teacher-promotions-form .form-select {
            background: #fff;
            border-color: var(--admin-border);
            color: #0f172a;
        }

        html[data-theme="light"] .teacher-promotions-pill {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        html[data-theme="light"] .teacher-promotions-pill--soft {
            background: rgba(148, 163, 184, 0.16);
            color: #334155;
        }

        html[data-theme="light"] .teacher-promotion-cta-card {
            background: #f8fafc;
            border-color: var(--admin-border);
        }

        html[data-theme="light"] .teacher-promotions-card .cke_top,
        html[data-theme="light"] .teacher-promotions-card .cke_bottom,
        html[data-theme="light"] .teacher-promotions-card .cke_chrome {
            background: #ffffff;
            border-color: var(--admin-border);
        }

        html[data-theme="light"] .teacher-promotions-card .cke_toolgroup {
            background: #f8fafc;
            border-color: #dbe2ea;
        }

        html[data-theme="light"] .teacher-promotions-card .cke_button_icon {
            filter: none;
        }

        html[data-theme="light"] .teacher-promotions-card .cke_button_label,
        html[data-theme="light"] .teacher-promotions-card .cke_combo_text,
        html[data-theme="light"] .teacher-promotions-card .cke_combo_open,
        html[data-theme="light"] .teacher-promotions-card .cke_toolgroup a,
        html[data-theme="light"] .teacher-promotions-card .cke_path_item,
        html[data-theme="light"] .teacher-promotions-card .cke_path_empty {
            color: #334155 !important;
        }

        html[data-theme="light"] .teacher-promotions-card .cke_contents {
            background: #ffffff;
            border-color: var(--admin-border);
        }

        @media (max-width: 991.98px) {

            .teacher-promotions-hero,
            .teacher-promotions-form__footer,
            .teacher-promotions-item__meta,
            .teacher-promotions-item__footer,
            .teacher-mail-preview__footer,
            .teacher-promotion-cta-card__top {
                flex-direction: column;
                align-items: flex-start;
            }

            .teacher-promotions-actions {
                width: 100%;
            }

            .teacher-promotions-grid,
            .teacher-preview-meta {
                grid-template-columns: 1fr;
            }

            .teacher-promotions-stat {
                text-align: left;
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('backend/plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        (() => {
            const textarea = document.getElementById('teacher-promotion-message-html');
            const templateSelect = document.querySelector('[data-promo-input="template"]');
            const courseSelect = document.querySelector('[data-promo-input="course"]');
            const ctaToggle = document.getElementById('promotion-cta-enabled');
            const ctaFields = document.querySelector('[data-promo-cta-fields]');
            const recipientModes = document.querySelectorAll('[data-promo-recipient-mode]');
            const recipientModeCards = document.querySelectorAll('.teacher-promotions-mode');
            const filterPanel = document.querySelector('[data-promo-filter-panel]');
            const manualPanel = document.querySelector('[data-promo-manual-panel]');
            const studentSearchInput = document.querySelector('[data-promo-student-search]');
            const studentItems = document.querySelectorAll('[data-promo-student-item]');
            const studentCheckboxes = document.querySelectorAll('[data-promo-student-checkbox]');
            const selectAllStudentsButton = document.querySelector('[data-promo-select-all]');
            const clearAllStudentsButton = document.querySelector('[data-promo-clear-all]');
            const selectedCount = document.querySelector('[data-promo-selected-count]');
            const liveCountNodes = document.querySelectorAll('[data-promo-live-count]');
            const titleInput = document.querySelector('[data-promo-input="title"]');
            const ctaLabelInput = document.querySelector('[data-promo-input="cta_label"]');
            const ctaUrlInput = document.querySelector('[data-promo-input="cta_url"]');
            const previewTitle = document.querySelector('[data-promo-preview="title"]');
            const previewContent = document.querySelector('[data-promo-preview="content"]');
            const previewCtaWrap = document.querySelector('[data-promo-preview="cta_wrap"]');
            const previewCtaLink = document.querySelector('[data-promo-preview="cta_link"]');
            const previewSubject = document.querySelector('[data-promo-preview="subject"]');
            const previewSubjectLine = document.querySelector('[data-promo-preview="subject_line"]');
            const previewTemplateLabel = document.querySelector('[data-promo-preview="template_label"]');
            const templates = @json($templateOptions);
            const teacherName = @json(trim((string) ($teacher?->name_locale ?: $teacher?->name ?: 'Giảng viên')));
            const teacherPublicUrl = @json($teacherPublicUrl);
            const getRecipientMode = () => document.querySelector('[data-promo-recipient-mode]:checked')?.value ||
                'all';

            const getSelectedCourseOption = () => courseSelect?.options[courseSelect.selectedIndex] || null;
            const getSelectedCourseName = () => (getSelectedCourseOption()?.textContent || '').trim();
            const getSelectedCourseUrl = () => getSelectedCourseOption()?.dataset.courseUrl || teacherPublicUrl || '';

            const applyTemplateTokens = (value) => {
                const courseName = getSelectedCourseName();
                const replacements = {
                    '__TEACHER_NAME__': teacherName,
                    '__COURSE_NAME__': courseName || 'khoa hoc cua minh',
                    '__COURSE_URL__': getSelectedCourseUrl(),
                    '__TEACHER_URL__': teacherPublicUrl || '',
                    '__CTA_URL__': getSelectedCourseUrl(),
                };

                return Object.entries(replacements).reduce((result, [token, tokenValue]) => {
                    return result.split(token).join(tokenValue);
                }, value || '');
            };

            const setEditorData = (html) => {
                if (typeof CKEDITOR !== 'undefined' && textarea && CKEDITOR.instances[textarea.id]) {
                    CKEDITOR.instances[textarea.id].setData(html);
                    return;
                }

                if (textarea) {
                    textarea.value = html;
                }
            };

            const syncPreview = () => {
                const subject = (titleInput?.value || '').trim() || 'Thông báo từ giảng viên';

                if (previewTitle) previewTitle.textContent = subject;
                if (previewSubject) previewSubject.textContent = subject;
                if (previewSubjectLine) previewSubjectLine.textContent = subject;

                if (previewContent) {
                    let html = '';

                    if (typeof CKEDITOR !== 'undefined' && textarea && CKEDITOR.instances[textarea.id]) {
                        html = CKEDITOR.instances[textarea.id].getData();
                    } else if (textarea) {
                        html = textarea.value;
                    }

                    previewContent.innerHTML = html.trim() !== '' ? html :
                        '<p>Nội dung email sẽ hiển thị ở đây.</p>';
                }

                if (previewCtaWrap && previewCtaLink) {
                    const enabled = !!ctaToggle?.checked;
                    const label = (ctaLabelInput?.value || '').trim() || 'Xem chi tiết';
                    const url = (ctaUrlInput?.value || '').trim() || getSelectedCourseUrl() || '#';
                    previewCtaWrap.hidden = !enabled;
                    previewCtaLink.textContent = label;
                    previewCtaLink.setAttribute('href', url);
                }

                if (previewTemplateLabel && templateSelect) {
                    previewTemplateLabel.textContent = templates[templateSelect.value]?.label || 'Tự viết';
                }
            };

            const syncCtaFields = () => {
                if (ctaFields) {
                    ctaFields.style.display = ctaToggle?.checked ? '' : 'none';
                }

                if (ctaToggle?.checked && ctaUrlInput && !ctaUrlInput.value) {
                    ctaUrlInput.value = getSelectedCourseUrl();
                }

                syncPreview();
            };

            const syncRecipientMode = () => {
                const mode = getRecipientMode();

                recipientModeCards.forEach((card) => {
                    const input = card.querySelector('[data-promo-recipient-mode]');
                    card.classList.toggle('is-active', !!input?.checked);
                });

                if (filterPanel) {
                    filterPanel.hidden = mode !== 'filtered';
                }

                if (manualPanel) {
                    manualPanel.hidden = mode !== 'manual';
                }

                syncLiveRecipientCount();
            };

            const syncManualCount = () => {
                if (selectedCount) {
                    selectedCount.textContent = Array.from(studentCheckboxes).filter((checkbox) => checkbox.checked)
                        .length;
                }
            };

            const syncLiveRecipientCount = () => {
                const mode = getRecipientMode();
                let count = {{ (int) $recipientPreviewCount }};

                if (mode === 'manual') {
                    count = Array.from(studentCheckboxes).filter((checkbox) => checkbox.checked).length;
                } else if (mode === 'all') {
                    count = studentCheckboxes.length;
                }

                liveCountNodes.forEach((node) => {
                    node.textContent = count;
                });
            };

            const filterStudents = () => {
                const keyword = (studentSearchInput?.value || '').trim().toLowerCase();

                studentItems.forEach((item) => {
                    const haystack = item.dataset.search || '';
                    item.hidden = keyword !== '' && !haystack.includes(keyword);
                });
            };

            const applySelectedTemplate = () => {
                const selectedKey = templateSelect?.value || 'custom';
                const template = templates[selectedKey];

                if (!template) {
                    syncPreview();
                    return;
                }

                if (previewTemplateLabel) {
                    previewTemplateLabel.textContent = template.label || 'Tự viết';
                }

                if (selectedKey === 'custom') {
                    syncPreview();
                    return;
                }

                if (titleInput) {
                    titleInput.value = applyTemplateTokens(template.title || '');
                }

                setEditorData(applyTemplateTokens(template.html || ''));

                if (ctaToggle) {
                    ctaToggle.checked = !!template.cta_enabled;
                }

                if (ctaLabelInput) {
                    ctaLabelInput.value = applyTemplateTokens(template.cta_label || '');
                }

                if (ctaUrlInput) {
                    ctaUrlInput.value = applyTemplateTokens(template.cta_url || getSelectedCourseUrl());
                }

                syncCtaFields();
                syncPreview();
            };

            templateSelect?.addEventListener('change', applySelectedTemplate);
            recipientModes.forEach((radio) => radio.addEventListener('change', syncRecipientMode));
            studentSearchInput?.addEventListener('input', filterStudents);
            studentCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', () => {
                syncManualCount();
                syncLiveRecipientCount();
            }));
            selectAllStudentsButton?.addEventListener('click', () => {
                studentCheckboxes.forEach((checkbox) => {
                    checkbox.checked = true;
                });
                syncManualCount();
                syncLiveRecipientCount();
            });
            clearAllStudentsButton?.addEventListener('click', () => {
                studentCheckboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });
                syncManualCount();
                syncLiveRecipientCount();
            });
            courseSelect?.addEventListener('change', () => {
                if ((templateSelect?.value || 'custom') !== 'custom') {
                    applySelectedTemplate();
                    return;
                }

                if (ctaToggle?.checked && ctaUrlInput && !ctaUrlInput.value) {
                    ctaUrlInput.value = getSelectedCourseUrl();
                }

                syncPreview();
            });

            titleInput?.addEventListener('input', syncPreview);
            ctaLabelInput?.addEventListener('input', syncPreview);
            ctaUrlInput?.addEventListener('input', syncPreview);
            ctaToggle?.addEventListener('change', syncCtaFields);

            if (typeof CKEDITOR !== 'undefined' && textarea) {
                CKEDITOR.replace(textarea.id, {
                    height: 280,
                });

                CKEDITOR.on('instanceReady', (event) => {
                    if (event.editor.name !== textarea.id) {
                        return;
                    }

                    event.editor.on('change', syncPreview);
                    syncPreview();
                });
            } else {
                textarea?.addEventListener('input', syncPreview);
            }

            syncRecipientMode();
            syncManualCount();
            syncLiveRecipientCount();
            filterStudents();
            syncCtaFields();
            syncPreview();
        })();
    </script>
@endsection
