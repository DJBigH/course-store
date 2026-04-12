<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Teacher Orders Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            vertical-align: top;
        }

        th {
            background: #e2e8f0;
            font-weight: 700;
            text-align: left;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>{{ __('teacher::dashboard.orders.export.order') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.student') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.email') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.phone') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.payment_method') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.paid_at') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.course_count') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.courses') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.gross') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.discount') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.net') }}</th>
                <th>{{ __('teacher::dashboard.orders.export.revenue') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $item)
                <tr>
                    <td>{{ $item->order?->code ?: $item->order?->id }}</td>
                    <td>{{ $item->order?->customer_name_display }}</td>
                    <td>{{ $item->order?->customer_email_display }}</td>
                    <td>{{ $item->order?->customer_phone_display }}</td>
                    <td>{{ $item->order?->payment_method_label }}</td>
                    <td>{{ optional($item->payment_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->item_count }}</td>
                    <td>{{ $item->details->pluck(fn ($detail) => $detail->courses?->name_locale ?: $detail->courses?->name ?: '')->implode(' | ') }}</td>
                    <td>{{ $item->gross_amount }}</td>
                    <td>{{ $item->allocated_discount }}</td>
                    <td>{{ $item->net_revenue }}</td>
                    <td>{{ $item->teacher_revenue }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">{{ __('teacher::dashboard.orders.export.empty') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
