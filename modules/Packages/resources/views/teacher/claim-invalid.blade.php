@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        <div class="teacher-panel" style="
            background: radial-gradient(circle at top right, rgba(239,68,68,0.08), transparent 26%),
                        linear-gradient(180deg, rgba(17,24,39,0.94) 0%, rgba(15,23,42,0.98) 100%);
            border-radius: 26px;
            padding: 3rem 2rem;
            text-align: center;
            max-width: 560px;
            margin: 3rem auto;
        ">
            <div style="font-size:3.5rem;margin-bottom:1rem;">
                🔗
            </div>
            <h3 style="color:#f8fbff;font-weight:800;margin-bottom:0.75rem;">
                Liên kết không còn hiệu lực
            </h3>
            <p style="color:#a9bbd5;line-height:1.7;margin-bottom:2rem;">
                @if ($reason === 'expired_or_used')
                    Liên kết nhận gói này đã <strong style="color:#f87171;">hết hạn</strong> hoặc đã được sử dụng trước đó.<br>
                    Nếu bạn cần hỗ trợ, vui lòng liên hệ quản trị viên.
                @else
                    Liên kết không hợp lệ hoặc đã bị thu hồi.
                @endif
            </p>
            <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-house me-2"></i>Về trang chủ giáo viên
            </a>
        </div>
    </div>
@endsection
