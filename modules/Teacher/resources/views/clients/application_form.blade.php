@extends('layouts.client')

@section('content')

    @php
        $copy = trans('teacher::portal.form');
        $selectedPackageId = (string) old('package_id', $application?->package_id ?? ($packages->first()->id ?? ''));
        $selectedPaymentMethod = old('payment_method', $application?->payment_method ?? 'bank_transfer');
        $selectedPackage = $packages->firstWhere('id', (int) $selectedPackageId);
        $selectedPrice = (float) ($selectedPackage?->price ?? 0);
        
        $bankEnabled = (int) setting('payment_bank_enabled', '1') === 1;
        $vnpayEnabled = (int) setting('payment_vnpay_enabled', '1') === 1;
        $momoEnabled = (int) setting('payment_momo_enabled', '1') === 1;
    @endphp

    <section class="teacher-apply-page py-5">
        <div class="container">
            <div class="teacher-apply-shell" id="wizard-container">
                <!-- Stepper -->
                <div class="teacher-stepper">
                    @foreach(['step_1_label', 'step_2_label', 'step_3_label', 'step_4_label', 'step_5_label'] as $index => $labelKey)
                        <div class="teacher-step-item {{ $index === 0 ? 'is-active' : '' }}" data-step-target="{{ $index + 1 }}">
                            <div class="teacher-step-circle">{{ $index + 1 }}</div>
                            <div class="teacher-step-label">{{ $copy['wizard'][$labelKey] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="teacher-apply-intro text-center mb-5">
                    <h1 class="fw-bold display-5 mb-2">{{ $application?->exists ? $copy['edit_title'] : $copy['create_title'] }}</h1>
                    <p class="text-muted lead mx-auto" style="max-width: 700px;">{{ $copy['intro'] }}</p>
                </div>

                <form id="teacher-application-form" 
                    action="{{ $application?->exists ? route('teacher.account.update', ['locale' => app()->getLocale()]) : route('teacher.account.submit', ['locale' => app()->getLocale()]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Step 1: Personal -->
                    <div class="wizard-step is-active" data-step="1">
                        <div class="teacher-apply-card">
                            <div class="teacher-section-heading mb-4">
                                <h3>{{ $copy['wizard']['step_1_label'] }}</h3>
                                <p class="text-muted">Thông tin cơ bản để chúng tôi nhận diện bạn.</p>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">{{ $copy['fields']['full_name'] }}</label>
                                    <input type="text" name="full_name" class="form-control" required maxlength="100"
                                        placeholder="{{ $copy['fields']['full_name_placeholder'] }}"
                                        value="{{ old('full_name', $application?->full_name ?? $student?->name ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ $copy['fields']['email'] }}</label>
                                    <input type="email" name="email" class="form-control" required maxlength="100"
                                        placeholder="{{ $copy['fields']['email_placeholder'] }}"
                                        value="{{ old('email', $application?->email ?? $student?->email ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ $copy['fields']['display_name'] }}</label>
                                    <input type="text" name="display_name" class="form-control" maxlength="100"
                                        placeholder="{{ $copy['fields']['display_name_placeholder'] }}"
                                        value="{{ old('display_name', $application?->display_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ $copy['fields']['phone'] }} <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" required maxlength="20"
                                        placeholder="{{ $copy['fields']['phone_placeholder'] }}"
                                        value="{{ old('phone', $application?->phone) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ $copy['fields']['headline'] }} <span class="text-danger">*</span></label>
                                    <input type="text" name="headline" class="form-control" required minlength="5" maxlength="255"
                                        placeholder="{{ $copy['fields']['headline_placeholder'] }}"
                                        value="{{ old('headline', $application?->headline) }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-5">
                                <button type="button" class="btn-wizard btn-wizard-next" data-action="next">
                                    {{ $copy['wizard']['next'] }} <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Professional -->
                    <div class="wizard-step" data-step="2">
                        <div class="teacher-apply-card">
                            <div class="teacher-section-heading mb-4">
                                <h3>{{ $copy['wizard']['step_2_label'] }}</h3>
                                <p class="text-muted">Chia sẻ về kinh nghiệm giảng dạy của bạn.</p>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">{{ $copy['fields']['experience_years'] }} <span class="text-danger">*</span></label>
                                    <input type="number" min="0" max="80" name="experience_years" class="form-control" required
                                        placeholder="{{ $copy['fields']['experience_years_placeholder'] }}"
                                        value="{{ old('experience_years', $application?->experience_years) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ $copy['fields']['specialties'] }} <span class="text-danger">*</span></label>
                                    <input type="text" name="specialties" class="form-control" required maxlength="255"
                                        placeholder="{{ $copy['fields']['specialties_placeholder'] }}"
                                        value="{{ old('specialties', is_array($application?->specialties) ? implode(', ', $application->specialties) : '') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ $copy['fields']['bio'] }} <span class="text-danger">*</span></label>
                                    <textarea name="bio" class="form-control" required minlength="50" maxlength="5000" rows="6"
                                        placeholder="{{ $copy['fields']['bio_placeholder'] }}">{{ old('bio', $application?->bio) }}</textarea>
                                    <div class="form-text small">Tối thiểu 50 ký tự.</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn-wizard btn-wizard-prev" data-action="prev">
                                    <i class="fas fa-arrow-left"></i> {{ $copy['wizard']['prev'] }}
                                </button>
                                <button type="button" class="btn-wizard btn-wizard-next" data-action="next">
                                    {{ $copy['wizard']['next'] }} <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Documents -->
                    <div class="wizard-step" data-step="3">
                        <div class="teacher-apply-card">
                            <div class="teacher-section-heading mb-4">
                                <h3>{{ $copy['wizard']['step_3_label'] }}</h3>
                                <p class="text-muted">Các tài liệu và liên kết chứng minh năng lực.</p>
                            </div>
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label">{{ $copy['fields']['cv_file'] }} @unless($application?->cv_file_path) <span class="text-danger">*</span> @endunless</label>
                                    <div class="cv-upload-zone" onclick="document.getElementById('cv_file_input').click()">
                                        <div class="cv-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                        <h5 class="mb-1">Bấm hoặc kéo thả file CV vào đây</h5>
                                        <p class="text-muted small">Hỗ trợ PDF, JPG, PNG (Max 5MB)</p>
                                        <div id="cv-file-name" class="mt-2 fw-bold text-primary">
                                            @if($application?->cv_file_path) <i class="fas fa-file-alt"></i> {{ $application->cv_file }} @endif
                                        </div>
                                    </div>
                                    <input type="file" name="cv_file" id="cv_file_input" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ $copy['fields']['portfolio_url'] }}</label>
                                    <input type="url" name="portfolio_url" class="form-control" placeholder="{{ $copy['fields']['portfolio_placeholder'] }}"
                                        value="{{ old('portfolio_url', $application?->portfolio_url) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ $copy['fields']['intro_video_url'] }}</label>
                                    <input type="url" name="intro_video_url" class="form-control" placeholder="{{ $copy['fields']['intro_video_placeholder'] }}"
                                        value="{{ old('intro_video_url', $application?->intro_video_url) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ $copy['fields']['facebook_url'] }}</label>
                                    <input type="url" name="facebook_url" class="form-control" placeholder="{{ $copy['fields']['facebook_placeholder'] }}"
                                        value="{{ old('facebook_url', $application?->facebook_url) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ $copy['fields']['youtube_url'] }}</label>
                                    <input type="url" name="youtube_url" class="form-control" placeholder="{{ $copy['fields']['youtube_placeholder'] }}"
                                        value="{{ old('youtube_url', $application?->youtube_url) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ $copy['fields']['linkedin_url'] }}</label>
                                    <input type="url" name="linkedin_url" class="form-control" placeholder="{{ $copy['fields']['linkedin_placeholder'] }}"
                                        value="{{ old('linkedin_url', $application?->linkedin_url) }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn-wizard btn-wizard-prev" data-action="prev">
                                    <i class="fas fa-arrow-left"></i> {{ $copy['wizard']['prev'] }}
                                </button>
                                <button type="button" class="btn-wizard btn-wizard-next" data-action="next">
                                    {{ $copy['wizard']['next'] }} <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Package & Payment -->
                    <div class="wizard-step" data-step="4">
                        <div class="teacher-apply-card">
                            <div class="teacher-section-heading mb-4 d-flex justify-content-between align-items-end">
                                <div>
                                    <h3>{{ $copy['wizard']['step_4_label'] }}</h3>
                                    <p class="text-muted mb-0">Chọn gói dịch vụ và hoàn tất đăng ký.</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4 py-2 fw-bold" 
                                    style="border-width: 2px;"
                                    data-bs-toggle="modal" data-bs-target="#comparePackagesModal">
                                    <i class="fas fa-columns me-2"></i> {{ $copy['wizard']['compare_btn'] ?? 'So sánh tính năng' }}
                                </button>
                            </div>

                            <!-- Category Tabs -->
                            <ul class="nav nav-pills teacher-category-pills mb-4 p-1 rounded-pill bg-dark bg-opacity-10 d-inline-flex" id="packageTabs" role="tablist">
                                @foreach($groupedPackages as $category => $items)
                                    @php $catSlug = \Illuminate\Support\Str::slug($category); @endphp
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link rounded-pill px-4 fw-bold text-uppercase {{ $loop->first ? 'active' : '' }}" 
                                                id="tab-{{ $catSlug }}" 
                                                data-bs-toggle="pill" 
                                                data-bs-target="#cat-{{ $catSlug }}" 
                                                type="button" 
                                                role="tab">
                                            {{ str_replace('_', ' ', $category) }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content mb-5" id="packageTabContent">
                                @foreach($groupedPackages as $category => $items)
                                    @php $catSlug = \Illuminate\Support\Str::slug($category); @endphp
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="cat-{{ $catSlug }}" role="tabpanel">
                                        <div class="row g-4">
                                            @foreach ($items as $package)
                                                <div class="col-lg-4">
                                                    <label class="teacher-package-option {{ $selectedPackageId === (string) $package->id ? 'is-selected' : '' }}" data-package-card>
                                                        <input type="radio" name="package_id" value="{{ $package->id }}" class="d-none"
                                                            data-package-price="{{ (float) $package->price }}"
                                                            {{ $selectedPackageId === (string) $package->id ? 'checked' : '' }}>
                                                        <div class="teacher-package-option__code">{{ strtoupper((string) $package->code) }}</div>
                                                        <div class="fw-bold fs-5 mt-2">{{ $package->name_locale ?: $package->name }}</div>
                                                        <div class="teacher-package-option__price">
                                                            {{ (float) $package->price > 0 ? money($package->price) : $copy['package']['free'] }}
                                                        </div>
                                                        <p class="text-muted small mb-3 flex-grow-1">{{ $package->description_locale ?: $package->description }}</p>
                                                        <div class="teacher-package-option__meta border-top pt-3 small text-muted">
                                                            <i class="fas fa-percent me-1"></i> {{ str_replace(':rate', rtrim(rtrim(number_format((float) $package->commission_rate, 2, '.', ''), '0'), '.'), $copy['package']['commission']) }}<br>
                                                            <i class="fas fa-book me-1"></i> {{ $package->effective_course_limit ? str_replace(':count', $package->effective_course_limit, $copy['package']['course_limit']) : $copy['package']['unlimited'] }}
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="p-4 rounded-4 teacher-summary-hint border d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <span class="text-muted small fw-bold text-uppercase">Giá gói đã chọn:</span>
                                    <strong class="fs-4 ms-2 text-primary" data-selected-price>{{ $selectedPrice > 0 ? money($selectedPrice) : $copy['package']['free'] }}</strong>
                                </div>
                                <div class="text-muted small" data-payment-hint>
                                    {{ $selectedPrice > 0 ? $copy['package']['payment_required'] : $copy['package']['free_hint'] }}
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn-wizard btn-wizard-prev" data-action="prev">
                                    <i class="fas fa-arrow-left"></i> {{ $copy['wizard']['prev'] }}
                                </button>
                                <button type="button" class="btn-wizard btn-wizard-next" data-action="next">
                                    {{ $copy['wizard']['next'] }} <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Payment -->
                    <div class="wizard-step" data-step="5">
                        <div class="teacher-apply-card">
                            <div class="teacher-section-heading mb-4 text-center">
                                <h3>{{ $copy['wizard']['step_5_label'] }}</h3>
                                <p class="text-muted">Chọn phương thức và xem thông tin thanh toán.</p>
                            </div>

                            <div class="row g-4" id="step-5-row">
                                <div class="col-lg-7" id="payment-setup-area">
                                    <div id="payment-methods-wrapper">
                                        <label class="form-label mb-3 fw-bold">1. Chọn phương thức thanh toán</label>
                                        <div class="d-flex flex-column gap-2">
                                            <label class="teacher-payment-option border p-3 rounded-4 d-flex align-items-center gap-3 {{ $bankEnabled ? 'cursor-pointer' : 'is-disabled' }}">
                                                <input type="radio" name="payment_method" value="bank_transfer" {{ $selectedPaymentMethod === 'bank_transfer' ? 'checked' : '' }} {{ $bankEnabled ? '' : 'disabled' }}>
                                                <div class="d-flex justify-content-between flex-grow-1 align-items-center">
                                                    <span>{{ __('teacher::portal.payment_methods.bank_transfer') }}</span>
                                                    @unless($bankEnabled) <span class="badge bg-secondary small">{{ __('teacher::portal.payment_methods.maintenance') }}</span> @endunless
                                                </div>
                                            </label>
                                            <label class="teacher-payment-option border p-3 rounded-4 d-flex align-items-center gap-3 {{ $vnpayEnabled ? 'cursor-pointer' : 'is-disabled' }}">
                                                <input type="radio" name="payment_method" value="vnpay" {{ $selectedPaymentMethod === 'vnpay' ? 'checked' : '' }} {{ $vnpayEnabled ? '' : 'disabled' }}>
                                                <div class="d-flex justify-content-between flex-grow-1 align-items-center">
                                                    <span>{{ __('teacher::portal.payment_methods.vnpay') }}</span>
                                                    @unless($vnpayEnabled) <span class="badge bg-secondary small">{{ __('teacher::portal.payment_methods.maintenance') }}</span> @endunless
                                                </div>
                                            </label>
                                            <label class="teacher-payment-option border p-3 rounded-4 d-flex align-items-center gap-3 {{ $momoEnabled ? 'cursor-pointer' : 'is-disabled' }}">
                                                <input type="radio" name="payment_method" value="momo" {{ $selectedPaymentMethod === 'momo' ? 'checked' : '' }} {{ $momoEnabled ? '' : 'disabled' }}>
                                                <div class="d-flex justify-content-between flex-grow-1 align-items-center">
                                                    <span>{{ __('teacher::portal.payment_methods.momo') }}</span>
                                                    @unless($momoEnabled) <span class="badge bg-secondary small">{{ __('teacher::portal.payment_methods.maintenance') }}</span> @endunless
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="teacher-summary-hint p-4 rounded-4 border mt-4 payment-info-box {{ $selectedPaymentMethod === 'bank_transfer' ? '' : 'd-none' }}" data-payment-target="bank_transfer">
                                        <div class="d-flex gap-3">
                                            <div class="fs-4 text-primary"><i class="fas fa-info-circle"></i></div>
                                            <div>
                                                <div class="fw-bold mb-1">Xác nhận đơn đăng ký</div>
                                                <p class="mb-0 small opacity-75">Sau khi bạn nhấn "Gửi hồ sơ", hệ thống sẽ chuyển bạn đến trang theo dõi để xem chi tiết thông tin chuyển khoản và QR Code thanh toán.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    </div>
                                </div>
                                    <div class="col-lg-5" id="payment-summary-area">
                                        <div class="p-4 rounded-4 border d-flex flex-column teacher-summary-card">
                                            <div id="payment-summary">
                                                <div class="d-flex align-items-center gap-2 mb-4 text-muted small fw-bold text-uppercase border-bottom pb-2">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                    <span>Chi tiết thanh toán</span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between mb-3 small">
                                                    <span class="text-muted">Gói dịch vụ:</span>
                                                    <span class="fw-bold" data-selected-package-name>{{ $selectedPackage?->name_locale ?: $selectedPackage?->name ?: 'N/A' }}</span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between mb-3 small">
                                                    <span class="text-muted">Phí đăng ký:</span>
                                                    <span class="fw-bold" data-selected-price>{{ $selectedPrice > 0 ? money($selectedPrice) : $copy['package']['free'] }}</span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between mb-4 pt-3 border-top">
                                                    <span class="fw-bold">Tổng cộng:</span>
                                                    <div class="text-end">
                                                        <div class="fs-2 fw-bold text-primary" data-selected-price style="line-height: 1;">{{ $selectedPrice > 0 ? money($selectedPrice) : $copy['package']['free'] }}</div>
                                                        <div class="text-muted small mt-1">Đã bao gồm VAT</div>
                                                    </div>
                                                </div>

                                                <div class="p-3 rounded-4 teacher-summary-hint small text-muted text-center" data-payment-hint>
                                                    <i class="fas fa-shield-alt me-1 text-success"></i> Thanh toán an toàn 100%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5 pt-4 border-top">
                                <button type="button" class="btn-wizard btn-wizard-prev" data-action="prev">
                                    <i class="fas fa-arrow-left"></i> {{ $copy['wizard']['prev'] }}
                                </button>
                                <button type="submit" class="btn-wizard btn-wizard-next" id="submit-application">
                                    <span>{{ $application?->exists ? $copy['actions']['update'] : $copy['actions']['submit'] }}</span>
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Success State (Hidden by default) -->
                <div id="success-state" class="teacher-apply-card success-card d-none text-center">
                    <div class="success-animation mb-4">
                        <div class="success-icon"><i class="fas fa-check"></i></div>
                    </div>
                    <h1 class="fw-bold mb-3">Cảm ơn bạn!</h1>
                    <p class="fs-5 text-muted mb-4">{{ $copy['wizard']['success_msg'] }}</p>
                    <div class="p-3 teacher-summary-hint rounded-4 d-inline-block border mb-5">
                        <span class="text-muted">Hệ thống sẽ tự động chuyển hướng sau <span id="redirect-timer" class="fw-bold text-primary">5</span> giây...</span>
                    </div>
                    <div>
                        <a href="{{ route('teacher.account.status', ['locale' => app()->getLocale()]) }}" class="btn btn-primary px-5 py-3 rounded-pill fw-bold shadow-sm">
                            {{ $copy['actions']['view_status'] }} <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('modals')
    <!-- Comparison Modal - Moved to standard modals yield for correct DOM placement -->
    <div class="modal fade" id="comparePackagesModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="z-index: 10000;">
            <div class="modal-content teacher-comparison-modal border-0 shadow-lg">
                <div class="modal-header border-0 pb-0 d-flex align-items-center">
                    <h5 class="modal-title fw-bold fs-4 ms-2 mt-2">
                        <i class="fas fa-columns text-primary me-2"></i>
                        {{ __('teacher::portal.form.comparison.title') }}
                    </h5>
                    <button type="button" class="btn-close-custom ms-auto border-0 bg-transparent p-3 fs-4 text-muted hover-opacity-100" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle comparison-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;"></th>
                                    <th style="width: 35%;">
                                        <select class="form-select rounded-pill border-2 fw-bold" id="compare-select-1">
                                            @foreach($packages as $pkg)
                                                <option value="{{ $pkg->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $pkg->name_locale ?: $pkg->name }}</option>
                                            @endforeach
                                        </select>
                                    </th>
                                    <th style="width: 35%;">
                                        <select class="form-select rounded-pill border-2 fw-bold" id="compare-select-2">
                                            @foreach($packages as $pkg)
                                                <option value="{{ $pkg->id }}" {{ $loop->index === 1 ? 'selected' : '' }}>{{ $pkg->name_locale ?: $pkg->name }}</option>
                                            @endforeach
                                        </select>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="comparison-body">
                                <!-- JS Populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-5 fw-bold shadow-sm" data-bs-dismiss="modal">
                         {{ __('teacher::portal.form.wizard.prev') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .teacher-apply-page {
            --teacher-accent: #2563eb;
            --teacher-card-bg: #ffffff;
            --teacher-card-border: rgba(37, 99, 235, 0.12);
            --teacher-text: #1e293b;
            --teacher-muted: #64748b;
            --teacher-input-bg: #f8fafc;
            --teacher-input-border: #e2e8f0;
            --teacher-summary-bg: rgba(37, 99, 235, 0.04);
            
            color: var(--teacher-text);
            min-height: 80vh;
            background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.05), transparent 400px),
                        radial-gradient(circle at bottom left, rgba(37, 99, 235, 0.03), transparent 400px);
        }

        html[data-theme="dark"] .teacher-apply-page {
            --teacher-card-bg: #111827;
            --teacher-card-border: rgba(96, 165, 250, 0.15);
            --teacher-text: #f1f5f9;
            --teacher-muted: #94a3b8;
            --teacher-input-bg: #0f172a;
            --teacher-input-border: rgba(148, 163, 184, 0.2);
            --teacher-summary-bg: rgba(59, 130, 246, 0.08);
            background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 400px),
                        #0a0f1a;
        }

        /* Stepper UI */
        .teacher-stepper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 800px;
            margin: 0 auto 3rem;
            position: relative;
            padding: 0 1rem;
        }

        .teacher-stepper::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--teacher-input-border);
            z-index: 1;
        }

        .teacher-step-item {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .teacher-step-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--teacher-card-bg);
            border: 2px solid var(--teacher-input-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--teacher-muted);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .teacher-step-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--teacher-muted);
            transition: color 0.3s ease;
            text-align: center;
        }

        .teacher-step-item.is-active .teacher-step-circle {
            border-color: var(--teacher-accent);
            color: var(--teacher-accent);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
            transform: scale(1.1);
        }

        .teacher-step-item.is-active .teacher-step-label {
            color: var(--teacher-accent);
        }

        .teacher-step-item.is-completed .teacher-step-circle {
            background: var(--teacher-accent);
            border-color: var(--teacher-accent);
            color: #fff;
        }

        /* Wizard Logic Display */
        .wizard-step {
            display: none;
            animation: slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .wizard-step.is-active {
            display: block;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Premium Card Styles */
        .teacher-apply-card {
            padding: 2.5rem;
            border-radius: 32px;
            background: var(--teacher-card-bg);
            border: 1px solid var(--teacher-card-border);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .teacher-section-heading h3 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }

        /* Pricing Cards */
        .teacher-package-option {
            display: flex;
            flex-direction: column;
            padding: 2rem;
            border: 2px solid var(--teacher-input-border);
            border-radius: 24px;
            background: var(--teacher-card-bg);
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
        }

        .teacher-package-option:hover {
            border-color: var(--teacher-accent);
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.15);
        }

        .teacher-package-option.is-selected {
            border-color: var(--teacher-accent);
            background: var(--teacher-summary-bg);
        }

        .teacher-package-option__price {
            font-size: 1.75rem;
            font-weight: 800;
            margin: 1rem 0;
            color: var(--teacher-accent);
        }

        .teacher-payment-option {
            transition: all 0.2s ease;
        }

        .teacher-payment-option.is-disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8fafc;
        }

        html[data-theme="dark"] .teacher-payment-option.is-disabled {
            background: #0f172a;
        }

        .teacher-payment-option:not(.is-disabled):hover {
            border-color: var(--teacher-accent);
            background: var(--teacher-summary-bg);
        }

        .teacher-summary-card {
            background: linear-gradient(145deg, var(--teacher-input-bg), var(--teacher-summary-bg));
            border-color: var(--teacher-card-border) !important;
        }

        .payment-info-box {
            background: var(--teacher-summary-bg);
            border-color: var(--teacher-card-border) !important;
        }

        .teacher-summary-hint {
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(37, 99, 235, 0.1);
            backdrop-filter: blur(8px);
        }

        html[data-theme="dark"] .teacher-summary-hint {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(148, 163, 184, 0.1);
        }

        /* File Upload UX */
        .cv-upload-zone {
            border: 2px dashed var(--teacher-input-border);
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--teacher-input-bg);
        }

        .cv-upload-zone:hover {
            border-color: var(--teacher-accent);
            background: var(--teacher-summary-bg);
        }

        .cv-upload-icon {
            font-size: 2.5rem;
            color: var(--teacher-accent);
            margin-bottom: 1rem;
        }

        /* Form Controls */
        .teacher-apply-page .form-control {
            border-radius: 14px;
            padding: 0.8rem 1.1rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .teacher-apply-page .form-control:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.1);
        }

        /* Validation Error Animation */
        .ajax-error {
            color: #ef4444;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        /* Buttons */
        .btn-wizard {
            padding: 0.9rem 2rem;
            border-radius: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .btn-wizard-next {
            background: var(--teacher-accent);
            color: #fff;
            border: none;
        }

        .btn-wizard-next:hover {
            background: #1d4ed8;
            transform: scale(1.02);
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3);
        }

        .btn-wizard-prev {
            background: transparent;
            border: 2px solid var(--teacher-input-border);
            color: var(--teacher-muted);
        }

        .btn-wizard-prev:hover {
            border-color: var(--teacher-muted);
            color: var(--teacher-text);
        }

        /* Success Card */
        .success-card {
            text-align: center;
            padding: 4rem 2rem;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #22c55e;
            color: #fff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 30px rgba(34, 197, 94, 0.3);
            animation: bounceIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }

        @media (max-width: 767.98px) {
            .teacher-stepper { margin-bottom: 2rem; }
            .teacher-step-label { font-size: 0.75rem; }
            .teacher-apply-card { padding: 1.5rem; border-radius: 24px; }
            .teacher-package-option { padding: 1.5rem; }
        }
        /* CKEditor 4 Theme & Customizations */
        .cke_chrome {
            border-radius: 16px !important;
            border: 1px solid var(--teacher-input-border) !important;
            box-shadow: none !important;
            overflow: hidden;
        }
        
        .cke_inner {
            background: var(--teacher-input-bg) !important;
        }

        .cke_top {
            background: #f8fafc !important;
            border-bottom: 1px solid var(--teacher-input-border) !important;
            padding: 8px !important;
        }

        html[data-theme="dark"] .cke_top {
            background: #2d3748 !important;
            border-bottom-color: rgba(255,255,255,0.05) !important;
        }

        html[data-theme="dark"] .cke_chrome {
            border-color: rgba(255,255,255,0.15) !important;
        }

        /* Invert icons in dark mode to make them visible */
        html[data-theme="dark"] .cke_button_icon {
            filter: invert(1) brightness(100) contrast(100);
            opacity: 0.85;
        }

        html[data-theme="dark"] .cke_button:hover .cke_button_icon {
            opacity: 1;
        }

        /* Style combo boxes (Format, Styles) */
        html[data-theme="dark"] .cke_combo_button {
            background: #1a202c !important;
            border-color: rgba(255,255,255,0.1) !important;
            box-shadow: none !important;
        }

        html[data-theme="dark"] .cke_combo_text {
            color: #e2e8f0 !important;
            text-shadow: none !important;
        }

        html[data-theme="dark"] .cke_combo_arrow {
            border-top-color: #e2e8f0 !important;
        }

        /* Dark Mode for CKEditor 4 UI */
        html[data-theme="dark"] .cke_bottom { background: #1e293b !important; border-top-color: rgba(255,255,255,0.1) !important; }
        html[data-theme="dark"] .cke_status { color: #94a3b8 !important; }
        html[data-theme="dark"] .cke_button_label { color: #e2e8f0 !important; }
        html[data-theme="dark"] .cke_combo_label { color: #e2e8f0 !important; }
        html[data-theme="dark"] .cke_toolbar_separator { background-color: rgba(255,255,255,0.2) !important; }

        .ck-editor__editable_inline {
            min-height: 250px;
        }

        /* Comparison Modal Styles */
        .teacher-comparison-modal {
            background: var(--teacher-card-bg);
            border-radius: 24px;
        }
        
        .comparison-table th {
            padding: 1.5rem 1rem;
            background: var(--teacher-summary-bg);
            border-radius: 12px;
        }

        .comparison-table td {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--teacher-input-border);
        }

        .feature-label {
            font-weight: 700;
            color: var(--teacher-muted);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .feature-value {
            font-weight: 600;
            font-size: 1.05rem;
            text-align: center;
        }

        .feature-value.is-check { font-size: 1.25rem; }
        
        .comparison-table select {
            background-color: var(--teacher-card-bg);
            color: var(--teacher-text);
            padding: 0.75rem 1.25rem;
        }

        html[data-theme="dark"] .comparison-table select option {
            background-color: #1e293b;
            color: #f1f5f9;
        }

        .btn-close-custom {
            transition: all 0.2s ease;
            cursor: pointer;
            color: var(--teacher-muted);
        }
        
        .btn-close-custom:hover {
            color: var(--teacher-text);
            transform: rotate(90deg);
        }

        .teacher-category-pills .nav-link {
            color: var(--teacher-muted);
            border: none;
            transition: all 0.3s ease;
        }

        .teacher-category-pills .nav-link.active {
            background-color: var(--bs-primary);
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.3);
        }

        .teacher-category-pills .nav-link:hover:not(.active) {
            color: var(--teacher-text);
            background-color: rgba(var(--bs-primary-rgb), 0.1);
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('backend/plugins/ckeditor/ckeditor.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // --- CKEditor 4 Initialization ---
            const initBioEditor = () => {
                const bioTextarea = document.querySelector('textarea[name="bio"]');
                if (!bioTextarea) return;

                if (!bioTextarea.id) {
                    bioTextarea.id = 'bio-editor-' + Math.random().toString(36).substring(2, 9);
                }

                const editor = CKEDITOR.replace(bioTextarea.id, {
                    height: 300,
                    toolbar: [
                        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote'] },
                        { name: 'links', items: ['Link', 'Unlink'] },
                        { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                        { name: 'styles', items: ['Format'] },
                        { name: 'tools', items: ['Maximize'] }
                    ],
                    // Use project's file manager if needed, but standard image is usually enough
                    removePlugins: 'elementspath,exportpdf,notification',
                    resize_enabled: false
                });

                const syncTheme = () => {
                    const isDark = document.documentElement.dataset.theme === 'dark';
                    if (editor.document && editor.document.getBody()) {
                        editor.document.getBody().setStyle('background-color', isDark ? '#0f172a' : '#ffffff');
                        editor.document.getBody().setStyle('color', isDark ? '#f1f5f9' : '#1e293b');
                    }
                };

                editor.on('instanceReady', syncTheme);
                
                // Observe theme change
                const observer = new MutationObserver(syncTheme);
                observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

                return editor;
            };

            const bioEditor = initBioEditor();

            const form = document.getElementById('teacher-application-form');
            const wizardContainer = document.getElementById('wizard-container');
            const successState = document.getElementById('success-state');
            const steps = document.querySelectorAll('.wizard-step');
            const stepperItems = document.querySelectorAll('.teacher-step-item');
            const nextBtns = document.querySelectorAll('[data-action="next"]');
            const prevBtns = document.querySelectorAll('[data-action="prev"]');
            const cvInput = document.getElementById('cv_file_input');
            const cvFileName = document.getElementById('cv-file-name');
            const packageInputs = document.querySelectorAll('input[name="package_id"]');

            // --- Comparison Feature ---
            const packagesData = @json($packages);
            const comparisonCopy = @json(__('teacher::portal.form.comparison'));
            const pkgSelect1 = document.getElementById('compare-select-1');
            const pkgSelect2 = document.getElementById('compare-select-2');
            const comparisonBody = document.getElementById('comparison-body');

            const renderComparison = () => {
                const pkg1 = packagesData.find(p => p.id == pkgSelect1.value);
                const pkg2 = packagesData.find(p => p.id == pkgSelect2.value);
                if (!pkg1 || !pkg2) return;

                const features = [
                    { key: 'price', label: comparisonCopy.features.price, value: (p) => p.price > 0 ? formatMoney(p.price) : "{{ __('teacher::landing.ui.free_label') }}" },
                    { key: 'billing', label: comparisonCopy.features.billing, value: (p) => comparisonCopy.values[p.billing_cycle] || p.billing_cycle },
                    { key: 'commission', label: comparisonCopy.features.commission, value: (p) => `${p.commission_rate}%` },
                    { key: 'course_limit', label: comparisonCopy.features.course_limit, value: (p) => p.course_limit || comparisonCopy.values.unlimited },
                    { key: 'payout_limit', label: comparisonCopy.features.payout_limit, value: (p) => p.payout_account_limit },
                    { key: 'support', label: comparisonCopy.features.support, value: (p) => p.support_level === 'priority' ? comparisonCopy.values.priority : comparisonCopy.values.standard },
                    { key: 'priority_review', label: comparisonCopy.features.priority_review, isBool: true, field: 'priority_review' },
                    { key: 'ai_quiz', label: comparisonCopy.features.ai_quiz, isBool: true, field: 'can_use_ai_quiz' },
                    { key: 'certificates', label: comparisonCopy.features.certificates, isBool: true, field: 'can_issue_certificates' },
                    { key: 'coupons', label: comparisonCopy.features.coupons, isBool: true, field: 'can_manage_coupons' },
                    { key: 'promotions', label: comparisonCopy.features.promotions, isBool: true, field: 'can_send_promotions' },
                    { key: 'student_manage', label: comparisonCopy.features.student_manage, isBool: true, field: 'can_manage_students' },
                    { key: 'student_progress', label: comparisonCopy.features.student_progress, isBool: true, field: 'can_view_student_progress' },
                    { key: 'activity_logs', label: comparisonCopy.features.activity_logs, isBool: true, field: 'can_view_activity_logs' },
                    { key: 'bundles', label: comparisonCopy.features.bundles, isBool: true, field: 'can_sell_bundles' },
                    { key: 'scheduling', label: comparisonCopy.features.scheduling, isBool: true, field: 'can_schedule_content' },
                    { key: 'affiliate', label: comparisonCopy.features.affiliate, isBool: true, field: 'can_use_affiliate_links' },
                    { key: 'duplicate', label: comparisonCopy.features.duplicate, isBool: true, field: 'can_duplicate_courses' },
                ];

                let html = '';
                features.forEach(f => {
                    const val1 = f.isBool 
                        ? (pkg1[f.field] ? `<i class="fas fa-check-circle text-success"></i>` : `<i class="fas fa-times-circle text-danger opacity-50"></i>`)
                        : f.value(pkg1);
                    const val2 = f.isBool 
                        ? (pkg2[f.field] ? `<i class="fas fa-check-circle text-success"></i>` : `<i class="fas fa-times-circle text-danger opacity-50"></i>`)
                        : f.value(pkg2);

                    html += `
                        <tr>
                            <td class="feature-label">${f.label}</td>
                            <td class="feature-value ${f.isBool ? 'is-check' : ''}">${val1}</td>
                            <td class="feature-value ${f.isBool ? 'is-check' : ''}">${val2}</td>
                        </tr>
                    `;
                });
                comparisonBody.innerHTML = html;
            };

            if (pkgSelect1 && pkgSelect2) {
                pkgSelect1.addEventListener('change', renderComparison);
                pkgSelect2.addEventListener('change', renderComparison);
                document.getElementById('comparePackagesModal').addEventListener('show.bs.modal', renderComparison);
            }
            const paymentMethodsWrapper = document.getElementById('payment-methods-wrapper');
            const selectedPriceEl = document.querySelectorAll('[data-selected-price]');
            const paymentHintEl = document.querySelectorAll('[data-payment-hint]');
            
            let currentStep = parseInt(sessionStorage.getItem('teacher_apply_step')) || 1;
            const totalSteps = steps.length;

            const copy = {
                free: @json($copy['package']['free']),
                freeHint: @json($copy['package']['free_hint']),
                paidHint: @json($copy['package']['payment_required']),
                submitting: @json($copy['wizard']['submitting']),
                submit: @json($application?->exists ? $copy['actions']['update'] : $copy['actions']['submit']),
                maintenance: @json(__('teacher::portal.payment_methods.maintenance'))
            };

            // --- Formatting & Helpers ---
            const formatMoney = (val) => new Intl.NumberFormat('vi-VN').format(val) + 'd';

            const updateQR = () => {
                const packageInput = form.querySelector('input[name="package_id"]:checked');
                const emailInput = form.querySelector('input[name="email"]');
                if (!packageInput) return;

                const price = parseFloat(packageInput.dataset.packagePrice || 0);
                const email = emailInput ? emailInput.value : '';
                const bankBin = @json(setting('bank_transfer_bank_bin', '970407')); // Default Techcombank
                const accNum = @json(setting('bank_transfer_account_number', '61043040524'));
                const prefix = @json(setting('bank_transfer_note_prefix', 'CK'));
                const accName = @json(setting('bank_transfer_account_name', 'Nguyễn Văn A'));
                
                const note = `${prefix} ${email}`;
                const qrUrl = `https://img.vietqr.io/image/${bankBin}-${accNum}-compact2.png?amount=${price}&addInfo=${encodeURIComponent(note)}&accountName=${encodeURIComponent(accName)}`;
                
                const qrImg = document.getElementById('payment-qr-code');
                const downloadBtn = document.getElementById('download-qr-btn');
                
                if (qrImg) qrImg.src = qrUrl;
                if (downloadBtn) {
                    downloadBtn.href = qrUrl;
                    downloadBtn.download = `payment-qr-${Date.now()}.png`;
                }
            };

            const updateWizardUI = () => {
                const selectedPackage = form.querySelector('input[name="package_id"]:checked');
                if (!selectedPackage) return;

                // Update cards selection state
                packageInputs.forEach(input => {
                    const card = input.closest('[data-package-card]');
                    if (card) {
                        card.classList.toggle('is-selected', input.checked);
                    }
                });

                const price = parseFloat(selectedPackage.dataset.packagePrice || 0);
                const name = selectedPackage.closest('[data-package-card]').querySelector('.fw-bold.fs-5').textContent;
                const isFree = price <= 0;

                // Update text elements
                if (selectedPriceEl) selectedPriceEl.forEach(el => el.textContent = isFree ? copy.free : formatMoney(price));
                document.querySelectorAll('[data-selected-package-name]').forEach(el => el.textContent = name);
                if (paymentHintEl) paymentHintEl.forEach(el => el.textContent = isFree ? copy.freeHint : copy.paidHint);

                // Toggle Step 5 Layout
                const setupArea = document.getElementById('payment-setup-area');
                const summaryArea = document.getElementById('payment-summary-area');

                if (setupArea && summaryArea) {
                    if (isFree) {
                        setupArea.style.display = 'none';
                        summaryArea.classList.remove('col-lg-5');
                        summaryArea.classList.add('col-lg-8', 'mx-auto');
                    } else {
                        setupArea.style.display = 'block';
                        summaryArea.classList.remove('col-lg-8', 'mx-auto');
                        summaryArea.classList.add('col-lg-5');
                    }
                }

                updateQR();
            };

            // --- Step Navigation ---
            const updateStepper = () => {
                stepperItems.forEach(item => {
                    const stepNum = parseInt(item.dataset.stepTarget);
                    item.classList.toggle('is-active', stepNum === currentStep);
                    item.classList.toggle('is-completed', stepNum < currentStep);
                });
            };

            const goToStep = (stepNum, skipScroll = false) => {
                if (stepNum < 1 || stepNum > totalSteps) return;
                
                steps.forEach(s => s.classList.remove('is-active'));
                const targetStep = document.querySelector(`.wizard-step[data-step="${stepNum}"]`);
                if (targetStep) {
                    targetStep.classList.add('is-active');
                    currentStep = stepNum;
                    sessionStorage.setItem('teacher_apply_step', currentStep);
                    updateStepper();
                    if (!skipScroll) {
                        window.scrollTo({ top: wizardContainer.offsetTop - 100, behavior: 'smooth' });
                    }
                }
            };

            // Restore step and data on load
            const restoreFormData = () => {
                const savedData = JSON.parse(sessionStorage.getItem('teacher_apply_data') || '{}');
                Object.keys(savedData).forEach(name => {
                    const inputs = form.querySelectorAll(`[name="${name}"]`);
                    inputs.forEach(input => {
                        if (input.type === 'file') return;
                        
                        if (input.type === 'radio') {
                            if (input.value.toString() === savedData[name].toString()) {
                                input.checked = true;
                            }
                        } else {
                            input.value = savedData[name];
                        }
                    });
                });
                
                // Trigger change for package logic
                const selectedPackage = form.querySelector('input[name="package_id"]:checked');
                if (selectedPackage) selectedPackage.dispatchEvent(new Event('change'));
                
                // Trigger change for payment method
                const selectedPayment = form.querySelector('input[name="payment_method"]:checked');
                if (selectedPayment) selectedPayment.dispatchEvent(new Event('change'));

                // Final UI Update
                updateWizardUI();
            };

            const autoSaveData = () => {
                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => {
                    if (!(value instanceof File)) data[key] = value;
                });
                sessionStorage.setItem('teacher_apply_data', JSON.stringify(data));
            };

            restoreFormData();
            if (currentStep > 1) {
                goToStep(currentStep, true);
            }

            // --- Form Observation ---
            form.addEventListener('input', autoSaveData);
            form.addEventListener('change', autoSaveData);

            nextBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Sync CKEditor before validation
                    if (bioEditor) {
                        bioEditor.updateElement();
                    }
                    
                    if (validateCurrentStep()) {
                        goToStep(currentStep + 1);
                    }
                });
            });

            prevBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    goToStep(currentStep - 1);
                });
            });

            // --- Maintenance Alert ---
            document.querySelectorAll('.teacher-payment-option.is-disabled').forEach(opt => {
                opt.addEventListener('click', (e) => {
                    e.preventDefault();
                    alert('Phương thức thanh toán này hiện đang bảo trì. Vui lòng chọn phương thức khác.');
                });
            });

            // --- Client-side Validation ---
            const validateCurrentStep = () => {
                const currentStepEl = document.querySelector(`.wizard-step[data-step="${currentStep}"]`);
                const inputs = currentStepEl.querySelectorAll('input, textarea, select');
                let isValid = true;

                clearErrors();

                inputs.forEach(input => {
                    const val = input.value.trim();
                    
                    // 1. Required check
                    if (input.hasAttribute('required') && !val) {
                        showError(input, 'Trường này là bắt buộc');
                        isValid = false;
                        return;
                    }

                    if (!val) return; // Skip other checks if empty and not required

                    // 2. Email check
                    if (input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                        showError(input, 'Email không hợp lệ (Ví dụ: abc@gmail.com)');
                        isValid = false;
                    }

                    // 3. URL check
                    if (input.type === 'url' && !/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test(val)) {
                        showError(input, 'URL không hợp lệ (Phải bắt đầu bằng http:// hoặc https://)');
                        isValid = false;
                    }

                    // 4. Number range check
                    if (input.type === 'number') {
                        const num = parseFloat(val);
                        if (input.hasAttribute('min') && num < parseFloat(input.getAttribute('min'))) {
                            showError(input, `Giá trị tối thiểu là ${input.getAttribute('min')}`);
                            isValid = false;
                        } else if (input.hasAttribute('max') && num > parseFloat(input.getAttribute('max'))) {
                            showError(input, `Giá trị tối đa là ${input.getAttribute('max')}`);
                            isValid = false;
                        }
                    }

                    // 5. Minlength check
                    if (input.hasAttribute('minlength') && val.length < parseInt(input.getAttribute('minlength'))) {
                        showError(input, `Trường này phải có ít nhất ${input.getAttribute('minlength')} ký tự (Hiện có: ${val.length})`);
                        isValid = false;
                    }
                });

                return isValid;
            };

            // --- AJAX Error Handling ---
            const showError = (input, message) => {
                input.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'ajax-error';
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                
                // If in input-group, append after the group
                const group = input.closest('.input-group');
                if (group) {
                    group.parentNode.appendChild(errorDiv);
                } else {
                    input.parentNode.appendChild(errorDiv);
                }
            };

            const clearErrors = () => {
                document.querySelectorAll('.ajax-error').forEach(el => el.remove());
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            };

            // --- File Upload UI ---
            if (cvInput) {
                cvInput.addEventListener('change', (e) => {
                    if (e.target.files.length > 0) {
                        cvFileName.innerHTML = `<i class="fas fa-file-pdf text-danger"></i> ${e.target.files[0].name}`;
                    }
                });
            }

            // --- Payment Method Selection ---
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            const paymentInfoBoxes = document.querySelectorAll('.payment-info-box');

            paymentMethods.forEach(input => {
                input.addEventListener('change', () => {
                    const method = input.value;
                    paymentInfoBoxes.forEach(box => {
                        if (box.dataset.paymentTarget === 'other') {
                            box.classList.toggle('d-none', method === 'bank_transfer');
                        } else {
                            box.classList.toggle('d-none', box.dataset.paymentTarget !== method);
                        }
                    });
                });
            });

            packageInputs.forEach(input => {
                input.addEventListener('change', updateWizardUI);
            });

            const emailField = form.querySelector('input[name="email"]');
            if (emailField) emailField.addEventListener('input', updateQR);


            // --- AJAX Submission ---
            let isSubmitting = false;
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (isSubmitting) return;
                
                const submitBtn = document.getElementById('submit-application');
                if (!submitBtn) return;
                
                isSubmitting = true;
                const btnSpan = submitBtn.querySelector('span');
                const btnIcon = submitBtn.querySelector('i');

                clearErrors();
                
                // Sync CKEditor data
                if (bioEditor) {
                    bioEditor.updateElement();
                }

                // Show loading
                submitBtn.disabled = true;
                btnSpan.textContent = copy.submitting;
                btnIcon.className = 'fas fa-spinner fa-spin';

                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    });

                    const result = await response.json();

                    if (response.status === 422) {
                        // Validation errors
                        for (const [field, messages] of Object.entries(result.errors)) {
                            const input = form.querySelector(`[name="${field}"]`) || form.querySelector(`[name="${field}[]"]`);
                            if (input) {
                                showError(input, messages[0]);
                                // Find step of this input and go to it
                                const stepOfError = input.closest('.wizard-step')?.dataset.step;
                                if (stepOfError && stepOfError != currentStep) {
                                    goToStep(parseInt(stepOfError));
                                }
                            }
                        }
                    } else if (response.ok) {
                        // Success!
                        sessionStorage.removeItem('teacher_apply_step');
                        sessionStorage.removeItem('teacher_apply_data');
                        form.classList.add('d-none');
                        document.querySelector('.teacher-stepper').classList.add('d-none');
                        document.querySelector('.teacher-apply-intro').classList.add('d-none');
                        successState.classList.remove('d-none');
                        window.scrollTo({ top: wizardContainer.offsetTop - 50, behavior: 'smooth' });

                        // Redirect timer
                        let seconds = 5;
                        const timerEl = document.getElementById('redirect-timer');
                        const statusUrl = "{{ route('teacher.account.status', ['locale' => app()->getLocale()]) }}";
                        
                        const interval = setInterval(() => {
                            seconds--;
                            if (timerEl) timerEl.textContent = seconds;
                            if (seconds <= 0) {
                                clearInterval(interval);
                                window.location.href = statusUrl;
                            }
                        }, 1000);
                    } else {
                        alert(result.message || 'Đã có lỗi xảy ra, vui lòng thử lại sau.');
                    }
                } catch (error) {
                    console.error('Submission error:', error);
                    alert('Đã có lỗi xảy ra, vui lòng thử lại sau.');
                } finally {
                    isSubmitting = false;
                    if (submitBtn) submitBtn.disabled = false;
                    if (btnSpan) btnSpan.textContent = "{{ $application?->exists ? $copy['actions']['update'] : $copy['actions']['submit'] }}";
                    if (btnIcon) btnIcon.className = 'fas fa-paper-plane';
                }
            });
        });
    </script>
@endsection
