@extends('layouts.teacher')

@section('content')
    @php
        $commission = rtrim(rtrim(number_format((float) $teacher->commission_rate, 2, '.', ''), '0'), '.');
    @endphp

    <div class="teacher-panel">
        <div class="teacher-section-title mb-4">
            <div>
                <h3 class="fw-bold mb-2">{{ __('teacher::dashboard.earnings.title') }}</h3>
                <p class="text-muted mb-0">{{ __('teacher::dashboard.earnings.description') }}</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.earnings.gross_revenue') }}</div>
                    <div class="teacher-stat-card__value">{{ money($summary['gross_amount'], 'đ', '0 đ') }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="teacher-stat-card">
                    <div class="teacher-stat-card__label">{{ __('teacher::dashboard.earnings.teacher_revenue', ['rate' => $commission]) }}</div>
                    <div class="teacher-stat-card__value">{{ money($summary['teacher_revenue'], 'đ', '0 đ') }}</div>
                </div>
            </div>
        </div>

        <div class="teacher-panel">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('teacher::dashboard.earnings.table.order') }}</th>
                            <th>{{ __('teacher::dashboard.earnings.table.course') }}</th>
                            <th>{{ __('teacher::dashboard.earnings.table.student') }}</th>
                            <th>{{ __('teacher::dashboard.earnings.table.gross') }}</th>
                            <th>{{ __('teacher::dashboard.earnings.table.discount') }}</th>
                            <th>{{ __('teacher::dashboard.earnings.table.net') }}</th>
                            <th>{{ __('teacher::dashboard.earnings.table.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>#{{ $item->order?->code }}</td>
                                <td>{{ $item->courses?->name_locale ?: '-' }}</td>
                                <td>{{ $item->order?->students?->name ?: '-' }}</td>
                                <td>{{ money($item->finance_breakdown['gross_amount'], 'đ', '0 đ') }}</td>
                                <td class="text-danger">-{{ money($item->finance_breakdown['allocated_discount'], 'đ', '0 đ') }}</td>
                                <td>{{ money($item->finance_breakdown['net_revenue'], 'đ', '0 đ') }}</td>
                                <td>{{ money($item->finance_breakdown['teacher_revenue'], 'đ', '0 đ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('teacher::dashboard.earnings.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
@endsection
