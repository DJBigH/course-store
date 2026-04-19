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
    $mailStyles = $mailStyles ?? [
        'body_text' => 'font-size:14px;line-height:1.7;color:' . $mailTheme['text'] . ';',
        'panel' => 'border:1px solid ' . $mailTheme['line'] . ';border-radius:12px;overflow:hidden;',
        'panel_fill' => 'padding:14px 16px;background:' . $mailTheme['surface'] . ';font-size:13px;line-height:1.7;color:' . $mailTheme['muted'] . ';',
    ];

    if ($status === 'approved') {
        $mailTitle = '[Hệ thống] Thông báo chấp nhận yêu cầu hủy hợp tác';
        $mailHeading = 'Yêu cầu hủy hợp tác đã được duyệt';
        $statusLabel = 'Đã chấp nhận';
        $statusColor = $mailTheme['primary_dark'];
    } else {
        $mailTitle = '[Hệ thống] Thông báo từ chối yêu cầu hủy hợp tác';
        $mailHeading = 'Yêu cầu hủy hợp tác bị từ chối';
        $statusLabel = 'Đã từ chối';
        $statusColor = $mailTheme['danger'];
    }
    $mailEyebrow = 'Thông báo từ Ban quản trị';
    $preheader = 'Kết quả xử lý yêu cầu hủy hợp tác của bạn.';
@endphp

@section('mail_content')
    <div style="{{ $mailStyles['body_text'] }}">
        Xin chào <strong>{{ $teacher->name }}</strong>,
    </div>

    <div style="height:12px;"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        @if ($status === 'approved')
            Chúng tôi rất tiếc khi phải thông báo rằng yêu cầu ngừng hợp tác của bạn đã được <strong>phê duyệt</strong>. Tài khoản của bạn đã được chuyển về cấp độ Học viên. Toàn bộ các bài giảng của bạn đã được chuyển sang trạng thái lưu trữ.
        @else
            Yêu cầu ngừng hợp tác của bạn đã bị <strong>từ chối</strong> bởi Ban quản trị. Mọi quyền lợi giảng viên của bạn vẫn được giữ nguyên.
        @endif
    </div>

    <div style="height:20px;"></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="{{ $mailStyles['panel'] }}">
        <tr>
            <td style="padding:18px 16px;background:{{ $mailTheme['surface_alt'] }};">
                <div style="font-size:12px;color:{{ $mailTheme['muted'] }};text-transform:uppercase;font-weight:700;margin-bottom:8px;">Trạng thái xử lý:</div>
                <div style="font-size:20px;font-weight:800;color:{{ $statusColor }};">
                    {{ $statusLabel }}
                </div>
                
                @if ($adminNote)
                    <div style="height:16px;border-top:1px solid {{ $mailTheme['line'] }};margin-top:16px;padding-top:16px;"></div>
                    <div style="font-size:12px;color:{{ $mailTheme['muted'] }};text-transform:uppercase;font-weight:700;margin-bottom:8px;">Ghi chú từ Admin:</div>
                    <div style="font-size:14px;line-height:1.6;color:{{ $mailTheme['text'] }};border-left:4px solid {{ $statusColor }};padding-left:12px;font-style:italic;">
                        {{ $adminNote }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <div style="height:24px;"></div>

    <div style="{{ $mailStyles['body_text'] }}">
        @if ($status === 'approved')
            Chúng tôi chân thành cảm ơn sự đóng góp của bạn cho cộng đồng trong suốt thời gian qua. Chúc bạn gặt hái được nhiều thành công trong những dự án sắp tới.
        @else
            Nếu bạn có bất kỳ thắc mắc nào về quyết định này, vui lòng liên hệ với bộ phận hỗ trợ thông qua tính năng "Góp ý - Báo cáo" trong kênh giảng viên.
        @endif
    </div>

    <div style="height:30px;"></div>

    <div style="font-size:13px;color:{{ $mailTheme['muted'] }};">
        Trân trọng,<br>
        <strong>Ban quản trị BigK Udemy</strong>
    </div>
@endsection
