@extends('emails.layouts.client')

@php
    $mailTheme = $mailTheme ?? [
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
    $mailTokens = $mailTokens ?? [
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
    $mailStyles = $mailStyles ?? [
        'body_text' => 'font-size:14px;line-height:1.7;color:' . $mailTheme['text'] . ';',
        'muted_text' => 'font-size:12px;line-height:1.6;color:' . $mailTheme['muted'] . ';',
        'panel' =>
            'border:1px solid ' .
            $mailTheme['line'] .
            ';border-radius:' .
            $mailTokens['panel_radius'] .
            ';overflow:hidden;',
        'panel_fill' =>
            'padding:14px 16px;background:' .
            $mailTheme['surface'] .
            ';font-size:13px;line-height:1.7;color:' .
            $mailTheme['muted'] .
            ';',
    ];
    $purposeLabel = __(app(\App\Support\StudentTwoFactorService::class)->purposeLabelKey($purpose));
    $mailTitle = __('students::clients/email.two_factor_code.subject');
    $mailEyebrow = __('auth::clients/email.common.security_notice');
    $mailHeading = __('students::clients/email.two_factor_code.title');
    $mailSubtitle = __('students::clients/email.two_factor_code.subtitle', ['purpose' => $purposeLabel]);
    $preheader = __('students::clients/email.two_factor_code.preheader');
@endphp

@section('mail_content')
    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.two_factor_code.greeting', ['name' => $student->name]) }}
    </div>

    <div style="height:12px;"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.two_factor_code.line_1', ['purpose' => $purposeLabel]) }}
    </div>

    <div style="height:16px;"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $mailStyles['panel'] }}">
        <tr>
            <td style="padding:18px 16px;background:{{ $mailTheme['surface_alt'] }};text-align:center;">
                <div style="font-size:30px;letter-spacing:8px;font-weight:800;color:{{ $mailTheme['primary_dark'] }};">
                    {{ $code }}
                </div>
            </td>
        </tr>
    </table>

    <div style="height:16px;"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.two_factor_code.line_2', ['minutes' => $expiresInMinutes]) }}
    </div>

    <div style="height:{{ $mailTokens['section_gap_lg'] }};"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $mailStyles['panel'] }}">
        <tr>
            <td style="{{ $mailStyles['panel_fill'] }}">
                {{ __('students::clients/email.two_factor_code.panel') }}
            </td>
        </tr>
    </table>
@endsection
