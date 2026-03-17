@extends('emails.layouts.client')

@php
    $orderDate = $order->payment_complete_date ?? $order->created_at;
    $orderDateText = $orderDate ? \Carbon\Carbon::parse($orderDate)->format('d/m/Y H:i') : null;

    $subTotal = 0;
    if (!empty($order->detail)) {
        foreach ($order->detail as $detail) {
            $qty = (int) ($detail->qty ?? 1);
            $price = (int) ($detail->price ?? 0);
            $subTotal += $qty * $price;
        }
    }

    $discount = (int) ($order->discount ?? 0);
    $grandTotal = (int) ($order->total ?? 0);
    $billingName = $order->students->name ?? '-';
    $billingEmail = $order->students->email ?? '-';
    $orderUrl = route('students.account.order-detail', ['locale' => app()->getLocale(), 'id' => $order->id]);

    $mailTitle = __('students::clients/email.order_paid.subject', ['code' => $order->code]);
    $mailEyebrow = __('students::clients/email.order_paid.eyebrow');
    $mailHeading = __('students::clients/email.order_paid.title');
    $mailSubtitle = '<strong style="color:#ffffff;">' .
        e(__('students::clients/email.order_paid.order_code_label', ['code' => $order->code])) .
        '</strong>' .
        ($orderDateText ? ' <span style="opacity:.95;">(' . e(__('students::clients/email.order_paid.order_date', ['date' => $orderDateText])) . ')</span>' : '');
    $preheader = __('students::clients/email.order_paid.preheader', ['code' => $order->code]);
@endphp

@section('mail_content')
    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.order_paid.greeting', ['name' => $billingName]) }}
    </div>

    <div style="height:{{ $mailTokens['section_gap_sm'] }};"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.order_paid.intro') }}
    </div>

    <div style="height:{{ $mailTokens['section_gap_md'] }};"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $mailStyles['panel'] }}">
        <tr>
            <td style="{{ $mailStyles['table_head'] }}">
                {{ __('students::clients/email.order_paid.products') }}
            </td>
            <td align="center" style="{{ $mailStyles['table_head_center'] }}">
                {{ __('students::clients/email.order_paid.quantity') }}
            </td>
            <td align="right" style="{{ $mailStyles['table_head_right'] }}">
                {{ __('students::clients/email.order_paid.price') }}
            </td>
        </tr>

        @forelse($order->detail as $detail)
            @php
                $name = $detail->courses->name_locale ?? $detail->courses->name ?? __('students::clients/email.order_paid.products');
                $qty = (int) ($detail->qty ?? 1);
                $price = (int) ($detail->price ?? 0);
            @endphp
            <tr>
                <td style="padding:12px 14px;border-top:1px solid {{ $mailTheme['line'] }};">
                    <div style="font-size:14px;font-weight:600;color:{{ $mailTheme['text'] }};">
                        {{ $name }}
                    </div>
                </td>
                <td align="center"
                    style="padding:12px 10px;border-top:1px solid {{ $mailTheme['line'] }};font-size:14px;color:{{ $mailTheme['text'] }};">
                    {{ $qty }}
                </td>
                <td align="right"
                    style="padding:12px 14px;border-top:1px solid {{ $mailTheme['line'] }};font-size:14px;color:{{ $mailTheme['text'] }};">
                    {{ money($price) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3"
                    style="padding:12px 14px;border-top:1px solid {{ $mailTheme['line'] }};color:{{ $mailTheme['muted'] }};">
                    {{ __('students::clients/email.order_paid.empty_products') }}
                </td>
            </tr>
        @endforelse
    </table>

    <div style="height:{{ $mailTokens['section_gap_md'] }};"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $mailStyles['panel'] }}">
        <tr>
            <td style="{{ $mailStyles['stats_label'] }}">
                {{ __('students::clients/email.order_paid.subtotal') }}
            </td>
            <td align="right" style="{{ $mailStyles['stats_value'] }}">
                {{ moneyLocale($subTotal > 0 ? $subTotal : $grandTotal + $discount) }}
            </td>
        </tr>

        @if ($discount > 0)
            <tr>
                <td
                    style="padding:10px 14px;font-size:13px;color:{{ $mailTheme['muted'] }};border-top:1px solid {{ $mailTheme['line'] }};">
                    {{ __('students::clients/email.order_paid.discount') }}
                </td>
                <td align="right"
                    style="padding:10px 14px;font-size:13px;color:{{ $mailTheme['danger'] }};border-top:1px solid {{ $mailTheme['line'] }};">
                    -{{ moneyLocale($discount) }}
                </td>
            </tr>
        @endif

        <tr>
            <td style="{{ $mailStyles['stats_total_label'] }}">
                {{ __('students::clients/email.order_paid.total') }}
            </td>
            <td align="right" style="{{ $mailStyles['stats_total_value'] }}">
                {{ money($grandTotal) }}
            </td>
        </tr>
    </table>

    <div style="height:{{ $mailTokens['section_gap_md'] }};"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $mailStyles['panel'] }}">
        <tr>
            <td style="{{ $mailStyles['table_head'] }}">
                {{ __('students::clients/email.order_paid.billing_title') }}
            </td>
        </tr>
        <tr>
            <td style="padding:12px 14px;">
                <div style="font-size:14px;font-weight:700;color:{{ $mailTheme['text'] }};">
                    {{ $billingName }}
                </div>
                <div style="margin-top:4px;font-size:13px;color:{{ $mailTheme['muted'] }};">
                    {{ $billingEmail }}
                </div>
            </td>
        </tr>
    </table>

    <div style="height:{{ $mailTokens['section_gap_lg'] }};"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <a href="{{ $orderUrl }}" style="{{ $mailStyles['button_primary'] }}">
                    {{ __('students::clients/email.order_paid.view_order') }}
                </a>

                <div style="margin-top:10px;font-size:12px;color:{{ $mailTheme['muted'] }};line-height:1.5;">
                    {{ __('students::clients/email.order_paid.fallback_link') }}
                    <div style="margin-top:6px;word-break:break-all;color:{{ $mailTheme['primary'] }};">
                        {{ $orderUrl }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top:18px;font-size:12px;color:{{ $mailTheme['muted'] }};line-height:1.6;">
        {{ __('students::clients/email.order_paid.thanks', ['app' => config('app.name')]) }}
        {{ __('students::clients/email.order_paid.support') }}
    </div>
@endsection
