<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $certificate->course_name_snapshot }} - Certificate</title>
    <style>
        :root {
            color-scheme: light;
            --certificate-ink: #0f172a;
            --certificate-muted: #475569;
            --certificate-line: #d7e2f0;
            --certificate-accent: #2563eb;
            --certificate-accent-2: #0ea5e9;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #eef4fb;
            color: var(--certificate-ink);
            font-family: "Segoe UI", Arial, sans-serif;
            padding: 24px;
        }

        .certificate-toolbar {
            max-width: 1080px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .certificate-toolbar__group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .certificate-toolbar a,
        .certificate-toolbar button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 18px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: var(--certificate-ink);
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        .certificate-toolbar button {
            background: linear-gradient(135deg, var(--certificate-accent) 0%, var(--certificate-accent-2) 100%);
            border-color: transparent;
            color: #fff;
        }

        .certificate-page {
            max-width: 1080px;
            margin: 0 auto;
            background: #fff;
            border: 12px solid #e2ecf7;
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.14);
            overflow: hidden;
        }

        .certificate-page__inner {
            position: relative;
            padding: 56px 64px;
            border: 2px solid var(--certificate-line);
            border-radius: 18px;
            margin: 18px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, 0.10), transparent 28%),
                radial-gradient(circle at bottom left, rgba(14, 165, 233, 0.08), transparent 24%),
                #fff;
        }

        .certificate-kicker {
            display: inline-flex;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.10);
            color: var(--certificate-accent);
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-size: 12px;
        }

        .certificate-title {
            margin: 20px 0 10px;
            font-size: 52px;
            line-height: 1.05;
        }

        .certificate-subtitle,
        .certificate-meta,
        .certificate-signatures small {
            color: var(--certificate-muted);
        }

        .certificate-student {
            margin: 26px 0 18px;
            font-size: 40px;
            font-weight: 800;
            color: #111827;
        }

        .certificate-course {
            margin: 0 0 30px;
            font-size: 26px;
            font-weight: 700;
            color: #1d4ed8;
        }

        .certificate-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin: 28px 0 34px;
        }

        .certificate-summary__item {
            padding: 18px 20px;
            border-radius: 18px;
            background: #f8fbff;
            border: 1px solid var(--certificate-line);
        }

        .certificate-summary__item span {
            display: block;
            margin-bottom: 6px;
            color: var(--certificate-muted);
            font-size: 14px;
        }

        .certificate-summary__item strong {
            font-size: 22px;
        }

        .certificate-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 34px;
        }

        .certificate-signatures {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 28px;
            margin-top: 34px;
        }

        .certificate-signature {
            padding-top: 18px;
            border-top: 1px solid var(--certificate-line);
        }

        .certificate-signature strong {
            display: block;
            font-size: 18px;
            margin-bottom: 6px;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .certificate-toolbar {
                display: none !important;
            }

            .certificate-page {
                max-width: none;
                box-shadow: none;
                border-radius: 0;
                border-width: 0;
            }

            .certificate-page__inner {
                margin: 0;
                min-height: 100vh;
            }
        }

        @media (max-width: 767.98px) {
            body {
                padding: 12px;
            }

            .certificate-page__inner {
                padding: 28px 22px;
            }

            .certificate-title {
                font-size: 34px;
            }

            .certificate-student {
                font-size: 28px;
            }

            .certificate-course {
                font-size: 20px;
            }

            .certificate-summary,
            .certificate-signatures {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-toolbar">
        <div class="certificate-toolbar__group">
            <a href="{{ $backUrl ?? url()->previous() }}">{{ $backLabel ?? 'Quay lai' }}</a>
        </div>
        <div class="certificate-toolbar__group">
            <button type="button" onclick="window.print()">In / Luu PDF</button>
        </div>
    </div>

    <section class="certificate-page">
        <div class="certificate-page__inner">
            @if (!empty($certificate->revoked_at))
                <div style="margin-bottom:20px;padding:14px 16px;border-radius:16px;background:#fff7ed;border:1px solid #fdba74;color:#9a3412;font-weight:700;">
                    Chung chi nay da bi thu hoi{{ !empty($certificate->revoke_reason) ? ': ' . $certificate->revoke_reason : '.' }}
                </div>
            @endif
            <span class="certificate-kicker">Certificate of Completion</span>
            <h1 class="certificate-title">Chung Nhan Hoan Thanh</h1>
            <p class="certificate-subtitle">Chung nhan nay duoc cap cho hoc vien da hoan thanh hoac duoc giang vien xac nhan hoan thanh khoa hoc.</p>

            <div class="certificate-student">{{ $certificate->student_name_snapshot }}</div>
            <p class="certificate-course">{{ $certificate->course_name_snapshot }}</p>

            <div class="certificate-meta">
                <span>Ma chung chi: <strong>{{ $certificate->code }}</strong></span>
                <span>Ngay cap: <strong>{{ optional($certificate->issued_at)->format('d/m/Y') }}</strong></span>
            </div>

            <div class="certificate-summary">
                <div class="certificate-summary__item">
                    <span>Tien do ghi nhan</span>
                    <strong>{{ $certificate->progress_percent }}%</strong>
                </div>
                <div class="certificate-summary__item">
                    <span>Bai da hoan thanh</span>
                    <strong>{{ $certificate->completed_lessons }}/{{ $certificate->total_lessons }}</strong>
                </div>
                <div class="certificate-summary__item">
                    <span>Giang vien xac nhan</span>
                    <strong>{{ $certificate->teacher_name_snapshot }}</strong>
                </div>
            </div>

            @if (!empty($certificate->note))
                <div class="certificate-summary__item" style="margin-bottom: 28px;">
                    <span>Ghi chu tu giang vien</span>
                    <strong style="font-size: 18px; line-height: 1.5;">{{ $certificate->note }}</strong>
                </div>
            @endif

            <div class="certificate-signatures">
                <div class="certificate-signature">
                    <strong>{{ $certificate->teacher_name_snapshot }}</strong>
                    <small>Giang vien cap chung chi</small>
                </div>
                <div class="certificate-signature">
                    <strong>{{ $certificate->student_name_snapshot }}</strong>
                    <small>Hoc vien nhan chung chi</small>
                </div>
            </div>
        </div>
    </section>

    @if (!empty($autoPrint))
        <script>
            window.addEventListener('load', () => {
                window.setTimeout(() => window.print(), 250);
            });
        </script>
    @endif
</body>
</html>
