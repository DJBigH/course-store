@extends('emails.layouts.client')

@php
    $mailTitle = __('students::clients/email.account_deactivated.subject');
    $mailEyebrow = __('auth::clients/email.common.security_notice');
    $mailHeading = __('students::clients/email.account_deactivated.title');
    $mailSubtitle = __('students::clients/email.account_deactivated.subtitle');
    $preheader = __('students::clients/email.account_deactivated.preheader');
@endphp

@section('mail_content')
    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.account_deactivated.greeting', ['name' => $student->name]) }}
    </div>

    <div style="height:12px;"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.account_deactivated.line_1') }}
    </div>

    <div style="height:{{ $mailTokens['section_gap_sm'] }};"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        {{ __('students::clients/email.account_deactivated.line_2') }}
    </div>

    <div style="height:16px;"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $mailStyles['panel'] }}">
        <tr>
            <td style="{{ $mailStyles['panel_fill'] }}">
                {{ __('students::clients/email.account_deactivated.panel') }}
            </td>
        </tr>
    </table>

    <div style="height:{{ $mailTokens['section_gap_lg'] }};"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <a href="{{ $loginUrl }}" style="{{ $mailStyles['button_primary'] }}">
                    {{ __('students::clients/email.account_deactivated.button') }}
                </a>
            </td>
        </tr>
    </table>

    <div style="height:{{ $mailTokens['section_gap_lg'] }};"></div>

    <div style="{{ $mailStyles['muted_text'] }}">
        {{ __('students::clients/email.account_deactivated.footer') }}
    </div>
@endsection
