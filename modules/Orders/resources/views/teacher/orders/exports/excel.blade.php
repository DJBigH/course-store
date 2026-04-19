<table>
    <thead>
        <tr>
            <th>{{ __('orders::teacher/orders.export.order') }}</th>
            <th>{{ __('orders::teacher/orders.export.student') }}</th>
            <th>{{ __('orders::teacher/orders.export.email') }}</th>
            <th>{{ __('orders::teacher/orders.export.phone') }}</th>
            <th>{{ __('orders::teacher/orders.export.payment_method') }}</th>
            <th>{{ __('orders::teacher/orders.export.paid_at') }}</th>
            <th>{{ __('orders::teacher/orders.export.course_count') }}</th>
            <th>{{ __('orders::teacher/orders.export.courses') }}</th>
            <th>{{ __('orders::teacher/orders.export.gross') }}</th>
            <th>{{ __('orders::teacher/orders.export.discount') }}</th>
            <th>{{ __('orders::teacher/orders.export.net') }}</th>
            <th>{{ __('orders::teacher/orders.export.revenue') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($orders as $item)
            <tr>
                <td>{{ $item->order?->code ?: $item->order?->id }}</td>
                <td>{{ $item->order?->customer_name_display ?: '' }}</td>
                <td>{{ $item->order?->customer_email_display ?: '' }}</td>
                <td>{{ $item->order?->customer_phone_display ?: '' }}</td>
                <td>{{ $item->order?->payment_method_label ?: '' }}</td>
                <td>{{ optional($item->payment_at)->format('Y-m-d H:i:s') }}</td>
                <td>{{ $item->item_count }}</td>
                <td>{{ $item->details->pluck(fn ($detail) => $detail->courses?->name_locale ?: $detail->courses?->name ?: '')->implode(' | ') }}</td>
                <td>{{ $item->gross_amount }}</td>
                <td>{{ $item->allocated_discount }}</td>
                <td>{{ $item->net_revenue }}</td>
                <td>{{ $item->teacher_revenue }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
