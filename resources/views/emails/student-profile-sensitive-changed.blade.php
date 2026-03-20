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
        'panel' => 'border:1px solid ' . $mailTheme['line'] . ';border-radius:' . $mailTokens['panel_radius'] . ';overflow:hidden;',
        'panel_fill' => 'padding:14px 16px;background:' . $mailTheme['surface'] . ';font-size:13px;line-height:1.7;color:' . $mailTheme['muted'] . ';',
        'table_head' => 'background:' . $mailTheme['surface'] . ';padding:12px 14px;font-size:12px;color:' . $mailTheme['muted'] . ';font-weight:700;',
        'table_cell' => 'padding:12px 14px;font-size:13px;color:' . $mailTheme['text'] . ';border-top:1px solid ' . $mailTheme['line'] . ';vertical-align:top;',
        'button_primary' => 'display:inline-block;background:' . $mailTheme['primary'] . ';color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;padding:12px 18px;border-radius:' . $mailTokens['button_radius'] . ';',
    ];
    $mailTitle = __('students::clients/email.profile_sensitive_changed.subject');
    $mailEyebrow = __('auth::clients/email.common.security_notice');
    $mailHeading = __('students::clients/email.profile_sensitive_changed.title');
    $mailSubtitle = __('students::clients/email.profile_sensitive_changed.subtitle');
    $preheader = __('students::clients/email.profile_sensitive_changed.preheader');
@endphp

@section('mail_content')
    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.profile_sensitive_changed.greeting', ['name' => $student->name]) }}
    </div>

    <div style="height:12px;"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.profile_sensitive_changed.line_1') }}
    </div>

    <div style="height:12px;"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.profile_sensitive_changed.line_2') }}
    </div>

    @if (!empty($changes))
        <div style="height:18px;"></div>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $mailStyles['panel'] }}">
            <tr>
                <td style="{{ $mailStyles['table_head'] }}">{{ __('students::clients/email.profile_sensitive_changed.changed_fields') }}</td>
            </tr>

            @foreach ($changes as $change)
                <tr>
                    <td style="{{ $mailStyles['table_cell'] }}">
                        <strong>{{ __('students::clients/email.profile_sensitive_changed.fields.' . $change['field']) }}</strong><br>
                        <span style="color:{{ $mailTheme['muted'] }};">{{ __('students::clients/email.profile_sensitive_changed.from') }}:</span>
                        {{ $change['old'] !== '' ? $change['old'] : '-' }}<br>
                        <span style="color:{{ $mailTheme['muted'] }};">{{ __('students::clients/email.profile_sensitive_changed.to') }}:</span>
                        {{ $change['new'] !== '' ? $change['new'] : '-' }}
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    <div style="height:18px;"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <a href="{{ $loginUrl }}" style="{{ $mailStyles['button_primary'] }}">
                    {{ __('students::clients/email.profile_sensitive_changed.button') }}
                </a>
            </td>
        </tr>
    </table>

    <div style="height:18px;"></div>

    <div style="{{ $mailStyles['muted_text'] }}">
        {{ __('students::clients/email.profile_sensitive_changed.footer') }}
    </div>
@endsection
