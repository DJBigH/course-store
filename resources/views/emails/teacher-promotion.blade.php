@extends('emails.layouts.client')

@php
    $mailTheme = $mailTheme ?? [
        'bg'           => '#f3f5f9',
        'card'         => '#ffffff',
        'surface'      => '#f8fafc',
        'surface_alt'  => '#fbfdff',
        'line'         => '#eef2f7',
        'text'         => '#111827',
        'muted'        => '#6b7280',
        'muted_soft'   => '#9ca3af',
        'primary'      => '#2563eb',
        'primary_dark' => '#1d4ed8',
        'primary_soft' => '#dbeafe',
        'accent_soft'  => '#e0e7ff',
        'danger'       => '#dc2626',
        'shadow'       => '0 10px 30px rgba(17,24,39,0.08)',
    ];
    $mailTokens = $mailTokens ?? [
        'card_radius'    => '14px',
        'panel_radius'   => '12px',
        'button_radius'  => '10px',
        'container_width'=> '600px',
        'section_gap_sm' => '10px',
        'section_gap_md' => '14px',
        'section_gap_lg' => '18px',
        'body_padding'   => '26px',
        'header_padding' => '22px 26px',
        'footer_padding' => '18px 26px',
    ];
    $mailStyles = $mailStyles ?? [
        'body_text'      => 'font-size:14px;line-height:1.7;color:' . $mailTheme['text'] . ';',
        'muted_text'     => 'font-size:12px;line-height:1.6;color:' . $mailTheme['muted'] . ';',
        'panel'          => 'border:1px solid ' . $mailTheme['line'] . ';border-radius:' . $mailTokens['panel_radius'] . ';overflow:hidden;',
        'panel_fill'     => 'padding:14px 16px;background:' . $mailTheme['surface'] . ';font-size:13px;line-height:1.7;color:' . $mailTheme['muted'] . ';',
        'table_head'     => 'background:' . $mailTheme['surface'] . ';padding:12px 14px;font-size:12px;color:' . $mailTheme['muted'] . ';font-weight:700;',
        'button_primary' => 'display:inline-block;background:' . $mailTheme['primary'] . ';color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;padding:12px 18px;border-radius:' . $mailTokens['button_radius'] . ';',
    ];
    $courseName  = $course ? ($course->name_locale ?? $course->name ?? null) : null;
    $teacherName = trim((string) ($teacher->name_locale ?? $teacher->name ?? 'Teacher'));
    $studentName = trim((string) ($student->name ?? 'bạn'));
    $messageHtml = trim((string) ($promotion->filters['message_html'] ?? ''));
    $messageHtml = $messageHtml !== '' ? $messageHtml : nl2br(e($promotion->message ?? ''));
    $ctaEnabled  = !empty($promotion->filters['cta_enabled']);
    $ctaLabel    = trim((string) ($promotion->filters['cta_label'] ?? 'Xem chi tiết'));
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
