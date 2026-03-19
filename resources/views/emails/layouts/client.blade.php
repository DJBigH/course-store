<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mailTitle ?? config('app.name') }}</title>
</head>

@php
    $mailTheme = [
        'bg' => '#f3f5f9',
        'card' => '#ffffff',
        'surface' => '#f8fafc',
        'surface_alt' => '#fbfdff',
        'line' => '#eef2f7',
        'text' => '#111827',
        'muted' => '#6b7280',
        'muted_soft' => '#9ca3af',
        'primary' => '#2563eb',
        'primary_dark' => '#1d4ed8',
        'primary_soft' => '#dbeafe',
        'accent_soft' => '#e0e7ff',
        'danger' => '#dc2626',
        'shadow' => '0 10px 30px rgba(17,24,39,0.08)',
    ];

    $mailTokens = [
        'card_radius' => '14px',
        'panel_radius' => '12px',
        'button_radius' => '10px',
        'container_width' => '600px',
        'section_gap_sm' => '10px',
        'section_gap_md' => '14px',
        'section_gap_lg' => '18px',
        'body_padding' => '26px',
        'header_padding' => '22px 26px',
        'footer_padding' => '18px 26px',
    ];

    $mailStyles = [
        'body_text' => 'font-size:14px;line-height:1.7;color:' . $mailTheme['text'] . ';',
        'muted_text' => 'font-size:12px;line-height:1.6;color:' . $mailTheme['muted'] . ';',
        'panel' => 'border:1px solid ' . $mailTheme['line'] . ';border-radius:' . $mailTokens['panel_radius'] . ';overflow:hidden;',
        'panel_fill' =>
            'padding:14px 16px;background:' .
            $mailTheme['surface'] .
            ';font-size:13px;line-height:1.7;color:' .
            $mailTheme['muted'] .
            ';',
        'table_head' =>
            'background:' .
            $mailTheme['surface'] .
            ';padding:12px 14px;font-size:12px;color:' .
            $mailTheme['muted'] .
            ';font-weight:700;',
        'table_head_center' =>
            'background:' .
            $mailTheme['surface'] .
            ';padding:12px 10px;font-size:12px;color:' .
            $mailTheme['muted'] .
            ';font-weight:700;width:90px;',
        'table_head_right' =>
            'background:' .
            $mailTheme['surface'] .
            ';padding:12px 14px;font-size:12px;color:' .
            $mailTheme['muted'] .
            ';font-weight:700;width:140px;',
        'button_primary' =>
            'display:inline-block;background:' .
            $mailTheme['primary'] .
            ';color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;padding:12px 18px;border-radius:' .
            $mailTokens['button_radius'] .
            ';',
        'stats_label' =>
            'padding:10px 14px;font-size:13px;color:' .
            $mailTheme['muted'] .
            ';background:' .
            $mailTheme['surface_alt'] .
            ';',
        'stats_value' =>
            'padding:10px 14px;font-size:13px;color:' .
            $mailTheme['text'] .
            ';background:' .
            $mailTheme['surface_alt'] .
            ';',
        'stats_total_label' =>
            'padding:12px 14px;font-size:14px;color:' .
            $mailTheme['text'] .
            ';font-weight:800;border-top:1px solid ' .
            $mailTheme['line'] .
            ';background:' .
            $mailTheme['surface'] .
            ';',
        'stats_total_value' =>
            'padding:12px 14px;font-size:14px;color:' .
            $mailTheme['primary'] .
            ';font-weight:900;border-top:1px solid ' .
            $mailTheme['line'] .
            ';background:' .
            $mailTheme['surface'] .
            ';',
    ];
@endphp

<body
    style="margin:0;padding:0;background:#f3f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans','Liberation Sans','PingFang SC','Hiragino Sans GB','Microsoft YaHei','Noto Sans CJK SC','Noto Sans CJK JP','Noto Sans CJK KR','Malgun Gothic',sans-serif;color:#111827;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        {{ $preheader ?? '' }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background:{{ $mailTheme['bg'] }};padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                    style="width:{{ $mailTokens['container_width'] }};max-width:{{ $mailTokens['container_width'] }};background:{{ $mailTheme['card'] }};border-radius:{{ $mailTokens['card_radius'] }};overflow:hidden;box-shadow:{{ $mailTheme['shadow'] }};">
                    @include('emails.partials.client-header', [
                        'eyebrow' => $mailEyebrow ?? __('auth::clients/email.common.system_notice'),
                        'title' => $mailHeading ?? config('app.name'),
                        'subtitle' => $mailSubtitle ?? null,
                        'mailTheme' => $mailTheme,
                        'mailTokens' => $mailTokens,
                    ])

                    <tr>
                        <td style="padding:{{ $mailTokens['body_padding'] }};">
                            @yield('mail_content')
                        </td>
                    </tr>

                    @include('emails.partials.client-footer', [
                        'mailTheme' => $mailTheme,
                        'mailTokens' => $mailTokens,
                    ])
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
