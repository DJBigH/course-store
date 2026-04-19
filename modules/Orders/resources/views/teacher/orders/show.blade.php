@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('teacher.dashboard.orders') }}" class="btn btn-link link-secondary p-0 mb-2 text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('orders::teacher/orders.actions.reset') }}
                </a>
                <h3 class="fw-bold mb-0">{{ __('orders::teacher/orders.show.title', ['code' => $order->code ?: $order->id]) }}</h3>
            </div>
            <div class="d-flex gap-2">
                <span class="teacher-status-badge">
                    {{ $order->status?->name_locale ?: __('orders::teacher/orders.status.paid') }}
                </span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="teacher-subtle-card mb-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-shopping-bag me-2"></i>{{ __('orders::teacher/orders.show.item_list') }}</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('orders::teacher/orders.table.course') }}</th>
                                    <th class="text-end">{{ __('orders::teacher/orders.table.gross') }}</th>
                                    <th class="text-end">{{ __('orders::teacher/orders.table.discount') }}</th>
                                    <th class="text-end">{{ __('orders::teacher/orders.table.net') }}</th>
                                    <th class="text-end">{{ __('orders::teacher/orders.table.revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $detail)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $detail->courses?->name_locale ?: $detail->courses?->name ?: '-' }}</div>
                                            <div class="small text-muted">{{ __('orders::teacher/orders.show.labels.commission') }}: {{ (float) data_get($detail, 'finance_breakdown.commission_rate', 0) }}%</div>
                                        </td>
                                        <td class="text-end">{{ moneyLocale(data_get($detail, 'finance_breakdown.gross_amount', 0), false) }}</td>
                                        <td class="text-end text-danger">-{{ moneyLocale(data_get($detail, 'finance_breakdown.allocated_discount', 0), true) }}</td>
                                        <td class="text-end">{{ moneyLocale(data_get($detail, 'finance_breakdown.net_revenue', 0), false) }}</td>
                                        <td class="text-end fw-bold">{{ moneyLocale(data_get($detail, 'finance_breakdown.teacher_revenue', 0), false) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th class="py-3">{{ __('orders::teacher/orders.show.summary_title') }}</th>
                                    <th class="text-end py-3">{{ moneyLocale($summary['gross_amount'], true) }}</th>
                                    <th class="text-end py-3 text-danger">-{{ moneyLocale($summary['allocated_discount'], true) }}</th>
                                    <th class="text-end py-3">{{ moneyLocale($summary['net_revenue'], true) }}</th>
                                    <th class="text-end py-3 text-primary" style="font-size: 1.1rem;">{{ moneyLocale($summary['teacher_revenue'], true) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="teacher-subtle-card mb-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-user me-2"></i>{{ __('orders::teacher/orders.show.student_info') }}</h5>
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">{{ __('orders::teacher/orders.show.labels.name') }}</label>
                        <div class="fw-bold">{{ $order->customer_name_display ?: '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">{{ __('orders::teacher/orders.show.labels.email') }}</label>
                        <div>{{ $order->customer_email_display ?: '-' }}</div>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small d-block mb-1">{{ __('orders::teacher/orders.show.labels.phone') }}</label>
                        <div>{{ $order->customer_phone_display ?: '-' }}</div>
                    </div>
                </div>

                <div class="teacher-subtle-card">
                    <h5 class="fw-bold mb-4"><i class="fas fa-credit-card me-2"></i>{{ __('orders::teacher/orders.show.payment_info') }}</h5>
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">{{ __('orders::teacher/orders.show.labels.method') }}</label>
                        <span class="badge rounded-pill" style="{{ $order->payment_method_badge_style }}">
                            {{ $order->payment_method_label ?: __('orders::teacher/orders.status.unknown') }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">{{ __('orders::teacher/orders.show.labels.date') }}</label>
                        <div>{{ optional($order->payment_at)->format('d/m/Y H:i') ?: __('orders::teacher/orders.status.no_time') }}</div>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small d-block mb-1">{{ __('orders::teacher/orders.show.labels.status') }}</label>
                        <div class="text-success fw-bold">{{ $order->status?->name_locale ?: __('orders::teacher/orders.status.paid') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
