@extends('layouts.backend')

@section('content')
    @php
        $statusColor = match ($application->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending_payment' => 'warning',
            default => 'info',
        };
    @endphp

    <div class="teacher-admin-detail py-4">
        {{-- Header Section --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('teacher-applications.index') }}">Danh sách hồ sơ</a></li>
                    <li class="breadcrumb-item active">Hồ sơ #{{ $application->id }}</li>
                </ol>
                <h2 class="h3 fw-bold mb-0">Chi tiết ứng tuyển giảng viên</h2>
            </nav>
            <div class="d-flex gap-2">
                <a href="{{ route('teacher-applications.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Quay lại
                </a>
            </div>
        </div>

        @if (session('msg'))
            <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4">
                <i class="fas fa-check-circle me-2"></i> {{ session('msg') }}
            </div>
        @endif
        @if (session('msg_danger'))
            <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('msg_danger') }}
            </div>
        @endif

        <div class="row g-4">
            {{-- Left: Applicant Information --}}
            <div class="col-lg-8">
                <div class="admin-card profile-card p-4 p-md-5 rounded-5 shadow-sm border mb-4">
                    {{-- Profile Header --}}
                    <div class="d-flex align-items-start gap-4 mb-5 pb-4 border-bottom">
                        <div class="profile-avatar bg-primary-soft text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 80px; height: 80px;">
                            {{ mb_substr($application->full_name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h1 class="h3 fw-bold mb-1">{{ $application->full_name }}</h1>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-envelope me-1"></i> {{ $application->email }} 
                                        <span class="mx-2">|</span> 
                                        <i class="fas fa-phone me-1"></i> {{ $application->phone ?: 'Chưa cung cấp' }}
                                    </p>
                                    <div class="badge bg-{{ $statusColor }}-soft text-{{ $statusColor }} rounded-pill px-3 py-2 fw-bold text-uppercase" style="font-size: 0.75rem;">
                                        {{ $application->display_status }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Info Sections --}}
                    <div class="row g-5">
                        {{-- Professional Info --}}
                        <div class="col-md-6">
                            <h4 class="h6 fw-bold text-uppercase letter-spacing-1 mb-4 text-primary">Thông tin chuyên môn</h4>
                            <div class="info-list">
                                <div class="info-item mb-3">
                                    <label class="text-muted small fw-bold d-block mb-1">Headline</label>
                                    <div class="fw-bold">{{ $application->headline ?: '-' }}</div>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-muted small fw-bold d-block mb-1">Kinh nghiệm</label>
                                    <div class="fw-bold">{{ $application->experience_years ?: 0 }} năm</div>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-muted small fw-bold d-block mb-1">Chuyên môn (Specialties)</label>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        @php 
                                            $rawSpecialties = $application->specialties;
                                            if (is_string($rawSpecialties)) {
                                                $decoded = json_decode($rawSpecialties, true);
                                                $specialties = (json_last_error() === JSON_ERROR_NONE) ? $decoded : explode(',', $rawSpecialties);
                                            } else {
                                                $specialties = (array) $rawSpecialties;
                                            }
                                        @endphp
                                        @forelse($specialties as $tag)
                                            @php $tag = trim((string)$tag); @endphp
                                            @if($tag && $tag !== '-')
                                                <span class="badge bg-light text-dark border rounded-pill px-3">{{ $tag }}</span>
                                            @endif
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Links & Documents --}}
                        <div class="col-md-6">
                            <h4 class="h6 fw-bold text-uppercase letter-spacing-1 mb-4 text-primary">Tài liệu & Liên kết</h4>
                            <div class="info-list">
                                <div class="info-item mb-3">
                                    <label class="text-muted small fw-bold d-block mb-1">Hồ sơ năng lực (Portfolio)</label>
                                    @if($application->portfolio_url && $application->portfolio_url !== '-')
                                        <a href="{{ $application->portfolio_url }}" target="_blank" class="text-decoration-none fw-bold text-primary">
                                            <i class="fas fa-external-link-alt me-1"></i> Xem Portfolio
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-muted small fw-bold d-block mb-1">Video giới thiệu</label>
                                    @if($application->intro_video_url && $application->intro_video_url !== '-')
                                        <a href="{{ $application->intro_video_url }}" target="_blank" class="text-decoration-none fw-bold text-primary">
                                            <i class="fab fa-youtube me-1"></i> Xem Video
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-muted small fw-bold d-block mb-1">CV Đính kèm</label>
                                    @if($application->cv_file_path)
                                        <a href="{{ asset('storage/' . $application->cv_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fas fa-file-pdf me-1"></i> {{ $application->cv_file ?: 'Download CV' }}
                                        </a>
                                    @else
                                        <span class="text-muted small">Không có tệp đính kèm</span>
                                    @endif
                                </div>
                                <div class="info-item mb-3">
                                    <label class="text-muted small fw-bold d-block mb-1">Mạng xã hội</label>
                                    <div class="d-flex gap-2">
                                        @if($application->facebook_url && $application->facebook_url !== '-') <a href="{{ $application->facebook_url }}" target="_blank" class="social-icon bg-light text-primary"><i class="fab fa-facebook-f"></i></a> @endif
                                        @if($application->youtube_url && $application->youtube_url !== '-') <a href="{{ $application->youtube_url }}" target="_blank" class="social-icon bg-light text-danger"><i class="fab fa-youtube"></i></a> @endif
                                        @if($application->linkedin_url && $application->linkedin_url !== '-') <a href="{{ $application->linkedin_url }}" target="_blank" class="social-icon bg-light text-info"><i class="fab fa-linkedin-in"></i></a> @endif
                                        @if(!$application->facebook_url && !$application->youtube_url && !$application->linkedin_url) <span class="text-muted">-</span> @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bio Section --}}
                        <div class="col-12 mt-2 pt-4 border-top">
                            <h4 class="h6 fw-bold text-uppercase letter-spacing-1 mb-3 text-primary">Giới thiệu bản thân (Bio)</h4>
                            <div class="bio-container p-4 rounded-4 bg-light border" style="line-height: 1.8;">
                                @if($application->bio)
                                    {!! $application->bio !!}
                                @else
                                    <span class="text-muted">Chưa cung cấp giới thiệu</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Application History Section --}}
                <div class="admin-card p-4 p-md-5 rounded-5 shadow-sm border mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h4 class="h6 fw-bold text-uppercase letter-spacing-1 mb-0 text-primary">
                            <i class="fas fa-history me-2"></i> Lịch sử ứng tuyển ({{ $history->count() + 1 }} lần)
                        </h4>
                        <span class="badge bg-light text-muted border rounded-pill">Bao gồm cả hồ sơ hiện tại</span>
                    </div>

                    <div class="history-timeline">
                        {{-- Current Application --}}
                        <div class="history-item is-current pb-4 mb-4 border-bottom position-relative">
                            <div class="d-flex gap-3">
                                <div class="history-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; z-index: 2;">
                                    <i class="fas fa-dot-circle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="fw-bold text-primary">Hồ sơ hiện tại (#{{ $application->id }})</div>
                                        <div class="small text-muted">{{ $application->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center mb-2">
                                        <span class="badge bg-{{ $statusColor }}-soft text-{{ $statusColor }} rounded-pill">{{ $application->display_status }}</span>
                                        <span class="small text-muted">• {{ $application->package?->name ?: 'N/A' }}</span>
                                    </div>
                                    @if($application->admin_note)
                                        <div class="p-3 rounded-4 bg-light small border-start border-4 border-{{ $statusColor }}">
                                            <strong class="d-block mb-1">Ghi chú hệ thống:</strong>
                                            {{ $application->admin_note }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Previous Applications --}}
                        @forelse($history as $item)
                            @php
                                $hColor = match ($item->status) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'pending_payment' => 'warning',
                                    default => 'info',
                                };
                            @endphp
                            <div class="history-item pb-4 mb-4 border-bottom last-child-border-0">
                                <div class="d-flex gap-3">
                                    <div class="history-icon bg-light text-muted border rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="fw-bold">Hồ sơ #{{ $item->id }}</div>
                                            <div class="small text-muted">{{ $item->created_at->format('d/m/Y H:i') }}</div>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center mb-2">
                                            <span class="badge bg-{{ $hColor }}-soft text-{{ $hColor }} rounded-pill">{{ $item->display_status }}</span>
                                            <span class="small text-muted">• {{ $item->package?->name ?: 'N/A' }}</span>
                                        </div>
                                        @if($item->admin_note)
                                            <div class="p-3 rounded-4 bg-light small border-start border-4 border-{{ $hColor }}">
                                                <strong class="d-block mb-1">Lý do/Ghi chú:</strong>
                                                {{ $item->admin_note }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small italic">
                                <i class="fas fa-info-circle me-1"></i> Không có lịch sử ứng tuyển trước đó
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Right: Actions & Audit --}}
            <div class="col-lg-4">
                {{-- Package Information --}}
                <div class="admin-card p-4 rounded-5 shadow-sm border mb-4 bg-primary-soft-gradient overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 p-3 opacity-10">
                        <i class="fas fa-gem fa-5x"></i>
                    </div>
                    <h4 class="h6 fw-bold text-uppercase mb-4 opacity-75">Gói đăng ký</h4>
                    <div class="mb-4">
                        <div class="h2 fw-bold mb-1">{{ $application->package?->name ?: 'N/A' }}</div>
                        @if($application->package?->code)
                            <div class="badge bg-white text-primary rounded-pill px-3">{{ $application->package->code }}</div>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="small text-muted mb-1">Thanh toán dự kiến</div>
                            <div class="h4 fw-bold text-primary mb-0">{{ money($application->payable_amount) }}</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted mb-1">Hình thức</div>
                            <div class="fw-bold">{{ $application->payment_method_label }}</div>
                        </div>
                    </div>
                </div>

                {{-- Decision Center --}}
                <div class="admin-card p-4 rounded-5 shadow-sm border mb-4">
                    <h4 class="h6 fw-bold text-uppercase mb-4"><i class="fas fa-gavel me-2"></i> Phê duyệt hồ sơ</h4>
                    
                    @if(in_array($application->status, ['approved', 'rejected', 'cancelled']))
                        <div class="alert alert-{{ $statusColor }}-soft text-{{ $statusColor }} rounded-4 border-0 mb-0 fw-bold">
                            <i class="fas fa-info-circle me-2"></i> Hồ sơ này đã ở trạng thái: {{ $application->display_status }}
                            @if($application->admin_note)
                                <div class="mt-2 pt-2 border-top border-{{ $statusColor }}-soft small fw-normal">
                                    <strong>Ghi chú:</strong> {{ $application->admin_note }}
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Approve Form --}}
                        <form method="POST" action="{{ route('teacher-applications.approve', $application->id) }}" class="mb-4" onsubmit="this.querySelector('button').disabled = true; return confirm('Bạn có chắc chắn muốn CHẤP THUẬN hồ sơ này không?')">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Ghi chú phê duyệt</label>
                                <textarea name="admin_note" class="form-control rounded-4" rows="3" placeholder="Nhập ghi chú gửi cho giảng viên...">{{ old('admin_note', $application->admin_note) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100 rounded-pill py-3 fw-bold shadow-success-sm">
                                <i class="fas fa-check-circle me-2"></i> Chấp thuận hồ sơ
                            </button>
                        </form>

                        <div class="separator mb-4"><span class="px-2 text-muted small">Hoặc từ chối</span></div>

                        {{-- Reject Form --}}
                        <form method="POST" action="{{ route('teacher-applications.reject', $application->id) }}" onsubmit="this.querySelector('button').disabled = true; return confirm('Bạn có chắc chắn muốn TỪ CHỐI hồ sơ này không?')">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Lý do từ chối <span class="text-danger">*</span></label>
                                <textarea name="admin_note" class="form-control rounded-4 border-danger-soft" rows="3" required placeholder="Nêu rõ lý do để ứng viên sửa đổi...">{{ old('admin_note', $application->admin_note) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-100 rounded-pill py-2 fw-bold">
                                <i class="fas fa-times-circle me-2"></i> Từ chối hồ sơ
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Audit Log --}}
                <div class="admin-card p-4 rounded-5 shadow-sm border">
                    <h4 class="h6 fw-bold text-uppercase mb-4"><i class="fas fa-history me-2"></i> Lịch sử xử lý</h4>
                    <div class="timeline-simple">
                        <div class="timeline-item pb-3 mb-3 border-bottom border-dashed">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-muted">Ngày xét duyệt</span>
                                <span class="small fw-bold">{{ optional($application->reviewed_at)->format('d/m/Y H:i') ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="timeline-item pb-3 mb-3 border-bottom border-dashed">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-muted">Người xét duyệt</span>
                                <span class="small fw-bold">{{ $application->reviewer?->name ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-muted">ID Giảng viên liên kết</span>
                                <span class="small fw-bold">
                                    @if($application->teacher_id)
                                        <a href="{{ route('teacher.edit', $application->teacher_id) }}" class="text-decoration-none">#{{ $application->teacher_id }}</a>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('stylesheets')
    <style>
        :root {
            --admin-primary: #2563eb;
            --admin-success: #10b981;
            --admin-danger: #ef4444;
            --admin-card-bg: #ffffff;
            --admin-border: rgba(0,0,0,0.08);
            --admin-text: #1e293b;
            --admin-muted: #64748b;
        }

        html[data-theme="dark"] {
            --admin-card-bg: #1e293b;
            --admin-border: rgba(255,255,255,0.1);
            --admin-text: #f1f5f9;
            --admin-muted: #94a3b8;
        }

        .teacher-admin-detail {
            color: var(--admin-text);
        }

        .admin-card {
            background: var(--admin-card-bg);
            border: 1px solid var(--admin-border) !important;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .bg-primary-soft { background: rgba(37, 99, 235, 0.1); }
        .bg-success-soft { background: rgba(16, 185, 129, 0.1); }
        .bg-warning-soft { background: rgba(245, 158, 11, 0.1); }
        .bg-danger-soft { background: rgba(239, 68, 68, 0.1); }
        .bg-info-soft { background: rgba(59, 130, 246, 0.1); }

        .text-success-soft { color: #10b981; }
        .text-danger-soft { color: #ef4444; }
        .text-warning-soft { color: #f59e0b; }
        .text-info-soft { color: #3b82f6; }

        .bg-primary-soft-gradient {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.05), rgba(37, 99, 235, 0.15));
        }

        .social-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .social-icon:hover {
            transform: translateY(-2px);
            opacity: 0.8;
        }

        .bio-container {
            background: rgba(0,0,0,0.02) !important;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        html[data-theme="dark"] .bio-container {
            background: rgba(255,255,255,0.03) !important;
        }

        .shadow-success-sm {
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
        }

        .separator {
            display: flex;
            align-items: center;
            text-align: center;
        }

        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--admin-border);
        }

        .border-dashed { border-style: dashed !important; }
        .letter-spacing-1 { letter-spacing: 0.05em; }

        /* Form Customization */
        .form-control {
            border: 1px solid var(--admin-border);
            background: rgba(0,0,0,0.01);
            color: var(--admin-text);
        }
        
        html[data-theme="dark"] .form-control {
            background: rgba(255,255,255,0.02);
        }

        .form-control:focus {
            background: transparent;
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .profile-avatar {
            border: 4px solid var(--admin-border);
        }
    </style>
@endsection

