@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        <div class="teacher-hero">
            <div class="teacher-hero--dashboard">
                <div class="teacher-hero__content">
                    <div class="teacher-hero__eyebrow">
                        <i class="fa-brands fa-telegram me-2"></i> Telegram Integration
                    </div>
                    <h2 class="teacher-hero__title">{{ __('teacher::teacher/telegram.title') }}</h2>
                    <p class="teacher-hero__desc">{{ __('teacher::teacher/telegram.title_1') }}</p>
                </div>
                <div class="teacher-hero__rail">
                    <div class="teacher-hero__mini teacher-hero__mini--glass">
                        <span>{{ __('teacher::teacher/telegram.status_label') }}</span>
                        @php $status = $teacher->getTelegramPackageStatus(); @endphp
                        @if ($status['status'] === 'active')
                            @if ($status['expires_at']->year >= 9000)
                                <strong class="text-success">{{ __('teacher::teacher/telegram.lifetime') }}</strong>
                            @else
                                <strong class="text-success">{{ __('teacher::teacher/telegram.status_active') }}</strong>
                                <div class="small mt-1 text-white-50">{{ __('teacher::teacher/telegram.expiry_date') }}: {{ $status['expires_at']->format('d/m/Y H:i') }}</div>
                            @endif
                        @else
                            <strong class="text-danger">{{ __('teacher::teacher/telegram.status_expired') }}</strong>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if (session('msg_success'))
            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">{{ session('msg_success') }}</div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">{{ session('msg_danger') }}</div>
        @endif
        @if (session('msg_warning'))
            <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4">{{ session('msg_warning') }}</div>
        @endif

        <div class="row g-4">
            {{-- Cấu hình Telegram --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-gear me-2"></i> {{ __('teacher::teacher/telegram.connection_settings') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('teacher.dashboard.telegram.settings') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label fw-bold">{{ __('teacher::teacher/telegram.chat_id') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-id-card"></i></span>
                                    <input type="password" name="telegram_chat_id" class="form-control border-start-0" 
                                           value="{{ old('telegram_chat_id', $teacher->telegram_chat_id) }}" 
                                           placeholder="{{ __('teacher::teacher/telegram.chat_id_placeholder') }}">
                                </div>
                                <div class="mt-2 small text-muted">
                                    <i class="fa-solid fa-circle-info me-1"></i> {!! __('teacher::teacher/telegram.get_chat_id_guide', ['bot' => '<a href="https://t.me/userinfobot" target="_blank">@userinfobot</a>']) !!}
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input" type="checkbox" name="is_telegram_notifications_enabled" id="notifySwitch" 
                                           value="1" {{ $teacher->is_telegram_notifications_enabled ? 'checked' : '' }}
                                           {{ $status['status'] !== 'active' ? 'disabled' : '' }}>
                                    <label class="form-check-label fw-bold" for="notifySwitch">{{ __('teacher::teacher/telegram.enable_notifications') }}</label>
                                </div>
                                @if ($status['status'] !== 'active')
                                    <div class="mt-2 text-danger small">
                                        <i class="fa-solid fa-lock me-1"></i> {{ __('teacher::teacher/telegram.need_package_to_enable') }}
                                    </div>
                                @endif
                            </div>

                            <div class="row g-2 mt-2">
                                <div class="col-8">
                                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-3" {{ $status['status'] !== 'active' ? 'disabled' : '' }}>
                                        <i class="fa-solid fa-save me-2"></i> {{ __('teacher::teacher/telegram.save_settings') }}
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" id="testTelegramBtn" class="btn btn-outline-info w-100 py-2 rounded-3 {{ $teacher->telegram_chat_id && $status['status'] === 'active' ? '' : 'd-none' }}">
                                        <i class="fa-solid fa-paper-plane me-2"></i> {{ __('teacher::teacher/telegram.test_connection') }}
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="mt-4 p-3 bg-light rounded-4 border">
                            <h6 class="fw-bold mb-2 small text-uppercase">{{ __('teacher::teacher/telegram.quick_guide') }}</h6>
                            <ol class="small mb-0 ps-3">
                                <li>{!! __('teacher::teacher/telegram.guide_step_1', ['bot' => '<a href="https://t.me/userinfobot" target="_blank">@userinfobot</a>']) !!}</li>
                                <li>{!! __('teacher::teacher/telegram.guide_step_2', ['bot' => '<a href="https://t.me/bigk_udemy_teacher_alert_bot" target="_blank">@bigk_udemy_teacher_alert_bot</a>']) !!}</li>
                                <li>{!! __('teacher::teacher/telegram.guide_step_3') !!}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danh sách gói --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="fa-solid fa-shopping-cart me-2"></i> {{ __('teacher::teacher/telegram.subscription_info') }}</h5>
                        @if ($status['status'] === 'active')
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="togglePackages">
                                <i class="fa-solid fa-plus me-1"></i> <span class="btn-text">{{ __('teacher::teacher/telegram.extend_upgrade_btn') }}</span>
                            </button>
                        @endif
                    </div>
                    <div class="card-body p-4 {{ $status['status'] === 'active' ? 'd-none' : '' }}" id="packagesContainer">
                        <div class="row g-3">
                            @foreach ($packages as $package)
                                <div class="col-md-6">
                                    <div class="package-card p-3 rounded-4 border h-100 d-flex flex-column transition-all">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="fw-bold mb-1">{{ $package->name }}</h6>
                                                <div class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                                    {{ $package->duration_label }}
                                                </div>
                                            </div>
                                            <i class="fa-brands fa-telegram text-primary fs-4 opacity-50"></i>
                                        </div>
                                        
                                        <div class="mb-3">
                                            @if ($package->sale_price)
                                                <div class="text-decoration-line-through text-muted small">{{ number_format($package->price) }}đ</div>
                                                <div class="fw-bold fs-5 text-primary">{{ number_format($package->sale_price) }}đ</div>
                                            @else
                                                <div class="fw-bold fs-5 text-primary">{{ number_format($package->price) }}đ</div>
                                            @endif
                                        </div>

                                        <p class="small text-muted mb-4 flex-grow-1">
                                            {{ $package->description ?: __('teacher::teacher/telegram.default_pkg_desc') }}
                                        </p>

                                        @if ($status['expires_at'] && $status['expires_at']->year >= 9000)
                                            <button class="btn btn-secondary w-100 rounded-3" disabled>{{ __('teacher::teacher/telegram.owned_lifetime') }}</button>
                                        @else
                                            <button type="button" class="btn btn-outline-primary w-100 rounded-3 purchase-btn" 
                                                    data-id="{{ $package->id }}" 
                                                    data-name="{{ $package->name }}"
                                                    data-price="{{ number_format($package->sale_price ?? $package->price) }}đ"
                                                    data-is-lifetime="{{ $package->duration_unit === 'lifetime' ? '1' : '0' }}">
                                                {{ __('teacher::teacher/telegram.buy_btn') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if ($status['status'] === 'active')
                        <div class="card-body p-5 text-center" id="activeStatusPlaceholder">
                            <div class="mb-3 text-success">
                                <i class="fa-solid fa-circle-check fs-1"></i>
                            </div>
                            <h6 class="fw-bold">{{ __('teacher::teacher/telegram.active_status_title') }}</h6>
                            <p class="small text-muted mb-0">{{ __('teacher::teacher/telegram.active_status_desc') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal xác nhận mua --}}
    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-primary">
                        <i class="fa-solid fa-circle-question fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-2">{{ __('teacher::teacher/telegram.confirm_purchase_title') }}</h5>
                    <p class="text-muted mb-4">{!! __('teacher::teacher/telegram.confirm_purchase_desc') !!}</p>
                    
                    <div id="lifetime-warning" class="alert alert-warning border-0 small mb-4 d-none">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> {!! __('teacher::teacher/telegram.lifetime_warning') !!}
                    </div>
                    <div id="stack-info" class="alert alert-info border-0 small mb-4">
                        <i class="fa-solid fa-clock me-1"></i> {!! __('teacher::teacher/telegram.stack_info') !!}
                    </div>

                    <form id="purchaseForm" method="POST" action="">
                        @csrf
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-light w-100 py-2 rounded-3 border" data-bs-dismiss="modal">{{ __('teacher::teacher/telegram.cancel') }}</button>
                            </div>
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary w-100 py-2 rounded-3">{{ __('teacher::teacher/telegram.confirm_pay') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        .package-card {
            transition: all 0.3s ease;
        }
        .package-card:hover {
            transform: translateY(-5px);
            border-color: var(--teacher-accent) !important;
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.08);
        }
        .custom-switch .form-check-input {
            width: 3em;
            height: 1.5em;
        }
        html[data-theme="dark"] .bg-light { background-color: #1e293b !important; }
        html[data-theme="dark"] .package-card { background-color: #0f172a; border-color: #334155 !important; }
        html[data-theme="dark"] .package-card:hover { border-color: var(--teacher-accent) !important; }
        
        /* Dark Mode for Modal */
        html[data-theme="dark"] .modal-content {
            background-color: #1e293b;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .modal-body .btn-light {
            background-color: #334155;
            border-color: #475569 !important;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .modal-body .text-muted {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .modal-body .alert-info {
            background-color: rgba(14, 165, 233, 0.1);
            color: #7dd3fc;
        }
        html[data-theme="dark"] .modal-body .alert-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
        }
        
        /* Dark Mode for Card */
        html[data-theme="dark"] .card {
            background-color: #1e293b;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .form-control {
            background-color: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .form-control:focus {
            background-color: #0f172a;
            color: #f1f5f9;
        }
        html[data-theme="dark"] .input-group-text {
            background-color: #334155;
            border-color: #334155;
            color: #94a3b8;
        }
        html[data-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#togglePackages').click(function() {
                const container = $('#packagesContainer');
                const placeholder = $('#activeStatusPlaceholder');
                
                if (container.hasClass('d-none')) {
                    container.removeClass('d-none').hide().fadeIn();
                    placeholder.fadeOut(function() { $(this).addClass('d-none'); });
                    $(this).html('<i class="fa-solid fa-xmark me-1"></i> {{ __('teacher::teacher/telegram.close') }}');
                } else {
                    container.fadeOut(function() { $(this).addClass('d-none'); });
                    placeholder.removeClass('d-none').hide().fadeIn();
                    $(this).html('<i class="fa-solid fa-plus me-1"></i> {{ __('teacher::teacher/telegram.extend_upgrade_btn') }}');
                }
            });

            $('#testTelegramBtn').click(function() {
                const btn = $(this);
                const originalHtml = btn.html();
                
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> {{ __('teacher::teacher/telegram.sending') }}');

                $.ajax({
                    url: '{{ route("teacher.dashboard.telegram.test") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || '{{ __('teacher::teacher/telegram.test_error') }}');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            $('.purchase-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const price = $(this).data('price');
                const isLifetime = $(this).data('is-lifetime') == '1';

                $('#modal-pkg-name').text(name);
                $('#modal-pkg-price').text(price);
                $('#purchaseForm').attr('action', `/teacher/telegram/purchase/${id}`);

                if (isLifetime) {
                    $('#lifetime-warning').removeClass('d-none');
                    $('#stack-info').addClass('d-none');
                } else {
                    $('#lifetime-warning').addClass('d-none');
                    $('#stack-info').removeClass('d-none');
                }

                $('#purchaseModal').modal('show');
            });
        });
    </script>
@endsection