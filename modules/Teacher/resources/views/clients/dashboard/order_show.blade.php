@extends('layouts.teacher')

@section('content')
    <div class="teacher-panel teacher-order-show-shell">
        <div class="teacher-hero teacher-hero--dashboard mb-4">
            <div class="teacher-hero__content">
                <span class="teacher-hero__eyebrow">{{ __('teacher::dashboard.orders.title') }}</span>
                <h3 class="teacher-hero__title">{{ __('teacher::dashboard.orders.show.title', ['code' => $order->code ?: $order->id]) }}</h3>
                <p class="teacher-hero__desc mb-0">{{ __('teacher::dashboard.orders.show.description') }}</p>
            </div>
            <div class="teacher-hero__rail">
                <div class="teacher-hero__mini teacher-hero__mini--glass">
                    <span>{{ __('teacher::dashboard.orders.show.payment_method') }}</span>
                    <strong>{{ $order->payment_method_label }}</strong>
                </div>
                <div class="teacher-hero__mini">
                    <span>{{ __('teacher::dashboard.orders.show.paid_at') }}</span>
                    <strong>{{ optional($order->payment_complete_date ?: $order->payment_date ?: $order->created_at)->format('d/m/Y H:i') ?: __('teacher::dashboard.orders.status.no_data') }}</strong>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('teacher.dashboard.orders') }}" class="btn btn-outline-secondary">{{ __('teacher::dashboard.orders.actions.back_to_list') }}</a>
            <a href="{{ route('teacher.dashboard.students.show', $order->student_id) }}" class="btn btn-outline-secondary">{{ __('teacher::dashboard.orders.actions.open_student') }}</a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.orders.list.item_count') }}</div>
                    <div class="teacher-stat-card__value">{{ $summary['item_count'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.orders.summary.gross') }}</div>
                    <div class="teacher-stat-card__value">{{ money($summary['gross_amount'], 'đ', '0 đ') }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.orders.summary.discount') }}</div>
                    <div class="teacher-stat-card__value text-danger">-{{ money($summary['allocated_discount'], 'đ', '0 đ') }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.orders.summary.revenue') }}</div>
                    <div class="teacher-stat-card__value">{{ money($summary['teacher_revenue'], 'đ', '0 đ') }}</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-4">
                <section class="teacher-subtle-card teacher-order-show-card">
                    <h4>{{ __('teacher::dashboard.orders.show.student_info') }}</h4>
                    <div class="teacher-order-show-info-list">
                        <div>
                            <span>{{ __('teacher::dashboard.orders.show.student_name') }}</span>
                            <strong>{{ $order->customer_name_display }}</strong>
                        </div>
                        <div>
                            <span>{{ __('teacher::dashboard.orders.show.student_email') }}</span>
                            <strong>{{ $order->customer_email_display }}</strong>
                        </div>
                        <div>
                            <span>{{ __('teacher::dashboard.orders.show.student_phone') }}</span>
                            <strong>{{ $order->customer_phone_display }}</strong>
                        </div>
                        <div>
                            <span>{{ __('teacher::dashboard.orders.show.student_address') }}</span>
                            <strong>{{ $order->customer_address_display }}</strong>
                        </div>
                    </div>
                </section>

                <section class="teacher-subtle-card teacher-order-show-card">
                    <h4>{{ __('teacher::dashboard.orders.show.order_info') }}</h4>
                    <div class="teacher-order-show-info-list">
                        <div>
                            <span>{{ __('teacher::dashboard.orders.show.order_code') }}</span>
                            <strong>#{{ $order->code ?: $order->id }}</strong>
                        </div>
                        <div>
                            <span>{{ __('teacher::dashboard.orders.show.order_status') }}</span>
                            <strong>{{ $order->status?->name_locale ?: __('teacher::dashboard.orders.status.paid') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('teacher::dashboard.orders.show.payment_method') }}</span>
                            <strong>{{ $order->payment_method_label }}</strong>
                        </div>
                        <div>
                            <span>{{ __('teacher::dashboard.orders.summary.net') }}</span>
                            <strong>{{ money($summary['net_revenue'], 'đ', '0 đ') }}</strong>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-8">
                <section class="teacher-subtle-card teacher-order-show-card">
                    <h4>{{ __('teacher::dashboard.orders.show.course_details') }}</h4>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('teacher::dashboard.orders.table.course') }}</th>
                                    <th>{{ __('teacher::dashboard.orders.table.paid_at') }}</th>
                                    <th>{{ __('teacher::dashboard.orders.table.gross') }}</th>
                                    <th>{{ __('teacher::dashboard.orders.table.discount') }}</th>
                                    <th>{{ __('teacher::dashboard.orders.table.net') }}</th>
                                    <th>{{ __('teacher::dashboard.orders.table.revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $detail)
                                    <tr>
                                        <td>
                                            <strong>{{ $detail->courses?->name_locale ?: $detail->courses?->name ?: '-' }}</strong>
                                        </td>
                                        <td>{{ optional($order->payment_complete_date ?: $order->payment_date ?: $detail->created_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                        <td>{{ money(data_get($detail, 'finance_breakdown.gross_amount', 0), 'đ', '0 đ') }}</td>
                                        <td class="text-danger">-{{ money(data_get($detail, 'finance_breakdown.allocated_discount', 0), 'đ', '0 đ') }}</td>
                                        <td>{{ money(data_get($detail, 'finance_breakdown.net_revenue', 0), 'đ', '0 đ') }}</td>
                                        <td class="fw-semibold">{{ money(data_get($detail, 'finance_breakdown.teacher_revenue', 0), 'đ', '0 đ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <style>
        .teacher-order-show-shell {
            display: grid;
            gap: 1rem;
        }

        .teacher-order-show-card {
            display: grid;
            gap: 1rem;
        }

        .teacher-order-show-card h4 {
            margin: 0;
            font-weight: 700;
        }

        .teacher-order-show-info-list {
            display: grid;
            gap: 0.9rem;
        }

        .teacher-order-show-info-list span {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--admin-text-soft);
            margin-bottom: 0.2rem;
        }

        .teacher-order-show-info-list strong {
            word-break: break-word;
        }
    </style>
@endsection
