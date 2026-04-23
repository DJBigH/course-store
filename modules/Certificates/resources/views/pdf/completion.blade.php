<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $certificate->course_name_snapshot }} - Certificate</title>
    <style>
        :root {
            color-scheme: light;
            --cert-gold: #c5a059;
            --cert-gold-light: #e2c99d;
            --cert-gold-dark: #8e6d2f;
            --cert-navy: #0f172a;
            --cert-blue: #1d4ed8;
            --cert-muted: #64748b;
            --cert-line: #e2e8f0;
            --cert-bg: #f8fafc;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #e2e8f0;
            color: var(--cert-navy);
            font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
            padding: 40px 20px;
        }

        .certificate-toolbar {
            max-width: 1000px;
            margin: 0 auto 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .certificate-toolbar a,
        .certificate-toolbar button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 44px;
            padding: 0 24px;
            border-radius: 8px;
            font-family: sans-serif;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-back {
            background: #fff;
            border: 1px solid var(--cert-line);
            color: var(--cert-navy);
        }

        .btn-print {
            background: var(--cert-navy);
            border: none;
            color: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
        }

        .certificate-outer {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.15);
            position: relative;
        }

        /* Decorative Border */
        .certificate-border {
            border: 2px solid var(--cert-gold);
            padding: 10px;
            position: relative;
        }

        .certificate-border::before {
            content: '';
            position: absolute;
            top: 5px; left: 5px; right: 5px; bottom: 5px;
            border: 1px solid var(--cert-gold-light);
            pointer-events: none;
        }

        .certificate-inner {
            background: #fff;
            border: 20px solid transparent;
            border-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0H100V100H0V0ZM5 5V95H95V5H5Z' fill='%23c5a059'/%3E%3C/svg%3E") 25 stretch;
            padding: 60px;
            position: relative;
            background-image: 
                radial-gradient(circle at center, rgba(197, 160, 89, 0.03) 0%, transparent 70%),
                url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M10 10L90 90M90 10L10 90' stroke='%23c5a059' stroke-width='0.1' opacity='0.2'/%3E%3C/svg%3E");
            text-align: center;
        }

        .cert-header {
            margin-bottom: 40px;
        }

        .cert-badge {
            width: 80px;
            margin: 0 auto 20px;
            color: var(--cert-gold);
        }

        .cert-category {
            font-family: sans-serif;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-size: 13px;
            font-weight: 700;
            color: var(--cert-gold-dark);
            margin-bottom: 15px;
        }

        .cert-title {
            font-size: 54px;
            margin: 0;
            color: var(--cert-navy);
            font-weight: 400;
            letter-spacing: -1px;
        }

        .cert-subtitle {
            font-style: italic;
            color: var(--cert-muted);
            margin: 20px 0;
            font-size: 18px;
        }

        .cert-recipient {
            font-size: 48px;
            font-weight: 700;
            color: var(--cert-navy);
            margin: 30px 0;
            padding-bottom: 10px;
            display: inline-block;
            border-bottom: 2px solid var(--cert-gold-light);
        }

        .cert-course-area {
            margin: 20px 0 40px;
        }

        .cert-course-label {
            display: block;
            font-size: 16px;
            color: var(--cert-muted);
            margin-bottom: 8px;
        }

        .cert-course-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--cert-blue);
        }

        .cert-grid {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 40px 0;
            padding: 20px;
            background: rgba(197, 160, 89, 0.05);
            border-radius: 8px;
        }

        .cert-grid-item {
            text-align: left;
        }

        .cert-grid-item span {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            color: var(--cert-muted);
            margin-bottom: 4px;
        }

        .cert-grid-item strong {
            font-size: 16px;
            color: var(--cert-navy);
        }

        .cert-note {
            max-width: 600px;
            margin: 0 auto 50px;
            font-style: italic;
            color: var(--cert-muted);
            line-height: 1.6;
            font-size: 15px;
        }

        .cert-footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            margin-top: 60px;
        }

        .cert-qr-top {
            position: absolute;
            top: 40px;
            right: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .cert-signature {
            border-top: 1px solid var(--cert-line);
            padding-top: 15px;
        }

        .cert-signature strong {
            display: block;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .cert-signature small {
            color: var(--cert-muted);
            font-size: 13px;
        }

        .cert-stamp {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .cert-qr-container {
            background: #fff;
            padding: 6px;
            border: 1px solid var(--cert-line);
            border-radius: 8px;
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .cert-qr-container img {
            width: 100%;
            height: 100%;
        }

        .cert-qr-hint {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--cert-gold-dark);
            font-weight: 700;
            margin-top: 5px;
        }

        @media print {
            body { padding: 0; background: #fff; }
            .certificate-toolbar { display: none; }
            .certificate-outer { box-shadow: none; padding: 0; }
            .certificate-border { border-color: #000; }
            .certificate-inner { -webkit-print-color-adjust: exact; }
        }

        @media (max-width: 768px) {
            .certificate-inner { padding: 40px 20px; }
            .cert-title { font-size: 36px; }
            .cert-recipient { font-size: 32px; }
            .cert-footer { grid-template-columns: 1fr; gap: 40px; text-align: center; }
            .cert-stamp { order: -1; }
            .cert-grid { flex-direction: column; gap: 15px; align-items: center; }
        }
    </style>
</head>
<body>
    <div class="certificate-toolbar">
        <a href="{{ $backUrl ?? url()->previous() }}" class="btn-back">← {{ $backLabel ?? __('certificates::teacher/messages.certificates.pdf.back_cta') }}</a>
        <button type="button" onclick="window.print()" class="btn-print">{{ __('certificates::teacher/messages.certificates.pdf.print_cta') }}</button>
    </div>

    <div class="certificate-outer">
        <div class="certificate-border">
            <div class="certificate-inner">
                @if (!empty($certificate->revoked_at))
                    <div style="background:#fef2f2; border:1px solid #fee2e2; color:#b91c1c; padding:15px; border-radius:8px; margin-bottom:30px; font-weight:700;">
                        {{ __('certificates::teacher/messages.certificates.pdf.revoked_warning') }}: {{ $certificate->revoke_reason ?? __('certificates::teacher/messages.certificates.pdf.no_reason') }}
                    </div>
                @endif

                <div class="cert-header">
                    @if($certificate->teacher && $certificate->teacher->packageHasFeature('can_verify_certificates'))
                        @php
                            $verifyUrl = route('certificates.verify', [
                                'locale' => app()->getLocale(),
                                'code' => $certificate->code
                            ]);
                            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verifyUrl);
                        @endphp
                        <div class="cert-qr-top">
                            <div class="cert-qr-container">
                                <img src="{{ $qrUrl }}" alt="QR Verification">
                            </div>
                            <span class="cert-qr-hint">{{ __('certificates::teacher/messages.certificates.pdf.verify_label') }}</span>
                        </div>
                    @endif

                    <svg class="cert-badge" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 15L15 12M12 15L9 12M12 15V21M12 3L12.9247 5.02545L15.118 5.60155L13.6708 7.37455L13.8164 9.59845L12 8.791L10.1836 9.59845L10.3292 7.37455L8.88197 5.60155L11.0753 5.02545L12 3Z" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="10" />
                    </svg>
                    <div class="cert-category">{{ __('certificates::teacher/messages.certificates.pdf.category') }}</div>
                    <h1 class="cert-title">{{ __('certificates::teacher/messages.certificates.pdf.title') }}</h1>
                </div>

                <div class="cert-body">
                    <p class="cert-subtitle">{{ __('certificates::teacher/messages.certificates.pdf.subtitle') }}</p>
                    <div class="cert-recipient">{{ $certificate->student_name_snapshot }}</div>
                    
                    <div class="cert-course-area">
                        <span class="cert-course-label">{{ __('certificates::teacher/messages.certificates.pdf.course_label_intro') }}</span>
                        <div class="cert-course-name">{{ $certificate->course_name_snapshot }}</div>
                    </div>

                    <div class="cert-grid">
                        <div class="cert-grid-item">
                            <span>{{ __('certificates::teacher/messages.certificates.pdf.code_label') }}</span>
                            <strong>{{ $certificate->code }}</strong>
                        </div>
                        <div class="cert-grid-item">
                            <span>{{ __('certificates::teacher/messages.certificates.pdf.date_label') }}</span>
                            <strong>{{ optional($certificate->issued_at)->format('d/m/Y') }}</strong>
                        </div>
                        <div class="cert-grid-item">
                            <span>{{ __('certificates::teacher/messages.certificates.pdf.progress_label') }}</span>
                            <strong>{{ $certificate->progress_percent }}% ({{ $certificate->completed_lessons }}/{{ $certificate->total_lessons }} {{ __('certificates::teacher/messages.certificates.pdf.unit_lesson') }})</strong>
                        </div>
                    </div>

                    @if (!empty($certificate->note))
                        <div class="cert-note">
                            "{{ $certificate->note }}"
                        </div>
                    @endif
                </div>

                <div class="cert-footer">
                    <div class="cert-signature">
                        <strong>{{ $certificate->teacher_name_snapshot }}</strong>
                        <small>{{ __('certificates::teacher/messages.certificates.pdf.teacher_role') }}</small>
                    </div>

                    <div class="cert-signature">
                        <strong>{{ $certificate->student_name_snapshot }}</strong>
                        <small>{{ __('certificates::teacher/messages.certificates.pdf.student_role') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (!empty($autoPrint))
        <script>
            window.addEventListener('load', () => {
                window.setTimeout(() => window.print(), 250);
            });
        </script>
    @endif
</body>
</html>
