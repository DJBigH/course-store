<table border="1">
    <thead>
        <tr>
            <th>Order Code</th>
            <th>Teacher</th>
            <th>Course</th>
            <th>Student</th>
            <th>Gross</th>
            <th>Allocated Discount</th>
            <th>Net Revenue</th>
            <th>Commission Rate</th>
            <th>Teacher Revenue</th>
            <th>Platform Revenue</th>
            <th>Paid At</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item->order?->code }}</td>
                <td>{{ $item->courses?->teacher?->name_locale ?: '' }}</td>
                <td>{{ $item->courses?->name_locale ?: '' }}</td>
                <td>{{ $item->order?->students?->name ?: '' }}</td>
                <td>{{ $item->finance_breakdown['gross_amount'] ?? 0 }}</td>
                <td>{{ $item->finance_breakdown['allocated_discount'] ?? 0 }}</td>
                <td>{{ $item->finance_breakdown['net_revenue'] ?? 0 }}</td>
                <td>{{ $item->finance_breakdown['commission_rate'] ?? 0 }}</td>
                <td>{{ $item->finance_breakdown['teacher_revenue'] ?? 0 }}</td>
                <td>{{ $item->finance_breakdown['platform_revenue'] ?? 0 }}</td>
                <td>{{ optional($item->order?->payment_complete_date ?: $item->order?->created_at)->format('Y-m-d H:i:s') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
