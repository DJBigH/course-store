@extends('layouts.teacher')

@section('content')
    <div class="teacher-page-shell">
        <div class="teacher-panel teacher-upgrade-shell">

            <div class="teacher-upgrade-hero">
                <div>
                    <span class="teacher-upgrade-kicker">
                        <i class="fa-solid fa-gift me-2"></i>Gói đặc quyền
                    </span>
                    <h3 class="teacher-upgrade-title">Bạn nhận được quà tặng!</h3>
                    <p class="teacher-upgrade-desc mb-0">
                        Admin vừa tặng bạn gói dịch vụ đặc quyền dưới đây. Xem thông tin và bấm <strong>Nhận gói</strong> để kích hoạt.
                    </p>
                </div>
                <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-outline-secondary teacher-upgrade-back">
                    Trở về Dashboard
                </a>
            </div>

            {{-- Thời hạn nhận quà --}}
            @php
                $daysLeft = $expiresAt ? max(now()->startOfDay()->diffInDays($expiresAt->copy()->startOfDay(), false), 0) : null;
            @endphp
            @if ($expiresAt && $daysLeft !== null)
                <div class="teacher-upgrade-notice mb-4">
                    <div class="teacher-upgrade-notice__icon">
                        <i class="fa-solid fa-hourglass-half" style="font-size:0.9rem;"></i>
                    </div>
                    <div>
                        <div class="teacher-upgrade-notice__title">Nhận trước ngày {{ $expiresAt->format('d/m/Y') }}</div>
                        <p class="teacher-upgrade-notice__desc mb-0">
                            Bạn còn <strong>{{ $daysLeft }} ngày</strong> để nhận gói này. Sau thời hạn, gói tặng sẽ bị huỷ tự động.
                        </p>
                    </div>
                </div>
            @endif

            <div class="row g-4 align-items-start">
                {{-- Thông tin gói được tặng --}}
                <div class="col-lg-8">
                    <div class="teacher-upgrade-card">
                        <div class="teacher-upgrade-card__top">
                            <span class="teacher-upgrade-card__tag">
                                <i class="fa-solid fa-star me-1"></i>
                                Gói đặc quyền tặng riêng
                            </span>
                            @if ($package?->is_exclusive)
                                <span class="teacher-upgrade-card__badge">
                                    <i class="fa-solid fa-lock me-1"></i> Exclusive
                                </span>
                            @endif
                        </div>

                        <div class="teacher-upgrade-card__body">
                            <h5>{{ $package?->name_locale ?? $package?->name }}</h5>

                            @if (($package?->description_locale ?? $package?->description ?? '') !== '')
                                <p class="teacher-upgrade-card__desc">
                                    {{ $package?->description_locale ?? $package?->description }}
                                </p>
                            @endif

                            <ul class="teacher-upgrade-card__features mt-3">
                                <li>
                                    <i class="fa-solid fa-book me-2 text-info"></i>
                                    Giới hạn khoá học:
                                    <strong>
                                        {{ $package?->effective_course_limit ?: 'Không giới hạn' }}
                                    </strong>
                                </li>
                                <li>
                                    <i class="fa-solid fa-percent me-2 text-warning"></i>
                                    Hoa hồng:
                                    <strong>
                                        {{ rtrim(rtrim(number_format((float)($package?->commission_rate ?? 0), 2, '.', ''), '0'), '.') }}%
                                    </strong>
                                </li>
                                @if ($package?->can_manage_coupons)
                                    <li>
                                        <i class="fa-solid fa-ticket me-2 text-success"></i>
                                        Mã giảm giá:
                                        <strong>{{ $package?->effective_coupon_limit ?: 'Không giới hạn' }}</strong>
                                    </li>
                                @endif
                                <li>
                                    <i class="fa-solid fa-calendar me-2 text-muted"></i>
                                    Thời hạn:
                                    <strong>
                                        {{ match($package?->billing_cycle) {
                                            'monthly' => '1 tháng',
                                            'yearly'  => '1 năm',
                                            default   => 'Vĩnh viễn'
                                        } }}
                                    </strong>
                                </li>
                            </ul>

                            {{-- Ghi chú của Admin (nếu có) --}}
                            @php
                                $rawNote = $application->admin_note ?? '';
                                $adminNote = '';
                                if (preg_match('/\|note:(.+)$/', $rawNote, $m)) {
                                    $adminNote = trim($m[1]);
                                }
                            @endphp
                            @if ($adminNote)
                                <div class="mt-3 p-3 rounded" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.25);">
                                    <div class="small fw-semibold mb-1" style="color:#fbbf24;">
                                        <i class="fa-solid fa-comment-dots me-1"></i> Lời nhắn từ Admin
                                    </div>
                                    <p class="mb-0 small" style="color:#e2c97a;">{{ $adminNote }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Xác nhận nhận quà --}}
                <div class="col-lg-4">
                    <aside class="teacher-upgrade-summary">
                        <div class="teacher-upgrade-summary__label">Xác nhận nhận gói</div>
                        <div class="teacher-upgrade-summary__name">
                            {{ $package?->name_locale ?? $package?->name }}
                        </div>
                        <div class="teacher-upgrade-summary__price" style="font-size:1.5rem;">
                            <span style="color:#fbbf24;"><i class="fa-solid fa-gift me-2"></i>Miễn phí</span>
                        </div>

                        <div class="teacher-upgrade-summary__stats mt-3">
                            <div class="teacher-upgrade-summary__stat">
                                <span>Hạn nhận quà</span>
                                <strong>{{ $expiresAt?->format('d/m/Y') ?? 'Không giới hạn' }}</strong>
                            </div>
                            <div class="teacher-upgrade-summary__stat">
                                <span>Người tặng</span>
                                <strong>Quản trị viên</strong>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('teacher.dashboard.package.claim.store', $application->claim_token) }}" id="claim-form" class="mt-4">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100 fw-bold" id="claim-btn"
                                    onclick="return confirm('Bạn xác nhận muốn nhận gói {{ $package?->name_locale }}?')">
                                <i class="fa-solid fa-gift me-2"></i>
                                Nhận gói ngay
                            </button>
                        </form>

                        <form method="POST" action="{{ route('teacher.dashboard.package.claim.decline', $application->claim_token) }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100"
                                    onclick="return confirm('Bạn chắc chắn muốn từ chối gói quà tặng này? Hành động này không thể hoàn tác.')">
                                <i class="fa-solid fa-xmark me-2"></i>
                                Từ chối nhận
                            </button>
                        </form>

                        <a href="{{ route('teacher.dashboard.index') }}" class="btn btn-link text-muted w-100 mt-2">
                            Để sau
                        </a>
                    </aside>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('stylesheets')
<style>
    .teacher-upgrade-shell {
        background:
            radial-gradient(circle at top right, rgba(245, 158, 11, 0.08), transparent 26%),
            linear-gradient(180deg, rgba(17, 24, 39, 0.94) 0%, rgba(15, 23, 42, 0.98) 100%);
    }

    .teacher-upgrade-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .teacher-upgrade-kicker {
        display: inline-flex;
        align-items: center;
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background: rgba(245, 158, 11, 0.16);
        color: #fcd34d;
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .teacher-upgrade-title {
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        font-size: clamp(1.8rem, 3vw, 2.4rem);
        font-weight: 900;
        color: #f8fbff;
    }

    .teacher-upgrade-desc {
        max-width: 700px;
        color: #a9bbd5;
        font-size: 1rem;
        line-height: 1.7;
    }

    .teacher-upgrade-card,
    .teacher-upgrade-summary,
    .teacher-upgrade-notice {
        border: 1px solid rgba(96, 165, 250, 0.18);
        background: rgba(15, 23, 42, 0.72);
        border-radius: 26px;
        box-shadow: 0 22px 54px rgba(2, 6, 23, 0.2);
    }

    .teacher-upgrade-card {
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top right, rgba(245, 158, 11, 0.08), transparent 28%),
            rgba(15, 23, 42, 0.80);
        border-color: rgba(245, 158, 11, 0.22);
    }

    .teacher-upgrade-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .teacher-upgrade-card__tag,
    .teacher-upgrade-card__badge {
        display: inline-flex;
        align-items: center;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
    }

    .teacher-upgrade-card__tag {
        background: rgba(245, 158, 11, 0.16);
        color: #fcd34d;
    }

    .teacher-upgrade-card__badge {
        background: rgba(139, 92, 246, 0.16);
        color: #c4b5fd;
    }

    .teacher-upgrade-card__body h5 {
        color: #f8fbff;
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .teacher-upgrade-card__desc {
        color: #a9bbd5;
        line-height: 1.65;
    }

    .teacher-upgrade-card__features {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .teacher-upgrade-card__features li {
        color: #c5d5e8;
        font-size: 0.94rem;
        padding: 0.45rem 0;
        border-bottom: 1px solid rgba(96, 165, 250, 0.08);
    }

    .teacher-upgrade-notice {
        display: flex;
        align-items: flex-start;
        gap: 0.95rem;
        padding: 1.15rem 1.25rem;
        background:
            radial-gradient(circle at top right, rgba(245, 158, 11, 0.1), transparent 24%),
            rgba(40, 28, 5, 0.85);
        border-color: rgba(245, 158, 11, 0.3);
    }

    .teacher-upgrade-notice__icon {
        width: 2rem;
        height: 2rem;
        flex: 0 0 2rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #1f1304;
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.98), rgba(245, 158, 11, 0.96));
        box-shadow: 0 10px 24px rgba(245, 158, 11, 0.22);
    }

    .teacher-upgrade-notice__title {
        color: #fef3c7;
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 0.3rem;
    }

    .teacher-upgrade-notice__desc {
        color: #fcd34d;
        line-height: 1.65;
    }

    .teacher-upgrade-summary {
        position: sticky;
        top: 96px;
        padding: 1.4rem;
    }

    .teacher-upgrade-summary__label {
        color: #8fb5e9;
        font-size: 0.84rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
    }

    .teacher-upgrade-summary__name {
        margin-top: 0.35rem;
        color: #f8fbff;
        font-size: 1.3rem;
        font-weight: 800;
    }

    .teacher-upgrade-summary__price {
        margin-top: 0.85rem;
        line-height: 1.1;
    }

    .teacher-upgrade-summary__stats {
        display: grid;
        gap: 0.7rem;
    }

    .teacher-upgrade-summary__stat {
        padding: 0.85rem 1rem;
        border-radius: 16px;
        border: 1px solid rgba(96, 165, 250, 0.14);
        background: rgba(18, 28, 50, 0.72);
    }

    .teacher-upgrade-summary__stat span {
        display: block;
        color: #8fb5e9;
        font-size: 0.82rem;
        margin-bottom: 0.3rem;
    }

    .teacher-upgrade-summary__stat strong {
        color: #f8fbff;
        font-size: 0.97rem;
        font-weight: 700;
    }

    .teacher-upgrade-back {
        white-space: nowrap;
    }

    @media (max-width: 1199.98px) {
        .teacher-upgrade-summary {
            position: static;
        }
    }
</style>
@endsection

@section('scripts')
<script>
document.getElementById('claim-form')?.addEventListener('submit', function () {
    const btn = document.getElementById('claim-btn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang kích hoạt...';
    }
});
</script>
@endsection
