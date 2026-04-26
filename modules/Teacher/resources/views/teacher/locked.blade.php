@extends('layouts.teacher')

@section('content')
<div class="teacher-panel d-flex align-items-center justify-content-center py-4">
    <div class="lock-card shadow-lg">
        <div class="lock-card__header text-center">
            <div class="lock-icon-wrapper mb-4">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <h2 class="lock-card__title">{{ __('teacher::public.locked_dashboard_title') }}</h2>
            <p class="lock-card__subtitle text-muted">{{ __('teacher::public.locked_dashboard_subtitle') }}</p>
        </div>

        <div class="lock-detail-box mt-4">
            <div class="lock-detail-box__title">
                <i class="fa-solid fa-circle-info me-2"></i> {{ __('teacher::public.locked_detail_title') }}
            </div>
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <label class="lock-label">{{ __('teacher::public.locked_reason_label') }}</label>
                    <div class="lock-reason-text">{{ $teacher->lock_reason }}</div>
                </div>
                <div class="col-sm-6">
                    <label class="lock-label">{{ __('teacher::public.locked_time_label') }}</label>
                    <div class="lock-value">{{ $teacher->locked_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="col-sm-6">
                    <label class="lock-label">{{ __('teacher::public.locked_admin_label') }}</label>
                    <div class="lock-value">{{ $teacher->lockedBy?->name ?? __('teacher::admin.table.system_fallback', [], 'Hệ thống') }}</div>
                </div>
            </div>
        </div>

        <div class="lock-footer mt-5 text-center">
            <p class="small text-muted mb-4">{{ __('teacher::public.locked_footer_note') }}</p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ url('/') }}" class="btn btn-primary px-4">
                    <i class="fa-solid fa-house me-2"></i>{{ __('teacher::public.locked_back_home') }}
                </a>
                <a href="mailto:{{ config('mail.from.address', 'support@example.com') }}" class="btn btn-outline-secondary px-4">
                    <i class="fa-solid fa-envelope me-2"></i>{{ __('teacher::public.locked_contact_support') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('stylesheets')
<style>
    .lock-card {
        max-width: 580px;
        width: 100%;
        padding: 2.5rem 2rem;
        border-radius: 24px;
        background: var(--admin-surface, #ffffff);
        border: 1px solid var(--admin-border, #e2e8f0);
    }

    .lock-icon-wrapper {
        width: 80px;
        height: 80px;
        background: #fff5f5;
        color: #e53e3e;
        font-size: 2.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
        animation: pulse-red 2s infinite;
    }

    .lock-card__title {
        font-weight: 800;
        font-size: 1.75rem;
        color: var(--admin-text, #1a202c);
        margin-bottom: 0.5rem;
    }

    .lock-detail-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
    }

    .lock-detail-box__title {
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        color: #e53e3e;
        display: flex;
        align-items: center;
    }

    .lock-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 2px;
        display: block;
    }

    .lock-reason-text {
        font-weight: 600;
        color: #1e293b;
        padding: 0.5rem 0;
        border-bottom: 1px dashed #cbd5e1;
    }

    .lock-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.9rem;
    }

    @keyframes pulse-red {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(229, 62, 62, 0.4); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(229, 62, 62, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(229, 62, 62, 0); }
    }

    /* DARK MODE OVERRIDES */
    html[data-theme="dark"] .lock-card {
        background: #1e293b;
        border-color: #334155;
    }

    html[data-theme="dark"] .lock-icon-wrapper {
        background: rgba(229, 62, 62, 0.1);
        color: #f87171;
    }

    html[data-theme="dark"] .lock-detail-box {
        background: rgba(15, 23, 42, 0.4);
        border-color: #334155;
    }

    html[data-theme="dark"] .lock-reason-text,
    html[data-theme="dark"] .lock-value {
        color: #f1f5f9;
    }

    html[data-theme="dark"] .lock-label {
        color: #94a3b8;
    }
</style>
@endsection
