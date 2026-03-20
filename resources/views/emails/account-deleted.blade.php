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
        'button_primary' =>
            'display:inline-block;background:' .
            $mailTheme['primary'] .
            ';color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;padding:12px 18px;border-radius:' .
            $mailTokens['button_radius'] .
            ';',
    ];
    $mailTitle = __('students::clients/email.account_deleted.subject');
    $mailEyebrow = __('auth::clients/email.common.security_notice');
    $mailHeading = __('students::clients/email.account_deleted.title');
    $mailSubtitle = __('students::clients/email.account_deleted.subtitle');
    $preheader = __('students::clients/email.account_deleted.preheader');
@endphp

@section('mail_content')
    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.account_deleted.greeting', ['name' => $account['name'] ?? ($account['email'] ?? '')]) }}
    </div>

    <div style="height:12px;"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.account_deleted.line_1') }}
    </div>

    <div style="height:{{ $mailTokens['section_gap_sm'] }};"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.account_deleted.line_2') }}
    </div>

    <div style="height:{{ $mailTokens['section_gap_sm'] }};"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.account_deleted.line_3') }}
    </div>

    <div style="height:16px;"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <a href="{{ $homeUrl }}" style="{{ $mailStyles['button_primary'] }}">
                    {{ __('students::clients/email.account_deleted.button') }}
                </a>
            </td>
        </tr>
    </table>

    <div style="height:{{ $mailTokens['section_gap_lg'] }};"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $mailStyles['panel'] }}">
        <tr>
            <td style="{{ $mailStyles['panel_fill'] }}">
                {{ __('students::clients/email.account_deleted.panel') }}
            </td>
        </tr>
    </table>

    <div style="height:{{ $mailTokens['section_gap_lg'] }};"></div>

    <div style="{{ $mailStyles['muted_text'] }}">
        {{ __('students::clients/email.account_deleted.footer') }}
    </div>
@endsection
