<table border="1">
    <thead>
        <tr>
            <th>Payout ID</th>
            <th>Teacher</th>
            <th>Email</th>
            <th>Amount</th>
            <th>Bank Name</th>
            <th>Account Name</th>
            <th>Account Number</th>
            <th>Status</th>
            <th>Admin Note</th>
            <th>Requested At</th>
            <th>Processed At</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($payouts as $payout)
            <tr>
                <td>{{ $payout->id }}</td>
                <td>{{ $payout->teacher?->name_locale ?: '' }}</td>
                <td>{{ $payout->teacher?->student?->email ?: '' }}</td>
                <td>{{ $payout->amount }}</td>
                <td>{{ $payout->bank_name }}</td>
                <td>{{ $payout->bank_account_name }}</td>
                <td>{{ $payout->bank_account_number }}</td>
                <td>{{ $payout->status }}</td>
                <td>{{ $payout->admin_note }}</td>
                <td>{{ optional($payout->created_at)->format('Y-m-d H:i:s') }}</td>
                <td>{{ optional($payout->processed_at)->format('Y-m-d H:i:s') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
