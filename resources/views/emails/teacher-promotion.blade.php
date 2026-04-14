@extends('emails.layouts.client')

@php
    $courseName = $course ? (localizedModelField($course, 'name', app()->getLocale()) ?: $course->name) : null;
    $teacherName = trim((string) ($teacher->name_locale ?: $teacher->name ?: 'Teacher'));
    $studentName = trim((string) ($student->name ?: 'ban'));
    $messageHtml = trim((string) ($promotion->filters['message_html'] ?? ''));
    $messageHtml = $messageHtml !== '' ? $messageHtml : nl2br(e($promotion->message));
    $ctaEnabled = !empty($promotion->filters['cta_enabled']);
    $ctaLabel = trim((string) ($promotion->filters['cta_label'] ?? 'Xem chi tiet'));
@endphp

@section('mail_content')
    <div style="{{ $mailStyles['body_text'] }}">
        Xin chào {{ $studentName }},
    </div>

    <div style="height:{{ $mailTokens['section_gap_md'] }};"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        Bạn vừa nhận được một thông điệp mới từ giảng viên {{ $teacherName }}.
    </div>

    @if ($courseName)
        <div style="height:{{ $mailTokens['section_gap_md'] }};"></div>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $mailStyles['panel'] }}">
            <tr>
                <td style="{{ $mailStyles['table_head'] }}">
                    Khóa học liên quan
                </td>
            </tr>
            <tr>
                <td style="padding:14px 16px;background:{{ $mailTheme['surface_alt'] }};font-size:14px;font-weight:700;color:{{ $mailTheme['text'] }};">
                    {{ $courseName }}
                </td>
            </tr>
        </table>
    @endif

    <div style="height:{{ $mailTokens['section_gap_md'] }};"></div>

    <div style="{{ $mailStyles['panel_fill'] }}">
        {!! $messageHtml !!}
    </div>

    <div style="height:{{ $mailTokens['section_gap_lg'] }};"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                @if ($ctaEnabled)
                    <a href="{{ $actionUrl }}" style="{{ $mailStyles['button_primary'] }}">
                        {{ $ctaLabel }}
                    </a>
                @endif

                <div style="margin-top:10px;font-size:12px;color:{{ $mailTheme['muted'] }};line-height:1.6;">
                    Link tham khảo:
                    <div style="margin-top:6px;word-break:break-all;color:{{ $mailTheme['primary'] }};">
                        {{ $actionUrl }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top:18px;font-size:12px;color:{{ $mailTheme['muted'] }};line-height:1.7;">
        Email này được gửi từ hệ thống {{ config('app.name') }} theo yêu cầu của giảng viên.
        @if ($fallbackUrl !== $actionUrl)
            Nếu nút CTA không mở được, bạn có thể truy cập trang thay thế này:
            <div style="margin-top:6px;word-break:break-all;color:{{ $mailTheme['primary'] }};">
                {{ $fallbackUrl }}
            </div>
        @endif
    </div>
@endsection
