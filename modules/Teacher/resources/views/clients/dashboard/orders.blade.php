@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.orders.title') }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::dashboard.orders.description') }}</p>
            </div>
        </div>

        @if (!$teacher->packageHasFeature('can_export_orders'))
            <div class="alert alert-warning border-0 mb-4">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
                    <div>
                        <strong>{{ __('teacher::dashboard.package_features.upsell_title') }}</strong>
                        <div class="mt-1 text-muted">{{ __('teacher::dashboard.package_features.orders_locked_export') }}</div>
                    </div>
                    <a href="{{ route('teacher.dashboard.package.upgrade') }}" class="btn btn-sm btn-warning">
                        {{ __('teacher::dashboard.package_features.upgrade_cta') }}
                    </a>
                </div>
            </div>
        @endif

        <form method="GET" action="{{ route('teacher.dashboard.orders') }}" class="teacher-orders-filter mb-4">
            <div class="row g-3">
                <div class="col-xl-4">
                    <label class="form-label">{{ __('teacher::dashboard.orders.filters.search') }}</label>
                    <input
                        type="text"
                        name="q"
                        class="form-control"
                        value="{{ $directory['search'] }}"
                        placeholder="{{ __('teacher::dashboard.orders.filters.search_placeholder') }}">
                </div>
                <div class="col-md-6 col-xl-2">
                    <label class="form-label">{{ __('teacher::dashboard.orders.filters.course') }}</label>
                    <select name="course_id" class="form-select">
                        <option value="0">{{ __('teacher::dashboard.orders.filters.all') }}</option>
                        @foreach ($directory['courseOptions'] as $course)
                            <option value="{{ $course->id }}" @selected($directory['courseId'] === (int) $course->id)>
                                {{ $course->name_locale ?: $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-xl-2">
                    <label class="form-label">{{ __('teacher::dashboard.orders.filters.payment_method') }}</label>
                    <select name="payment_method" class="form-select">
                        <option value="">{{ __('teacher::dashboard.orders.filters.all') }}</option>
                        <option value="bank_transfer" @selected($directory['paymentMethod'] === 'bank_transfer')>{{ __('teacher::dashboard.orders.payment.bank_transfer') }}</option>
                        <option value="bank" @selected($directory['paymentMethod'] === 'bank')>{{ __('teacher::dashboard.orders.payment.bank_transfer') }}</option>
                        <option value="vnpay" @selected($directory['paymentMethod'] === 'vnpay')>VNPay</option>
                        <option value="momo" @selected($directory['paymentMethod'] === 'momo')>MoMo</option>
                    </select>
                </div>
                <div class="col-md-6 col-xl-2">
                    <label class="form-label">{{ __('teacher::dashboard.orders.filters.date_from') }}</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $directory['dateFrom'] }}">
                </div>
                <div class="col-md-6 col-xl-2">
                    <label class="form-label">{{ __('teacher::dashboard.orders.filters.date_to') }}</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $directory['dateTo'] }}">
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="submit" class="btn btn-primary">{{ __('teacher::dashboard.orders.actions.filter') }}</button>
                <a href="{{ route('teacher.dashboard.orders') }}" class="btn btn-outline-secondary">{{ __('teacher::dashboard.orders.actions.reset') }}</a>
                @if ($teacher->packageHasFeature('can_export_orders'))
                    <a href="{{ route('teacher.dashboard.orders.export', array_merge(['format' => 'excel'], request()->query())) }}" class="btn btn-outline-secondary">{{ __('teacher::dashboard.orders.actions.export_excel') }}</a>
                    <a href="{{ route('teacher.dashboard.orders.export', array_merge(['format' => 'csv'], request()->query())) }}" class="btn btn-outline-secondary">{{ __('teacher::dashboard.orders.actions.export_csv') }}</a>
                @endif
            </div>
        </form>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-2">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.orders.summary.orders') }}</div>
                    <div class="teacher-stat-card__value">{{ $directory['summary']['orders'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.orders.summary.students') }}</div>
                    <div class="teacher-stat-card__value">{{ $directory['summary']['students'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.orders.summary.courses') }}</div>
                    <div class="teacher-stat-card__value">{{ $directory['summary']['courses'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.orders.summary.gross') }}</div>
                    <div class="teacher-stat-card__value">{{ money($directory['summary']['gross_amount'], 'đ', '0 đ') }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.orders.summary.discount') }}</div>
                    <div class="teacher-stat-card__value text-danger">-{{ money($directory['summary']['allocated_discount'], 'đ', '0 đ') }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.orders.summary.revenue') }}</div>
                    <div class="teacher-stat-card__value">{{ money($directory['summary']['teacher_revenue'], 'đ', '0 đ') }}</div>
                </div>
            </div>
        </div>

        <div class="teacher-orders-list">
            @forelse ($orders as $item)
                <article class="teacher-subtle-card teacher-orders-card">
                    <div class="teacher-orders-card__head">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <h4 class="mb-0">{{ __('teacher::dashboard.orders.list.order_code', ['code' => $item->order?->code ?: $item->order?->id]) }}</h4>
                                <span class="teacher-status-badge">
                                    {{ $item->order?->status?->name_locale ?: __('teacher::dashboard.orders.status.paid') }}
                                </span>
                                <span class="badge rounded-pill" style="{{ $item->order?->payment_method_badge_style }}">
                                    {{ $item->order?->payment_method_label ?: __('teacher::dashboard.orders.status.unknown') }}
                                </span>
                            </div>
                            <div class="text-muted small">
                                {{ optional($item->payment_at)->format('d/m/Y H:i') ?: __('teacher::dashboard.orders.status.no_time') }}
                                • {{ $item->order?->customer_name_display ?: '-' }}
                                • {{ $item->order?->customer_email_display ?: '-' }}
                            </div>
                        </div>

                        <div class="teacher-orders-card__total">
                            <span>{{ __('teacher::dashboard.orders.summary.revenue') }}</span>
                            <strong>{{ money($item->teacher_revenue, 'đ', '0 đ') }}</strong>
                            <a href="{{ route('teacher.dashboard.orders.show', $item->order?->id) }}" class="btn btn-primary btn-sm mt-2">
                                {{ __('teacher::dashboard.orders.actions.view_detail') }}
                            </a>
                        </div>
                    </div>

                    <div class="teacher-orders-card__summary">
                        <div>
                            <span>{{ __('teacher::dashboard.orders.list.item_count') }}</span>
                            <strong>{{ $item->item_count }}</strong>
                        </div>
                        <div>
                            <span>{{ __('teacher::dashboard.orders.summary.gross') }}</span>
                            <strong>{{ money($item->gross_amount, 'đ', '0 đ') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('teacher::dashboard.orders.summary.discount') }}</span>
                            <strong class="text-danger">-{{ money($item->allocated_discount, 'đ', '0 đ') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('teacher::dashboard.orders.summary.net') }}</span>
                            <strong>{{ money($item->net_revenue, 'đ', '0 đ') }}</strong>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('teacher::dashboard.orders.table.course') }}</th>
                                    <th>{{ __('teacher::dashboard.orders.table.gross') }}</th>
                                    <th>{{ __('teacher::dashboard.orders.table.discount') }}</th>
                                    <th>{{ __('teacher::dashboard.orders.table.net') }}</th>
                                    <th>{{ __('teacher::dashboard.orders.table.revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item->details as $detail)
                                    <tr>
                                        <td>{{ $detail->courses?->name_locale ?: $detail->courses?->name ?: '-' }}</td>
                                        <td>{{ money(data_get($detail, 'finance_breakdown.gross_amount', 0), 'đ', '0 đ') }}</td>
                                        <td class="text-danger">-{{ money(data_get($detail, 'finance_breakdown.allocated_discount', 0), 'đ', '0 đ') }}</td>
                                        <td>{{ money(data_get($detail, 'finance_breakdown.net_revenue', 0), 'đ', '0 đ') }}</td>
                                        <td>{{ money(data_get($detail, 'finance_breakdown.teacher_revenue', 0), 'đ', '0 đ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @empty
                <div class="teacher-subtle-card text-center py-5 text-muted">
                    {{ __('teacher::dashboard.orders.empty') }}
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>

    <style>
        .teacher-orders-filter .form-label {
            font-weight: 600;
        }

        .teacher-orders-list {
            display: grid;
            gap: 1rem;
        }

        .teacher-orders-card {
            display: grid;
            gap: 1rem;
        }

        .teacher-orders-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .teacher-orders-card__total {
            min-width: 180px;
            text-align: right;
        }

        .teacher-orders-card__total span,
        .teacher-orders-card__summary span {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--admin-text-soft);
        }

        .teacher-orders-card__total strong {
            font-size: 1.2rem;
        }

        .teacher-orders-card__summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.85rem;
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: rgba(148, 163, 184, 0.08);
        }

        .teacher-orders-card__summary strong {
            font-size: 1rem;
        }

        @media (max-width: 991.98px) {
            .teacher-orders-card__head {
                flex-direction: column;
            }

            .teacher-orders-card__total {
                min-width: 0;
                text-align: left;
            }

            .teacher-orders-card__summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .teacher-orders-card__summary {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
@endsection
