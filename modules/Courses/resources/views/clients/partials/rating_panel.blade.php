@php
    $avgRating   = round((float)($course->ratings_avg_rating ?? 0), 1);
    $ratingCount = (int)($course->ratings_count ?? 0);
    $myRating    = $viewerCourseRating !== null ? (float)$viewerCourseRating : null;

    $breakdown = [];
    for ($i = 5; $i >= 1; $i--) {
        $cnt = (int)($ratingBreakdown[$i] ?? 0) + (int)($ratingBreakdown[$i + 0.5] ?? 0);
        $breakdown[$i] = ['cnt' => $cnt, 'pct' => $ratingCount > 0 ? ($cnt / $ratingCount) * 100 : 0];
    }

    $renderStars = function(float $r): string {
        $h = '';
        for ($s = 1; $s <= 5; $s++) {
            if ($r >= $s)            $h .= '<i class="fa-solid fa-star"></i>';
            elseif ($r >= $s - 0.5) $h .= '<i class="fa-solid fa-star-half-stroke"></i>';
            else                     $h .= '<i class="fa-regular fa-star"></i>';
        }
        return $h;
    };
@endphp

<div class="rp-root" id="course-rating-panel-root">

    {{-- ── OVERVIEW ──────────────────────────────── --}}
    <div class="rp-card rp-overview mb-4">
        <div class="rp-overview__score">
            <div class="rp-big-num">{{ number_format($avgRating, 1) }}</div>
            <div class="rp-static-stars">{!! $renderStars($avgRating) !!}</div>
            <div class="rp-vote-count">{{ $ratingCount }} {{ __('courses::clients/common.rating_votes') }}</div>
        </div>
        <div class="rp-overview__bars">
            @foreach($breakdown as $star => $data)
            <div class="rp-bar-row">
                <span class="rp-bar-label">{{ $star }}<i class="fa-solid fa-star ms-1"></i></span>
                <div class="rp-bar-track">
                    <div class="rp-bar-fill" style="width:{{ $data['pct'] }}%"></div>
                </div>
                <span class="rp-bar-pct">{{ round($data['pct']) }}%</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── RATING FORM ───────────────────────────── --}}
    @if(auth('students')->check() && $canRate && $myRating === null)
    <div class="rp-card rp-submit-card">
        <div class="rp-submit-header">
            <i class="fa-solid fa-pen-to-square text-warning"></i>
            <strong>{{ __('courses::clients/common.rating_title') }}</strong>
            <span class="rp-once-note">— {{ __('courses::clients/common.rating_once_note') }}</span>
        </div>

        <form data-course-rating-form novalidate
              action="{{ route('courses.rating.store', ['locale'=>app()->getLocale(),'slug'=>$course->slug_locale]) }}"
              method="POST">
            @csrf
            <input type="hidden" name="rating" value="" data-rating-input>

            {{-- 
                Star Picker: 5 .rp-star divs, each with 2 invisible hotspot buttons (left=half, right=full).
                JS directly changes fa-regular/fa-solid class on the <i> icon — no overlay, no pixel math.
            --}}
            <div class="rp-picker-wrap">
                <div class="rp-picker" data-rating-track id="rp-star-picker">
                    @for($i = 1; $i <= 5; $i++)
                    <div class="rp-star" data-star="{{ $i }}">
                        <i class="fa-regular fa-star rp-star-icon" data-star-icon="{{ $i }}"></i>
                        <button type="button" class="rp-half rp-half--left"
                                data-rating-option data-value="{{ $i - 0.5 }}"
                                title="{{ $i - 0.5 }} sao"></button>
                        <button type="button" class="rp-half rp-half--right"
                                data-rating-option data-value="{{ $i }}"
                                title="{{ $i }} sao"></button>
                    </div>
                    @endfor
                </div>
                <div class="rp-picker-label" data-rating-current-label id="rp-label">
                    {{ __('courses::clients/common.rating_hint_label') }}
                </div>
            </div>

            <div class="rp-error d-none" id="rp-error-box" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                <span id="rp-error-text"></span>
            </div>

            <div class="rp-submit-footer">
                <small class="text-muted">{{ __('courses::clients/common.rating_hint') }}</small>
                <button type="submit" class="btn btn-warning fw-bold px-4 rounded-pill">
                    <i class="fa-solid fa-paper-plane me-2"></i>{{ __('courses::clients/common.rating_submit') }}
                </button>
            </div>
        </form>
    </div>

    @elseif(auth('students')->check() && $myRating !== null)
    <div class="rp-card rp-done-card">
        <div class="rp-done-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div>
            <div class="rp-done-title">
                {{ __('courses::clients/common.rating_submitted_once', ['rating' => rtrim(rtrim(number_format($myRating,1,'.',''),'0'),'.')]) }}
            </div>
            <div class="rp-static-stars rp-done-stars">{!! $renderStars($myRating) !!}
                <span class="rp-done-val">{{ number_format($myRating,1) }}/5</span>
            </div>
            <p class="rp-done-note"><i class="fa-solid fa-lock me-1"></i>{{ __('courses::clients/common.rating_locked_note') }}</p>
        </div>
    </div>

    @elseif(auth('students')->check())
    <div class="rp-card rp-notice rp-notice--warn">
        <div class="rp-notice-icon"><i class="fa-solid fa-lock"></i></div>
        <div>
            <div class="fw-semibold">{{ __('courses::clients/common.rating_need_purchase') }}</div>
            <small class="text-muted">{{ __('courses::clients/common.rating_purchase_desc') }}</small>
        </div>
    </div>

    @else
    <div class="rp-card rp-notice rp-notice--info">
        <div class="rp-notice-icon"><i class="fa-solid fa-user-lock"></i></div>
        <div>
            <div class="fw-semibold">{{ __('courses::clients/common.rating_login_required') }}</div>
            <a href="{{ route('clients-login',['locale'=>app()->getLocale()]) }}"
               class="btn btn-sm btn-dark rounded-pill px-4 mt-2">
                <i class="fa-solid fa-right-to-bracket me-2"></i>{{ __('students::auth.login_title') }}
            </a>
        </div>
    </div>
    @endif

</div>

{{-- Self-contained star picker script — does NOT depend on detail.blade.php load order --}}
@if(auth('students')->check() && ($canRate ?? false) && ($viewerCourseRating ?? null) === null)
<script>
(function() {
    function initRpPicker() {
        var form = document.querySelector('[data-course-rating-form]');
        if (!form) return;

        var input  = form.querySelector('[data-rating-input]');
        var label  = form.querySelector('[data-rating-current-label]');
        var spots  = form.querySelectorAll('[data-rating-option]');
        var icons  = form.querySelectorAll('[data-star-icon]');
        var errBox = document.getElementById('rp-error-box');
        var errTxt = document.getElementById('rp-error-text');

        var FILLED = '#f59e0b';
        var EMPTY  = '#cbd5e1';

        var LABELS = {
            0.5:'😕 Rất tệ', 1:'😕 Rất tệ',
            1.5:'😐 Tệ',     2:'😐 Tệ',
            2.5:'🙂 Bình thường', 3:'🙂 Bình thường',
            3.5:'😊 Tốt',    4:'😊 Tốt',
            4.5:'🤩 Tuyệt vời', 5:'🤩 Xuất sắc!'
        };
        var chosen = null;

        function isDark() {
            return document.documentElement.getAttribute('data-theme') === 'dark';
        }

        // Paint stars using inline style — wins over any CSS
        function paint(val) {
            var emptyColor = isDark() ? '#475569' : '#cbd5e1';
            icons.forEach(function(icon, idx) {
                var n = idx + 1;
                if (val >= n) {
                    icon.className = 'rp-star-icon fa-solid fa-star';
                    icon.style.color = FILLED;
                } else if (val >= n - 0.5) {
                    icon.className = 'rp-star-icon fa-solid fa-star-half-stroke';
                    icon.style.color = FILLED;
                } else {
                    icon.className = 'rp-star-icon fa-regular fa-star';
                    icon.style.color = emptyColor;
                }
            });
        }

        function setLabel(val, locked) {
            if (!label) return;
            if (val > 0) {
                label.textContent = locked
                    ? ('✅ Đã chọn ' + val + ' sao – ' + (LABELS[val] || val + ' sao'))
                    : (val + ' sao – ' + (LABELS[val] || ''));
                label.classList.add('is-active');
            } else {
                label.textContent = '{{ __('courses::clients/common.rating_hint_label') }}';
                label.classList.remove('is-active');
            }
        }

        function showErr(msg) {
            if (errBox && errTxt) { errTxt.textContent = msg; errBox.classList.remove('d-none'); }
        }
        function hideErr() { if (errBox) errBox.classList.add('d-none'); }

        // Bind events
        spots.forEach(function(s) {
            s.addEventListener('mouseenter', function() {
                var v = parseFloat(s.dataset.value);
                paint(v); setLabel(v, false);
            });
            s.addEventListener('click', function() {
                chosen = parseFloat(s.dataset.value);
                if (input) input.value = chosen;
                paint(chosen); setLabel(chosen, true); hideErr();
            });
        });

        var track = form.querySelector('[data-rating-track]');
        if (track) {
            track.addEventListener('mouseleave', function() {
                paint(chosen || 0);
                if (chosen) setLabel(chosen, true); else setLabel(0, false);
            });
        }

        // Validate before submit
        form.addEventListener('submit', function(e) {
            var v = input ? parseFloat(input.value) : 0;
            if (!v || v < 0.5 || v > 5) {
                e.stopImmediatePropagation(); e.preventDefault();
                showErr('{{ __('courses::clients/common.rating_invalid') }}');
                var p = form.querySelector('.rp-picker');
                if (p) {
                    p.classList.add('rp-shake');
                    setTimeout(function() { p.classList.remove('rp-shake'); }, 500);
                }
            }
        }, true);
    }

    // Run after DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRpPicker);
    } else {
        initRpPicker();
    }
})();
</script>
@endif


<style>
/* ── CSS Variables (light + dark) ──────────────── */
.rp-root {
    --rp-bg:           #ffffff;
    --rp-border:       #e2e8f0;
    --rp-text:         #1e293b;
    --rp-muted:        #64748b;
    --rp-bar-bg:       #f1f5f9;
    --rp-star-empty:   #cbd5e1;
    --rp-star-filled:  #f59e0b;
    --rp-submit-bg:    #fffbeb;
    --rp-submit-bdr:   #fde68a;
    --rp-done-bg:      #f0fdf4;
    --rp-done-bdr:     #bbf7d0;
    --rp-warn-bg:      #fffbeb;
    --rp-warn-bdr:     #fde68a;
    --rp-info-bg:      #eff6ff;
    --rp-info-bdr:     #bfdbfe;
}
html[data-theme="dark"] .rp-root {
    --rp-bg:           #1e293b;
    --rp-border:       #334155;
    --rp-text:         #f1f5f9;
    --rp-muted:        #94a3b8;
    --rp-bar-bg:       #334155;
    --rp-star-empty:   #475569;
    --rp-submit-bg:    #1c1802;
    --rp-submit-bdr:   #78350f;
    --rp-done-bg:      #052e16;
    --rp-done-bdr:     #166534;
    --rp-warn-bg:      #1c1802;
    --rp-warn-bdr:     #78350f;
    --rp-info-bg:      #082f49;
    --rp-info-bdr:     #0e7490;
}

/* ── Base Card ─────────────────────────────────── */
.rp-card {
    background: var(--rp-bg);
    border: 1.5px solid var(--rp-border);
    border-radius: 16px;
    padding: 24px;
    color: var(--rp-text);
    transition: background .2s, border-color .2s;
}

/* ── Overview ──────────────────────────────────── */
.rp-overview          { display: flex; gap: 24px; align-items: center; flex-wrap: wrap; }
.rp-overview__score   { text-align: center; min-width: 120px; }
.rp-overview__bars    { flex: 1; min-width: 200px; display: grid; gap: 8px; }
.rp-big-num           { font-size: 3rem; font-weight: 900; line-height: 1; color: var(--rp-text); }
.rp-static-stars      { color: var(--rp-star-filled); font-size: 1.1rem; margin: 6px 0; }
.rp-vote-count        { font-size: .8rem; color: var(--rp-muted); font-weight: 600; }
.rp-bar-row           { display: flex; align-items: center; gap: 8px; font-size: .82rem; }
.rp-bar-label         { min-width: 46px; color: var(--rp-text); font-weight: 600; font-size: .82rem; }
.rp-bar-label .fa-star { color: var(--rp-star-filled); font-size: .7rem; }
.rp-bar-track         { flex: 1; height: 8px; background: var(--rp-bar-bg); border-radius: 999px; overflow: hidden; }
.rp-bar-fill          { height: 100%; background: linear-gradient(90deg,#f59e0b,#fbbf24); border-radius: 999px; transition: width .6s; }
.rp-bar-pct           { min-width: 34px; text-align: right; color: var(--rp-muted); font-size: .8rem; }

/* ── Submit Card ───────────────────────────────── */
.rp-submit-card       { background: var(--rp-submit-bg); border-color: var(--rp-submit-bdr); }
.rp-submit-header     { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; font-size: 1rem; color: var(--rp-text); }
.rp-once-note         { font-size: .8rem; font-weight: 400; color: var(--rp-muted); }

/* ── Star Picker ───────────────────────────────── */
.rp-picker-wrap { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }

.rp-picker {
    display: inline-flex;
    gap: 6px;          /* gap between stars is fine — no overlay needed */
    font-size: 2.5rem;
    line-height: 1;
    cursor: pointer;
    user-select: none;
}

.rp-star {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

/* THE ACTUAL STAR ICON — color set directly via JS style, CSS provides defaults only */
.rp-star-icon {
    color: #cbd5e1;   /* light mode default (empty) */
    display: block;
    pointer-events: none;
    transition: color .1s ease, transform .1s ease;
}
html[data-theme="dark"] .rp-star-icon {
    color: #475569;
}

/* Invisible half-star click zones */
.rp-half {
    position: absolute;
    top: 0;
    height: 100%;
    width: 50%;
    background: transparent;
    border: none;
    padding: 0;
    margin: 0;
    cursor: pointer;
    z-index: 10;
    outline: none;
}
.rp-half--left  { left: 0; }
.rp-half--right { right: 0; }

.rp-picker-label {
    font-size: .9rem;
    font-weight: 600;
    color: var(--rp-muted);
    min-height: 1.4em;
    transition: color .15s;
}
.rp-picker-label.is-active { color: #d97706; }
html[data-theme="dark"] .rp-picker-label.is-active { color: #fbbf24; }

/* ── Error ─────────────────────────────────────── */
.rp-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: .875rem;
    margin-bottom: 16px;
}
html[data-theme="dark"] .rp-error { background: rgba(220,38,38,.12); border-color: #991b1b; color: #fca5a5; }

/* ── Submit Footer ─────────────────────────────── */
.rp-submit-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }

/* ── Done Card ─────────────────────────────────── */
.rp-done-card  { background: var(--rp-done-bg); border-color: var(--rp-done-bdr); display: flex; align-items: flex-start; gap: 20px; }
.rp-done-icon  { width: 52px; height: 52px; background: #22c55e; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(34,197,94,.3); }
.rp-done-title { font-weight: 700; color: #15803d; margin-bottom: 6px; }
.rp-done-stars { display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
.rp-done-val   { font-weight: 700; color: #b45309; font-size: .85rem; }
.rp-done-note  { color: #166534; font-size: .82rem; margin: 0; }
html[data-theme="dark"] .rp-done-title { color: #86efac; }
html[data-theme="dark"] .rp-done-val   { color: #fcd34d; }
html[data-theme="dark"] .rp-done-note  { color: #86efac; }

/* ── Notice Cards ──────────────────────────────── */
.rp-notice          { display: flex; align-items: center; gap: 18px; }
.rp-notice--warn    { background: var(--rp-warn-bg); border-color: var(--rp-warn-bdr); }
.rp-notice--info    { background: var(--rp-info-bg); border-color: var(--rp-info-bdr); }
.rp-notice-icon     { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.rp-notice--warn .rp-notice-icon { background: rgba(251,191,36,.2); color: #d97706; }
.rp-notice--info .rp-notice-icon { background: rgba(59,130,246,.2); color: #2563eb; }
html[data-theme="dark"] .rp-notice--warn .rp-notice-icon { background: rgba(251,191,36,.15); color: #fbbf24; }
html[data-theme="dark"] .rp-notice--info .rp-notice-icon { background: rgba(59,130,246,.15); color: #93c5fd; }

/* ── Hover scale effect on stars ───────────────── */
.rp-star:hover .rp-star-icon { transform: scale(1.15); }

/* ── Shake ─────────────────────────────────────── */
@keyframes rp-shake {
    0%,100%{ transform:translateX(0) }
    20%    { transform:translateX(-6px) }
    40%    { transform:translateX(6px) }
    60%    { transform:translateX(-4px) }
    80%    { transform:translateX(4px) }
}
.rp-shake { animation: rp-shake .45s ease; }

@media (max-width: 576px) {
    .rp-picker { font-size: 2rem; }
    .rp-overview { flex-direction: column; }
    .rp-overview__score { border-bottom: 1px solid var(--rp-border); padding-bottom: 12px; width: 100%; }
}
</style>
