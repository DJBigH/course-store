@extends('layouts.client')

@section('content')
    @include('part.clients.page_title')

    @php
        $copy = trans('teacher::portal.status');
        $statusClass = match ($application->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending_payment' => 'warning',
            default => 'info',
        };
        $applicantTypeLabel = $application->applicant_type === 'guest'
            ? $copy['labels']['guest']
            : $copy['labels']['student'];
        $statusMessage = $copy['messages'][$application->status] ?? null;
        $paymentGuide = $copy['payment_guide'][$application->payment_method] ?? $copy['payment_guide']['bank_transfer'];
    @endphp

    <section class="teacher-application-status py-5">
        <div class="container">
            <div class="teacher-status-shell">
                <div class="teacher-status-header">
                    <div>
                        <span class="teacher-status-kicker">{{ $copy['kicker'] }}</span>
                        <h2>{{ $copy['title'] }}</h2>
                        <p class="mb-0">{{ $copy['intro'] }}</p>
                    </div>
                    @if (in_array($application->status, ['rejected', 'pending_payment', 'draft'], true))
                        <a href="{{ route('teacher.account.edit', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-primary">
                            {{ $copy['edit'] }}
                        </a>
                    @endif
                </div>

                @if (session('msg_success'))
                    <div class="alert alert-success">{{ session('msg_success') }}</div>
                @endif
                @if (session('msg_danger'))
                    <div class="alert alert-danger">{{ session('msg_danger') }}</div>
                @endif

                <div class="alert alert-{{ $statusClass }} teacher-status-alert">
                    <strong>{{ str_replace(':status', $application->display_status, $copy['current_status']) }}</strong>
                    @if ($statusMessage)
                        <span>{{ $statusMessage }}</span>
                    @endif
                </div>

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="teacher-status-card">
                            <h3 class="h5 fw-bold mb-3">{{ $copy['sections']['profile'] }}</h3>
                            <dl class="row mb-0">
                                <dt class="col-sm-5">{{ $copy['labels']['full_name'] }}</dt>
                                <dd class="col-sm-7">{{ $application->full_name }}</dd>
                                <dt class="col-sm-5">{{ $copy['labels']['display_name'] }}</dt>
                                <dd class="col-sm-7">{{ $application->display_name ?: '-' }}</dd>
                                <dt class="col-sm-5">{{ $copy['labels']['email'] }}</dt>
                                <dd class="col-sm-7">{{ $application->email }}</dd>
                                <dt class="col-sm-5">{{ $copy['labels']['applicant_type'] }}</dt>
                                <dd class="col-sm-7">{{ $applicantTypeLabel }}</dd>
                                <dt class="col-sm-5">{{ $copy['labels']['submitted_at'] }}</dt>
                                <dd class="col-sm-7">{{ optional($application->submitted_at)->format('d/m/Y H:i') ?: '-' }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="teacher-status-card">
                            <h3 class="h5 fw-bold mb-3">{{ $copy['sections']['package_payment'] }}</h3>
                            <p class="mb-2 fw-semibold">{{ $application->package?->name_locale ?: $application->package?->name ?: $copy['labels']['not_selected'] }}</p>
                            <p class="text-muted mb-2">{{ $application->package?->description_locale ?: $application->package?->description }}</p>
                            <div class="teacher-status-meta">
                                <span>{{ $application->package ? money($application->package->price) : '-' }}</span>
                                @if ($application->coupon_code)
                                    <span>{{ str_replace([':code', ':amount'], [$application->coupon_code, money($application->discount_amount ?? 0)], $copy['labels']['coupon']) }}</span>
                                @endif
                                @if ($application->package)
                                    <span>{{ str_replace(':amount', money($application->payable_amount), $copy['labels']['payable']) }}</span>
                                @endif
                                <span>{{ $application->payment_method_label }}</span>
                                <span>{{ str_replace(':rate', $application->package?->commission_rate ?? 0, $copy['labels']['commission']) }}</span>
                            </div>

                            @if ($application->status === 'pending_payment')
                                <div class="teacher-status-payment mt-3">
                                    <strong>{{ $copy['labels']['quick_guide'] }}</strong>
                                    <p class="mb-0">{{ $paymentGuide }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if (!empty($application->bio))
                    <div class="teacher-status-card mt-4">
                        <h3 class="h5 fw-bold mb-3">{{ $copy['sections']['bio'] }}</h3>
                        <p class="mb-0">{{ $application->bio }}</p>
                    </div>
                @endif

                @if (!empty($application->admin_note))
                    <div class="teacher-status-card mt-4 teacher-status-card--danger">
                        <h3 class="h5 fw-bold mb-3 text-danger">{{ $copy['sections']['admin_note'] }}</h3>
                        <p class="mb-0">{{ $application->admin_note }}</p>
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-4">
                    @if ($application->status === 'approved' && $application->teacher?->status === 'active')
                        <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-primary">
                            {{ $copy['actions']['go_dashboard'] }}
                        </a>
                    @endif

                    @if ($application->status === 'pending_payment')
                        <form method="POST" action="{{ route('teacher.account.mark-paid', ['locale' => app()->getLocale()]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                {{ $copy['actions']['mark_paid'] }}
                            </button>
                        </form>
                    @endif

                    @if (in_array($application->status, ['rejected', 'pending_payment', 'draft'], true))
                        <a href="{{ route('teacher.account.edit', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-primary">
                            {{ $copy['actions']['update_profile'] }}
                        </a>
                    @endif

                    <a href="{{ route('teacher.portal.index', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary">
                        {{ $copy['actions']['back_landing'] }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('stylesheets')
    <style>
        .teacher-application-status {
            --teacher-status-card-bg: #ffffff;
            --teacher-status-card-border: rgba(37, 99, 235, 0.12);
            --teacher-status-text: #0f172a;
            --teacher-status-muted: #64748b;
            --teacher-status-accent: #2563eb;
            --teacher-status-payment-bg: rgba(245, 158, 11, 0.12);
            color: var(--teacher-status-text);
        }

        html[data-theme="dark"] .teacher-application-status {
            --teacher-status-card-bg: #0f172a;
            --teacher-status-card-border: rgba(96, 165, 250, 0.2);
            --teacher-status-text: #e5eefc;
            --teacher-status-muted: #a9bbd5;
            --teacher-status-accent: #60a5fa;
            --teacher-status-payment-bg: rgba(245, 158, 11, 0.18);
        }

        .teacher-status-shell {
            max-width: 1100px;
            margin: 0 auto;
        }

        .teacher-status-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .teacher-status-kicker {
            display: inline-flex;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--teacher-status-accent);
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .teacher-status-header h2 {
            margin-top: 1rem;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 800;
            color: var(--teacher-status-text);
        }

        .teacher-status-header p {
            color: var(--teacher-status-text);
        }

        .teacher-status-alert {
            display: grid;
            gap: 0.45rem;
        }

        .teacher-status-card {
            padding: 1.35rem;
            border-radius: 26px;
            background: var(--teacher-status-card-bg);
            border: 1px solid var(--teacher-status-card-border);
            box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
            color: var(--teacher-status-text);
        }

        html[data-theme="dark"] .teacher-status-card {
            box-shadow: 0 22px 54px rgba(2, 6, 23, 0.28);
        }

        .teacher-status-card--danger {
            border: 1px solid rgba(220, 38, 38, 0.18);
        }

        .teacher-status-card h3,
        .teacher-status-card p,
        .teacher-status-card dt,
        .teacher-status-card dd,
        .teacher-status-card strong,
        .teacher-status-card span {
            color: var(--teacher-status-text);
        }

        .teacher-status-card .text-muted {
            color: var(--teacher-status-muted) !important;
        }

        .teacher-status-card dt {
            font-weight: 700;
        }

        .teacher-status-card dd,
        .teacher-status-card p {
            color: var(--teacher-status-muted);
        }

        .teacher-status-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            color: var(--teacher-status-accent);
            font-weight: 700;
        }

        .teacher-status-meta span {
            color: var(--teacher-status-accent);
        }

        .teacher-status-payment {
            padding: 1rem 1.1rem;
            border-radius: 18px;
            background: var(--teacher-status-payment-bg);
        }

        .teacher-status-payment strong {
            display: block;
            margin-bottom: 0.45rem;
            color: var(--teacher-status-text);
        }

        .teacher-status-payment p {
            color: var(--teacher-status-text);
        }

        html[data-theme="dark"] .teacher-application-status .btn-outline-secondary {
            color: #dbeafe;
            border-color: rgba(148, 163, 184, 0.35);
            transition: color 0.22s ease, background-color 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease, transform 0.22s ease;
        }

        html[data-theme="dark"] .teacher-application-status .btn-outline-secondary:hover,
        html[data-theme="dark"] .teacher-application-status .btn-outline-secondary:active {
            color: #0f172a;
            background: #dbeafe;
            border-color: #dbeafe;
            transform: translateY(-1px);
        }

        .teacher-application-status .btn-outline-secondary:focus-visible,
        .teacher-application-status .btn-primary:focus-visible {
            outline: none;
            box-shadow:
                0 0 0 3px rgba(8, 17, 31, 0.9),
                0 0 0 6px rgba(96, 165, 250, 0.48);
        }

        html[data-theme="dark"] .teacher-application-status .btn-outline-secondary:focus-visible,
        html[data-theme="dark"] .teacher-application-status .btn-primary:focus-visible {
            box-shadow:
                0 0 0 3px rgba(7, 17, 31, 0.96),
                0 0 0 6px rgba(125, 211, 252, 0.58);
        }
    </style>
@endsection
